<?php

namespace App\Services;

use App\Models\CareerProgressionAssessment;
use App\Models\Observation;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Readiness support tool for career progression.
 *
 * This service NEVER promotes a ratee or changes their position/career stage.
 * It only:
 *  1. resolves the ratee's current framework/career-stage context from their
 *     existing position/career-stage data (never from the observer's role);
 *  2. summarises COT evidence from existing observation records without
 *     altering them or adding a new scoring system;
 *  3. records append-only supervisor/admin readiness assessments.
 */
class CareerProgressionService
{
    public const RATING_SCALE_MAX = 6;

    /**
     * Current framework + career track + position + career stage for a ratee.
     */
    public function contextFor(Model $ratee): array
    {
        if ($ratee instanceof Teacher) {
            $framework = 'ppst';
            $track = 'classroom_teaching';
            $stageKey = $ratee->career_stage ?? $ratee->inferCareerStage()?->value;
            $position = $ratee->position ?? 'Teacher';
        } else {
            $framework = 'ppssh';
            $track = 'school_administration';
            $stageKey = $this->schoolHeadStageKey($ratee);
            $position = $ratee->position_level_label ?? $ratee->position ?? 'School Head';
        }

        $resolver = app(CareerStageResolver::class);

        return [
            'framework' => $framework,
            'framework_label' => $resolver->frameworkLabel($framework),
            'career_track' => $track,
            'career_track_label' => config("career_stages.frameworks.{$framework}.tracks.{$track}.label", $track),
            'position' => $position,
            'career_stage' => $stageKey,
            'career_stage_label' => $resolver->stageLabel($stageKey) ?: $stageKey,
        ];
    }

    /**
     * COT evidence summarised from the ratee's existing observations.
     * Never writes back to observations or cot_ratings.
     */
    public function evidenceFor(Model $ratee): array
    {
        $completed = $this->completedObservations($ratee)->get();

        $total = $completed->count();

        $perObservationAverages = $completed
            ->map(fn (Observation $observation) => $observation->cotRatings->avg('rating'))
            ->filter(fn ($value) => $value !== null);

        $averageRating = $perObservationAverages->isNotEmpty()
            ? round($perObservationAverages->avg(), 2)
            : null;

        $recent = $completed->first();
        $recentRating = null;
        $recentDate = null;
        if ($recent) {
            $recentRating = $recent->overall_score ?? round($recent->cotRatings->avg('rating'), 2);
            $recentDate = $recent->observation_date?->format('M d, Y');
        }

        $indicatorAverages = $this->indicatorAverages($completed);
        $strengths = $indicatorAverages->filter(fn (array $indicator) => $indicator['average_rating'] >= 5);
        $needsDevelopment = $indicatorAverages->filter(fn (array $indicator) => $indicator['average_rating'] < 4);

        return [
            'total_observations' => $total,
            'average_rating' => $averageRating,
            'recent_observation_rating' => $recentRating,
            'recent_observation_date' => $recentDate,
            'strengths' => $strengths,
            'needs_development' => $needsDevelopment,
        ];
    }

    /**
     * Current readiness status plus the full assessment history.
     */
    public function readinessFor(Model $ratee): array
    {
        $history = $this->assessmentsFor($ratee)->get();

        return [
            'assessment' => $history->first(),
            'status' => $history->first()?->status ?? 'not_yet_assessed',
            'history' => $history,
        ];
    }

    /**
     * Append a readiness assessment. The ratee's position/career stage are
     * snapshotted but never modified.
     */
    public function recordAssessment(Model $ratee, User $evaluator, array $data): CareerProgressionAssessment
    {
        $context = $this->contextFor($ratee);

        return CareerProgressionAssessment::create([
            'ratee_type' => $ratee->getMorphClass(),
            'ratee_id' => $ratee->getKey(),
            'evaluator_id' => $evaluator->id,
            'status' => $data['status'],
            'remarks' => $data['remarks'] ?? null,
            'assessed_at' => $data['assessed_at'] ?? now()->toDateString(),
            'position' => $context['position'],
            'career_stage' => $context['career_stage_label'],
            'framework' => $context['framework_label'],
        ]);
    }

    public function completedObservations(Model $ratee)
    {
        return Observation::where('observee_id', $ratee->getKey())
            ->where('observee_type', $ratee->getMorphClass())
            ->where('status', 'completed')
            ->whereHas('cotRatings')
            ->with(['cotRatings' => fn ($query) => $query->where('not_observed', false)])
            ->orderBy('observation_date', 'desc');
    }

    public function assessmentsFor(Model $ratee)
    {
        return CareerProgressionAssessment::with('evaluator')
            ->where('ratee_type', $ratee->getMorphClass())
            ->where('ratee_id', $ratee->getKey())
            ->latest('assessed_at');
    }

    /**
     * Average rating per indicator code across all completed observations.
     */
    private function indicatorAverages(Collection $observations): Collection
    {
        $ratings = $observations->flatMap->cotRatings;

        if ($ratings->isEmpty()) {
            return collect();
        }

        return $ratings->groupBy('indicator_code')->map(function ($group) {
            $first = $group->first();

            return [
                'code' => $first->indicator_code,
                'indicator' => $first->indicator,
                'domain' => $first->domain,
                'average_rating' => round($group->avg('rating'), 2),
                'occurrences' => $group->count(),
            ];
        })->values();
    }

    /**
     * PPSSH career stage derived from the school head's existing position data.
     */
    private function schoolHeadStageKey(Model $ratee): ?string
    {
        $position = $ratee->position_level ?? $ratee->position ?? null;

        return match ($position) {
            'principal_iii' => 'career_stage_iii',
            'principal_iv' => 'career_stage_iv',
            'principal_i', 'principal_ii' => 'career_stage_ii',
            'head_teacher', 'aspiring_school_head' => 'career_stage_i',
            default => null,
        };
    }
}
