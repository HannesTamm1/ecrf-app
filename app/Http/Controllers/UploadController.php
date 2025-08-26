<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{Project, Form, FormField};
use PhpOffice\PhpSpreadsheet\IOFactory;

class UploadController extends Controller
{
    // Parse project JSON and persist Project + Forms + Fields
    public function uploadJson(Request $request)
    {
        $file = $request->file('file');
        if (!$file || $file->getClientOriginalExtension() !== 'json') {
            return response()->json(['status' => 'error', 'message' => 'JSON file required'], 422);
        }
        $data = json_decode($file->get(), true);
        if (!$data)
            return response()->json(['status' => 'error', 'message' => 'Invalid JSON'], 422);

        // Expected keys per URS:
        // project.name, projectVersions.v/title, forms, formsVersions, followups.dump
        $projectName = data_get($data, 'project.name', 'Unnamed Project');
        $version = data_get($data, 'projectVersions.0.v') ?? data_get($data, 'projectVersions.v');

        DB::transaction(function () use ($data, $projectName, $version, &$project) {
            $project = Project::create([
                'name' => $projectName,
                'version' => (string) ($version ?? 'v0'),
                'raw_json' => $data,
            ]);

            // Build a map: form id -> title + fields (flatten as needed)
            $formsArr = data_get($data, 'forms', []);
            foreach ($formsArr as $frm) {
                $form = Form::create([
                    'project_id' => $project->id,
                    'external_id' => data_get($frm, 'id'),
                    'title' => data_get($frm, 'title', 'Untitled Form'),
                    'meta' => ['source' => 'json'],
                ]);

                // Extract fields; adjust to your JSON shape
                $fields = data_get($frm, 'fields', []);
                foreach ($fields as $f) {
                    FormField::create([
                        'form_id' => $form->id,
                        'name' => data_get($f, 'name'),
                        'label' => data_get($f, 'label'),
                        'type' => data_get($f, 'type'),
                        'required' => (bool) data_get($f, 'required', false),
                        'options' => data_get($f, 'items', []), // item labels/values
                        'logic' => [
                            'visible_if' => data_get($f, 'visible_if'),
                            'enable_if' => data_get($f, 'enable_if'),
                            'edit_check' => data_get($f, 'edit_check'),
                        ],
                        'meta' => [
                            'randomization' => data_get($f, 'randomization'),
                            'to_validate' => data_get($f, 'to_validate'),
                            'primary_endpoint' => data_get($f, 'primary_endpoint'),
                            'secondary_endpoint' => data_get($f, 'secondary_endpoint'),
                            'is_enable_chart' => data_get($f, 'is_enable_chart'),
                            'export_variable' => data_get($f, 'export_variable'),
                        ],
                    ]);
                }
            }
        });

        return response()->json([
            'status' => 'ok',
            'project_name' => $projectName,
            'forms' => Form::where('project_id', $project->id)->count(),
            'version' => (string) ($version ?? 'v0'),
            'project_id' => $project->id,
        ]);
    }

    // Read column headers from uploaded Excel
    public function uploadExcel(Request $request)
    {
        $file = $request->file('file');
        if (!$file)
            return response()->json(['status' => 'error', 'message' => 'Excel file required'], 422);
        $ext = strtolower($file->getClientOriginalExtension());
        if (!in_array($ext, ['xlsx', 'xls', 'csv'])) {
            return response()->json(['status' => 'error', 'message' => 'Only xlsx/xls/csv'], 422);
        }

        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getSheet(0);
        $firstRow = $sheet->rangeToArray('A1:' . $sheet->getHighestColumn() . '1', null, true, true, true)[1] ?? [];
        $columns = array_values(array_filter(array_map('trim', array_values($firstRow))));

        return response()->json(['status' => 'ok', 'columns' => $columns]);
    }
}
