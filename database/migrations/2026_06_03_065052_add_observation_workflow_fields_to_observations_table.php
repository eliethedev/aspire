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
        Schema::table('observations', function (Blueprint $table) {
            // Update status enum to include new statuses
            $table->enum('status', ['scheduled', 'in_progress', 'cot_completed', 'post_conference_pending', 'completed', 'pending'])->default('pending')->change();

            // Add new workflow fields
            $table->string('school_year')->nullable()->after('notes');
            $table->integer('quarter')->nullable()->after('school_year');
            $table->integer('observation_number')->nullable()->after('quarter');
            $table->string('subject')->nullable()->after('observation_number');
            $table->string('grade_level')->nullable()->after('subject');
            $table->enum('observation_mode', ['in_person', 'virtual', 'hybrid'])->default('in_person')->after('grade_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->dropColumn(['school_year', 'quarter', 'observation_number', 'subject', 'grade_level', 'observation_mode']);
            // Note: Reverting enum changes requires manual intervention or a separate migration
        });
    }
};
