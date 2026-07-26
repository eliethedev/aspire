<?php

namespace App\Services;

use App\Models\Observation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PDFReportService
{
    protected ObservationReportService $reportService;
    protected IndicatorTrendService $trendService;
    protected ObservationComparisonService $comparisonService;
    protected ProfessionalDevelopmentService $pdService;

    public function __construct(
        ObservationReportService $reportService,
        IndicatorTrendService $trendService,
        ObservationComparisonService $comparisonService,
        ProfessionalDevelopmentService $pdService
    ) {
        $this->reportService = $reportService;
        $this->trendService = $trendService;
        $this->comparisonService = $comparisonService;
        $this->pdService = $pdService;
    }

    public function generatePDF(Observation $observation): \Barryvdh\DomPDF\PDF
    {
        $observation->load([
            'observee.user', 'observee.school', 'observer',
            'preObservationPlanning', 'preConference', 'postConference',
            'cotRatings.aiFeedback',
        ]);

        $schoolName = $this->getSchoolName($observation);
        $teacherName = $this->getTeacherName($observation);
        $observerName = $this->getObserverName($observation);
        $obsDate = $observation->observation_date?->format('F d, Y') ?? 'N/A';

        $data = [
            'school_name' => $schoolName,
            'teacher_name' => $teacherName,
            'observer_name' => $observerName,
            'observation_date' => $obsDate,
            'quarter' => $observation->quarter ?? 'N/A',
            'observation_type' => $observation->observation_type ?? 'N/A',
            'subject' => $observation->subject ?? 'N/A',
            'grade_level' => $observation->grade_level ?? 'N/A',
            'school_year' => $observation->school_year ?? 'N/A',
            'overall_score' => $observation->overall_score,
            'cot_ratings' => $observation->cotRatings,
            'planning' => $observation->preObservationPlanning,
            'pre_conference' => $observation->preConference,
            'post_conference' => $observation->postConference,
            'observation' => $observation,
        ];

        $comparison = $this->comparisonService->compareWithPrevious($observation);
        $data['comparison'] = $comparison;

        if ($comparison) {
            $lowIndicators = $this->trendService->getConsistentlyLowIndicators(
                $observation->observee_id,
                $observation->observee_type
            );
            $data['pd_plan'] = $this->pdService->generatePDPlan(
                $lowIndicators->toArray(),
                $teacherName
            );
        } else {
            $data['pd_plan'] = null;
        }

        $summary = $this->reportService->generate($observation);
        $data['ai_summary'] = $this->extractAIInsights($summary);

        return Pdf::loadView('reports.post-observation', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setOption('isFontSubsettingEnabled', true);
    }

    public function streamPDF(Observation $observation)
    {
        $pdf = $this->generatePDF($observation);
        $filename = "post-observation-report-{$observation->id}.pdf";
        return $pdf->stream($filename);
    }

    public function downloadPDF(Observation $observation)
    {
        $pdf = $this->generatePDF($observation);
        $filename = "post-observation-report-{$observation->id}.pdf";
        return $pdf->download($filename);
    }

    private function extractAIInsights(string $markdown): array
    {
        $insights = [
            'strengths' => [],
            'areas_for_improvement' => [],
            'recommendations' => [],
        ];

        if (preg_match_all('/^- (.+)$/m', $markdown, $matches, PREG_SET_ORDER)) {
            $section = 'strengths';
            foreach ($matches as $match) {
                $line = $match[1];
                if (stripos($line, 'strength') !== false) {
                    $section = 'strengths';
                    continue;
                }
                if (stripos($line, 'improvement') !== false) {
                    $section = 'areas_for_improvement';
                    continue;
                }
                if (stripos($line, 'recommendation') !== false) {
                    $section = 'recommendations';
                    continue;
                }
                $insights[$section][] = $line;
            }
        }

        if (empty($insights['strengths']) && empty($insights['areas_for_improvement']) && empty($insights['recommendations'])) {
            if (preg_match('/### Personalized Strengths\n\n((?:- .+\n?)+)/', $markdown, $m)) {
                $insights['strengths'] = array_map(fn($l) => ltrim($l, '- '), explode("\n", trim($m[1])));
            }
            if (preg_match('/### Areas for Improvement\n\n((?:- .+\n?)+)/', $markdown, $m)) {
                $insights['areas_for_improvement'] = array_map(fn($l) => ltrim($l, '- '), explode("\n", trim($m[1])));
            }
            if (preg_match('/### Actionable Coaching Recommendations\n\n((?:- .+\n?)+)/', $markdown, $m)) {
                $insights['recommendations'] = array_map(fn($l) => ltrim($l, '- '), explode("\n", trim($m[1])));
            }
        }

        return $insights;
    }

    private function getSchoolName(Observation $observation): string
    {
        $observee = $observation->observee;
        if ($observee && method_exists($observee, 'school')) {
            if ($school = $observee->school) {
                return $school->name;
            }
        }
        return 'School Not Specified';
    }

    private function getTeacherName(Observation $observation): string
    {
        $observee = $observation->observee;
        if (!$observee) return 'Unknown Teacher';
        if (method_exists($observee, 'user')) {
            return $observee->user?->name ?? 'Unknown Teacher';
        }
        return $observee->name ?? 'Unknown Teacher';
    }

    private function getObserverName(Observation $observation): string
    {
        $observer = $observation->observer;
        if (!$observer) return 'Unknown Observer';
        if (method_exists($observer, 'user')) {
            return $observer->user?->name ?? 'Unknown Observer';
        }
        return $observer->name ?? 'Unknown Observer';
    }
}
