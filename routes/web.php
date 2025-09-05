<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\MappingController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\GenerateController;
use App\Models\FormField;
Route::get('/api/form-fields/{form}', function (\App\Models\Form $form) {
    return FormField::where('form_id', $form->id)->get(['name', 'label', 'type', 'required']);
});

Route::post('/map-columns', [ImportController::class, 'validateMappings']);
Route::post('/auto-match-suggestions', [ImportController::class, 'getAutoMatchSuggestions']);

Route::get('/', fn() => Inertia::render('UploadPage'))->name('upload');

Route::get('/wizard', fn() => Inertia::render('ImportWizard'))->name('wizard');
Route::get('/output', fn() => Inertia::render('OutputGenerator'))->name('output');

// API-style endpoints (no auth)
Route::prefix('api')->group(function () {
    Route::post('/upload/json', [UploadController::class, 'uploadJson']);
    Route::post('/upload/excel', [UploadController::class, 'uploadExcel']);

    Route::get('/forms', [FormController::class, 'index']);
    Route::post('/map-columns', [MappingController::class, 'validateMapping']);
    Route::post('/import', [ImportController::class, 'import']);

    Route::get('/generate/{type}', [GenerateController::class, 'generate']);
});
