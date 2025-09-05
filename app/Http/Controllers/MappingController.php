<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Form, FormField};

class MappingController extends Controller
{
    public function validateMapping(Request $request)
    {
        $data = $request->validate([
            'form_id' => 'required|integer|exists:forms,id',
            'mappings' => 'required|array', // ExcelCol => field.name
        ]);

        $form = Form::findOrFail($data['form_id']);
        $fieldNames = FormField::where('form_id', $form->id)->pluck('name')->toArray();

        $invalid = [];
        foreach ($data['mappings'] as $col => $field) {
            // Skip null or empty field mappings
            if ($field !== null && $field !== '' && !in_array($field, $fieldNames)) {
                $invalid[] = $field;
            }
        }

        return response()->json([
            'status' => empty($invalid) ? 'validated' : 'warnings',
            'valid_rows' => 0,
            'warnings' => empty($invalid) ? [] : ['Unknown field(s): ' . implode(', ', $invalid)]
        ]);
    }

    /**
     * Generate fuzzy matching suggestions for column mapping
     */
    public function getAutoMatchSuggestions(Request $request)
    {
        $request->validate([
            'form_id' => 'required|integer|exists:forms,id',
            'columns' => 'required|array',
        ]);

        $form = Form::with('fields')->findOrFail($request->form_id);
        $formFields = $form->fields;
        $excelColumns = $request->columns;

        $suggestions = [];

        foreach ($excelColumns as $column) {
            $bestMatch = $this->findBestMatch($column, $formFields);
            if ($bestMatch) {
                $suggestions[$column] = $bestMatch;
            }
        }

        return response()->json(['suggestions' => $suggestions]);
    }

    /**
     * Find the best matching form field for an Excel column using fuzzy matching
     */
    private function findBestMatch($column, $formFields)
    {
        $bestMatch = null;
        $bestScore = 0;
        $threshold = 0.6; // Minimum similarity threshold

        foreach ($formFields as $field) {
            // Check against field name
            $nameScore = $this->calculateSimilarity($column, $field->name);
            
            // Check against field label
            $labelScore = $field->label ? $this->calculateSimilarity($column, $field->label) : 0;
            
            // Use the higher score
            $score = max($nameScore, $labelScore);
            
            if ($score > $bestScore && $score >= $threshold) {
                $bestScore = $score;
                $bestMatch = [
                    'field_name' => $field->name,
                    'field_label' => $field->label,
                    'score' => $score,
                    'match_type' => $nameScore > $labelScore ? 'name' : 'label'
                ];
            }
        }

        return $bestMatch;
    }

    /**
     * Calculate string similarity using multiple algorithms
     */
    private function calculateSimilarity($str1, $str2)
    {
        if (empty($str1) || empty($str2)) {
            return 0;
        }

        // Normalize strings
        $str1 = strtolower(trim($str1));
        $str2 = strtolower(trim($str2));

        // Exact match
        if ($str1 === $str2) {
            return 1.0;
        }

        // Remove common separators and normalize
        $str1_normalized = preg_replace('/[_\-\s]+/', '', $str1);
        $str2_normalized = preg_replace('/[_\-\s]+/', '', $str2);

        if ($str1_normalized === $str2_normalized) {
            return 0.95;
        }

        // Contains check
        if (strpos($str1, $str2) !== false || strpos($str2, $str1) !== false) {
            return 0.9;
        }

        // Levenshtein distance based similarity
        $maxLen = max(strlen($str1), strlen($str2));
        if ($maxLen === 0) {
            return 1.0;
        }

        $distance = levenshtein($str1, $str2);
        $levenshteinSimilarity = 1 - ($distance / $maxLen);

        // Jaro-Winkler similarity (simplified version)
        $jaroSimilarity = $this->jaroSimilarity($str1, $str2);

        // Common words similarity
        $wordsSimilarity = $this->wordsimilarity($str1, $str2);

        // Return the highest similarity score
        return max($levenshteinSimilarity, $jaroSimilarity, $wordsSimilarity);
    }

    /**
     * Calculate Jaro similarity (simplified implementation)
     */
    private function jaroSimilarity($str1, $str2)
    {
        $len1 = strlen($str1);
        $len2 = strlen($str2);

        if ($len1 === 0 && $len2 === 0) {
            return 1.0;
        }

        if ($len1 === 0 || $len2 === 0) {
            return 0.0;
        }

        $matchWindow = max($len1, $len2) / 2 - 1;
        $matchWindow = max(0, $matchWindow);

        $str1Matches = array_fill(0, $len1, false);
        $str2Matches = array_fill(0, $len2, false);

        $matches = 0;
        $transpositions = 0;

        // Find matches
        for ($i = 0; $i < $len1; $i++) {
            $start = max(0, $i - $matchWindow);
            $end = min($i + $matchWindow + 1, $len2);

            for ($j = $start; $j < $end; $j++) {
                if ($str2Matches[$j] || $str1[$i] !== $str2[$j]) {
                    continue;
                }

                $str1Matches[$i] = true;
                $str2Matches[$j] = true;
                $matches++;
                break;
            }
        }

        if ($matches === 0) {
            return 0.0;
        }

        // Find transpositions
        $k = 0;
        for ($i = 0; $i < $len1; $i++) {
            if (!$str1Matches[$i]) {
                continue;
            }

            while (!$str2Matches[$k]) {
                $k++;
            }

            if ($str1[$i] !== $str2[$k]) {
                $transpositions++;
            }

            $k++;
        }

        return ($matches / $len1 + $matches / $len2 + ($matches - $transpositions / 2) / $matches) / 3;
    }

    /**
     * Calculate similarity based on common words
     */
    private function wordsimilarity($str1, $str2)
    {
        $words1 = preg_split('/[\s_\-]+/', strtolower($str1));
        $words2 = preg_split('/[\s_\-]+/', strtolower($str2));

        $words1 = array_filter($words1);
        $words2 = array_filter($words2);

        if (empty($words1) || empty($words2)) {
            return 0;
        }

        $intersection = array_intersect($words1, $words2);
        $union = array_unique(array_merge($words1, $words2));

        return count($intersection) / count($union);
    }
}
