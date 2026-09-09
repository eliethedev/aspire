<?php

namespace App\Services;

use App\Models\Observation;
use Illuminate\Support\Facades\Storage;
use App\Services\AISuggestionService;

class ObservationReportService
{
    protected AISuggestionService $aiSuggestions;

    public function __construct(AISuggestionService $aiSuggestions)
    {
        $this->aiSuggestions = $aiSuggestions;
    }

    public function generate(Observation $observation): string
    {
        $this->loadRelations($observation);

        $schoolName = $this->getSchoolName($observation);
        $teacherName = $this->getTeacherName($observation);
        $observerName = $this->getObserverName($observation);
        $obsDate = $observation->observation_date?->format('F d, Y') ?? 'N/A';
        $quarter = $observation->quarter ?? 'N/A';
        $obsType = $observation->observation_type ?? 'N/A';

        $planning = $observation->preObservationPlanning;
        $preCon = $observation->preConference;
        $postCon = $observation->postConference;
        $cotRatings = $observation->cotRatings;

        $md = '';

        // ==================== SECTION 1: COVER PAGE ====================
        $md .= $this->coverPage($schoolName, $teacherName, $observerName, $obsDate, $quarter, $obsType);

        // ==================== SECTION 2: COT RATING SUMMARY ====================
        $md .= $this->cotRatingSummary($cotRatings, $observation->overall_score, $observation->ratingScaleMax());

        // ==================== SECTION 3: PRE-OBSERVATION SUMMARY ====================
        $md .= $this->preObservationSummary($planning, $preCon);

        // ==================== SECTION 4: OBSERVATION HIGHLIGHTS (STAR NOTES) ====================
        $md .= $this->observationHighlights($postCon);

        // ==================== SECTION 5: ENHANCED POST-OBSERVATION CONFERENCE SUMMARY ====================
        $md .= $this->postConferenceSummary($postCon);

        // ==================== SECTION 6: AI-GENERATED INSIGHTS & RECOMMENDATIONS ====================
        $md .= $this->aiInsights($observation);

        // ==================== SECTION 7: TEACHER REFLECTION & AGREEMENT ====================
        $md .= $this->teacherReflection($postCon, $teacherName, $observerName);

        // ==================== SECTION 8: APPENDICES ====================
        $md .= $this->appendices($planning, $observation);

        $md .= "\n---\n\n*This document is generated to support teacher development and is based on one classroom observation.*\n";

        return $md;
    }

    protected function loadRelations(Observation $observation): void
    {
        $observation->loadMissing([
            'observee.user',
            'observee.school',
            'observer',
            'preObservationPlanning',
            'preConference',
            'postConference',
            'cotRatings.aiFeedback',
        ]);
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

    protected function getTeacherName(Observation $observation): string
    {
        $observee = $observation->observee;
        if (!$observee) return 'Unknown Teacher';
        if (method_exists($observee, 'user')) {
            return $observee->user?->name ?? 'Unknown Teacher';
        }
        return $observee->name ?? 'Unknown Teacher';
    }

    protected function getObserverName(Observation $observation): string
    {
        $observer = $observation->observer;
        if (!$observer) return 'Unknown Observer';
        if (method_exists($observer, 'user')) {
            return $observer->user?->name ?? 'Unknown Observer';
        }
        return $observer->name ?? 'Unknown Observer';
    }

    protected function coverPage(string $school, string $teacher, string $observer, string $date, string $quarter, string $obsType): string
    {
        return <<<MD
# Post-Observation Report

---

## {$school}

---

| | |
|---|---|
| **Teacher Observed** | {$teacher} |
| **Observer** | {$observer} |
| **Date of Observation** | {$date} |
| **Quarter** | {$quarter} |
| **Observation Type** | {$obsType} |

---

MD;
    }

    protected function cotRatingSummary($cotRatings, $overallScore, int $scaleMax = 6): string
    {
        $md = "## COT Rating Summary\n\n";
        $md .= "| # | Domain | Indicator | Rating | Comments |\n";
        $md .= "|---|--------|-----------|--------|----------|\n";

        $i = 0;
        foreach ($cotRatings as $rating) {
            $i++;
            $comments = $rating->comments ?? '-';
            $ratingDisplay = $rating->not_applicable ? 'N/A' : ($rating->not_observed ? 'NO' : number_format($rating->rating, 1));
            $md .= "| {$i} | {$rating->domain} | {$rating->indicator} | {$ratingDisplay} | {$comments} |\n";
        }

        $average = $overallScore ?? $cotRatings->avg('rating');
        if ($average === null) {
            return "## COT Rating Summary\n\n_No ratings available for this observation._\n";
        }
        $descriptive = $this->getDescriptiveRating($average, $scaleMax);

        $md .= "\n**Overall Average Rating:** " . number_format($average, 2) . " / {$scaleMax}.00\n";
        $md .= "**Descriptive Rating:** {$descriptive}\n\n";

        return $md;
    }

    protected function getDescriptiveRating(float $score, int $scaleMax = 6): string
    {
        $max = (float) $scaleMax;
        return match (true) {
            $score >= $max * 5.5 / 6.0 => 'Outstanding',
            $score >= $max * 4.5 / 6.0 => 'Very Satisfactory',
            $score >= $max * 3.5 / 6.0 => 'Satisfactory',
            $score >= $max * 2.5 / 6.0 => 'Fair',
            default         => 'Needs Improvement',
        };
    }

    protected function preObservationPlanningSummary($planning): string
    {
        if (!$planning) return '';

        $md = "### Pre-Observation Planning\n\n";

        if ($planning->suggested_focus) {
            $focus = is_array($planning->suggested_focus)
                ? implode(', ', $planning->suggested_focus)
                : $planning->suggested_focus;
            $md .= "- **Suggested Focus:** {$focus}\n";
        }

        if ($planning->ai_insights) {
            $insights = is_array($planning->ai_insights)
                ? (json_encode($planning->ai_insights) ?: '')
                : $planning->ai_insights;
            $md .= "- **AI Pre-Observation Insights:** {$insights}\n";
        }

        $md .= "\n";
        return $md;
    }

    protected function preObservationSummary($planning, $preCon): string
    {
        $md = "## Pre-Observation Summary\n\n";

        if ($planning) {
            $md .= $this->preObservationPlanningSummary($planning);
        }

        if ($preCon && $preCon->finalized_focus) {
            $md .= "### Finalized Observation Focus\n\n";
            $md .= "{$preCon->finalized_focus}\n\n";
        }

        if ($preCon && $preCon->discussion_notes) {
            $md .= "### Pre-Conference Discussion Notes\n\n";
            $md .= "{$preCon->discussion_notes}\n\n";
        }

        return $md;
    }

    protected function observationHighlights($postCon): string
    {
        $md = "## Observation Highlights (STAR Notes)\n\n";

        if ($postCon && $postCon->star_notes) {
            $md .= "{$postCon->star_notes}\n\n";
        } else {
            $md .= "*No STAR notes recorded.*\n\n";
        }

        return $md;
    }

    protected function postConferenceSummary($postCon): string
    {
        $md = "## Enhanced Post-Observation Conference Summary\n\n";
        $md .= "The conference followed the CID Enhanced Post Observation Conference framework:\n\n";

        // Step 1: Establishing a Warm Opening
        $md .= "### 1. Establishing a Warm Opening\n\n";
        $md .= "The supervisor established a supportive and collaborative atmosphere for the conference.\n\n";

        // Step 2: Focus on What's Going Well
        $md .= "### 2. Focus on What's Going Well\n\n";
        if ($postCon && $postCon->star_notes) {
            $md .= "{$postCon->star_notes}\n\n";
        } else {
            $md .= "*No specific strengths recorded.*\n\n";
        }

        // Step 3: Identify Challenges
        $md .= "### 3. Identify Challenges Facing the Teacher\n\n";
        if ($postCon && $postCon->challenges_facing_teacher) {
            $md .= "{$postCon->challenges_facing_teacher}\n\n";
        } else {
            $md .= "*No specific challenges identified.*\n\n";
        }

        // Step 4: Generating Ideas
        $md .= "### 4. Generating Ideas for Addressing Challenges\n\n";
        if ($postCon && $postCon->ideas_for_addressing_challenges) {
            $md .= "{$postCon->ideas_for_addressing_challenges}\n\n";
        } else {
            $md .= "*No specific ideas generated.*\n\n";
        }

        // Step 5: Prioritizing Next Steps
        $md .= "### 5. Prioritizing Next Steps\n\n";
        if ($postCon && $postCon->prioritized_next_steps) {
            $md .= "{$postCon->prioritized_next_steps}\n\n";
        } else {
            $md .= "*No next steps recorded.*\n\n";
        }

        // Step 6: Ending the Conference
        $md .= "### 6. Ending the Conference\n\n";
        $md .= "The conference concluded with a clear summary of agreements and a commitment to ongoing professional growth.\n\n";

        if ($postCon && $postCon->supervisor_notes) {
            $md .= "**Supervisor's Overall Comments:**\n\n{$postCon->supervisor_notes}\n\n";
        }

        return $md;
    }

    protected function aiInsights(Observation $observation): string
    {
        $md = "## AI-Generated Insights & Recommendations\n\n";

        $summary = $this->aiSuggestions->compileOverallSummary($observation);
        $postCon = $observation->postConference;

        if (!empty($summary['strengths']) || !empty($summary['areas_for_improvement']) || !empty($summary['recommendations'])) {
            $md .= "### Personalized Strengths\n\n";
            if (!empty($summary['strengths'])) {
                foreach ($summary['strengths'] as $s) {
                    $md .= "- {$s}\n";
                }
            } else {
                $md .= "*No specific strengths identified by AI.*\n";
            }
            $md .= "\n";

            $md .= "### Areas for Improvement\n\n";
            if (!empty($summary['areas_for_improvement'])) {
                foreach ($summary['areas_for_improvement'] as $a) {
                    $md .= "- {$a}\n";
                }
            } else {
                $md .= "*No specific areas for improvement identified by AI.*\n";
            }
            $md .= "\n";

            $md .= "### Actionable Coaching Recommendations\n\n";
            if (!empty($summary['recommendations'])) {
                foreach ($summary['recommendations'] as $r) {
                    $md .= "- {$r}\n";
                }
            } else {
                $md .= "*No specific recommendations generated.*\n";
            }
            $md .= "\n";
        } else {
            $md .= "*AI-generated feedback was not available for this observation.*\n\n";
        }

        // Comparison between plan and execution
        if ($postCon && $postCon->ai_comparison) {
            $comparison = is_array($postCon->ai_comparison)
                ? json_encode($postCon->ai_comparison)
                : $postCon->ai_comparison;
            $md .= "### Comparison Between Planned Lesson and Actual Execution\n\n";
            $md .= "{$comparison}\n\n";
        }

        return $md;
    }

    protected function teacherReflection($postCon, string $teacherName, string $observerName): string
    {
        $md = "## Teacher Reflection & Agreement\n\n";

        if ($postCon && $postCon->teacher_reflection) {
            $md .= "### Teacher's Self-Reflection\n\n";
            $md .= "{$postCon->teacher_reflection}\n\n";
        }

        if ($postCon && $postCon->prioritized_next_steps) {
            $md .= "### Agreed Next Steps and Support Needed\n\n";
            $md .= "{$postCon->prioritized_next_steps}\n\n";
        }

        $today = now()->format('F d, Y');
        $md .= <<<MD
### Signatures

| | |
|---|---|
| **Supervisor:** | _________________________ |
| | {$observerName} |
| **Date:** | {$today} |
| | |
| **Teacher:** | _________________________ |
| | {$teacherName} |
| **Date:** | {$today} |

MD;
        return $md;
    }

    protected function appendices($planning, Observation $observation): string
    {
        $md = "## Appendices\n\n";

        // Lesson plan content
        $md .= "### Appendix A: Lesson Plan Excerpt\n\n";
        if ($planning && $planning->lesson_plan_file) {
            $fullPath = Storage::disk('public')->path($planning->lesson_plan_file);
            if (file_exists($fullPath)) {
                $content = $this->extractText($fullPath);
                $md .= "```\n{$content}\n```\n\n";
            } else {
                $md .= "*Lesson plan file not found on disk.*\n\n";
            }
        } else {
            $md .= "*No lesson plan was uploaded.*\n\n";
        }

        // Raw observation notes
        $md .= "### Appendix B: Raw Observation Notes\n\n";
        if ($observation->notes) {
            $md .= "{$observation->notes}\n\n";
        } else {
            $md .= "*No raw observation notes recorded.*\n\n";
        }

        return $md;
    }

    protected function extractText(string $fullPath): string
    {
        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        return match ($ext) {
            'pdf' => $this->extractPdfText($fullPath),
            'docx' => $this->extractDocxText($fullPath),
            'txt', 'md' => file_get_contents($fullPath) ?: '',
            default => '[Unsupported file format: ' . $ext . ']',
        };
    }

    protected function extractPdfText(string $fullPath): string
    {
        try {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($fullPath);
            return $pdf->getText();
        } catch (\Exception $e) {
            return '[Could not extract PDF text: ' . $e->getMessage() . ']';
        }
    }

    protected function extractDocxText(string $fullPath): string
    {
        try {
            $zip = new \ZipArchive();
            if ($zip->open($fullPath) !== true) {
                return '[Could not open DOCX file]';
            }
            $xml = $zip->getFromName('word/document.xml');
            $zip->close();
            if ($xml === false) {
                return '[Could not read DOCX content]';
            }
            $xml = simplexml_load_string($xml);
            if ($xml === false) {
                return '[Could not parse DOCX XML]';
            }
            $body = $xml->body;
            $text = '';
            foreach ($body->xpath('//w:t') as $t) {
                $text .= (string)$t;
            }
            return $text;
        } catch (\Exception $e) {
            return '[Could not extract DOCX text: ' . $e->getMessage() . ']';
        }
    }
}
