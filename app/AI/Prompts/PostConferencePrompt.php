<?php

namespace App\AI\Prompts;

class PostConferencePrompt
{
    public static function build(array $context): string
    {
        $teacherName = $context['teacher_name'] ?? 'Unknown';
        $subject = $context['subject'] ?? 'N/A';
        $gradeLevel = $context['grade_level'] ?? 'N/A';
        $starNotes = $context['star_notes'] ?? 'N/A';
        $areasForImprovement = $context['areas_for_improvement'] ?? 'N/A';
        $challenges = $context['challenges'] ?? 'N/A';
        $ideas = $context['ideas'] ?? 'N/A';
        $nextSteps = $context['next_steps'] ?? 'N/A';
        $teacherReflection = $context['teacher_reflection'] ?? 'N/A';
        $supervisorNotes = $context['supervisor_notes'] ?? 'N/A';
        $rubrics = $context['rubrics'] ?? '';

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

{$rubrics}

Provide a brief comparison analysis (2-3 paragraphs) covering:
1. Alignment between plan and execution
2. Key achievements
3. Areas where the teacher exceeded or fell short of the plan
4. Recommended support strategies
PROMPT;

        return $prompt;
    }
}
