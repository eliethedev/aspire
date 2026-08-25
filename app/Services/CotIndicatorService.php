<?php

namespace App\Services;

use App\Models\CotIndicator;
use App\Models\CotIndicatorVersion;
use App\Models\Observation;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Single source of truth for COT/PPST indicator versions.
 *
 * All consumers (observation UI, AI, reports, analytics) resolve indicator
 * sets through this service. When the database is unseeded, it falls back to
 * the config/cot.php definitions so the system keeps working out of the box.
 *
 * Lifecycle & integrity:
 *  - Only published versions are used for new observations (drafts are not
 *    final and never enter the observation pipeline).
 *  - Observations pin cot_indicator_version_id at creation, so rendering an
 *    old observation always uses the version it was created against, even if
 *    that version is later edited or archived.
 *  - cot_ratings keep their own indicator snapshots (code/domain/description),
 *    so past ratings never change when an indicator is modified.
 */
class CotIndicatorService
{
    public const CACHE_KEY = 'cot_indicator_all';

    /**
     * All indicator versions with their indicators, cached.
     */
    public function getAllVersions(): Collection
    {
        return Cache::remember(self::CACHE_KEY, config('ai.cache.ttl', 3600), function () {
            return CotIndicatorVersion::with(['indicators' => function ($query) {
                $query->orderBy('sort_order')->orderBy('id');
            }])->orderBy('school_year')->get();
        });
    }

    /**
     * Resolve the version used for NEW observations.
     * Returns the published version for the school year, or the default
     * version when no school year is specified. Drafts are never used here.
     */
    public function getVersionModel(?string $schoolYear = null): ?CotIndicatorVersion
    {
        $versions = $this->getAllVersions();

        if ($schoolYear === null) {
            $default = $versions->first(fn (CotIndicatorVersion $v) => $v->is_default)
                ?? $versions->first(fn (CotIndicatorVersion $v) => $v->school_year === $this->defaultSchoolYear());

            return $default;
        }

        return $versions->first(fn (CotIndicatorVersion $v) => $v->school_year === $schoolYear && $v->isPublished());
    }

    /**
     * Config-shaped array: ['label', 'indicators' => [code, description, domain]].
     * Falls back to config('cot.versions.<school_year>') when unseeded.
     */
    public function getVersion(?string $schoolYear = null): array
    {
        $schoolYear = $schoolYear ?: $this->defaultSchoolYear();

        $model = $this->getVersionModel($schoolYear);

        if ($model) {
            return $this->toArray($model);
        }

        return $this->configFallback($schoolYear);
    }

    /**
     * The version to use when rendering an observation's COT sheet.
     * Uses the pinned version (historical integrity) or resolves by school year.
     */
    public function getVersionForObservation(Observation $observation): array
    {
        if ($observation->cot_indicator_version_id) {
            $versions = $this->getAllVersions();
            $pinned = $versions->first(fn (CotIndicatorVersion $v) => $v->id === (int) $observation->cot_indicator_version_id);

            if ($pinned) {
                return $this->toArray($pinned);
            }
        }

        $observee = $observation->observee;
        if ($observee instanceof Teacher) {
            $version = $this->resolveVersionForObservee(
                $observation->school_year,
                'teacher',
                $observee->career_stage,
            );

            if ($version) {
                return $this->toArray($version);
            }
        }

        return $this->getVersion($observation->school_year);
    }

    /**
     * Resolve the published instrument version for an observee.
     *
     * Preference order within the school year:
     *   1. a version matching the observee's exact career stage;
     *   2. a stage-agnostic version (career_stage NULL/'all') for the role;
     *   3. any remaining published version for the school year.
     *
     * Used when pinning the version for NEW observations so the correct
     * instrument follows the teacher's career stage.
     */
    public function resolveVersionForObservee(
        ?string $schoolYear,
        ?string $rateeRole,
        ?string $careerStage
    ): ?CotIndicatorVersion {
        $schoolYear = $schoolYear ?: $this->defaultSchoolYear();

        $candidates = $this->getAllVersions()->filter(
            fn (CotIndicatorVersion $v) => $v->school_year === $schoolYear && $v->isPublished()
        );

        if ($rateeRole !== null) {
            $candidates = $candidates->filter(
                fn (CotIndicatorVersion $v) => $v->rateeRole() === $rateeRole
            );
        }

        if ($careerStage !== null) {
            $exact = $candidates->first(
                fn (CotIndicatorVersion $v) => $v->career_stage === $careerStage
            );
            if ($exact) {
                return $exact;
            }
        }

        $generic = $candidates->first(
            fn (CotIndicatorVersion $v) => $v->career_stage === null || $v->career_stage === 'all'
        );

        return $generic ?? $candidates->first();
    }

    /**
     * Convenience wrapper returning the config-shaped array (indicators +
     * rating scale) for an observee model in a given school year.
     */
    public function getVersionForObservee(Model $observee, ?string $schoolYear = null): array
    {
        $rateeRole = $observee instanceof Teacher ? 'teacher' : 'school_head';
        $careerStage = $observee instanceof Teacher ? $observee->career_stage : null;

        $version = $this->resolveVersionForObservee($schoolYear, $rateeRole, $careerStage);

        if ($version) {
            return $this->toArray($version);
        }

        return $this->getVersion($schoolYear);
    }

    /**
     * The latest published version (highest school year).
     * Mirrors the previous behaviour of using the last entry in config/cot.php.
     */
    public function getLatestVersion(): array
    {
        $versions = $this->getAllVersions()
            ->filter(fn (CotIndicatorVersion $v) => $v->isPublished())
            ->sortByDesc('school_year');

        $latest = $versions->first();
        if ($latest) {
            return $this->toArray($latest);
        }

        $configVersions = config('cot.versions', []);
        $latestConfig = end($configVersions);
        if ($latestConfig) {
            return $this->mergeConfigMeta($latestConfig);
        }

        return ['label' => 'PPST COT', 'indicators' => [], 'status' => 'published'];
    }

    /**
     * School year of the default version.
     */
    public function defaultSchoolYear(): string
    {
        return config('cot.default_version', date('Y') . '-' . (date('Y') + 1));
    }

    /**
     * Forget service and RAG caches so admin edits are reflected immediately.
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);

        $prefix = config('ai.cache.key_prefix', 'ai_rag_');
        Cache::forget($prefix . 'all_domains');
        Cache::forget($prefix . 'system_context');
    }

    /**
     * Convert a version model into the config-shaped array the blades expect.
     */
    public function toArray(CotIndicatorVersion $version): array
    {
        return [
            'id' => $version->id,
            'school_year' => $version->school_year,
            'label' => $version->label,
            'status' => $version->status,
            'is_default' => $version->is_default,
            'ratee_role' => $version->rateeRole(),
            'career_stage' => $version->career_stage,
            'requires_post_conference' => $version->requiresPostConference(),
            'career_stage_label' => $version->careerStageLabel(),
            'rating_scale' => $version->ratingScale(),
            'rating_scale_css' => $version->ratingScaleCss(),
            'indicators' => $version->indicators->map(fn (CotIndicator $indicator) => [
                'id' => $indicator->id,
                'code' => $indicator->code,
                'description' => $indicator->description,
                'domain' => $indicator->domain,
                'sort_order' => $indicator->sort_order,
                'is_active' => $indicator->is_active,
            ])->toArray(),
        ];
    }

    private function configFallback(string $schoolYear): array
    {
        $config = config("cot.versions.{$schoolYear}");

        if (!$config) {
            $config = config('cot.versions.' . $this->defaultSchoolYear());
        }

        if (!$config) {
            return [
                'id' => null,
                'school_year' => $schoolYear,
                'label' => 'PPST COT',
                'status' => 'published',
                'is_default' => false,
                'ratee_role' => CotIndicatorVersion::DEFAULT_RATEE_ROLE,
                'career_stage' => null,
                'requires_post_conference' => true,
                'career_stage_label' => null,
                'rating_scale' => config('cot.rating_scale', []),
                'rating_scale_css' => config('cot.rating_scale_css', []),
                'indicators' => [],
            ];
        }

        return $this->mergeConfigMeta($config, $schoolYear);
    }

    private function mergeConfigMeta(array $config, ?string $schoolYear = null): array
    {
        $config['id'] = null;
        $config['school_year'] = $schoolYear ?? $config['school_year'] ?? null;
        $config['status'] = 'published';
        $config['is_default'] = ($config['school_year'] ?? null) === $this->defaultSchoolYear();
        $config['ratee_role'] = $config['ratee_role'] ?? CotIndicatorVersion::DEFAULT_RATEE_ROLE;
        $config['career_stage'] = $config['career_stage'] ?? null;
        $config['requires_post_conference'] = $config['requires_post_conference'] ?? true;
        $config['career_stage_label'] = $config['career_stage_label'] ?? null;
        $config['rating_scale'] = $config['rating_scale'] ?? config('cot.rating_scale', []);
        $config['rating_scale_css'] = $config['rating_scale_css'] ?? config('cot.rating_scale_css', []);

        return $config;
    }
}
