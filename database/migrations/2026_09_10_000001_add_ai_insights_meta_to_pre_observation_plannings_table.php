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
            $table->json('ai_insights_meta')->nullable()->after('ai_insights_reviewed');
        });
    }

    public function down(): void
    {
        Schema::table('pre_observation_plannings', function (Blueprint $table) {
            $table->dropColumn('ai_insights_meta');
        });
    }
};
