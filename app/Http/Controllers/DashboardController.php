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
        $teacher = $request->user()->teacher;

        if (!$teacher) {
            return redirect()->route('profile.edit')->with('error', 'Teacher profile not found. Please complete your profile.');
        }

        // Get teacher statistics (mock data for now)
        $stats = [
            'total_observations' => $teacher->observations()->count() ?? 0,
            'completed_observations' => $teacher->observations()->completed()->count() ?? 0,
            'average_cot_score' => 3.8, // Mock data
            'pending_feedback_count' => 2, // Mock data
        ];

        return view('teacher.dashboard', compact('stats'));
    }

    /**
     * Display supervisor dashboard.
     */
    private function supervisorDashboard(Request $request)
    {
        return view('supervisor.dashboard');
    }

    /**
     * Display school head dashboard.
     */
    private function schoolHeadDashboard(Request $request)
    {
        return view('school-head.dashboard');
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
            'latest_observations' => \App\Models\Observation::with(['teacher.user', 'teacher.school'])
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
