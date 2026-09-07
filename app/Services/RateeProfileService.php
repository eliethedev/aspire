<?php

namespace App\Services;

use App\Models\Observation;
use App\Models\SchoolHeadProfile;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Supervisor decision-support profile for a ratee (teacher or school head).
 *
 * This service is strictly read-only: it only summarises existing observation
 * and rating data. It never modifies observations, ratings, career stages or
 * user status, and it never produces promotion/disciplinary judgments.
 */
class RateeProfileService
{
    /**
     * Domains averaging below this (2-6 scale) are surfaced as areas that
     * may require attention. 4 ("Satisfactory") is the reference point.
     */
    public const ATTENTION_THRESHOLD = 4.0;

    /**
     * Minimum number of scored observations before a trend is considered
     * meaningful enough to surface "areas requiring attention".
     */
    public const MIN_OBSERVATIONS_FOR_ATTENTION = 2;

    /**
     * Build the full profile payload for a ratee.
     */
    public function for(Model $ratee): array
    {
        $observeeType = $ratee->getMorphClass();
        $observeeId = $ratee->getKey();

        $all = Observation::where('observee_id', $observeeId)
            ->where('observee_type', $observeeType)
            ->where('status', '!=', 'cancelled')
            ->orderBy('observation_date', 'desc');

        $scoredObservations = (clone $all)
            ->whereNotNull('overall_score')
            ->with(['cotRatings' => fn ($query) => $query->where('not_observed', false)->whereNotNull('rating')])
            ->get();

        $total = (clone $all)->count();
        $completed = (clone $all)->where('status', 'completed')->count();
        $inProgress = (clone $all)->whereIn('stage', ['pre_observation_planning', 'pre_conference', 'observation'])->count();
        $averageRating = $scoredObservations->avg('overall_score');

        $latest = $scoredObservations->first();
        $upcoming = (clone $all)
            ->where('status', 'scheduled')
            ->whereDate('observation_date', '>=', now()->toDateString())
            ->first();

        $domainSummary = $this->domainSummary($scoredObservations);
        $attention = $this->areasRequiringAttention($scoredObservations);

        $careerContext = app(CareerProgressionService::class)->contextFor($ratee);

        return [
            'ratee' => $ratee,
            'name' => $ratee->user->name ?? 'Unknown',
            'role_label' => $ratee instanceof Teacher ? 'Teacher' : 'School Head',
            'position' => $this->positionFor($ratee),
            'career_stage_label' => $careerContext['career_stage_label'] ?: null,
            'stats' => [
                'total' => $total,
                'completed' => $completed,
                'in_progress' => $inProgress,
                'average_rating' => $averageRating !== null ? round((float) $averageRating, 2) : null,
                'latest_observation' => $latest ? [
                    'date' => $latest->observation_date?->format('M d, Y'),
                    'rating' => $latest->overall_score,
                ] : null,
                'upcoming_observation' => $upcoming ? [
                    'date' => $upcoming->observation_date?->format('M d, Y'),
                ] : null,
            ],
            'domain_summary' => $domainSummary,
            'areas_attention' => $attention['items'],
            'attention_insufficient' => $attention['insufficient'],
            'actions' => $this->actionsFor($ratee),
        ];
    }

    /**
     * Average rating per PPST domain across scored observations, ordered by
     * the canonical PPST domain sequence.
     *
     * @return Collection<int, array{domain: string, average_rating: float, observation_count: int}>
     */
    public function domainSummary(Collection $scoredObservations): Collection
    {
        $ratings = $scoredObservations->flatMap->cotRatings;

        if ($ratings->isEmpty()) {
            return collect();
        }

        $order = $this->domainOrder();

        return $ratings->groupBy('domain')->map(function ($group) {
            return [
                'domain' => (string) $group->first()->domain,
                'average_rating' => round((float) $group->avg('rating'), 2),
                'observation_count' => $group->pluck('observation_id')->unique()->count(),
            ];
        })->sortBy(fn (array $item) => $order[$item['domain']] ?? PHP_INT_MAX)
            ->values();
    }

    /**
     * Domains with consistently lower ratings, using simple transparent logic:
     * the domain was rated in at least {@see MIN_OBSERVATIONS_FOR_ATTENTION}
     * observations and its average is below {@see ATTENTION_THRESHOLD}.
     *
     * @return array{items: Collection, insufficient: bool}
     */
    public function areasRequiringAttention(Collection $scoredObservations): array
    {
        $scoredObservationIds = $scoredObservations->pluck('id')->unique();

        if ($scoredObservationIds->count() < self::MIN_OBSERVATIONS_FOR_ATTENTION) {
            return ['items' => collect(), 'insufficient' => true];
        }

        $items = $this->domainSummary($scoredObservations)
            ->filter(function (array $domain) {
                return $domain['average_rating'] < self::ATTENTION_THRESHOLD
                    && $domain['observation_count'] >= self::MIN_OBSERVATIONS_FOR_ATTENTION;
            })
            ->map(fn (array $domain) => [
                'domain' => $domain['domain'],
                'average_rating' => $domain['average_rating'],
                'observation_count' => $domain['observation_count'],
            ])
            ->values();

        return ['items' => $items, 'insufficient' => false];
    }

    /**
     * Supervisor actions backed by existing routes only.
     *
     * @return array{schedule_observation: string, observation_history: string, post_conference: array|null}
     */
    public function actionsFor(Model $ratee): array
    {
        $isTeacher = $ratee instanceof Teacher;

        $scheduleUrl = $isTeacher
            ? route('supervisor.observations.create', ['teacher_id' => $ratee->getKey()])
            : route('supervisor.observations.create', ['school_head' => $ratee->getKey()]);

        $historyUrl = $isTeacher
            ? route('supervisor.observations.teacher-history', [
                'observeeId' => $ratee->getKey(),
                'type' => $ratee::class,
            ])
            : route('supervisor.school-heads.observations', $ratee);

        $pendingConference = Observation::where('observee_id', $ratee->getKey())
            ->where('observee_type', $ratee->getMorphClass())
            ->where('stage', 'post_conference')
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->latest('observation_date')
            ->first();

        return [
            'schedule_observation' => $scheduleUrl,
            'observation_history' => $historyUrl,
            'post_conference' => $pendingConference ? [
                'label' => $pendingConference->observation_date?->format('M d, Y') ?: 'Observation #'.$pendingConference->getKey(),
                'url' => route('supervisor.observations.postConference', $pendingConference),
            ] : null,
        ];
    }

    private function positionFor(Model $ratee): string
    {
        if ($ratee instanceof Teacher) {
            return $ratee->position_label ?? 'Teacher';
        }

        if ($ratee->position_level) {
            return $ratee->position_level_label;
        }

        if ($ratee->current_designation) {
            return $ratee->current_designation_label;
        }

        return $ratee->position ?? 'School Head';
    }

    /**
     * Canonical PPST domain order (by name) used to present the summary.
     *
     * @return array<string, int>
     */
    private function domainOrder(): array
    {
        $order = [];
        foreach (config('ppst.domains', []) as $index => $domain) {
            $order[$domain['name']] = $index;
        }

        return $order;
    }
}
