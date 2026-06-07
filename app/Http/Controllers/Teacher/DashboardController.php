<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Observation;
use App\Models\CotRating;
use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $teacher = Auth::user()->teacher;

        $observationsQuery = Observation::where('observee_id', $teacher->id)
            ->where('observee_type', Teacher::class);

        $stats = [
            'total_observations' => (clone $observationsQuery)->count(),
            'average_cot_score' => round((clone $observationsQuery)->whereNotNull('overall_score')->avg('overall_score') ?? 0, 2),
            'completed' => (clone $observationsQuery)->where('status', 'completed')->count(),
            'upcoming' => (clone $observationsQuery)->where('status', 'scheduled')->count(),
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

        $recentFeedback = null;
        if ($recentObservation && $recentObservation->postConference) {
            $recentFeedback = $recentObservation->postConference;
        }

        return view('teacher.dashboard', compact('stats', 'recentObservation', 'nextObservation', 'cotScores', 'cotLabels', 'recentFeedback'));
    }
}
