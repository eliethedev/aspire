<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A school year may host multiple COT instruments scoped by ratee role
     * and career stage (e.g. generic teacher, Master Teacher I-II, school
     * head), so school_year is no longer globally unique.
     */
    public function up(): void
    {
        Schema::table('cot_indicator_versions', function (Blueprint $table) {
            $table->dropUnique('cot_indicator_versions_school_year_unique');
        });
    }

    public function down(): void
    {
        Schema::table('cot_indicator_versions', function (Blueprint $table) {
            $table->unique('school_year', 'cot_indicator_versions_school_year_unique');
        });
    }
};
