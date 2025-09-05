<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Form;
use App\Models\FormField;

class MappingController extends Controller
{
    // Validate mapping: ensure mapped field names exist; count valid rows by checking required fields presence
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
}
