<?php

namespace App\AI\Services;

use App\AI\Prompts\PostConferencePrompt;
use App\Models\Observation;

class PostConferenceService extends AIService
{
    protected string $stage = 'post_conference';

    public function generateComparison(Observation $observation, bool $templateFallback = true): ?string
    {
        $observation->loadMissing(['observee.user', 'postConference', 'preObservationPlanning']);
        $teacherName = $observation->observee?->user?->name ?? 'Unknown';
        $postConference = $observation->postConference;
        $planning = $observation->preObservationPlanning;

        if ($this->isAvailable()) {
            $rubricContext = $this->rubrics->getSystemContext();

            $prompt = PostConferencePrompt::build([
                'teacher_name' => $teacherName,
                'subject' => $observation->subject ?? 'N/A',
                'grade_level' => $observation->grade_level ?? 'N/A',
                'star_notes' => $postConference?->star_notes ?? 'N/A',
                'areas_for_improvement' => $postConference?->areas_for_improvement ?? 'N/A',
                'challenges' => $postConference?->challenges_facing_teacher ?? 'N/A',
                'ideas' => $postConference?->ideas_for_addressing_challenges ?? 'N/A',
                'next_steps' => $postConference?->prioritized_next_steps ?? 'N/A',
                'teacher_reflection' => $postConference?->teacher_reflection ?? 'N/A',
                'supervisor_notes' => $postConference?->supervisor_notes ?? 'N/A',
                'rubrics' => $rubricContext,
            ]);

            $result = $this->generate($prompt);
            if ($result) {
                return $result;
            }
        }

        if ($templateFallback && config('ai.fallback', true)) {
            return $this->buildFallbackComparison($teacherName, $observation, $postConference, $planning);
        }

        return null;
    }

    protected function buildFallbackComparison(
        string $teacherName,
        Observation $observation,
        $postConference,
        $planning
    ): string {
        $subject = $observation->subject ?? 'N/A';
        $gradeLevel = $observation->grade_level ?? 'N/A';
        $starNotes = $postConference?->star_notes ?? 'N/A';
        $areasForImprovement = $postConference?->areas_for_improvement ?? 'N/A';
        $challenges = $postConference?->challenges_facing_teacher ?? 'None identified';
        $ideas = $postConference?->ideas_for_addressing_challenges ?? 'None identified';
        $nextSteps = $postConference?->prioritized_next_steps ?? 'None identified';
        $teacherReflection = $postConference?->teacher_reflection ?? 'Not provided';
        $supervisorNotes = $postConference?->supervisor_notes ?? 'Not provided';
        $objective = $planning?->objective ?? 'Not specified';

        $text = "**Post-Conference Analysis: {$teacherName}**\n\n";

        $text .= "**Alignment Between Plan and Execution**\n";
        $text .= "The pre-observation plan focused on the following objective: \"{$objective}\". ";
        if ($starNotes && $starNotes !== 'N/A') {
            $text .= "During the actual observation, the following strengths were noted: {$starNotes}.\n";
        } else {
            $text .= "The observation provided insight into the teacher's instructional practices for {$subject} at {$gradeLevel}.\n";
        }
        $text .= "Overall, the lesson demonstrated alignment with the planned objectives, with specific areas identified for continued growth.\n\n";

        $text .= "**Key Achievements**\n";
        if ($starNotes && $starNotes !== 'N/A') {
            $text .= "The teacher demonstrated several strengths during the observation: {$starNotes}\n";
        } else {
            $text .= "The teacher effectively delivered the lesson content and maintained student engagement throughout the observation period.\n";
        }
        if ($teacherReflection && $teacherReflection !== 'Not provided') {
            $text .= "The teacher's self-reflection noted: {$teacherReflection}\n";
        }
        $text .= "\n";

        $text .= "**Areas for Growth**\n";
        if ($areasForImprovement && $areasForImprovement !== 'N/A') {
            $text .= "Identified areas for improvement: {$areasForImprovement}\n";
        }
        if ($challenges && $challenges !== 'None identified') {
            $text .= "Challenges facing the teacher: {$challenges}\n";
        }
        $text .= "\n";

        $text .= "**Recommended Support Strategies**\n";
        if ($ideas && $ideas !== 'None identified') {
            $text .= "Strategies to address challenges: {$ideas}\n";
        }
        if ($nextSteps && $nextSteps !== 'None identified') {
            $text .= "Prioritized next steps: {$nextSteps}\n";
        }
        if ($supervisorNotes && $supervisorNotes !== 'Not provided') {
            $text .= "Supervisor's additional notes: {$supervisorNotes}\n";
        }
        $text .= "\n";

        $text .= "**Summary**\n";
        $text .= "The post-conference discussion provided valuable insights into the teacher's instructional practices. ";
        $text .= "The agreed-upon next steps will support continued professional growth and improved student outcomes ";
        $text .= "in {$subject} at {$gradeLevel}.\n";

        return $text;
    }
}
