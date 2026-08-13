<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Version context columns so a version is uniquely identified by its
 * framework + career track + ratee position + instrument, not by
 * school_year + career_stage alone (Career Stage II exists under both
 * PPST and PPSSH).
 *
 * Existing rows are backfilled so the current Teacher I-III COT versions
 * resolve to PPST / Classroom Teaching / Teacher I-III / COT. Their
 * career_stage is intentionally left untouched (null = generic, still used
 * as the app-wide fallback), preserving current observation behaviour.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cot_indicator_versions', function (Blueprint $table) {
            $table->string('framework', 20)->nullable()->after('ratee_role');
            $table->string('career_track', 60)->nullable()->after('framework');
            $table->string('ratee_position', 60)->nullable()->after('career_track');
            $table->string('instrument', 50)->nullable()->after('ratee_position');

            $table->index(
                ['school_year', 'framework', 'career_track', 'ratee_position', 'instrument'],
                'cot_indicator_versions_context_idx'
            );
        });

        $this->backfillContext();
    }

    public function down(): void
    {
        Schema::table('cot_indicator_versions', function (Blueprint $table) {
            $table->dropIndex('cot_indicator_versions_context_idx');
            $table->dropColumn(['framework', 'career_track', 'ratee_position', 'instrument']);
        });
    }

    private function backfillContext(): void
    {
        $teacherStages = [
            'teacher_i_iii' => 'teacher_i_iii',
            'teacher_iv_vii' => 'teacher_iv_vii',
            'master_teacher_i_ii' => 'master_teacher_i_ii',
            'master_teacher_iii_v' => 'master_teacher_iii_v',
        ];

        $schoolHeadStages = [
            'career_stage_i' => 'aspiring_school_head',
            'career_stage_ii' => 'school_principal_i_ii',
            'career_stage_iii' => 'school_principal_iii',
            'career_stage_iv' => 'school_principal_iv',
        ];

        $versions = DB::table('cot_indicator_versions')
            ->whereNull('framework')
            ->select(['id', 'ratee_role', 'career_stage'])
            ->get();

        foreach ($versions as $version) {
            if ($version->ratee_role === 'school_head') {
                DB::table('cot_indicator_versions')->where('id', $version->id)->update([
                    'framework' => 'ppssh',
                    'career_track' => 'school_administration',
                    'ratee_position' => $schoolHeadStages[$version->career_stage] ?? null,
                    'instrument' => 'cot',
                ]);

                continue;
            }

            DB::table('cot_indicator_versions')->where('id', $version->id)->update([
                'framework' => 'ppst',
                'career_track' => 'classroom_teaching',
                'ratee_position' => $teacherStages[$version->career_stage] ?? 'teacher_i_iii',
                'instrument' => 'cot',
            ]);
        }
    }
};
