<?php

namespace App\Http\Controllers;

use App\Models\Form;
use Illuminate\Http\Request;

class FormController extends Controller
{
    public function index(Request $request)
    {
        $q = Form::query()->withCount('fields')->orderBy('title');
        if ($request->has('project_id'))
            $q->where('project_id', $request->integer('project_id'));
        $forms = $q->get(['id', 'project_id', 'external_id', 'title']);
        return response()->json($forms);
    }
}
