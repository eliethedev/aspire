<?php

namespace App\Services;

use App\Models\CareerAdvancement;
use App\Models\CareerProgressionAssessment;
use App\Models\Observation;
use App\Models\Teacher;
use Illuminate\Support\Collection;
/**
 * Computes whether each teacher's observed performance aligns with the
 * expectations of their current position / career stage, and surfaces which
 * teachers are ready to move to the next stage so a supervisor can allow or
 * announce the advancement.
 */
class CareerMonitorService
{
    public const ALIGNED = 'aligned';

    public const PARTIAL = 'partial';

    public const NOT_ALIGNED = 'not_aligned';

    public const INSUFFICIENT = 'insufficient';

    /** COT 6-point scale threshold for a stage-appropriate (aligned) rating. */
    public const ALIGNED_THRESHOLD = 4.5;

    /** Minimum completed observations before alignment can be judged. */
    public const MIN_OBSERVATIONS = 2;

    public function __construct(
        private CareerProgressionService $careerService,
    ) {}

    /**
     * Alignment rows for every teacher in the supervisor's school.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function forSchool(int $schoolId): Collection
    {
        $teachers = Teacher::query()
            ->with('user')
            ->where('school_id', $schoolId)
            ->orderBy('id')
            ->get();

        $evidence = $this->evidenceFor($teachers->pluck('id'));
        $assessments = $this->assessmentsFor($teachers->pluck('id'));
        $advancements = $this->advancementsFor($teachers->pluck('id'));

        return $teachers->map(function (Teacher $teacher) use ($evidence, $assessments, $advancements) {
            $context = $this->careerService->contextFor($teacher);
            $currentStage = $context['career_stage'];
            $stats = $evidence->get($teacher->id);
            $avgScore = $stats?->avg_score !== null ? round((float) $stats->avg_score, 2) : null;
            $observationsCount = (int) ($stats->observations_count ?? 0);

            $alignment = $this->align($avgScore, $observationsCount);
            $nextStage = $this->careerService->nextStageKey($currentStage);
            $nextStageOptions = $this->careerService->nextStageOptions($currentStage);

            return [
                'teacher' => $teacher,
                'current_stage' => $currentStage,
                'current_stage_label' => $context['career_stage_label'],
                'position' => $context['position'],
                'next_stage' => $nextStage,
                'next_stage_label' => $nextStageOptions[$nextStage] ?? null,
                'alignment' => $alignment,
                'alignment_label' => $this->label($alignment),
                'alignment_tone' => $this->tone($alignment),
                'avg_score' => $avgScore,
                'observations_count' => $observationsCount,
                'last_observation_date' => $stats?->last_observation_date,
                'assessment_status' => $assessments->get($teacher->id)?->status,
                'latest_advancement' => $advancements->get($teacher->id),
            ];
        });
    }

    /**
     * Single-teacher alignment snapshot (used on the monitor rows / profile).
     */
    public function align(?float $avgScore, int $observationsCount): string
    {
        if ($observationsCount < self::MIN_OBSERVATIONS || $avgScore === null) {
            return self::INSUFFICIENT;
        }

        if ($avgScore >= self::ALIGNED_THRESHOLD) {
            return self::ALIGNED;
        }

        if ($avgScore >= 3.5) {
            return self::PARTIAL;
        }

        return self::NOT_ALIGNED;
    }

    public function label(string $level): string
    {
        return match ($level) {
            self::ALIGNED => 'Aligned with position',
            self::PARTIAL => 'Partially aligned',
            self::NOT_ALIGNED => 'Needs development',
            default => 'Insufficient data',
        };
    }

    public function tone(string $level): string
    {
        return match ($level) {
            self::ALIGNED => 'emerald',
            self::PARTIAL => 'amber',
            self::NOT_ALIGNED => 'red',
            default => 'slate',
        };
    }

    private function evidenceFor(Collection $teacherIds): Collection
    {
        return Observation::where('observee_type', Teacher::class)
            ->whereIn('observee_id', $teacherIds)
            ->where('status', 'completed')
            ->whereNotNull('overall_score')
            ->groupBy('observee_id')
            ->selectRaw('observee_id, count(*) as observations_count, avg(overall_score) as avg_score, max(observation_date) as last_observation_date')
            ->get()
            ->keyBy('observee_id');
    }

    private function assessmentsFor(Collection $teacherIds): Collection
    {
        return CareerProgressionAssessment::where('ratee_type', Teacher::class)
            ->whereIn('ratee_id', $teacherIds)
            ->latest('assessed_at')
            ->latest('id')
            ->get()
            ->groupBy('ratee_id')
            ->map->first();
    }

    private function advancementsFor(Collection $teacherIds): Collection
    {
        return CareerAdvancement::with('supervisor')
            ->whereIn('teacher_id', $teacherIds)
            ->latest('created_at')
            ->get()
            ->groupBy('teacher_id')
            ->map->first();
    }
}
