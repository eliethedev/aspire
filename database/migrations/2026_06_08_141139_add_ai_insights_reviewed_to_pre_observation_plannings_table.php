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
        Schema::table('pre_observation_plannings', function (Blueprint $table) {
            $table->boolean('ai_insights_reviewed')->default(false)->after('ai_insights');
        });
    }

    public function down(): void
    {
        Schema::table('pre_observation_plannings', function (Blueprint $table) {
            $table->dropColumn('ai_insights_reviewed');
        });
    }
};
