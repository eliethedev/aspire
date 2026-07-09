<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->foreignId('form_template_id')->nullable()->constrained('form_templates')->nullOnDelete();
        });

        Schema::table('pre_observation_plannings', function (Blueprint $table) {
            $table->json('form_responses')->nullable()->after('observation_tool');
        });

        Schema::table('pre_conferences', function (Blueprint $table) {
            $table->json('form_responses')->nullable()->after('instructional_materials');
        });

        Schema::table('post_conferences', function (Blueprint $table) {
            $table->json('form_responses')->nullable()->after('supervisor_notes');
        });
    }

    public function down(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->dropForeign(['form_template_id']);
            $table->dropColumn('form_template_id');
        });

        Schema::table('pre_observation_plannings', function (Blueprint $table) {
            $table->dropColumn('form_responses');
        });

        Schema::table('pre_conferences', function (Blueprint $table) {
            $table->dropColumn('form_responses');
        });

        Schema::table('post_conferences', function (Blueprint $table) {
            $table->dropColumn('form_responses');
        });
    }
};
