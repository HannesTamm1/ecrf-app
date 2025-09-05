<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\MappingController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\GenerateController;
use App\Http\Controllers\AuthController;
use App\Models\FormField;

Route::get('/api/form-fields/{form}', function (\App\Models\Form $form) {
    return FormField::where('form_id', $form->id)->get(['name', 'label', 'type', 'required']);
});

Route::post('/map-columns', [ImportController::class, 'validateMappings']);
Route::post('/auto-match-suggestions', [MappingController::class, 'getAutoMatchSuggestions']);

// Authentication routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

// Redirect root to main app if authenticated, otherwise to login
Route::get('/', function () {
    return auth()->check() ? Inertia::render('UploadPage') : redirect('/login');
})->name('home');

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/wizard', fn() => Inertia::render('ImportWizard'))->name('wizard');
    Route::get('/output', fn() => Inertia::render('OutputGenerator'))->name('output');
    
    // API-style endpoints
    Route::prefix('api')->group(function () {
        Route::post('/upload/json', [UploadController::class, 'uploadJson']);
        Route::post('/upload/excel', [UploadController::class, 'uploadExcel']);
        
        Route::get('/forms', [FormController::class, 'index']);
        Route::post('/map-columns', [MappingController::class, 'validateMapping']);
        Route::post('/import', [ImportController::class, 'import']);
        
        Route::get('/generate/{type}', [GenerateController::class, 'generate']);
    });
});
