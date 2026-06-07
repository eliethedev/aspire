<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\School;
use App\Models\Observation;
use App\Models\CotRating;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    public function index()
    {
        // Get system statistics
        $stats = [
            'total_users' => User::count(),
            'total_schools' => School::where('is_active', true)->count(),
            'total_observations' => Observation::count(),
            'pending_cots' => Observation::where('stage', '!=', 'post_conference')->count(),
            'active_observations' => Observation::whereIn('stage', ['pre_observation_planning', 'pre_conference', 'observation'])->count(),
        ];

        // Performance summary
        $performance = [
            'average_cot_score' => CotRating::avg('rating') ?? 0,
            'teacher_growth_trend' => $this->calculateTeacherGrowthTrend(),
            'completed_observations_this_month' => Observation::where('stage', 'post_conference')
                ->whereMonth('created_at', now()->month)
                ->count(),
        ];

        // Get recent activity
        $recentActivity = [
            'latest_users' => User::latest()->take(5)->get(),
            'latest_observations' => Observation::with(['observee.user', 'observee.school'])
                ->latest()
                ->take(5)
                ->get(),
            'latest_schools' => School::latest()->take(3)->get(),
        ];

        // System status
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
        $thisMonthAvg = Observation::where('stage', 'post_conference')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->avg('overall_score') ?? 0;

        $lastMonthAvg = Observation::where('stage', 'post_conference')
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
            DB::connection()->getPdo();
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
            Storage::disk('public')->exists('.');
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