<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_templates', function (Blueprint $table) {
            $table->string('observation_type')->nullable()->after('school_year')
                ->comment('teacher_observation, school_head_observation, or null for all');
        });
    }

    public function down(): void
    {
        Schema::table('form_templates', function (Blueprint $table) {
            $table->dropColumn('observation_type');
        });
    }
};
