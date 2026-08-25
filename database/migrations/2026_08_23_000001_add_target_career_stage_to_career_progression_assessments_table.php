<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('career_progression_assessments', function (Blueprint $table) {
            $table->string('target_career_stage')->nullable()->after('status');

            $table->index('target_career_stage');
        });
    }

    public function down(): void
    {
        Schema::table('career_progression_assessments', function (Blueprint $table) {
            $table->dropIndex(['target_career_stage']);
            $table->dropColumn('target_career_stage');
        });
    }
};
