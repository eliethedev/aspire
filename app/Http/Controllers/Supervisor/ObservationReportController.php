<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\CotRating;
use App\Models\Observation;
use App\Models\Teacher;
use App\Models\User;
use App\Services\CotDocumentService;
use App\Services\IndicatorTrendService;
use App\Services\ObservationComparisonService;
use App\Services\ObservationReportService;
use App\Services\PDFReportService;
use App\Services\ProfessionalDevelopmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ObservationReportController extends Controller
{
    use Concerns\AuthorizesObservations;

    /**
     * Display reports hub. Three tabs share this route:
     * overview (default), analytics, performance.
     */
    public function reports(Request $request)
    {
        $user = Auth::user();

        $view = $request->view ?? 'overview';
        if (! in_array($view, ['overview', 'analytics', 'performance'], true)) {
            $view = 'overview';
        }

        $data = match ($view) {
            'analytics' => ['view' => $view] + $this->reportsAnalyticsData($user),
            'performance' => ['view' => $view] + $this->reportsPerformanceData($user),
            default => ['view' => 'overview'] + $this->reportsOverviewData($user),
        };

        return view('supervisor.reports.index', $data);
    }

    /**
     * Data for the Overview tab: headline stats, recent activity, simple trends.
     */
    private function reportsOverviewData(User $user): array
    {
        $baseQuery = Observation::where('observer_id', $user->id);

        $stats = [
            'total_teachers' => Teacher::whereHas('user', fn ($query) => $query->where('school_id', $user->school_id))->count(),
            'total_observations' => (clone $baseQuery)->count(),
            'completed_observations' => (clone $baseQuery)->where('status', 'completed')->count(),
            'in_progress_observations' => (clone $baseQuery)
                ->whereIn('stage', ['pre_observation_planning', 'observation'])
                ->where('status', '!=', 'cancelled')
                ->count(),
        ];

        $recentObservations = Observation::with('observee.user')
            ->where('observer_id', $user->id)
            ->latest()
            ->take(8)
            ->get();

        $chartData = (clone $baseQuery)
            ->get(['created_at'])
            ->groupBy(fn (Observation $observation) => $observation->created_at?->format('Y-m'))
            ->sortKeys()
            ->map->count();

        $scoresData = (clone $baseQuery)
            ->whereNotNull('overall_score')
            ->latest('observation_date')
            ->take(10)
            ->get()
            ->reverse()
            ->values();

        return compact('stats', 'recentObservations', 'chartData', 'scoresData');
    }

    /**
     * Data for the Analytics tab: monthly activity/score trends, COT rating
     * distribution, domain averages, strongest/weakest indicators.
     */
    private function reportsAnalyticsData(User $user): array
    {
        $windowStart = now()->subMonths(11)->startOfMonth()->toDateString();
        $windowEnd = now()->endOfMonth()->toDateString();

        $months = collect(range(11, 0))->map(fn ($i) => now()->subMonths($i));

        $monthlyLabels = $months->map(fn ($date) => $date->format('M Y'))->values();

        $countRows = Observation::where('observer_id', $user->id)
            ->where('status', '!=', 'cancelled')
            ->whereBetween('observation_date', [$windowStart, $windowEnd])
            ->get(['observation_date', 'created_at']);
        $counts = $countRows
            ->groupBy(fn (Observation $observation) => ($observation->observation_date ?? $observation->created_at)?->format('Y-m'))
            ->map->count();
        $monthlyCounts = $months->map(fn ($date) => (int) ($counts->get($date->format('Y-m'), 0)))->values();

        $averageRows = Observation::where('observer_id', $user->id)
            ->whereNotNull('overall_score')
            ->whereBetween('observation_date', [$windowStart, $windowEnd])
            ->get(['observation_date', 'overall_score']);
        $averages = $averageRows
            ->groupBy(fn (Observation $observation) => $observation->observation_date?->format('Y-m'))
            ->map(fn ($group) => round((float) $group->avg('overall_score'), 2));
        $monthlyAverages = $months->map(fn ($date) => $averages->get($date->format('Y-m')))->values();

        $distributionRows = CotRating::whereHas('observation', fn ($query) => $query->where('observer_id', $user->id))
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

        $domainAverages = CotRating::whereHas('observation', fn ($query) => $query->where('observer_id', $user->id))
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

        $indicatorStats = CotRating::whereHas('observation', fn ($query) => $query->where('observer_id', $user->id))
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

        $statusCounts = Observation::where('observer_id', $user->id)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return compact('monthlyLabels', 'monthlyCounts', 'monthlyAverages', 'distribution', 'domainAverages', 'strengths', 'weaknesses', 'statusCounts');
    }

    /**
     * Data for the Performance tab: per-teacher score summaries and trends
     * across the supervisor's completed observations.
     */
    private function reportsPerformanceData(User $user): array
    {
        $teachers = Teacher::with('user')
            ->whereHas('user', fn ($query) => $query->where('school_id', $user->school_id))
            ->get();

        $scoredObservations = Observation::where('observer_id', $user->id)
            ->where('observee_type', Teacher::class)
            ->whereIn('observee_id', $teachers->pluck('id'))
            ->whereNotNull('overall_score')
            ->orderBy('observation_date')
            ->get(['observee_id', 'overall_score', 'observation_date'])
            ->groupBy('observee_id');

        $rows = $teachers->map(function (Teacher $teacher) use ($scoredObservations) {
            $scores = $scoredObservations->get($teacher->id, collect());
            $values = $scores->pluck('overall_score')->map(fn ($score) => (float) $score);
            $latest = $scores->last();
            $previous = $scores->count() >= 2 ? $scores[$scores->count() - 2]->overall_score : null;
            $latestScore = $latest?->overall_score;
            $average = $values->isNotEmpty() ? round($values->avg(), 2) : null;

            return [
                'teacher' => $teacher,
                'position' => $teacher->position ?: 'Teacher',
                'position_label' => $teacher->position_label ?: 'Teacher',
                'observations_count' => $scores->count(),
                'average' => $average,
                'band' => $this->performanceBand($average),
                'trend' => $previous !== null && $latestScore !== null
                    ? round((float) $latestScore - (float) $previous, 2)
                    : null,
                'last_observed' => $latest?->observation_date,
            ];
        });

        $observed = $rows->filter(fn ($row) => $row['observations_count'] > 0);
        $sorted = $observed
            ->sort(function ($a, $b) {
                return [$b['average'], strtolower($a['teacher']->user->name)] <=> [$a['average'], strtolower($b['teacher']->user->name)];
            })
            ->values()
            ->concat(
                $rows->filter(fn ($row) => $row['observations_count'] === 0)
                    ->sortBy(fn ($row) => strtolower($row['teacher']->user->name))
                    ->values()
            );

        return [
            'performanceRows' => $sorted,
            'performanceSummary' => [
                'teachers_total' => $rows->count(),
                'teachers_observed' => $observed->count(),
                'school_average' => $observed->isNotEmpty() ? round($observed->avg('average'), 2) : null,
                'improving' => $observed->where('trend', '>', 0)->count(),
                'declining' => $observed->where('trend', '<', 0)->count(),
                'needs_attention' => $observed->where('average', '<', 4)->count(),
            ],
        ];
    }

    /**
     * DepEd descriptive band for an average COT score (2-6 scale).
     */
    private function performanceBand(?float $average): ?array
    {
        if ($average === null) {
            return null;
        }

        return match (true) {
            $average >= 5.5 => ['label' => 'Outstanding', 'class' => 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400'],
            $average >= 4.5 => ['label' => 'Very Satisfactory', 'class' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400'],
            $average >= 3.5 => ['label' => 'Satisfactory', 'class' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400'],
            $average >= 2.5 => ['label' => 'Unsatisfactory', 'class' => 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400'],
            default => ['label' => 'Poor', 'class' => 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400'],
        };
    }

    /**
     * Export observations as CSV
     */
    public function exportReports()
    {
        $user = Auth::user();

        $observations = Observation::with(['observee.user', 'observer'])
            ->where('observer_id', $user->id)
            ->latest()
            ->get();

        $filename = 'observations-report-'.now()->format('Y-m-d').'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($observations) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Type', 'Observee', 'Observer', 'Date', 'Stage', 'Status', 'Score', 'Subject', 'Grade Level', 'School Year', 'Term', 'Created At']);

            foreach ($observations as $obs) {
                fputcsv($handle, [
                    $obs->id,
                    $obs->observation_type,
                    $obs->observee?->user?->name ?? 'N/A',
                    $obs->observer?->name ?? 'N/A',
                    $obs->observation_date?->format('Y-m-d'),
                    $obs->stage,
                    $obs->status,
                    $obs->overall_score,
                    $obs->subject ?? 'N/A',
                    $obs->grade_level_label ?? 'N/A',
                    $obs->school_year ?? 'N/A',
                    $obs->quarter ?? 'N/A',
                    $obs->created_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Download Post-Observation Report
     */
    public function downloadReport(Observation $observation)
    {
        $this->authorizeObservation($observation);

        $reportService = app(ObservationReportService::class);
        $markdown = $reportService->generate($observation);

        $filename = 'post-observation-report-'
            .preg_replace('/[^a-z0-9]/i', '-', $observation->observee?->user?->name ?? 'teacher')
            .'-'
            .($observation->observation_date?->format('Y-m-d') ?? date('Y-m-d'))
            .'.md';

        return response()->streamDownload(function () use ($markdown) {
            echo $markdown;
        }, $filename, [
            'Content-Type' => 'text/markdown; charset=utf-8',
        ]);
    }

    /**
     * Download Post-Observation Report as PDF
     */
    public function downloadReportPDF(Observation $observation)
    {
        $this->authorizeObservation($observation);

        $pdfService = app(PDFReportService::class);

        return $pdfService->downloadPDF($observation);
    }

    /**
     * Generate (or regenerate) the completed COT document for an observation.
     */
    public function generateCotDocument(Observation $observation)
    {
        $this->authorizeObservation($observation);

        $service = app(CotDocumentService::class);
        $errors = $service->canGenerate($observation);

        if ($errors) {
            return redirect()->back()->with('error', 'Cannot generate the COT document: '.implode(' ', $errors));
        }

        try {
            $service->generateDocument($observation);
        } catch (\Throwable $e) {
            Log::error('COT document generation failed', ['observation_id' => $observation->id, 'error' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Failed to generate the COT document. Please try again.');
        }

        return redirect()->back()->with('success', 'COT document generated successfully.');
    }

    /**
     * Preview the completed COT document in the browser.
     */
    public function previewCotDocument(Observation $observation)
    {
        $this->authorizeObservation($observation);

        $service = app(CotDocumentService::class);
        $errors = $service->canGenerate($observation);

        if ($errors) {
            return redirect()->back()->with('error', 'Cannot preview the COT document: '.implode(' ', $errors));
        }

        return response(view('reports.cot-document', $service->viewData($observation)))
            ->header('Content-Type', 'text/html');
    }

    /**
     * Download the generated COT document (DOCX).
     */
    public function downloadCotDocument(Observation $observation)
    {
        $this->authorizeObservation($observation);

        $service = app(CotDocumentService::class);
        $path = $service->documentPath($observation);

        if (!$path) {
            return redirect()->back()->with('error', 'No COT document has been generated for this observation yet. Generate it first.');
        }

        return Storage::disk(CotDocumentService::DISK)->download(
            $path,
            basename($path)
        );
    }

    /**
     * Download the COT document as PDF.
     */
    public function downloadCotPdf(Observation $observation)
    {
        $this->authorizeObservation($observation);

        $service = app(CotDocumentService::class);
        $errors = $service->canGenerate($observation);

        if ($errors) {
            return redirect()->back()->with('error', 'Cannot generate the COT document PDF: '.implode(' ', $errors));
        }

        $filename = $service->filenameFor($observation).'.pdf';

        return $service->generatePdf($observation)->download($filename);
    }

    /**
     * View indicator trends for an observee
     */
    public function indicatorTrends(Observation $observation)
    {
        $this->authorizeObservation($observation);

        $trendService = app(IndicatorTrendService::class);
        $trends = $trendService->getIndicatorTrends(
            $observation->observee_id,
            $observation->observee_type
        );

        return view('supervisor.observations.indicator-trends', [
            'observation' => $observation,
            'trends' => $trends,
        ]);
    }

    /**
     * View progress comparison between current and previous observation
     */
    public function progressComparison(Observation $observation)
    {
        $this->authorizeObservation($observation);

        $comparisonService = app(ObservationComparisonService::class);
        $comparison = $comparisonService->compareWithPrevious($observation);

        $trendService = app(IndicatorTrendService::class);
        $lowIndicators = $trendService->getConsistentlyLowIndicators(
            $observation->observee_id,
            $observation->observee_type
        );

        $pdService = app(ProfessionalDevelopmentService::class);
        $pdPlan = $pdService->generatePDPlan($lowIndicators->toArray(), $observation->observee?->user?->name ?? 'Teacher', $observation->ratingScaleMax());

        return view('supervisor.observations.progress-comparison', [
            'observation' => $observation,
            'comparison' => $comparison,
            'lowIndicators' => $lowIndicators,
            'pdPlan' => $pdPlan,
        ]);
    }

    /**
     * View PD recommendations for an observee
     */
    public function pdRecommendations(Observation $observation)
    {
        $this->authorizeObservation($observation);

        $trendService = app(IndicatorTrendService::class);
        $lowIndicators = $trendService->getConsistentlyLowIndicators(
            $observation->observee_id,
            $observation->observee_type
        );

        $pdService = app(ProfessionalDevelopmentService::class);
        $recommendations = $pdService->getRecommendations($lowIndicators->toArray());
        $pdPlan = $pdService->generatePDPlan($lowIndicators->toArray(), $observation->observee?->user?->name ?? 'Teacher', $observation->ratingScaleMax());

        return view('supervisor.observations.pd-recommendations', [
            'observation' => $observation,
            'recommendations' => $recommendations,
            'pdPlan' => $pdPlan,
            'lowIndicators' => $lowIndicators,
        ]);
    }
}
