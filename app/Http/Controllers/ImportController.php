<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Form, FormField, ImportedRecord};
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Str;

class ImportController extends Controller
{
    public function import(Request $request)
    {
        // Parse mappings if it's a JSON string
        if (is_string($request->input('mappings'))) {
            $request->merge(['mappings' => json_decode($request->input('mappings'), true)]);
        }

        $validated = $request->validate([
            'form_id' => 'required|integer|exists:forms,id',
            'file' => 'required|file',
            'mappings' => 'required|array', // ExcelCol => field.name
        ]);

        $form = Form::with('fields')->findOrFail($validated['form_id']);
        $requiredFields = $form->fields->where('required', true)->pluck('name')->toArray();

        $file = $validated['file'];
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getSheet(0);

        // Read header
        $highestColumn = $sheet->getHighestColumn();
        $highestRow = $sheet->getHighestRow();
        $header = $sheet->rangeToArray('A1:' . $highestColumn . '1', null, true, true, true)[1] ?? [];
        $header = array_map('trim', array_values($header));

        // Build column index: HeaderText => column letter index
        $headerMap = [];
        foreach ($header as $idx => $name) {
            $headerMap[$name] = $idx; // zero-based in our array rows below
        }

        $imported = 0;
        $warnings = [];
        $qualityScores = [];
        // Iterate data rows
        for ($row = 2; $row <= $highestRow; $row++) {
            $rowArr = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, null, true, true, false)[0] ?? [];
            // Raw row by header
            $raw = [];
            foreach ($header as $i => $h)
                $raw[$h] = $rowArr[$i] ?? null;

            // Apply mappings -> mapped_row
            $mapped = [];
            foreach ($validated['mappings'] as $excelCol => $fieldName) {
                $mapped[$fieldName] = $raw[$excelCol] ?? null;
            }

            // Check required fields
            $missingRequired = array_filter($requiredFields, fn($r) => !isset($mapped[$r]) || $mapped[$r] === null || $mapped[$r] === '');
            if (count($missingRequired)) {
                $warnings[] = "Row {$row} missing required: " . implode(', ', $missingRequired);
                // Still store for traceability, but you could choose to skip
            }

            // Calculate quality score (0-100)
            $qualityScore = $this->calculateQualityScore($mapped, $requiredFields, $validated['mappings'], $form->fields);
            $qualityScores[] = $qualityScore;

            ImportedRecord::create([
                'form_id' => $form->id,
                'original_columns' => $header,
                'mapping_used' => $validated['mappings'],
                'raw_row' => $raw,
                'mapped_row' => $mapped,
                'quality_score' => $qualityScore,
            ]);
            $imported++;
        }

        $averageQuality = count($qualityScores) > 0 ? round(array_sum($qualityScores) / count($qualityScores), 2) : 0;

        return response()->json([
            'status' => 'success',
            'imported' => $imported,
            'warnings' => $warnings,
            'average_quality_score' => $averageQuality,
        ]);
    }

    /**
     * Calculate data quality score (0-100) based on:
     * - Required field completeness (40% weight)
     * - Data validation success (40% weight) 
     * - Mapping accuracy (20% weight)
     */
    private function calculateQualityScore(array $mapped, array $requiredFields, array $mappings, $formFields): float
    {
        $score = 0;

        // 1. Required field completeness (40 points)
        $totalRequired = count($requiredFields);
        if ($totalRequired > 0) {
            $completedRequired = 0;
            foreach ($requiredFields as $required) {
                if (isset($mapped[$required]) && $mapped[$required] !== null && $mapped[$required] !== '') {
                    $completedRequired++;
                }
            }
            $score += ($completedRequired / $totalRequired) * 40;
        } else {
            $score += 40; // No required fields = full points
        }

        // 2. Data validation success (40 points)
        $totalFields = count($mapped);
        if ($totalFields > 0) {
            $validFields = 0;
            foreach ($mapped as $fieldName => $value) {
                if ($value !== null && $value !== '') {
                    // Basic validation - check if field has content
                    $validFields++;
                }
            }
            $score += ($validFields / $totalFields) * 40;
        }

        // 3. Mapping accuracy (20 points)
        $totalMappings = count($mappings);
        if ($totalMappings > 0) {
            $validMappings = 0;
            foreach ($mappings as $excelCol => $fieldName) {
                if ($fieldName !== null && $fieldName !== '') {
                    $validMappings++;
                }
            }
            $score += ($validMappings / $totalMappings) * 20;
        } else {
            $score += 20; // No mappings = full points
        }

        return round($score, 2);
    }
}
