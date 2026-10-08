<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Observation;
use App\Models\Teacher;
use App\Services\TeacherAttentionService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the supervisor dashboard.
     */
    public function dashboard()
    {
        $user = Auth::user();

        $observationsQuery = Observation::where('observer_id', $user->id);
        $obs = (clone $observationsQuery)->get();
        $obsScored = $obs->whereNotNull('overall_score');

        $stats = [
            'total_teachers' => Teacher::whereHas('user', fn ($q) => $q->where('school_id', $user->school_id))->count(),
            'total_observations' => $obs->count(),
            'completed' => $obs->where('status', 'completed')->count(),
            'in_progress' => $obs->where('status', 'in_progress')->count(),
            'scheduled' => $obs->where('status', 'scheduled')->count(),
            'average_score' => round($obsScored->avg('overall_score') ?? 0, 2),
            'stage_pre_planning' => $obs->where('stage', 'pre_observation_planning')->count(),
            'stage_pre_conference' => $obs->where('stage', 'pre_conference')->count(),
            'stage_observation' => $obs->where('stage', 'observation')->count(),
            'stage_post_conference' => $obs->where('stage', 'post_conference')->count(),
        ];

        $recentObservations = (clone $observationsQuery)
            ->with(['observee.user', 'preObservationPlanning', 'preConference', 'postConference'])
            ->withCount('cotRatings')
            ->latest()
            ->take(5)
            ->get();

        // Active observations awaiting the supervisor's next action.
        $todoObservations = (clone $observationsQuery)
            ->with(['observee.user', 'preObservationPlanning', 'preConference', 'postConference'])
            ->withCount('cotRatings')
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->latest('updated_at')
            ->take(5)
            ->get();

        $cotScores = (clone $observationsQuery)
            ->whereNotNull('overall_score')
            ->orderBy('observation_date')
            ->orderBy('id')
            ->pluck('overall_score')
            ->map(fn ($s) => (float) $s)
            ->toArray();

        $cotLabels = (clone $observationsQuery)
            ->whereNotNull('overall_score')
            ->orderBy('observation_date')
            ->orderBy('id')
            ->get()
            ->map(fn ($o, $i) => 'Obs '.($i + 1))
            ->toArray();

        // Trend = overall average vs average of all but the latest scored
        // observation (both share the same deterministic ordering above).
        $prevAvg = count($cotScores) > 1
            ? round(array_sum(array_slice($cotScores, 0, -1)) / (count($cotScores) - 1), 2)
            : null;

        $trend = $prevAvg ? ($stats['average_score'] - round($prevAvg, 2)) : 0;
        $trendLabel = ($trend > 0 ? '+' : '').number_format($trend, 1);

        // Teachers that need the supervisor's attention (dashboard card -> teachers list).
        $attention = $user->school_id
            ? app(TeacherAttentionService::class)->forSchool($user->school_id)
            : [];
        $needsAttention = collect($attention)
            ->filter(fn ($row) => $row['level'] !== 'ok')
            ->sortByDesc('level')
            ->values();

        $user->loadMissing('school');
        $schoolName = $user->school?->name ?? 'Your School';

        // Rating scale ceiling for the supervisor's teachers (6/7/8).
        // Prefers the most common instrument ceiling among the supervisor's
        // scored observations, then the most common career stage in the
        // supervisor's school. Defaults to 6 (Teacher I-III).
        $scaleMax = 6;
        $scoredMaxes = $obsScored->map(fn ($o) => (int) $o->ratingScaleMax())->filter(fn ($v) => $v >= 5 && $v <= 8);
        if ($scoredMaxes->isNotEmpty()) {
            $counts = array_count_values($scoredMaxes->all());
            arsort($counts);
            $scaleMax = (int) array_key_first($counts);
        } elseif ($user->school_id) {
            $topStage = Teacher::whereHas('user', fn ($q) => $q->where('school_id', $user->school_id))
                ->pluck('career_stage')
                ->filter()
                ->countBy()
                ->sortDesc()
                ->keys()
                ->first();
            $scaleMax = match ($topStage) {
                'teacher_iv_vii' => 7,
                'master_teacher_i_ii', 'master_teacher_iii_v' => 8,
                default => 6,
            };
        }

        // Observation groups: teachers in the supervisor's school with their observation files.
        // Note: observations use the observee morph (observee_id/observee_type),
        // so we attach them manually instead of relying on Teacher::observations().
        $schoolGroups = collect();
        if ($user->school_id) {
            $teachers = Teacher::with('user')
                ->whereHas('user', fn ($q) => $q->where('school_id', $user->school_id))
                ->orderBy('id')
                ->get();

            $obsByTeacher = $teachers->isNotEmpty()
                ? Observation::where('observee_type', Teacher::class)
                    ->whereIn('observee_id', $teachers->pluck('id')->all())
                    ->latest('observation_date')
                    ->get()
                    ->groupBy('observee_id')
                : collect();

            foreach ($teachers as $teacher) {
                $teacher->setRelation('observations', $obsByTeacher->get($teacher->id, collect()));
            }

            $schoolGroups = $teachers;
        }

        return view('supervisor.dashboard', compact(
            'stats', 'recentObservations', 'todoObservations', 'cotScores', 'cotLabels', 'trend', 'trendLabel',
            'attention', 'needsAttention', 'schoolName', 'schoolGroups', 'scaleMax'
        ));
    }
}
