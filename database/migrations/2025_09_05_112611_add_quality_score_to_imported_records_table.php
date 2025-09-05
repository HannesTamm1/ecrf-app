<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('imported_records', function (Blueprint $table) {
            $table->decimal('quality_score', 5, 2)->nullable()->comment('Data quality score from 0-100');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('imported_records', function (Blueprint $table) {
            $table->dropColumn('quality_score');
        });
    }
};
