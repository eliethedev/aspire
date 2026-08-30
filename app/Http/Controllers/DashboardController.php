<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the main dashboard based on user role.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Redirect to appropriate dashboard based on role
        if ($user->isTeacher()) {
            return $this->teacherDashboard($request);
        } elseif ($user->isSupervisor()) {
            return $this->supervisorDashboard($request);
        } elseif ($user->isSchoolHead()) {
            return $this->schoolHeadDashboard($request);
        } elseif ($user->isAdmin()) {
            return $this->adminDashboard($request);
        }
        
        // Default fallback
        return view('dashboard.default');
    }

    /**
     * Display teacher dashboard.
     */
    private function teacherDashboard(Request $request)
    {
        return app(\App\Http\Controllers\Teacher\DashboardController::class)->index();
    }

    /**
     * Display supervisor dashboard.
     */
    private function supervisorDashboard(Request $request)
    {
        return redirect()->route('supervisor.dashboard');
    }

    /**
     * Display school head dashboard.
     */
    private function schoolHeadDashboard(Request $request)
    {
        $user = Auth::user();
        $schoolHead = $user->schoolHeadProfile;

        if (!$schoolHead) {
            return view('school-head.dashboard', [
                'stats' => ['total' => 0, 'scheduled' => 0, 'pending_confirmation' => 0, 'completed' => 0],
                'nextObservation' => null,
                'recentObservations' => collect(),
                'schoolTeachers' => collect(),
                'cotScores' => [],
                'cotLabels' => [],
                'trend' => 0,
                'avgScore' => 0,
                'teacherCount' => 0,
                'unreadNotifications' => 0,
                'latestFeedbacks' => collect(),
                'coObservationStats' => ['total' => 0, 'upcoming' => 0, 'completed' => 0],
                'recentCoObservations' => collect(),
            ]);
        }

        $observationsQuery = \App\Models\Observation::where('observee_id', $schoolHead->id)
            ->where('observee_type', \App\Models\SchoolHeadProfile::class)
            ->with(['observer', 'preObservationPlanning', 'postConference']);

        $obs = (clone $observationsQuery)->get();

        $completedObs = $obs->where('status', 'completed')->whereNotNull('overall_score');

        $stats = [
            'total' => $obs->count(),
            'scheduled' => $obs->where('status', 'scheduled')->count(),
            'completed' => $completedObs->count(),
            'in_progress' => $obs->where('status', 'in_progress')->count(),
            'pending_confirmation' => $obs->where('confirmation_status', 'pending')
                ->where('stage', 'pre_observation_planning')
                ->where('status', '!=', 'cancelled')
                ->count(),
        ];

        $avgScore = round($completedObs->avg('overall_score') ?? 0, 2);

        $prevAvg = (clone $observationsQuery)
            ->whereNotNull('overall_score')
            ->orderBy('observation_date')
            ->take(max(count($completedObs) - 1, 1))
            ->avg('overall_score');
        $trend = $prevAvg ? round($avgScore - round($prevAvg, 2), 2) : 0;

        $nextObservation = (clone $observationsQuery)
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->oldest('observation_date')
            ->first();

        $recentObservations = (clone $observationsQuery)
            ->latest()
            ->take(5)
            ->get();

        $cotScores = $completedObs->sortBy('observation_date')->pluck('overall_score')->toArray();
        $cotLabels = $completedObs->sortBy('observation_date')
            ->map(fn($o) => 'Obs ' . ($o->getKey()) . ' - ' . $o->observation_date->format('M d'))
            ->values()->toArray();

        $schoolTeachers = collect();
        $teacherCount = 0;
        if ($schoolHead->school_id) {
            $schoolTeachers = \App\Models\Teacher::where('school_id', $schoolHead->school_id)
                ->with('user', 'subjects')
                ->get();
            $teacherCount = $schoolTeachers->count();
        }

        $unreadNotifications = $user->unreadNotifications()->count();

        $latestFeedbacks = \App\Models\AiFeedback::whereIn('observation_id', $obs->pluck('id'))
            ->where('status', 'published')
            ->latest()
            ->take(4)
            ->get();

        $coObservations = \App\Models\Observation::where('school_head_id', $user->id)
            ->with(['observee.user', 'observer'])
            ->get();

        $coObservationStats = [
            'total' => $coObservations->count(),
            'upcoming' => $coObservations
                ->whereIn('status', ['scheduled', 'in_progress'])
                ->where('status', '!=', 'cancelled')
                ->count(),
            'completed' => $coObservations->where('status', 'completed')->count(),
        ];

        $recentCoObservations = $coObservations
            ->sortByDesc('observation_date')
            ->take(5)
            ->values();

        return view('school-head.dashboard', compact(
            'stats', 'nextObservation', 'recentObservations', 'schoolTeachers',
            'cotScores', 'cotLabels', 'trend', 'avgScore', 'teacherCount',
            'unreadNotifications', 'latestFeedbacks',
            'coObservationStats', 'recentCoObservations'
        ));
    }

    /**
     * Display admin dashboard.
     */
    private function adminDashboard(Request $request)
    {
        // Get actual statistics from database
        $stats = [
            'total_users' => \App\Models\User::count(),
            'total_schools' => \App\Models\School::where('is_active', true)->count(),
            'total_observations' => \App\Models\Observation::count(),
            'pending_cots' => \App\Models\Observation::where('stage', '!=', 'post_conference')->count(),
            'active_observations' => \App\Models\Observation::whereIn('stage', ['pre_observation_planning', 'pre_conference', 'observation'])->count(),
        ];

        // Performance summary
        $performance = [
            'average_cot_score' => \App\Models\CotRating::avg('rating') ?? 0,
            'teacher_growth_trend' => $this->calculateTeacherGrowthTrend(),
            'completed_observations_this_month' => \App\Models\Observation::where('stage', 'post_conference')
                ->whereMonth('created_at', now()->month)
                ->count(),
        ];

        // Get recent activity
        $recentActivity = [
            'latest_users' => \App\Models\User::latest()->take(5)->get(),
            'latest_observations' => \App\Models\Observation::with(['observee.user', 'observee.school'])
                ->latest()
                ->take(5)
                ->get(),
            'latest_schools' => \App\Models\School::latest()->take(3)->get(),
        ];

        // Get system status (basic checks)
        $systemStatus = [
            'database' => $this->checkDatabaseStatus(),
            'api_services' => 'Operational',
            'email_service' => $this->checkEmailService(),
            'file_storage' => $this->checkFileStorage(),
            'ai_processing' => 'Offline',
            'server_usage' => $this->getServerUsage(),
        ];

        return view('admin.dashboard', compact('stats', 'performance', 'recentActivity', 'systemStatus'));
    }

    /**
     * Calculate teacher growth trend based on recent observations
     */
    private function calculateTeacherGrowthTrend(): string
    {
        $thisMonthAvg = \App\Models\Observation::where('stage', 'post_conference')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->avg('overall_score') ?? 0;

        $lastMonthAvg = \App\Models\Observation::where('stage', 'post_conference')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->avg('overall_score') ?? 0;

        if ($thisMonthAvg > $lastMonthAvg) {
            return 'improving';
        } elseif ($thisMonthAvg < $lastMonthAvg) {
            return 'declining';
        } else {
            return 'stable';
        }
    }

    /**
     * Check database connection status
     */
    private function checkDatabaseStatus(): string
    {
        try {
            \Illuminate\Support\Facades\DB::connection()->getPdo();
            return 'Connected';
        } catch (\Exception $e) {
            return 'Disconnected';
        }
    }

    /**
     * Check email service status
     */
    private function checkEmailService(): string
    {
        // Check if mail configuration is set
        return config('mail.mailers.smtp.host') ? 'Operational' : 'Not Configured';
    }

    /**
     * Check file storage status
     */
    private function checkFileStorage(): string
    {
        try {
            \Illuminate\Support\Facades\Storage::disk('public')->exists('.');
            return 'Operational';
        } catch (\Exception $e) {
            return 'Error';
        }
    }

    /**
     * Get server usage metrics
     */
    private function getServerUsage(): array
    {
        return [
            'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
            'memory_peak' => round(memory_get_peak_usage(true) / 1024 / 1024, 2) . ' MB',
            'load_average' => function_exists('sys_getloadavg') ? sys_getloadavg()[0] ?? 0 : 0,
        ];
    }
}
