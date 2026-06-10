<?php

namespace App\Services;

use App\Models\CotRating;
use App\Models\AiFeedback;
use App\Models\Observation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AIFeedbackService
{
    protected GeminiService $gemini;

    public function __construct(GeminiService $gemini)
    {
        $this->gemini = $gemini;
    }

    public function generateFeedback(int $cotRatingId): ?AiFeedback
    {
        $cotRating = CotRating::with(['observation.teacher'])->findOrFail($cotRatingId);

        if (!$this->gemini->isConfigured()) {
            return null;
        }

        return $this->generateWithGemini($cotRating);
    }

    protected function generateWithGemini(CotRating $cotRating): ?AiFeedback
    {
        $observation = $cotRating->observation;
        $teacherName = $observation?->observee?->name ?? 'Unknown';
        $subject = $observation->subject ?? 'N/A';
        $gradeLevel = $observation->grade_level ?? 'N/A';
        $obsType = $observation->observation_type ?? 'N/A';
        $domain = $cotRating->domain;
        $indicator = $cotRating->indicator;
        $rating = $cotRating->numericRating();
        $percentage = $cotRating->isNotObserved() ? 0 : round(($rating / 6) * 100, 1);

        $scoreLabel = $cotRating->isNotObserved() ? 'NO (Not Observed)' : "{$rating}/6";
        $prompt = <<<PROMPT
You are an expert classroom observation analyst for the Department of Education. Generate detailed feedback for a COT (Classroom Observation Tool) rating.

Teacher: {$teacherName}
Subject: {$subject}
Grade Level: {$gradeLevel}
Observation Type: {$obsType}

Rating Domain: {$domain}
Rating Indicator: {$indicator}
Score: {$scoreLabel}
Percentage: {$percentage}%

Provide a comprehensive analysis with these sections:
1. **analysis** — Detailed analysis of the teacher's performance in this domain
2. **recommendations** — Array of 2-3 specific, actionable recommendations
3. **strengths** — Array of 1-3 observable strengths demonstrated
4. **areas_for_improvement** — Array of 1-2 areas needing improvement

Respond in JSON format with keys: analysis, recommendations, strengths, areas_for_improvement
PROMPT;

        $data = $this->gemini->generateJson($prompt, [
            'temperature' => 0.4,
            'max_output_tokens' => 1024,
        ]);

        if (!$data) {
            return null;
        }

        $aiFeedback = AiFeedback::create([
            'cot_rating_id' => $cotRating->id,
            'analysis' => $data['analysis'] ?? 'Analysis generated.',
            'recommendations' => $data['recommendations'] ?? [],
            'strengths' => $data['strengths'] ?? [],
            'areas_for_improvement' => $data['areas_for_improvement'] ?? [],
            'confidence_score' => 0.90,
            'model_version' => 'gemini-' . config('services.gemini.model', 'gemini-2.0-flash'),
        ]);

        Log::info("Gemini AI Feedback generated for COT Rating {$cotRating->id}");
        return $aiFeedback;
    }

    public function generatePreObservationInsights(Observation $observation): ?string
    {
        $teacherName = $observation->observee?->name ?? 'Unknown';
        $planning = $observation->preObservationPlanning;
        $subject = $observation->subject ?? 'N/A';
        $gradeLevel = $observation->grade_level ?? 'N/A';
        $schoolYear = $observation->school_year ?? 'N/A';
        $obsType = $observation->observation_type ?? 'N/A';
        $stage = $observation->stage ?? 'N/A';
        $lessonPlanFile = $planning?->lesson_plan_file;
        $objective = $planning?->objective ?? 'Not specified';
        $strategies = $planning?->teaching_strategies ?? 'Not specified';
        $materials = $planning?->materials ?? 'Not specified';
        $assessment = $planning?->assessment_methods ?? 'Not specified';

        $lessonPlanContent = '';
        if ($lessonPlanFile && Storage::disk('public')->exists($lessonPlanFile)) {
            $fullPath = Storage::disk('public')->path($lessonPlanFile);
            $lessonPlanContent = $this->extractLessonPlanText($fullPath);
        }

        if ($this->gemini->isConfigured()) {
            $lessonPlanSection = $lessonPlanContent
                ? "--- Lesson Plan Content ---\n{$lessonPlanContent}"
                : "Lesson Plan: {$lessonPlanFile}";

            $prompt = <<<PROMPT
You are an expert instructional coach. Analyze this pre-observation data and provide actionable insights for the supervisor.

Teacher: {$teacherName}
Subject: {$subject}
Grade Level: {$gradeLevel}
School Year: {$schoolYear}
Observation Type: {$obsType}
Stage: {$stage}

{$lessonPlanSection}

Objective: {$objective}
Teaching Strategies: {$strategies}
Materials: {$materials}
Assessment Methods: {$assessment}

Provide concise, specific insights:
1. Key areas to focus on during observation
2. Suggested discussion points for the pre-conference
3. Potential challenges to watch for
4. Recommended coaching strategies

Keep it brief (3-5 short paragraphs).
PROMPT;

            $result = $this->gemini->generate($prompt, [
                'temperature' => 0.5,
                'max_output_tokens' => 800,
            ]);

            if ($result) {
                return $result;
            }
        }

        return $this->buildPreObservationFallback(
            $teacherName, $objective, $strategies, $materials, $assessment, $lessonPlanContent, $subject, $gradeLevel
        );
    }

    public function generatePostConferenceComparison(Observation $observation): ?string
    {
        if (!$this->gemini->isConfigured()) {
            return null;
        }

        $teacherName = $observation->observee?->name ?? 'Unknown';
        $postConference = $observation->postConference;
        $subject = $observation->subject ?? 'N/A';
        $gradeLevel = $observation->grade_level ?? 'N/A';
        $starNotes = $postConference?->star_notes ?? 'N/A';
        $areasForImprovement = $postConference?->areas_for_improvement ?? 'N/A';
        $challenges = $postConference?->challenges_facing_teacher ?? 'N/A';
        $ideas = $postConference?->ideas_for_addressing_challenges ?? 'N/A';
        $nextSteps = $postConference?->prioritized_next_steps ?? 'N/A';
        $teacherReflection = $postConference?->teacher_reflection ?? 'N/A';
        $supervisorNotes = $postConference?->supervisor_notes ?? 'N/A';

        $prompt = <<<PROMPT
You are an expert instructional coach. Compare the teacher's pre-observation plan with the actual observation outcomes and provide a post-conference analysis.

Teacher: {$teacherName}
Subject: {$subject}
Grade Level: {$gradeLevel}

Post-Conference Notes:
- Star Notes (what went well): {$starNotes}
- Areas for Improvement: {$areasForImprovement}
- Challenges Facing Teacher: {$challenges}
- Ideas for Addressing Challenges: {$ideas}
- Prioritized Next Steps: {$nextSteps}
- Teacher Reflection: {$teacherReflection}
- Supervisor Notes: {$supervisorNotes}

Provide a brief comparison analysis (2-3 paragraphs) covering:
1. Alignment between plan and execution
2. Key achievements
3. Areas where the teacher exceeded or fell short of the plan
4. Recommended support strategies
PROMPT;

        return $this->gemini->generate($prompt, [
            'temperature' => 0.5,
            'max_output_tokens' => 800,
        ]);
    }

    public function getFeedbackNeedingReview(): \Illuminate\Support\Collection
    {
        return AiFeedback::where('confidence_score', '<', 0.60)
            ->with(['cotRating.observation.teacher'])
            ->get();
    }

    public function regenerateFeedback(int $cotRatingId): ?AiFeedback
    {
        AiFeedback::where('cot_rating_id', $cotRatingId)->delete();
        return $this->generateFeedback($cotRatingId);
    }

    public function isGeminiConfigured(): bool
    {
        return $this->gemini->isConfigured();
    }

    protected function buildPreObservationFallback(
        string $teacherName,
        string $objective,
        string $strategies,
        string $materials,
        string $assessment,
        string $lessonPlanContent,
        string $subject,
        string $gradeLevel
    ): string {
        $insights = [];
        $insights[] = "**Pre-Observation Insights for {$teacherName}**";
        $insights[] = "";

        if ($objective && $objective !== 'Not specified') {
            $insights[] = "**Key Focus Areas:** The lesson objective \"{$objective}\" should be the primary focus during observation. Pay attention to how the teacher communicates this objective to students and whether lesson activities align with achieving it.";
        } else {
            $insights[] = "**Key Focus Areas:** Review the lesson plan to identify the central learning objectives and observe how well the lesson activities support student mastery of those objectives.";
        }

        if ($strategies && $strategies !== 'Not specified') {
            $insights[] = "**Teaching Strategies to Observe:** The teacher plans to use: {$strategies}. Watch for the effectiveness of these strategies in engaging students and promoting understanding.";
        } else {
            $insights[] = "**Teaching Strategies:** Observe the instructional methods used, noting student engagement levels and the variety of techniques employed.";
        }

        if ($materials && $materials !== 'Not specified') {
            $insights[] = "**Materials & Resources:** Prepare to evaluate how effectively the teacher uses: {$materials}. Check if materials are appropriate for the grade level and learning objectives.";
        }

        if ($assessment && $assessment !== 'Not specified') {
            $insights[] = "**Assessment Methods:** The teacher plans to assess learning through: {$assessment}. Observe whether assessment is integrated throughout the lesson and provides timely feedback.";
        }

        $insights[] = "**Pre-Conference Discussion Points:**";
        $insights[] = "- Ask the teacher how they will differentiate instruction for diverse learners.";
        $insights[] = "- Discuss how success will be measured during this lesson.";
        $insights[] = "- Explore what support the teacher feels they need to deliver this lesson effectively.";
        $insights[] = "";

        if ($lessonPlanContent) {
            $wordCount = str_word_count($lessonPlanContent);
            $hasObjectives = stripos($lessonPlanContent, 'objective') !== false;
            $hasActivities = stripos($lessonPlanContent, 'activity') !== false || stripos($lessonPlanContent, 'procedure') !== false;
            $hasAssessment = stripos($lessonPlanContent, 'assessment') !== false || stripos($lessonPlanContent, 'evaluation') !== false;

            if ($hasObjectives && $hasActivities && $hasAssessment) {
                $insights[] = "**Lesson Plan Completeness:** The uploaded lesson plan appears comprehensive with clear objectives, activities, and assessment components.";
            } elseif ($wordCount < 100) {
                $insights[] = "**Lesson Plan Note:** The uploaded lesson plan is brief. Consider discussing with the teacher whether additional details are available to better understand the lesson flow.";
            }
        }

        $insights[] = "**Potential Challenges:** Monitor time management, student comprehension checks, and the transition between lesson phases. These are common areas where teachers may need support during {$subject} for Grade {$gradeLevel}.";

        return implode("\n", $insights);
    }

    protected function extractLessonPlanText(string $fullPath): string
    {
        if (!file_exists($fullPath) || !is_readable($fullPath)) {
            return '';
        }

        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        try {
            return match ($extension) {
                'pdf' => $this->extractPdfText($fullPath),
                'docx' => $this->extractDocxText($fullPath),
                'txt' => file_get_contents($fullPath),
                default => '',
            };
        } catch (\Exception $e) {
            Log::warning("Failed to extract lesson plan text: {$e->getMessage()}");
            return '';
        }
    }

    protected function extractPdfText(string $fullPath): string
    {
        if (!class_exists(\Smalot\PdfParser\Parser::class)) {
            return '';
        }

        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($fullPath);
        $text = $pdf->getText();

        return mb_substr($text, 0, 8000);
    }

    protected function extractDocxText(string $fullPath): string
    {
        $zip = new \ZipArchive();
        if ($zip->open($fullPath) !== true) {
            return '';
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if (!$xml) {
            return '';
        }

        $xml = simplexml_load_string($xml);
        if (!$xml) {
            return '';
        }

        $namespaces = $xml->getNamespaces(true);
        $ns = $namespaces['w'] ?? '';
        $body = $xml->children($ns);

        $textParts = [];
        $paragraphs = $body->children($ns)->p ?? [];
        foreach ($paragraphs as $paragraph) {
            $parts = [];
            $runs = $paragraph->children($ns)->r ?? [];
            foreach ($runs as $run) {
                $t = $run->children($ns)->t ?? null;
                if ($t !== null) {
                    $parts[] = (string)$t;
                }
            }
            if ($parts) {
                $textParts[] = implode('', $parts);
            }
        }

        $text = implode("\n", $textParts);
        return mb_substr($text, 0, 8000);
    }
}
