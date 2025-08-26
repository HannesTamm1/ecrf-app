<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Project, Form, FormField, ImportedRecord};
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GenerateController extends Controller
{
    public function generate(string $type, Request $request)
    {
        $projectId = $request->integer('project_id');
        $project = Project::with('forms.fields')->findOrFail($projectId);

        $ss = new Spreadsheet();

        switch ($type) {
            case 'scheduler':
                // Placeholder since scheduler assignments are not detailed in DB schema.
                // We’ll still create one sheet and include fields with scheduler/event columns blank.
                $sheet = $ss->getActiveSheet();
                $sheet->setTitle(substr('scheduler_overview', 0, 31));
                $sheet->fromArray([
                    ['Scheduler', 'Event', 'Form', 'Field', 'Type', 'Required', 'Label', 'Randomization', 'To_validate', 'Item values', 'Edit_check', 'Export_variable', 'Visible_if', 'Enable_if']
                ], null, 'A1');
                $row = 2;
                foreach ($project->forms as $form) {
                    foreach ($form->fields as $f) {
                        $sheet->fromArray([
                            [
                                '',
                                '',
                                $form->title,
                                $f->name,
                                $f->type,
                                $f->required ? 'Y' : 'N',
                                $f->label,
                                data_get($f->meta, 'randomization'),
                                data_get($f->meta, 'to_validate'),
                                json_encode($f->options),
                                data_get($f->logic, 'edit_check'),
                                data_get($f->meta, 'export_variable'),
                                data_get($f->logic, 'visible_if'),
                                data_get($f->logic, 'enable_if'),
                            ]
                        ], null, 'A' . $row++);
                    }
                }
                break;

            case 'form':
                $sheet = $ss->getActiveSheet();
                $sheet->setTitle(substr('form_overview', 0, 31));
                $sheet->fromArray([
                    [
                        'Form',
                        'Field',
                        'Type',
                        'Required',
                        'Label',
                        'Randomization',
                        'To_validate',
                        'Primary_endpoint',
                        'Secondary_endpoint',
                        'Is_enable_chart',
                        'Item label',
                        'Item value',
                        'Selected',
                        'Edit_check',
                        'Visible_if',
                        'Enable_if'
                    ]
                ], null, 'A1');
                $row = 2;
                foreach ($project->forms as $form) {
                    foreach ($form->fields as $f) {
                        $options = $f->options ?: [];
                        if (!count($options)) {
                            $sheet->fromArray([
                                [
                                    $form->title,
                                    $f->name,
                                    $f->type,
                                    $f->required ? 'Y' : 'N',
                                    $f->label,
                                    data_get($f->meta, 'randomization'),
                                    data_get($f->meta, 'to_validate'),
                                    data_get($f->meta, 'primary_endpoint'),
                                    data_get($f->meta, 'secondary_endpoint'),
                                    data_get($f->meta, 'is_enable_chart') ? 'Y' : 'N',
                                    '',
                                    '',
                                    '',
                                    data_get($f->logic, 'edit_check'),
                                    data_get($f->logic, 'visible_if'),
                                    data_get($f->logic, 'enable_if'),
                                ]
                            ], null, 'A' . $row++);
                        } else {
                            foreach ($options as $opt) {
                                $sheet->fromArray([
                                    [
                                        $form->title,
                                        $f->name,
                                        $f->type,
                                        $f->required ? 'Y' : 'N',
                                        $f->label,
                                        data_get($f->meta, 'randomization'),
                                        data_get($f->meta, 'to_validate'),
                                        data_get($f->meta, 'primary_endpoint'),
                                        data_get($f->meta, 'secondary_endpoint'),
                                        data_get($f->meta, 'is_enable_chart') ? 'Y' : 'N',
                                        data_get($opt, 'label') ?? data_get($opt, 'text'),
                                        data_get($opt, 'value'),
                                        data_get($opt, 'selected') ? 'Y' : 'N',
                                        data_get($f->logic, 'edit_check'),
                                        data_get($f->logic, 'visible_if'),
                                        data_get($f->logic, 'enable_if'),
                                    ]
                                ], null, 'A' . $row++);
                            }
                        }
                    }
                }
                break;

            case 'conditional':
                $sheet = $ss->getActiveSheet();
                $sheet->setTitle(substr('conditional_fields', 0, 31));
                $sheet->fromArray([
                    [
                        'Scheduler',
                        'Event',
                        'Form',
                        'Field',
                        'Type',
                        'Label',
                        'Required',
                        'Item values',
                        'Edit_check',
                        'Visible_if',
                        'Enable_if'
                    ]
                ], null, 'A1');
                $row = 2;
                foreach ($project->forms as $form) {
                    foreach ($form->fields as $f) {
                        if (data_get($f->logic, 'edit_check') || data_get($f->logic, 'visible_if') || data_get($f->logic, 'enable_if')) {
                            $sheet->fromArray([
                                [
                                    '',
                                    '',
                                    $form->title,
                                    $f->name,
                                    $f->type,
                                    $f->label,
                                    $f->required ? 'Y' : 'N',
                                    json_encode($f->options),
                                    data_get($f->logic, 'edit_check'),
                                    data_get($f->logic, 'visible_if'),
                                    data_get($f->logic, 'enable_if'),
                                ]
                            ], null, 'A' . $row++);
                        }
                    }
                }
                break;

            default:
                return response()->json(['status' => 'error', 'message' => 'Unknown export type'], 400);
        }

        // File naming per URS
        $name = $project->name ?: 'project';
        $version = $project->version ?: 'v0';
        $date = now()->format('Y-m-d');

        $filename = match ($type) {
            'scheduler' => "{$name}_structure_{$version}_{$date}.xlsx",
            'form' => "{$name}_forms_Structure_{$version}_{$date}.xlsx",
            'conditional' => "{$name}_conditional_fields_{$version}_{$date}.xlsx",
        };

        $writer = new Xlsx($ss);
        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', "attachment; filename=\"{$filename}\"");

        return $response;
    }
}
