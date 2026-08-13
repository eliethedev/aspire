<?php

namespace App\Services;

use App\Models\CotIndicatorVersion;

/**
 * Resolves the career stage for a version from its selected standards
 * framework, career track, and ratee position.
 *
 * This is the single source of truth for the Create/Edit Version flows.
 * The browser-provided `career_stage` value is never trusted: every store
 * and update re-resolves the stage here and rejects combinations that do
 * not exist in config/career_stages.php (e.g. PPST + School Principal, or
 * PPSSH + Teacher I-III).
 */
class CareerStageResolver
{
    /**
     * All frameworks keyed by value: ['ppst' => 'PPST', 'ppssh' => 'PPSSH'].
     */
    public function frameworks(): array
    {
        $frameworks = config('career_stages.frameworks', []);

        return array_map(fn (array $framework) => $framework['label'], $frameworks);
    }

    /**
     * Career tracks of a framework keyed by value.
     */
    public function tracksFor(string $framework): array
    {
        $tracks = config("career_stages.frameworks.{$framework}.tracks", []);

        return array_map(fn (array $track) => $track['label'], $tracks);
    }

    /**
     * Ratee positions of a framework track as a list of option arrays:
     * [['value', 'label', 'career_stage'], ...].
     */
    public function positionsFor(string $framework, string $track): array
    {
        $positions = config("career_stages.frameworks.{$framework}.tracks.{$track}.positions", []);

        return array_values(array_map(
            fn (string $key, array $position) => [
                'value' => $key,
                'label' => $position['label'],
                'career_stage' => $position['career_stage'],
            ],
            array_keys($positions),
            $positions,
        ));
    }

    /**
     * Resolve the career stage key for a framework + track + position.
     * Returns null when the combination is not defined (invalid).
     */
    public function resolve(?string $framework, ?string $track, ?string $position): ?string
    {
        if ($framework === null || $track === null || $position === null) {
            return null;
        }

        $stage = config("career_stages.frameworks.{$framework}.tracks.{$track}.positions.{$position}.career_stage");

        return $stage ?: null;
    }

    /**
     * Human-readable stage label, e.g. "Career Stage I".
     */
    public function stageLabel(?string $careerStage): string
    {
        if ($careerStage === null || $careerStage === '') {
            return '';
        }

        return config("career_stages.career_stage_labels.{$careerStage}", $careerStage);
    }

    /**
     * Human-readable framework label, e.g. "PPST".
     */
    public function frameworkLabel(?string $framework): string
    {
        return $framework
            ? config("career_stages.frameworks.{$framework}.label", $framework)
            : '';
    }

    /**
     * Whether the framework/track/position combination resolves to a stage.
     */
    public function isSupported(?string $framework, ?string $track, ?string $position): bool
    {
        return $this->resolve($framework, $track, $position) !== null;
    }

    /**
     * Ratee role derived from the framework ('teacher' for PPST,
     * 'school_head' for PPSSH), keeping the version compatible with the
     * existing observation resolver.
     */
    public function rateeRoleFor(string $framework): string
    {
        return $framework === 'ppssh'
            ? 'school_head'
            : CotIndicatorVersion::DEFAULT_RATEE_ROLE;
    }
}
