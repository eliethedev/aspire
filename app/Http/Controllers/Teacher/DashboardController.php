<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\CotRating;
use App\Models\Observation;
use App\Models\Teacher;
use App\Models\PreObservationPlanning;
use App\Models\PreConference;
use App\Models\PostConference;
use App\Models\AiFeedback;
use App\Services\CotIndicatorService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $teacher = Auth::user()->teacher;

        $observationsQuery = Observation::where('observee_id', $teacher->id)
            ->where('observee_type', Teacher::class)
            ->with(['preObservationPlanning', 'preConference', 'postConference']);

        $obs = (clone $observationsQuery)->get();

        $stats = [
            'total' => $obs->count(),
            'average_cot_score' => round($obs->whereNotNull('overall_score')->avg('overall_score') ?? 0, 2),
            'completed' => $obs->where('status', 'completed')->count(),
            'in_progress' => $obs->where('status', 'in_progress')->count(),
            'scheduled' => $obs->where('status', 'scheduled')->count(),
            'stage_pre_planning' => $obs->where('stage', 'pre_observation_planning')->count(),
            'stage_pre_conference' => $obs->where('stage', 'pre_conference')->count(),
            'stage_observation' => $obs->where('stage', 'observation')->count(),
            'stage_post_conference' => $obs->where('stage', 'post_conference')->count(),
            'pending_confirmation' => $obs->where('confirmation_status', 'pending')
                ->where('stage', 'pre_observation_planning')
                ->where('status', '!=', 'cancelled')
                ->count(),
        ];

        $recentObservation = (clone $observationsQuery)
            ->with('observer')
            ->whereNotNull('overall_score')
            ->latest('observation_date')
            ->first();

        $nextObservation = (clone $observationsQuery)
            ->with('observer')
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->oldest('observation_date')
            ->first();

        $cotScores = (clone $observationsQuery)
            ->whereNotNull('overall_score')
            ->orderBy('observation_date')
            ->pluck('overall_score')
            ->toArray();

        $cotLabels = (clone $observationsQuery)
            ->whereNotNull('overall_score')
            ->orderBy('observation_date')
            ->get()
            ->map(fn($o, $i) => 'Obs ' . ($i + 1) . ' - ' . $o->observation_date->format('M d'))
            ->toArray();

        $prevAvg = (clone $observationsQuery)
            ->whereNotNull('overall_score')
            ->orderBy('observation_date')
            ->take(max(count($cotScores) - 1, 1))
            ->avg('overall_score');

        $trend = $prevAvg ? ($stats['average_cot_score'] - round($prevAvg, 2)) : 0;

        $recentFeedback = null;
        if ($recentObservation && $recentObservation->relationLoaded('postConference') && $recentObservation->postConference) {
            $recentFeedback = $recentObservation->postConference;
        }

        $latestFeedbacks = AiFeedback::whereIn('observation_id', $obs->pluck('id'))
            ->where('status', 'published')
            ->latest()
            ->take(4)
            ->get();

        // Focus observation: the current schedule takes priority (nearest
        // upcoming scheduled/in-progress), otherwise the latest scored one,
        // otherwise the latest observation overall. Cycle Progress reflects
        // THIS observation only — not an aggregate of all observations.
        $focusObservation = $nextObservation
            ?? $recentObservation
            ?? (clone $observationsQuery)->with('observer')->latest('observation_date')->first();

        if ($focusObservation) {
            $focusObservation->loadCount('cotRatings');
        }

        // "My review" workflow: 3 teacher-facing phases — Pre-Observation
        // (lesson planning + pre-conference chat), Observation (classroom
        // visit), Post Observation (feedback + next steps). Completion is
        // based on the focus observation only.
        $hasPlanning = $focusObservation && (bool) $focusObservation->preObservationPlanning;
        $hasPreConference = $focusObservation && (bool) $focusObservation->preConference;
        $hasRatings = $focusObservation && ($focusObservation->cot_ratings_count ?? 0) > 0;
        $hasPostConference = $focusObservation && (bool) $focusObservation->postConference;

        $stageLabels = [
            'pre_observation' => 'Pre-Observation',
            'observation' => 'Observation',
            'post_observation' => 'Post Observation',
        ];

        $stageIcons = [
            'pre_observation' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            'observation' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z',
            'post_observation' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        ];

        $stageDone = [
            'pre_observation' => $hasPlanning && $hasPreConference,
            'observation' => $hasRatings,
            'post_observation' => $hasPostConference,
        ];

        $stageStatus = [];
        foreach ($stageLabels as $key => $label) {
            $stageStatus[$key] = [
                'done' => $stageDone[$key],
                'label' => $label,
                'icon' => $stageIcons[$key],
            ];
        }

        // Observation groups: the teacher's own observation files, newest first.
        $observations = $obs->sortByDesc(fn ($o) => $o->observation_date?->timestamp ?? 0)->values();

        // Rating scale ceiling for this teacher's instrument.
        // Teacher I-III => 6.00, Teacher IV-VII => 7.00, Master Teacher => 8.00.
        // Resolved from the teacher's pinned COT version so the dashboard
        // denominator never shows 7.0 for a 6-point teacher.
        $scaleMax = 6;
        $scaleMin = 2;
        try {
            $version = app(CotIndicatorService::class)->getVersionForObservee($teacher);
            $keys = array_keys($version['rating_scale'] ?? []);
            if ($keys !== []) {
                $scaleMax = (int) max($keys);
                $scaleMin = (int) min($keys);
            } else {
                $scaleMax = match ($teacher->career_stage) {
                    'teacher_iv_vii' => 7,
                    'master_teacher_i_ii', 'master_teacher_iii_v' => 8,
                    default => 6,
                };
            }
        } catch (\Throwable) {
            $scaleMax = match ($teacher->career_stage) {
                'teacher_iv_vii' => 7,
                'master_teacher_i_ii', 'master_teacher_iii_v' => 8,
                default => 6,
            };
        }

        return view('teacher.dashboard', compact(
            'stats', 'recentObservation', 'nextObservation', 'focusObservation', 'cotScores', 'cotLabels',
            'recentFeedback', 'latestFeedbacks', 'trend', 'stageStatus', 'observations', 'scaleMax', 'scaleMin'
        ));
    }

    /**
     * Personal analytics for the logged-in teacher: monthly activity and
     * score trends, COT rating distribution, domain averages, and their
     * strongest / weakest indicators.
     */
    public function analytics()
    {
        $teacher = Auth::user()->teacher;

        $baseQuery = Observation::where('observee_id', $teacher->id)
            ->where('observee_type', Teacher::class)
            ->where('status', '!=', 'cancelled');

        $all = (clone $baseQuery)->get();

        $stats = [
            'total' => $all->count(),
            'completed' => $all->where('status', 'completed')->count(),
            'average_score' => round((float) ($all->whereNotNull('overall_score')->avg('overall_score') ?? 0), 2),
            'best_score' => ($best = $all->max('overall_score')) !== null ? round((float) $best, 2) : null,
        ];

        $windowStart = now()->subMonths(11)->startOfMonth()->toDateString();
        $windowEnd = now()->endOfMonth()->toDateString();
        $months = collect(range(11, 0))->map(fn ($i) => now()->subMonths($i));
        $monthlyLabels = $months->map(fn ($date) => $date->format('M Y'))->values();

        $counts = (clone $baseQuery)
            ->whereBetween('observation_date', [$windowStart, $windowEnd])
            ->get(['observation_date', 'created_at'])
            ->groupBy(fn (Observation $observation) => ($observation->observation_date ?? $observation->created_at)?->format('Y-m'))
            ->map->count();
        $monthlyCounts = $months->map(fn ($date) => (int) ($counts->get($date->format('Y-m'), 0)))->values();

        $averages = (clone $baseQuery)
            ->whereNotNull('overall_score')
            ->whereBetween('observation_date', [$windowStart, $windowEnd])
            ->get(['observation_date', 'overall_score'])
            ->groupBy(fn (Observation $observation) => $observation->observation_date?->format('Y-m'))
            ->map(fn ($group) => round((float) $group->avg('overall_score'), 2));
        $monthlyAverages = $months->map(fn ($date) => $averages->get($date->format('Y-m')))->values();

        $distributionRows = CotRating::whereHas('observation', fn ($query) => $query
            ->where('observee_id', $teacher->id)
            ->where('observee_type', Teacher::class))
            ->selectRaw('rating, not_observed, COUNT(*) as total')
            ->groupBy('rating', 'not_observed')
            ->get();
        $distribution = [
            'labels' => ['Poor (2)', 'Unsatisfactory (3)', 'Satisfactory (4)', 'Very Sat. (5)', 'Outstanding (6)', 'Not Observed'],
            'counts' => [
                (int) ($distributionRows->firstWhere(fn ($row) => (int) $row->rating === 2 && ! $row->not_observed)->total ?? 0),
                (int) ($distributionRows->firstWhere(fn ($row) => (int) $row->rating === 3 && ! $row->not_observed)->total ?? 0),
                (int) ($distributionRows->firstWhere(fn ($row) => (int) $row->rating === 4 && ! $row->not_observed)->total ?? 0),
                (int) ($distributionRows->firstWhere(fn ($row) => (int) $row->rating === 5 && ! $row->not_observed)->total ?? 0),
                (int) ($distributionRows->firstWhere(fn ($row) => (int) $row->rating === 6 && ! $row->not_observed)->total ?? 0),
                (int) ($distributionRows->firstWhere(fn ($row) => (bool) $row->not_observed)->total ?? 0),
            ],
        ];

        $domainAverages = CotRating::whereHas('observation', fn ($query) => $query
            ->where('observee_id', $teacher->id)
            ->where('observee_type', Teacher::class))
            ->where('not_observed', false)
            ->where('not_applicable', false)
            ->whereNotNull('domain')
            ->selectRaw('domain, AVG(rating) as average, COUNT(*) as total')
            ->groupBy('domain')
            ->orderByDesc('average')
            ->get()
            ->map(fn ($row) => [
                'domain' => $row->domain,
                'average' => round((float) $row->average, 2),
                'total' => (int) $row->total,
            ]);

        $indicatorStats = CotRating::whereHas('observation', fn ($query) => $query
            ->where('observee_id', $teacher->id)
            ->where('observee_type', Teacher::class))
            ->where('not_observed', false)
            ->where('not_applicable', false)
            ->selectRaw('indicator_code, MAX(indicator) as indicator, AVG(rating) as average, COUNT(*) as total')
            ->groupBy('indicator_code')
            ->havingRaw('COUNT(*) > 0')
            ->get()
            ->map(fn ($row) => [
                'code' => $row->indicator_code,
                'indicator' => $row->indicator,
                'average' => round((float) $row->average, 2),
                'total' => (int) $row->total,
            ]);

        $strengths = $indicatorStats->filter(fn ($row) => $row['average'] >= 4.5)->sortByDesc('average')->take(5)->values();
        $weaknesses = $indicatorStats->filter(fn ($row) => $row['average'] <= 3.5)->sortBy('average')->take(5)->values();

        $statusCounts = Observation::where('observee_id', $teacher->id)
            ->where('observee_type', Teacher::class)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $scaleMax = 6;
        $scaleMin = 2;
        try {
            $version = app(CotIndicatorService::class)->getVersionForObservee($teacher);
            $keys = array_keys($version['rating_scale'] ?? []);
            if ($keys !== []) {
                $scaleMax = (int) max($keys);
                $scaleMin = (int) min($keys);
            } else {
                $scaleMax = match ($teacher->career_stage) {
                    'teacher_iv_vii' => 7,
                    'master_teacher_i_ii', 'master_teacher_iii_v' => 8,
                    default => 6,
                };
            }
        } catch (\Throwable) {
            $scaleMax = match ($teacher->career_stage) {
                'teacher_iv_vii' => 7,
                'master_teacher_i_ii', 'master_teacher_iii_v' => 8,
                default => 6,
            };
        }

        return view('teacher.analytics', compact(
            'stats', 'monthlyLabels', 'monthlyCounts', 'monthlyAverages',
            'distribution', 'domainAverages', 'strengths', 'weaknesses', 'statusCounts',
            'scaleMax', 'scaleMin'
        ));
    }
}
