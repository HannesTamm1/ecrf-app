<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imported_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->cascadeOnDelete();
            // Traceability
            $table->jsonb('original_columns')->nullable(); // list of original column names
            $table->jsonb('mapping_used')->nullable();     // mapping dict ExcelCol -> field.name
            $table->jsonb('raw_row')->nullable();          // original row values
            $table->jsonb('mapped_row')->nullable();       // normalized row values by field
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imported_records');
    }
};
