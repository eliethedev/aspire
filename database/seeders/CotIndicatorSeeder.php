<?php

namespace Database\Seeders;

use App\Models\CotIndicator;
use App\Models\CotIndicatorVersion;
use App\Models\PpstStandard;
use App\Services\CotIndicatorService;
use Illuminate\Database\Seeder;

class CotIndicatorSeeder extends Seeder
{
    public function run(): void
    {
        $versions = config('cot.versions', []);
        $defaultSchoolYear = config('cot.default_version', '');

        $ppstIdsByCode = PpstStandard::pluck('id', 'indicator_code');

        foreach ($versions as $schoolYear => $config) {
            $version = CotIndicatorVersion::where('school_year', $schoolYear)->first();

            if ($version) {
                // Idempotent: never overwrite admin-managed content on re-seed.
                $version->update([
                    'label' => $config['label'],
                    'is_default' => $schoolYear === $defaultSchoolYear,
                ]);

                if ($version->framework === null) {
                    $version->framework = $this->frameworkFor($config);
                    $version->career_track = $this->careerTrackFor($config);
                    $version->ratee_position = $this->rateePositionFor($config);
                    $version->instrument = $config['instrument'] ?? CotIndicatorVersion::DEFAULT_INSTRUMENT;
                    $version->save();
                }

                if ($version->career_stage === null) {
                    $version->career_stage = $config['career_stage'] ?? null;
                    $version->save();
                }

                if ($version->rating_scale === null) {
                    $version->rating_scale = $config['rating_scale'] ?? null;
                    $version->rating_scale_css = $config['rating_scale_css'] ?? null;
                    $version->save();
                }

                continue;
            }

            $version = CotIndicatorVersion::create([
                'school_year' => $schoolYear,
                'label' => $config['label'],
                'is_default' => $schoolYear === $defaultSchoolYear,
                'status' => CotIndicatorVersion::STATUS_PUBLISHED,
                'ratee_role' => $config['ratee_role'] ?? CotIndicatorVersion::DEFAULT_RATEE_ROLE,
                'framework' => $this->frameworkFor($config),
                'career_track' => $this->careerTrackFor($config),
                'ratee_position' => $this->rateePositionFor($config),
                'instrument' => $config['instrument'] ?? CotIndicatorVersion::DEFAULT_INSTRUMENT,
                'career_stage' => $config['career_stage'] ?? null,
                'rating_scale' => $config['rating_scale'] ?? null,
                'rating_scale_css' => $config['rating_scale_css'] ?? null,
            ]);

            $indicators = $config['indicators'] ?? [];
            foreach ($indicators as $sortOrder => $indicator) {
                CotIndicator::create([
                    'version_id' => $version->id,
                    'ppst_standard_id' => $ppstIdsByCode[$indicator['code']] ?? null,
                    'code' => $indicator['code'],
                    'description' => $indicator['description'],
                    'domain' => $indicator['domain'],
                    'sort_order' => $sortOrder,
                    'is_active' => true,
                ]);
            }

            $this->command->info("Seeded COT indicator version: {$config['label']}");
        }

        app(CotIndicatorService::class)->clearCache();

        $this->backfillPpstStandardIds($ppstIdsByCode->toArray());

        $this->command->info('COT indicator versions seeded successfully.');
    }

    /**
     * The standards framework a seeded version belongs to. PPST for teaching
     * personnel, PPSSH for school heads.
     */
    private function frameworkFor(array $config): string
    {
        $role = $config['ratee_role'] ?? CotIndicatorVersion::DEFAULT_RATEE_ROLE;

        return $role === 'school_head' ? 'ppssh' : 'ppst';
    }

    /**
     * The career track within the version's framework.
     */
    private function careerTrackFor(array $config): string
    {
        $role = $config['ratee_role'] ?? CotIndicatorVersion::DEFAULT_RATEE_ROLE;

        return $role === 'school_head' ? 'school_administration' : 'classroom_teaching';
    }

    /**
     * The ratee position group the seeded version targets. Teacher versions
     * default to the Teacher I-III COT (which stays stage-agnostic so it
     * remains the app-wide fallback instrument).
     */
    private function rateePositionFor(array $config): ?string
    {
        $role = $config['ratee_role'] ?? CotIndicatorVersion::DEFAULT_RATEE_ROLE;

        if ($role === 'school_head') {
            return $config['ratee_position'] ?? null;
        }

        return $config['ratee_position'] ?? 'teacher_i_iii';
    }

    /**
     * Link existing indicators to their canonical PPST standard by code.
     * Idempotent: only fills nulls, never overwrites an existing link.
     */
    private function backfillPpstStandardIds(array $ppstIdsByCode): void
    {
        $missing = CotIndicator::whereNull('ppst_standard_id')->get();

        foreach ($missing as $indicator) {
            if (isset($ppstIdsByCode[$indicator->code])) {
                $indicator->update(['ppst_standard_id' => $ppstIdsByCode[$indicator->code]]);
            }
        }

        if ($missing->isNotEmpty()) {
            $this->command->info('Linked existing indicators to their PPST standards.');
        }
    }
}
