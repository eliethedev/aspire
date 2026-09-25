<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Offline clinical-supervision workflow (IT adviser mandate).
 *
 * State machine on `observations.status`:
 *   scheduled ─┐ (legacy rows: scheduled + confirmation_status=pending
 *   pending_teacher_confirmation ─┘  behave identically via
 *        │  Observation::isPendingTeacherConfirmation())
 *        ▼  teacher accepts + uploads DLL (confirm-by-teacher)
 *   confirmed_ready_for_download
 *        │  supervisor prepares + downloads the offline package
 *        ▼
 *   downloaded_offline ──► (tablet encodes with zero connectivity)
 *        │  push sync of the pre-scheduled observation
 *        ▼
 *   completed_offline ──► post-observation analytics ──► synced ──► finalized
 *
 * `scheduled` is intentionally kept as the creation status: dozens of
 * existing counters filter on it literally, and scheduled +
 * confirmation_status=pending IS the pending state (see the helper).
 */
return new class extends Migration
{
    /**
     * Full status list: every legacy value plus the offline workflow states.
     * MySQL enums must be redeclared wholesale on ALTER.
     */
    public const STATUSES = [
        'pending',
        'scheduled',
        'pending_teacher_confirmation',
        'confirmed_ready_for_download',
        'downloaded_offline',
        'completed_offline',
        'in_progress',
        'cot_completed',
        'completed',
        'cancelled',
        'synced',
        'finalized',
    ];

    public const LEGACY_STATUSES = [
        'pending',
        'scheduled',
        'in_progress',
        'cot_completed',
        'completed',
        'cancelled',
    ];

    public function up(): void
    {
        // MySQL only: extend the enum. Other drivers (e.g. SQLite in tests)
        // treat the column as plain text and need no alteration.
        if (DB::getDriverName() === 'mysql') {
            $list = implode("','", self::STATUSES);
            DB::statement("ALTER TABLE `observations` MODIFY `status` ENUM('{$list}') NOT NULL DEFAULT 'pending'");
        }

        Schema::table('observations', function (Blueprint $table) {
            $table->timestamp('teacher_confirmed_at')->nullable()->after('confirmed_at');
            $table->string('lesson_plan_path')->nullable()->after('teacher_confirmed_at');
            $table->text('lesson_plan_summary')->nullable()->after('lesson_plan_path');
            $table->json('pre_observation_ai_prompts')->nullable()->after('lesson_plan_summary');
            $table->timestamp('offline_downloaded_at')->nullable()->after('pre_observation_ai_prompts');
        });
    }

    public function down(): void
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->dropColumn([
                'teacher_confirmed_at',
                'lesson_plan_path',
                'lesson_plan_summary',
                'pre_observation_ai_prompts',
                'offline_downloaded_at',
            ]);
        });

        if (DB::getDriverName() === 'mysql') {
            $list = implode("','", self::LEGACY_STATUSES);
            DB::statement("ALTER TABLE `observations` MODIFY `status` ENUM('{$list}') NOT NULL DEFAULT 'pending'");
        }
    }
};
