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
            $table->text('supervisor_notes')->nullable()->after('suggested_focus');
            $table->string('observation_tool')->nullable()->after('supervisor_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pre_observation_plannings', function (Blueprint $table) {
            $table->dropColumn(['supervisor_notes', 'observation_tool']);
        });
    }
};
