<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\School;
use App\Models\Observation;
use App\Models\CotRating;
use App\Models\CoachingAgreement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // User stats by role
        $totalUsers = User::count();
        $totalTeachers = User::where('role', 'teacher')->count();
        $totalSupervisors = User::where('role', 'supervisor')->count();
        $totalSchoolHeads = User::where('role', 'school_head')->count();
        $totalSchools = School::where('is_active', true)->count();

        // Observation stats
        $observations = Observation::query();
        $totalObservations = (clone $observations)->count();
        $completedObservations = (clone $observations)->where('status', 'completed')->count();
        $scheduledObservations = (clone $observations)->where('status', 'scheduled')->count();
        $inProgressObservations = (clone $observations)->where('status', 'in_progress')->count();

        // Scores
        $avgCotScore = CotRating::whereNotNull('rating')->avg('rating');
        $avgOverallScore = (clone $observations)->whereNotNull('overall_score')->avg('overall_score');

        // Coaching
        $activeAgreements = CoachingAgreement::where('status', 'active')->count();
        $totalAgreements = CoachingAgreement::count();

        // Observations by status (for pie chart)
        $statusBreakdown = (clone $observations)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        // Observations by school (for bar chart)
        $schoolStats = School::where('is_active', true)
            ->withCount(['users as teachers_count' => fn($q) => $q->where('role', 'teacher')])
            ->get()
            ->map(function ($school) {
                $teacherIds = $school->users()->where('role', 'teacher')->pluck('users.id');
                $observationCount = Observation::whereIn('observee_id', $teacherIds)
                    ->where('observee_type', \App\Models\Teacher::class)
                    ->count();
                $completedCount = Observation::whereIn('observee_id', $teacherIds)
                    ->where('observee_type', \App\Models\Teacher::class)
                    ->where('status', 'completed')
                    ->count();
                $avgScore = Observation::whereIn('observee_id', $teacherIds)
                    ->where('observee_type', \App\Models\Teacher::class)
                    ->whereNotNull('overall_score')
                    ->avg('overall_score');

                return [
                    'name' => $school->name,
                    'teachers_count' => $school->teachers_count,
                    'observations_count' => $observationCount,
                    'completed_count' => $completedCount,
                    'avg_score' => $avgScore ? round($avgScore, 2) : null,
                ];
            })
            ->sortByDesc('observations_count')
            ->values();

        // Monthly observation trend (last 6 months)
        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $count = Observation::whereYear('observation_date', $date->year)
                ->whereMonth('observation_date', $date->month)
                ->count();
            $completed = Observation::whereYear('observation_date', $date->year)
                ->whereMonth('observation_date', $date->month)
                ->where('status', 'completed')
                ->count();
            $monthlyTrend[] = [
                'month' => $date->format('M Y'),
                'total' => $count,
                'completed' => $completed,
            ];
        }

        // COT domain averages
        $domainAverages = CotRating::whereNotNull('rating')
            ->select('domain', DB::raw('avg(rating) as avg_rating'), DB::raw('count(*) as count'))
            ->groupBy('domain')
            ->having('count', '>=', 3)
            ->orderByDesc('avg_rating')
            ->get();

        // Top performing teachers (by avg score, min 2 observations)
        $topTeachers = User::where('role', 'teacher')
            ->with('teacher')
            ->get()
            ->map(function ($user) {
                $score = Observation::where('observee_id', $user->teacher?->id)
                    ->where('observee_type', \App\Models\Teacher::class)
                    ->whereNotNull('overall_score')
                    ->avg('overall_score');
                $obsCount = Observation::where('observee_id', $user->teacher?->id)
                    ->where('observee_type', \App\Models\Teacher::class)
                    ->count();
                return [
                    'name' => $user->name,
                    'school' => $user->school?->name ?? 'N/A',
                    'avg_score' => $score ? round($score, 2) : null,
                    'observations' => $obsCount,
                ];
            })
            ->filter(fn($t) => $t['observations'] >= 2 && $t['avg_score'] !== null)
            ->sortByDesc('avg_score')
            ->take(10)
            ->values();

        // Recent activity
        $recentObservations = Observation::with(['observee', 'observer'])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.reports.index', compact(
            'totalUsers',
            'totalTeachers',
            'totalSupervisors',
            'totalSchoolHeads',
            'totalSchools',
            'totalObservations',
            'completedObservations',
            'scheduledObservations',
            'inProgressObservations',
            'avgCotScore',
            'avgOverallScore',
            'activeAgreements',
            'totalAgreements',
            'statusBreakdown',
            'schoolStats',
            'monthlyTrend',
            'domainAverages',
            'topTeachers',
            'recentObservations'
        ));
    }
}
