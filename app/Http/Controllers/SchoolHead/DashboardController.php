<?php

namespace App\Http\Controllers\SchoolHead;

use App\Http\Controllers\Controller;
use App\Models\CoachingAgreement;
use App\Models\CotRating;
use App\Models\Observation;
use App\Models\PreObservationPlanning;
use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;

/**
 * School head dashboard.
 *
 * Mirrors the ASPIRE goal — streamline instructional supervision so the
 * school head spends less time on paperwork and more time guiding teachers:
 * classroom observations (COT), lesson plans, COT results and reports, and
 * post-observation coaching. All wording below stays in plain school terms.
 */
class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $schoolHead = $user->schoolHeadProfile;

        $schoolId = $schoolHead?->school_id ?? $user->school_id;

        $teachers = collect();
        if ($schoolId) {
            $teachers = Teacher::with(['user:id,name,email', 'subjects'])
                ->where('school_id', $schoolId)
                ->orderBy('position')
                ->get();
        }

        $teacherCount = $teachers->count();
        $quarter = $this->currentQuarter();
        $schoolYear = now()->format('Y') . '-' . (now()->year + 1);

        $stats = $this->observationStats($schoolId);
        $avgScore = $stats['average_score'];
        $trend = $stats['vs_previous'];

        // ---------------------------------------------------------------
        // Things that need the school head's attention
        // ---------------------------------------------------------------
        $lessonPlansToReview = $this->pendingLessonPlanCount($schoolId);
        $coachingToSign = $this->pendingCoachingCount($schoolId);
        $confirmations = Observation::where('school_head_id', $user->id)
            ->where('confirmation_status', 'pending')
            ->where('status', '!=', 'cancelled')
            ->count();

        $attention = [
            'items' => [
                [
                    'label' => 'Lesson plans to review',
                    'hint' => 'DLLs/DLPs submitted by teachers for the coming observations',
                    'count' => $lessonPlansToReview,
                    'route' => $lessonPlansToReview > 0 ? route('school-head.lesson-plans.index') : null,
                    'tone' => 'indigo',
                ],
                [
                    'label' => 'Observations to confirm',
                    'hint' => 'Scheduled observations waiting for your confirmation',
                    'count' => $confirmations,
                    'route' => $confirmations > 0 ? route('school-head.observations.index') : null,
                    'tone' => 'sky',
                ],
                [
                    'label' => 'Coaching agreements to sign',
                    'hint' => 'Agreements made after observations, for your signature',
                    'count' => $coachingToSign,
                    'route' => $coachingToSign > 0 ? route('school-head.coaching.index') : null,
                    'tone' => 'emerald',
                ],
            ],
            'total' => $lessonPlansToReview + $confirmations + $coachingToSign,
        ];

        // ---------------------------------------------------------------
        // Teachers and their observations (real COT data)
        // ---------------------------------------------------------------
        [$teacherRows, $recentScores, $cotTrend, $cotLabels] = $this->teacherObservationRows($teachers, $schoolId, $quarter, $schoolYear);

        $rubricScoring = $this->rubricDomains($recentScores);

        $coaching = $this->coachingSummaries($schoolId);

        $dll = $this->dllStatusBoard($teachers, $schoolId, $quarter);

        $quickStats = [
            'total' => $stats['total'],
            'completed' => $stats['completed'],
            'in_progress' => $stats['in_progress'],
            'completion_rate' => $stats['total'] > 0 ? round(($stats['completed'] / $stats['total']) * 100) : 0,
            'teacher_count' => $teacherCount,
            'avg_score' => $avgScore,
            'trend' => $trend,
        ];

        return view('school-head.dashboard', compact(
            'user', 'schoolHead', 'attention', 'teacherRows', 'rubricScoring',
            'cotTrend', 'cotLabels', 'coaching', 'dll', 'quickStats',
            'quarter', 'schoolYear'
        ));
    }

    // ------------------------------------------------------------------
    // Observation aggregates (real data)
    // ------------------------------------------------------------------

    protected function observationStats(?int $schoolId): array
    {
        $completed = 0;
        $total = 0;
        $inProgress = 0;
        $avg = null;
        $trend = 0;

        if ($schoolId) {
            $base = Observation::query()
                ->whereHasMorph('observee', [Teacher::class], fn ($q) => $q->where('school_id', $schoolId));

            $total = (clone $base)->count();
            $inProgress = (clone $base)->whereIn('status', ['scheduled', 'in_progress'])->count();

            $completedObs = (clone $base)->where('status', 'completed')->whereNotNull('overall_score')->get();
            $completed = $completedObs->count();
            $avg = round($completedObs->avg('overall_score') ?? 0, 1);

            $prev = (clone $base)->where('status', 'completed')
                ->whereNotNull('overall_score')
                ->orderBy('observation_date')
                ->take(max($completedObs->count() - 1, 1))
                ->avg('overall_score');
            $trend = $prev ? round($avg - round($prev, 1), 1) : 0;
        }

        return [
            'total' => $total,
            'completed' => $completed,
            'in_progress' => $inProgress,
            'average_score' => $avg,
            'vs_previous' => $trend,
        ];
    }

    protected function pendingLessonPlanCount(?int $schoolId): int
    {
        if (! $schoolId) {
            return 0;
        }

        return PreObservationPlanning::query()
            ->whereNotNull('lesson_plan_file')
            ->whereHas('observation', function ($q) use ($schoolId) {
                $q->whereHasMorph('observee', [Teacher::class], fn ($qq) => $qq->where('school_id', $schoolId))
                    ->whereIn('status', ['scheduled', 'in_progress']);
            })
            ->count();
    }

    protected function pendingCoachingCount(?int $schoolId): int
    {
        if (! $schoolId) {
            return 0;
        }

        return CoachingAgreement::query()
            ->whereIn('status', ['draft', 'active'])
            ->whereHas('teacher', fn ($q) => $q->where('school_id', $schoolId))
            ->count();
    }

    /**
     * One row per teacher: how many observations are done, the latest COT
     * result, and a plain remark on where the teacher stands.
     */
    protected function teacherObservationRows($teachers, ?int $schoolId, int $quarter, string $schoolYear): array
    {
        $rows = [];
        $trend = [];
        $labels = [];
        $recentScores = collect();

        if ($schoolId) {
            $obs = Observation::query()
                ->whereHasMorph('observee', [Teacher::class], fn ($q) => $q->where('school_id', $schoolId))
                ->with(['observee.user:id,name', 'cotRatings'])
                ->get();

            $completedSorted = $obs
                ->where('status', 'completed')
                ->whereNotNull('overall_score')
                ->sortBy('observation_date')
                ->values();

            $trend = $completedSorted->pluck('overall_score')->map(fn ($v) => (float) $v)->values()->toArray();
            $labels = $completedSorted
                ->map(fn ($o, $i) => '#' . ($i + 1) . ' · ' . ($o->observation_date?->format('M d') ?? ''))
                ->values()
                ->toArray();

            $recentScores = $completedSorted->reverse()->take(5)->values();

            $byTeacher = $obs->groupBy('observee_id');
            $quarterKey = (string) $quarter;

            foreach ($teachers as $t) {
                $all = $byTeacher->get($t->id, collect());
                $cycle = $all->filter(fn ($o) => (string) $o->quarter === $quarterKey);
                $rows_for_teacher = $cycle->isEmpty() ? $all : $cycle;

                $done = $rows_for_teacher->where('status', 'completed')->whereNotNull('overall_score');
                $latest = $done->sortByDesc('observation_date')->first();

                $latestScore = $latest ? (float) $latest->overall_score : null;
                $latestResult = null;
                $remark = 'Not started';

                if ($latest) {
                    $max = (float) $latest->ratingScaleMax();
                    $latestResult = CotRating::descriptiveTotal($latestScore, $max);
                    $ratio = $max > 0 ? $latestScore / $max : 0;
                    $remark = match (true) {
                        $ratio >= 0.75 => 'On track',
                        $ratio >= 0.55 => 'For coaching',
                        default => 'Needs support',
                    };
                } elseif ($rows_for_teacher->where('status', '!=', 'cancelled')->isNotEmpty()) {
                    $remark = 'Observation scheduled';
                }

                $rows[] = [
                    'teacher' => $t->user?->name ?? 'Unassigned',
                    'position' => $t->positionLabel ?? 'Faculty',
                    'observations_done' => $done->count(),
                    'has_observation' => $rows_for_teacher->where('status', '!=', 'cancelled')->isNotEmpty(),
                    'latest_score' => $latestScore,
                    'latest_result' => $latestResult,
                    'remark' => $remark,
                ];
            }
        }

        $rows = collect($rows)
            ->sortByDesc('observations_done')
            ->values()
            ->all();

        return [$rows, $recentScores, $trend, $labels];
    }

    /**
     * Domain scores for the most recent completed teacher observations.
     */
    protected function rubricDomains($recentScores): array
    {
        return $recentScores->map(function ($obs) {
            $ratings = $obs->cotRatings
                ->filter(fn ($r) => ! $r->not_observed && ! $r->not_applicable && $r->rating)
                ->groupBy(fn ($r) => $r->domain ?: 'Other');

            $domains = $ratings
                ->map(fn ($group) => round($group->avg('rating'), 1));

            $sorted = $domains->sortDesc();
            $max = $obs->ratingScaleMax();

            return [
                'id' => $obs->id,
                'teacher' => $obs->observee?->user?->name ?? 'Unknown',
                'date' => $obs->observation_date?->format('M d, Y'),
                'subject' => $obs->subject,
                'score' => (float) $obs->overall_score,
                'scale_max' => $max,
                'descriptive' => CotRating::descriptiveTotal((float) $obs->overall_score, (float) $max),
                'domains' => $domains,
                'strength' => $sorted->keys()->first(),
                'attention' => $sorted->keys()->last(),
            ];
        })->values()->all();
    }

    protected function coachingSummaries(?int $schoolId): array
    {
        $agreements = collect();

        if ($schoolId) {
            $agreements = CoachingAgreement::query()
                ->with(['teacher.user:id,name', 'supervisor:id,name'])
                ->whereHas('teacher', fn ($q) => $q->where('school_id', $schoolId))
                ->latest()
                ->take(4)
                ->get();
        }

        $items = $agreements->map(function ($a) {
            $signed = filled($a->supervisor_signed_at) ? 'signed' : 'pending_supervisor';

            return [
                'teacher' => $a->teacher?->user?->name ?? 'Unknown',
                'supervisor' => $a->supervisor?->name ?? '—',
                'focus' => collect($a->focus_areas ?? [])->take(2)->implode(', '),
                'status' => $a->status,
                'signature_state' => $signed,
                'route' => route('school-head.coaching.show', $a),
            ];
        })->values();

        return ['items' => $items->toArray()];
    }

    /**
     * Which teachers submitted their lesson plan (DLL/DLP) for review.
     */
    protected function dllStatusBoard($teachers, ?int $schoolId, int $quarter): array
    {
        $submitted = 0;
        $notSubmitted = 0;
        $forChecking = 0;
        $rows = [];

        if ($schoolId) {
            $cyclePlans = PreObservationPlanning::query()
                ->whereNotNull('lesson_plan_file')
                ->with(['observation' => fn ($q) => $q->with(['observee.user:id,name'])])
                ->whereHas('observation', function ($q) use ($schoolId) {
                    $q->whereHasMorph('observee', [Teacher::class], fn ($qq) => $qq->where('school_id', $schoolId));
                })
                ->get();

            foreach ($teachers as $t) {
                $plans = $cyclePlans->filter(fn ($p) => $p->observation?->observee_id === $t->id);

                if ($plans->isNotEmpty()) {
                    $hasCompleted = $plans->contains(fn ($p) => $p->observation?->status === 'completed');
                    $payload = $hasCompleted
                        ? ['status' => 'submitted', 'label' => 'Submitted']
                        : ['status' => 'for_checking', 'label' => 'For checking'];
                    $submitted += $plans->count();
                } else {
                    $scheduled = Observation::query()
                        ->whereHasMorph('observee', [Teacher::class], fn ($q) => $q->where('school_id', $schoolId)->where('id', $t->id))
                        ->whereIn('status', ['scheduled', 'in_progress'])
                        ->count();

                    $payload = $scheduled > 0
                        ? ['status' => 'not_submitted', 'label' => 'Not yet submitted']
                        : ['status' => 'no_observation', 'label' => 'No observation yet'];
                }

                $rows[] = [
                    'teacher' => $t->user?->name ?? 'Unknown',
                    ...$payload,
                ];
            }

            $notSubmitted = collect($rows)->where('status', 'not_submitted')->count();
            $forChecking = collect($rows)->where('status', 'for_checking')->count();
        }

        return [
            'submitted' => $submitted,
            'not_submitted' => $notSubmitted,
            'for_checking' => $forChecking,
            'rows' => $rows,
            'sample' => $schoolId === null,
        ];
    }

    protected function currentQuarter(): int
    {
        $month = (int) now()->format('n');

        return match (true) {
            in_array($month, [6, 7, 8], true) => 1,
            in_array($month, [9, 10, 11], true) => 2,
            in_array($month, [12, 1, 2], true) => 3,
            default => 4,
        };
    }
}