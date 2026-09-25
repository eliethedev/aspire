<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Supervisor lesson-plan review gate: preparing (and downloading) the
 * offline package requires the observer to confirm they reviewed the
 * teacher's DLL. Works on MySQL and SQLite (tests) alike.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->timestamp('lesson_plan_reviewed_at')->nullable()->after('offline_downloaded_at');
            $table->foreignId('lesson_plan_reviewed_by')->nullable()->after('lesson_plan_reviewed_at')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lesson_plan_reviewed_by');
            $table->dropColumn('lesson_plan_reviewed_at');
        });
    }
};
