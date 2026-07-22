<?php

namespace App\Http\Controllers\SchoolHead;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\Observation;
use App\Models\CoachingAgreement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $schoolId = $user->school_id;

        $teacherIds = Teacher::whereHas('user', fn($q) => $q->where('school_id', $schoolId))
            ->pluck('id');

        $observations = Observation::whereIn('observee_id', $teacherIds)
            ->where('observee_type', Teacher::class);

        $totalObservations = (clone $observations)->count();
        $completedObservations = (clone $observations)->where('status', 'completed')->count();
        $avgScore = (clone $observations)->whereNotNull('overall_score')->avg('overall_score');
        $totalTeachers = $teacherIds->count();
        $activeAgreements = CoachingAgreement::whereHas('teacher', fn($q) => $q->whereIn('id', $teacherIds))
            ->where('status', 'active')->count();

        // Quarterly data
        $quarterlyData = [];
        $currentYear = now()->year;
        for ($q = 1; $q <= 4; $q++) {
            $startMonth = ($q - 1) * 3 + 1;
            $count = (clone $observations)
                ->whereYear('observation_date', $currentYear)
                ->whereRaw('QUARTER(observation_date) = ?', [$q])
                ->count();
            $quarterlyData[] = [
                'quarter' => "Q{$q}",
                'count' => $count,
            ];
        }

        // Recent observations
        $recentObservations = (clone $observations)
            ->with(['observee.user', 'observer'])
            ->latest()
            ->take(10)
            ->get();

        return view('school-head.reports.index', compact(
            'totalObservations',
            'completedObservations',
            'avgScore',
            'totalTeachers',
            'activeAgreements',
            'quarterlyData',
            'recentObservations'
        ));
    }
}
