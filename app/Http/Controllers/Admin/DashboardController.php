<?php

namespace App\Http\Controllers\Admin;

use App\AI\Providers\AIProviderManager;
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\CotRating;
use App\Models\Invitation;
use App\Models\Observation;
use App\Models\School;
use App\Models\SupportMessage;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    public function index()
    {
        $roleCounts = User::selectRaw('role, COUNT(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');

        $totalObservations = Observation::count();
        $completedTotal = Observation::where('stage', 'post_conference')->count();

        // Get system statistics
        $stats = [
            'total_users' => User::count(),
            'total_schools' => School::where('is_active', true)->count(),
            'total_observations' => $totalObservations,
            'pending_cots' => Observation::where('stage', '!=', 'post_conference')->count(),
            'active_observations' => Observation::whereIn('stage', ['pre_observation_planning', 'pre_conference', 'observation'])->count(),
            'completed_total' => $completedTotal,
            'completion_rate' => $totalObservations > 0 ? round(($completedTotal / $totalObservations) * 100) : 0,
            'total_admins' => (int) ($roleCounts['admin'] ?? 0),
            'total_supervisors' => (int) ($roleCounts['supervisor'] ?? 0),
            'total_school_heads' => (int) ($roleCounts['school_head'] ?? 0),
            'total_teachers' => (int) ($roleCounts['teacher'] ?? 0),
            'open_support' => SupportMessage::where('status', SupportMessage::STATUS_OPEN)->count(),
            'total_announcements' => Announcement::count(),
            'pending_invitations' => Invitation::pending()->count(),
        ];

        // Performance summary
        $performance = [
            'average_cot_score' => CotRating::avg('rating') ?? 0,
            'teacher_growth_trend' => $this->calculateTeacherGrowthTrend(),
            'completed_observations_this_month' => Observation::where('stage', 'post_conference')
                ->whereMonth('created_at', now()->month)
                ->count(),
            'score_scale_max' => $this->globalScaleMax(),
        ];

        // Get recent activity from audit logs
        $recentAuditLogs = AuditLog::with('user')
            ->latest()
            ->take(10)
            ->get();

        // Latest observations across the system
        $recentObservations = Observation::with(['observee', 'teacher.user', 'school'])
            ->latest()
            ->take(6)
            ->get();

        // Top schools by observation volume
        $topSchools = $this->topSchools();

        // System status
        $systemStatus = $this->runStatusChecks();

        // Chart data for dashboard visualizations
        $charts = [
            'monthly' => $this->monthlyObservations(),
            'scores' => $this->monthlyAverageScores(),
            'byStatus' => $this->observationsByStatus(),
            'usersByRole' => $this->usersByRole(),
            'ratingBands' => $this->ratingBandDistribution(),
            'scoreMax' => $this->globalScaleMax(),
        ];

        // Observation groups: schools -> latest observation files.
        $schoolFolders = School::where('is_active', true)
            ->orderBy('name')
            ->take(6)
            ->get()
            ->map(fn ($school) => [
                'school' => $school,
                'observations' => Observation::with(['observee', 'teacher.user'])
                    ->where('school_id', $school->id)
                    ->latest()
                    ->take(3)
                    ->get(),
            ]);

        return view('admin.dashboard', compact('stats', 'performance', 'recentAuditLogs', 'recentObservations', 'topSchools', 'systemStatus', 'charts', 'schoolFolders'));
    }

    public function systemStatus(): JsonResponse
    {
        $checks = $this->runStatusChecks();

        return response()->json($checks);
    }

    protected function runStatusChecks(): array
    {
        return [
            'database' => $this->checkDatabaseStatus(),
            'api_services' => $this->checkApiStatus(),
            'email_service' => $this->checkEmailService(),
            'file_storage' => $this->checkFileStorage(),
            'ai_processing' => $this->checkAiStatus(),
            'server_usage' => $this->getServerUsage(),
            'checked_at' => now()->toIso8601String(),
        ];
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
     * Check API / routing services status.
     * Uses direct service checks instead of a self-request,
     * which is unreliable on XAMPP / local dev servers.
     */
    private function checkApiStatus(): string
    {
        try {
            // If the page loaded and this method is running, routing works.
            // Verify the app can still resolve routes and respond.
            $route = \Illuminate\Support\Facades\Route::getRoutes();
            if (! $route || $route->count() === 0) {
                return 'Degraded';
            }

            // Verify the key HTTP-related services are registered
            if (! app()->bound('url') || ! app()->bound('router')) {
                return 'Degraded';
            }

            return 'Operational';
        } catch (\Exception $e) {
            return 'Degraded';
        }
    }

    /**
     * Check AI processing status
     */
    private function checkAiStatus(): string
    {
        if (! config('ai.enabled', true)) {
            return 'Disabled';
        }

        try {
            $manager = app(AIProviderManager::class);
            $chain = $manager->fallbackChain('pre_observation');

            if ($chain === []) {
                return 'No Provider';
            }

            $primary = $chain[0]['provider'] ?? 'unknown';
            $model = $chain[0]['model'] ?? '';

            return 'Online (' . $primary . ')';
        } catch (\Exception $e) {
            return 'Error';
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

    /**
     * Observations created per month for the last 6 months.
     */
    private function monthlyObservations(): array
    {
        $labels = [];
        $counts = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = now()->subMonths($i);
            $labels[] = $d->format('M Y');
            $counts[] = Observation::whereYear('created_at', $d->year)
                ->whereMonth('created_at', $d->month)
                ->count();
        }

        return ['labels' => $labels, 'counts' => $counts];
    }

    /**
     * Average overall observation score per month for the last 6 months.
     */
    private function monthlyAverageScores(): array
    {
        $averages = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = now()->subMonths($i);
            $avg = Observation::whereYear('created_at', $d->year)
                ->whereMonth('created_at', $d->month)
                ->whereNotNull('overall_score')
                ->avg('overall_score');
            $averages[] = $avg !== null ? round((float) $avg, 2) : null;
        }

        return $averages;
    }

    /**
     * Observation counts grouped by status.
     */
    private function observationsByStatus(): array
    {
        $rows = Observation::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $labels = [];
        $counts = [];
        foreach (['pending', 'scheduled', 'in_progress', 'cot_completed', 'completed', 'cancelled'] as $status) {
            if (isset($rows[$status]) && (int) $rows[$status] > 0) {
                $labels[] = ucwords(str_replace('_', ' ', $status));
                $counts[] = (int) $rows[$status];
            }
        }

        return ['labels' => $labels, 'counts' => $counts];
    }

    /**
     * User counts grouped by role.
     */
    private function usersByRole(): array
    {
        $rows = User::selectRaw('role, COUNT(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');

        $labels = [];
        $counts = [];
        foreach (['admin', 'supervisor', 'school_head', 'teacher'] as $role) {
            $labels[] = ucwords(str_replace('_', ' ', $role));
            $counts[] = (int) ($rows[$role] ?? 0);
        }

        return ['labels' => $labels, 'counts' => $counts];
    }

    /**
     * Highest rating-scale ceiling across all COT instruments (2-6, 3-7, 4-8).
     * Used as the chart axis max so averages are never misread as "out of 7".
     */
    private function globalScaleMax(): int
    {
        $max = 6;
        foreach (config('cot.versions', []) as $yearVersions) {
            foreach ((array) $yearVersions as $version) {
                $keys = array_keys($version['rating_scale'] ?? []);
                if ($keys !== []) {
                    $max = max($max, max($keys));
                }
            }
        }

        return $max;
    }

    /**
     * Distribution of scored observations across DepEd adjectival bands.
     * Each observation is banded against its own instrument ceiling so
     * mixed career stages stay comparable.
     */
    private function ratingBandDistribution(): array
    {
        $labels = ['Outstanding', 'Very Satisfactory', 'Satisfactory', 'Unsatisfactory', 'Poor'];
        $counts = array_fill_keys($labels, 0);

        Observation::with('cotIndicatorVersion')
            ->whereNotNull('overall_score')
            ->select(['id', 'overall_score', 'cot_indicator_version_id'])
            ->chunk(500, function ($observations) use (&$counts) {
                foreach ($observations as $observation) {
                    $band = CotRating::descriptiveTotal(
                        (float) $observation->overall_score,
                        (float) $observation->ratingScaleMax()
                    );
                    // Normalize legacy "Needs Improvement" into the Poor band.
                    if ($band === 'Needs Improvement') {
                        $band = 'Poor';
                    }
                    if (array_key_exists($band, $counts)) {
                        $counts[$band]++;
                    }
                }
            });

        return ['labels' => $labels, 'counts' => array_values($counts)];
    }

    /**
     * Top schools by observation volume with average score.
     */
    private function topSchools(): array
    {
        $rows = Observation::selectRaw('school_id, COUNT(*) as total, AVG(overall_score) as avg_score')
            ->whereNotNull('school_id')
            ->groupBy('school_id')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        if ($rows->isEmpty()) {
            return [];
        }

        $names = School::whereIn('id', $rows->pluck('school_id'))->pluck('name', 'id');

        return $rows->map(fn ($row) => [
            'name' => $names[$row->school_id] ?? 'Unknown school',
            'total' => (int) $row->total,
            'avg_score' => $row->avg_score !== null ? round((float) $row->avg_score, 2) : null,
        ])->all();
    }
}