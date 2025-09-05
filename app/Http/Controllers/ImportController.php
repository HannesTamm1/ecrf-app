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
        $errors = [];
        
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

            try {
                ImportedRecord::create([
                    'form_id' => $form->id,
                    'original_columns' => $header,
                    'mapping_used' => $validated['mappings'],
                    'raw_row' => $raw,
                    'mapped_row' => $mapped,
                ]);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row {$row}: " . $e->getMessage();
            }
        }

        return response()->json([
            'status' => 'success',
            'imported' => $imported,
            'warnings' => $warnings,
            'errors' => $errors,
            'message' => "Successfully imported {$imported} records" . 
                        (count($warnings) ? " with " . count($warnings) . " warnings" : "") .
                        (count($errors) ? " and " . count($errors) . " errors" : "")
        ]);
    }

    /**
     * Validate column mappings and provide feedback
     */
    public function validateMappings(Request $request)
    {
        $validated = $request->validate([
            'form_id' => 'required|integer|exists:forms,id',
            'mappings' => 'required|array',
        ]);

        $form = Form::with('fields')->findOrFail($validated['form_id']);
        $formFields = $form->fields->keyBy('name');
        $requiredFields = $form->fields->where('required', true)->pluck('name')->toArray();

        $feedback = [
            'valid' => true,
            'errors' => [],
            'warnings' => [],
            'mapped_required' => [],
            'unmapped_required' => [],
        ];

        // Check if all mapped fields exist
        foreach ($validated['mappings'] as $excelCol => $fieldName) {
            if (!$formFields->has($fieldName)) {
                $feedback['errors'][] = "Field '{$fieldName}' does not exist in the selected form";
                $feedback['valid'] = false;
            }
        }

        // Check required field coverage
        $mappedFields = array_values($validated['mappings']);
        foreach ($requiredFields as $requiredField) {
            if (in_array($requiredField, $mappedFields)) {
                $feedback['mapped_required'][] = $requiredField;
            } else {
                $feedback['unmapped_required'][] = $requiredField;
                $feedback['warnings'][] = "Required field '{$requiredField}' is not mapped";
            }
        }

        return response()->json($feedback);
    }

    /**
     * Get auto-matching suggestions using fuzzy string matching
     */
    public function getAutoMatchSuggestions(Request $request)
    {
        $validated = $request->validate([
            'form_id' => 'required|integer|exists:forms,id',
            'excel_columns' => 'required|array',
        ]);

        $form = Form::with('fields')->findOrFail($validated['form_id']);
        $suggestions = [];

        foreach ($validated['excel_columns'] as $column) {
            $columnSuggestions = [];
            
            foreach ($form->fields as $field) {
                // Calculate similarity scores
                $nameScore = $this->calculateSimilarity($column, $field->name);
                $labelScore = $field->label ? $this->calculateSimilarity($column, $field->label) : 0;
                
                $maxScore = max($nameScore, $labelScore);
                
                if ($maxScore > 0.6) { // Only include if similarity > 60%
                    $columnSuggestions[] = [
                        'field_name' => $field->name,
                        'field_label' => $field->label,
                        'score' => $maxScore,
                        'confidence' => round($maxScore * 100)
                    ];
                }
            }

            // Sort by score descending and take top 3
            usort($columnSuggestions, fn($a, $b) => $b['score'] <=> $a['score']);
            $suggestions[$column] = array_slice($columnSuggestions, 0, 3);
        }

        return response()->json(['suggestions' => $suggestions]);
    }

    /**
     * Calculate string similarity using Levenshtein distance
     */
    private function calculateSimilarity($str1, $str2)
    {
        $str1 = strtolower(trim($str1));
        $str2 = strtolower(trim($str2));
        
        if ($str1 === $str2) return 1.0;
        if (empty($str1) || empty($str2)) return 0.0;
        
        $maxLen = max(strlen($str1), strlen($str2));
        $distance = levenshtein($str1, $str2);
        
        return 1 - ($distance / $maxLen);
    }
}