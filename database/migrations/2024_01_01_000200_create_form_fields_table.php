<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->cascadeOnDelete();
            $table->string('name');        // machine name (e.g., eta)
            $table->string('label')->nullable(); // human label (e.g., Età)
            $table->string('type')->nullable();
            $table->boolean('required')->default(false);
            $table->jsonb('options')->nullable();   // item values/labels if any
            // conditional logic/edit checks kept for exports
            $table->jsonb('logic')->nullable();     // visible_if, enable_if, edit_check, etc.
            $table->jsonb('meta')->nullable();      // randomization, to_validate, endpoints, etc.
            $table->timestamps();
            $table->unique(['form_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_fields');
    }
};
