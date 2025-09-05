<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Project;
use App\Models\Form;
use App\Models\FormField;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create sample users
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@ecrf.com',
            'password' => Hash::make('password'),
        ]);

        $researcher = User::factory()->create([
            'name' => 'Research Coordinator',
            'email' => 'researcher@ecrf.com',
            'password' => Hash::make('password'),
        ]);

        $user = User::factory()->create([
            'name' => 'Data Entry User',
            'email' => 'user@ecrf.com',
            'password' => Hash::make('password'),
        ]);

        // Create sample project with forms and fields
        $project = Project::create([
            'name' => 'Clinical Trial Demo Project',
            'version' => 'v1.0',
            'file_hash' => hash('sha256', 'demo-project-' . now()),
            'raw_json' => [
                'title' => 'Clinical Trial Demo Project',
                'description' => 'Sample project for demonstrating ECRF functionality',
                'created_at' => now()->toISOString(),
            ]
        ]);

        // Create Demographics form
        $demographicsForm = Form::create([
            'project_id' => $project->id,
            'external_id' => 1,
            'title' => 'Demographics',
            'meta' => ['description' => 'Patient demographic information']
        ]);

        // Add fields to Demographics form
        $demographicsFields = [
            ['name' => 'patient_id', 'label' => 'Patient ID', 'type' => 'text', 'required' => true],
            ['name' => 'age', 'label' => 'Age', 'type' => 'number', 'required' => true],
            ['name' => 'gender', 'label' => 'Gender', 'type' => 'select', 'required' => true, 'options' => ['Male', 'Female', 'Other']],
            ['name' => 'birth_date', 'label' => 'Date of Birth', 'type' => 'date', 'required' => true],
            ['name' => 'weight', 'label' => 'Weight (kg)', 'type' => 'number', 'required' => false],
            ['name' => 'height', 'label' => 'Height (cm)', 'type' => 'number', 'required' => false],
            ['name' => 'ethnicity', 'label' => 'Ethnicity', 'type' => 'select', 'required' => false, 'options' => ['Caucasian', 'African American', 'Hispanic', 'Asian', 'Other']],
        ];

        foreach ($demographicsFields as $field) {
            FormField::create([
                'form_id' => $demographicsForm->id,
                'name' => $field['name'],
                'label' => $field['label'],
                'type' => $field['type'],
                'required' => $field['required'],
                'options' => $field['options'] ?? [],
                'logic' => [],
                'meta' => []
            ]);
        }

        // Create Medical History form
        $medicalForm = Form::create([
            'project_id' => $project->id,
            'external_id' => 2,
            'title' => 'Medical History',
            'meta' => ['description' => 'Patient medical history and conditions']
        ]);

        // Add fields to Medical History form
        $medicalFields = [
            ['name' => 'diabetes', 'label' => 'History of Diabetes', 'type' => 'radio', 'required' => true, 'options' => ['Yes', 'No']],
            ['name' => 'hypertension', 'label' => 'History of Hypertension', 'type' => 'radio', 'required' => true, 'options' => ['Yes', 'No']],
            ['name' => 'medications', 'label' => 'Current Medications', 'type' => 'textarea', 'required' => false],
            ['name' => 'allergies', 'label' => 'Known Allergies', 'type' => 'textarea', 'required' => false],
            ['name' => 'previous_surgeries', 'label' => 'Previous Surgeries', 'type' => 'textarea', 'required' => false],
        ];

        foreach ($medicalFields as $field) {
            FormField::create([
                'form_id' => $medicalForm->id,
                'name' => $field['name'],
                'label' => $field['label'],
                'type' => $field['type'],
                'required' => $field['required'],
                'options' => $field['options'] ?? [],
                'logic' => [],
                'meta' => []
            ]);
        }

        // Create Laboratory Results form
        $labForm = Form::create([
            'project_id' => $project->id,
            'external_id' => 3,
            'title' => 'Laboratory Results',
            'meta' => ['description' => 'Laboratory test results and values']
        ]);

        // Add fields to Laboratory Results form
        $labFields = [
            ['name' => 'hemoglobin', 'label' => 'Hemoglobin (g/dL)', 'type' => 'number', 'required' => true],
            ['name' => 'white_blood_cell', 'label' => 'White Blood Cell Count', 'type' => 'number', 'required' => true],
            ['name' => 'platelet_count', 'label' => 'Platelet Count', 'type' => 'number', 'required' => true],
            ['name' => 'glucose', 'label' => 'Glucose (mg/dL)', 'type' => 'number', 'required' => false],
            ['name' => 'creatinine', 'label' => 'Creatinine (mg/dL)', 'type' => 'number', 'required' => false],
            ['name' => 'test_date', 'label' => 'Test Date', 'type' => 'date', 'required' => true],
        ];

        foreach ($labFields as $field) {
            FormField::create([
                'form_id' => $labForm->id,
                'name' => $field['name'],
                'label' => $field['label'],
                'type' => $field['type'],
                'required' => $field['required'],
                'options' => $field['options'] ?? [],
                'logic' => [],
                'meta' => []
            ]);
        }
    }
}
