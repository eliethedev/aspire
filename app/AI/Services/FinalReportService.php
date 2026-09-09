<?php

namespace App\AI\Services;

use App\AI\Prompts\FinalReportPrompt;
use App\Models\Observation;

class FinalReportService extends AIService
{
    protected string $stage = 'final_report';

    public function generateReportSummary(Observation $observation): ?string
    {
        $observation->loadMissing([
            'observee.user',
            'observee.school',
            'cotRatings.aiFeedback',
            'postConference',
            'preObservationPlanning',
        ]);

        $teacherName = $observation->observee?->user?->name ?? 'Unknown';
        $schoolName = $this->getSchoolName($observation);
        $scaleMax = $observation->ratingScaleMax();
        $overallScore = $observation->overall_score;
        $descriptiveRating = $this->getDescriptiveRating($overallScore ? (float) $overallScore : 0, $scaleMax);

        $guidanceService = app(ObservationGuidanceService::class);
        $summary = $guidanceService->compileOverallSummary($observation);

        if ($this->isAvailable()) {
            $rubricContext = $this->rubrics->getSystemContext();

            $prompt = FinalReportPrompt::build([
                'teacher_name' => $teacherName,
                'school_name' => $schoolName,
                'subject' => $observation->subject ?? 'N/A',
                'grade_level' => $observation->grade_level ?? 'N/A',
                'school_year' => $observation->school_year ?? 'N/A',
                'overall_score' => $overallScore ? number_format($overallScore, 2) . ' / ' . number_format($scaleMax, 2) : 'N/A',
                'descriptive_rating' => $descriptiveRating,
                'strengths' => $summary['strengths'] ?? [],
                'areas_for_improvement' => $summary['areas_for_improvement'] ?? [],
                'recommendations' => $summary['recommendations'] ?? [],
                'rubrics' => $rubricContext,
            ]);

            $result = $this->generate($prompt, [
                'temperature' => 0.6,
                'max_output_tokens' => 2048,
            ]);

            if ($result) {
                return $result;
            }
        }

        if (config('ai.fallback', true)) {
            return $this->buildFallbackReportSummary(
                $teacherName,
                $schoolName,
                $observation,
                $overallScore,
                $descriptiveRating,
                $summary
            );
        }

        return null;
    }

    protected function buildFallbackReportSummary(
        string $teacherName,
        string $schoolName,
        Observation $observation,
        ?float $overallScore,
        string $descriptiveRating,
        array $summary
    ): string {
        $subject = $observation->subject ?? 'N/A';
        $gradeLevel = $observation->grade_level ?? 'N/A';
        $schoolYear = $observation->school_year ?? 'N/A';
        $quarter = $observation->quarter ?? 'N/A';
        $obsDate = $observation->observation_date?->format('F d, Y') ?? 'N/A';
        $postCon = $observation->postConference;

        $scoreText = $overallScore ? number_format($overallScore, 2) . ' / ' . number_format($observation->ratingScaleMax(), 2) : 'N/A';
        $strengthsText = !empty($summary['strengths'])
            ? implode("\n  - ", $summary['strengths'])
            : 'No specific strengths identified';
        $areasText = !empty($summary['areas_for_improvement'])
            ? implode("\n  - ", $summary['areas_for_improvement'])
            : 'No specific areas identified';
        $recommendationsText = !empty($summary['recommendations'])
            ? implode("\n  - ", $summary['recommendations'])
            : 'No specific recommendations';

        $report = "**Final Observation Report Summary**\n\n";
        $report .= "**Teacher:** {$teacherName}\n";
        $report .= "**School:** {$schoolName}\n";
        $report .= "**Subject:** {$subject} | **Grade Level:** {$gradeLevel}\n";
        $report .= "**School Year:** {$schoolYear} | **Quarter:** {$quarter}\n";
        $report .= "**Observation Date:** {$obsDate}\n";
        $report .= "**Overall Score:** {$scoreText} ({$descriptiveRating})\n\n";

        $report .= "**Executive Summary**\n";
        $report .= "A classroom observation of {$teacherName} was conducted on {$obsDate} ";
        $report .= "for {$subject} at {$gradeLevel}. ";
        if ($overallScore) {
            $report .= "The teacher received an overall rating of {$scoreText}, ";
            $report .= "which is described as \"{$descriptiveRating}.\" ";
        }
        $report .= "The post-conference discussion identified key strengths and areas for continued professional growth.\n\n";

        $report .= "**Observed Strengths**\n";
        $report .= "- {$strengthsText}\n\n";

        $report .= "**Areas for Improvement**\n";
        $report .= "- {$areasText}\n\n";

        $report .= "**Professional Development Plan**\n";
        $report .= "Based on the observation results, the following development goals are recommended:\n";
        $report .= "- {$recommendationsText}\n\n";

        $report .= "**Support Needed**\n";
        $report .= "To support the teacher's professional growth, the following may be provided:\n";
        $report .= "- Access to relevant professional development resources and training\n";
        if ($postCon?->prioritized_next_steps) {
            $report .= "- Follow-up on agreed next steps: {$postCon->prioritized_next_steps}\n";
        }
        $report .= "- Peer mentoring or coaching support as needed\n";
        $report .= "- Regular check-ins to monitor progress on development goals\n\n";

        $report .= "**Next Steps**\n";
        $report .= "1. Teacher to implement agreed-upon strategies in upcoming lessons\n";
        $report .= "2. Schedule follow-up observation within the next quarter\n";
        $report .= "3. Document progress and adjust support strategies as needed\n";
        $report .= "4. Share best practices with the learning area team\n\n";

        $report .= "*This summary is generated based on observation data and rating analysis. ";
        $report .= "It serves as a guide for professional development planning.*\n";

        return $report;
    }

    protected function getSchoolName(Observation $observation): string
    {
        $observee = $observation->observee;
        if ($observee && method_exists($observee, 'school')) {
            if ($school = $observee->school) {
                return $school->name;
            }
        }
        return 'School Not Specified';
    }

    protected function getDescriptiveRating(float $score, int $scaleMax = 6): string
    {
        $max = (float) $scaleMax;
        return match (true) {
            $score >= $max * 5.5 / 6.0 => 'Outstanding',
            $score >= $max * 4.5 / 6.0 => 'Very Satisfactory',
            $score >= $max * 3.5 / 6.0 => 'Satisfactory',
            $score >= $max * 2.5 / 6.0 => 'Fair',
            default => 'Needs Improvement',
        };
    }
}
