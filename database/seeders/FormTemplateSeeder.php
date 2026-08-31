<?php

namespace Database\Seeders;

use App\Models\FormField;
use App\Models\FormSection;
use App\Models\FormTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FormTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $schoolYear = date('Y') . '-' . (date('Y') + 1);

        $this->createTeacherTemplate($schoolYear);
        $this->createSchoolHeadTemplate($schoolYear);

        $this->command->info('Form templates seeded successfully.');
    }

    private function createTeacherTemplate(string $schoolYear): void
    {
        $template = FormTemplate::create([
            'name' => "DepEd COT Instrument (Teacher) SY {$schoolYear}",
            'description' => 'Standard DepEd Classroom Observation Tool (COT) for teacher observations based on PPST indicators.',
            'school_year' => $schoolYear,
            'observation_type' => 'teacher_observation',
            'is_active' => true,
            'version' => 1,
        ]);

        $this->buildSections($template, $schoolYear);

        $this->command->info("Created teacher form template: {$template->name}");
    }

    private function createSchoolHeadTemplate(string $schoolYear): void
    {
        $template = FormTemplate::create([
            'name' => "School Head Observation SY {$schoolYear}",
            'description' => 'Observation form for school head evaluation, including leadership, management, and instructional supervision indicators.',
            'school_year' => $schoolYear,
            'observation_type' => 'school_head_observation',
            'is_active' => true,
            'version' => 1,
        ]);

        // ===== PRE-OBSERVATION PLANNING =====
        $planningSection = $template->sections()->create([
            'key' => 'pre_observation_planning',
            'label' => 'Pre-Observation Planning',
            'order' => 0,
        ]);

        $planningSection->fields()->createMany([
            [
                'key' => 'school_document',
                'label' => 'School Document',
                'type' => 'file',
                'help_text' => 'Upload the school document for review.',
                'order' => 0,
            ],
            [
                'key' => 'leadership_focus',
                'label' => 'Leadership Focus Areas',
                'type' => 'textarea',
                'help_text' => 'Areas of school leadership to focus on during the observation.',
                'placeholder' => 'e.g. Strategic planning, instructional leadership, stakeholder engagement...',
                'order' => 1,
            ],
            [
                'key' => 'supervisor_notes',
                'label' => 'Supervisor Notes',
                'type' => 'textarea',
                'help_text' => 'Preliminary notes or reminders before the school head visit.',
                'placeholder' => 'Preliminary notes for the school head observation...',
                'order' => 2,
                'column_map' => 'supervisor_notes',
            ],
        ]);

        // ===== PRE-CONFERENCE =====
        $preConfSection = $template->sections()->create([
            'key' => 'pre_conference',
            'label' => 'Pre-Conference',
            'order' => 1,
        ]);

        $preConfSection->fields()->createMany([
            [
                'key' => 'conference_date',
                'label' => 'Pre-Conference Date',
                'type' => 'date',
                'required' => true,
                'order' => 0,
                'column_map' => 'conference_date',
            ],
            [
                'key' => 'discussion_notes',
                'label' => 'Pre-Conference Discussion Notes',
                'type' => 'textarea',
                'help_text' => 'Document key discussion points about school goals, initiatives, and challenges.',
                'placeholder' => 'Key discussion points from the pre-conference meeting...',
                'order' => 1,
                'column_map' => 'discussion_notes',
            ],
            [
                'key' => 'finalized_focus',
                'label' => 'Finalized Observation Focus',
                'type' => 'textarea',
                'help_text' => 'Areas agreed upon to be the focus of the school head observation.',
                'placeholder' => 'e.g. Instructional supervision, school management, community engagement...',
                'required' => true,
                'order' => 2,
                'column_map' => 'finalized_focus',
            ],
            [
                'key' => 'teacher_reflection',
                'label' => 'School Head Reflection',
                'type' => 'textarea',
                'help_text' => 'The school head\'s reflection on their goals and anticipated challenges.',
                'placeholder' => 'School head\'s reflection on goals, programs, and expected outcomes...',
                'order' => 3,
                'column_map' => 'teacher_reflection',
            ],
        ]);

        // ===== OBSERVATION =====
        $obsSection = $template->sections()->create([
            'key' => 'observation',
            'label' => 'School Head Observation Ratings',
            'order' => 2,
        ]);

        $obsSection->fields()->createMany([
            [
                'key' => 'heading_instr_leadership',
                'label' => 'Instructional Leadership',
                'type' => 'heading',
                'order' => 0,
            ],
            [
                'key' => 'rating_il1',
                'label' => 'IL-1: School Vision and Culture',
                'type' => 'rating',
                'metadata' => ['indicator_code' => 'IL-1', 'domain' => 'Instructional Leadership'],
                'required' => true,
                'order' => 1,
            ],
            [
                'key' => 'rating_il2',
                'label' => 'IL-2: Curriculum Implementation',
                'type' => 'rating',
                'metadata' => ['indicator_code' => 'IL-2', 'domain' => 'Instructional Leadership'],
                'required' => true,
                'order' => 2,
            ],
            [
                'key' => 'rating_il3',
                'label' => 'IL-3: Teacher Development',
                'type' => 'rating',
                'metadata' => ['indicator_code' => 'IL-3', 'domain' => 'Instructional Leadership'],
                'required' => true,
                'order' => 3,
            ],
            [
                'key' => 'heading_mgmt',
                'label' => 'School Management',
                'type' => 'heading',
                'order' => 4,
            ],
            [
                'key' => 'rating_mgmt1',
                'label' => 'M-1: Resource Management',
                'type' => 'rating',
                'metadata' => ['indicator_code' => 'M-1', 'domain' => 'School Management'],
                'required' => true,
                'order' => 5,
            ],
            [
                'key' => 'rating_mgmt2',
                'label' => 'M-2: Administrative Operations',
                'type' => 'rating',
                'metadata' => ['indicator_code' => 'M-2', 'domain' => 'School Management'],
                'required' => true,
                'order' => 6,
            ],
            [
                'key' => 'rating_mgmt3',
                'label' => 'M-3: School Environment and Safety',
                'type' => 'rating',
                'metadata' => ['indicator_code' => 'M-3', 'domain' => 'School Management'],
                'required' => true,
                'order' => 7,
            ],
            [
                'key' => 'heading_engagement',
                'label' => 'Stakeholder Engagement',
                'type' => 'heading',
                'order' => 8,
            ],
            [
                'key' => 'rating_se1',
                'label' => 'S-1: Community Partnerships',
                'type' => 'rating',
                'metadata' => ['indicator_code' => 'S-1', 'domain' => 'Stakeholder Engagement'],
                'required' => true,
                'order' => 9,
            ],
            [
                'key' => 'rating_se2',
                'label' => 'S-2: Parent and Community Involvement',
                'type' => 'rating',
                'metadata' => ['indicator_code' => 'S-2', 'domain' => 'Stakeholder Engagement'],
                'required' => true,
                'order' => 10,
            ],
            [
                'key' => 'other_comments',
                'label' => 'Other Comments',
                'type' => 'textarea',
                'help_text' => 'Additional observations or notes about the school head.',
                'placeholder' => 'Any additional comments about the school head observation...',
                'order' => 11,
            ],
            [
                'key' => 'evidence_files',
                'label' => 'Evidence Files',
                'type' => 'file',
                'help_text' => 'Upload supporting evidence files.',
                'order' => 12,
                'column_map' => 'evidence_files',
            ],
        ]);

        // ===== POST-CONFERENCE =====
        $postConfSection = $template->sections()->create([
            'key' => 'post_conference',
            'label' => 'Post-Conference',
            'order' => 3,
        ]);

        $postConfSection->fields()->createMany([
            [
                'key' => 'conference_date',
                'label' => 'Post-Conference Date',
                'type' => 'date',
                'order' => 0,
                'column_map' => 'conference_date',
            ],
            [
                'key' => 'star_notes',
                'label' => 'STAR Notes - What\'s Going Well',
                'type' => 'textarea',
                'help_text' => 'Document specific observations of effective leadership practices.',
                'placeholder' => 'Describe what went well during the school head observation...',
                'order' => 1,
                'column_map' => 'star_notes',
            ],
            [
                'key' => 'challenges_facing_school_head',
                'label' => 'Identify Challenges',
                'type' => 'textarea',
                'help_text' => 'Identify challenges or difficulties observed.',
                'placeholder' => 'What challenges did the school head face?',
                'order' => 2,
            ],
            [
                'key' => 'areas_for_improvement',
                'label' => 'Areas for Improvement',
                'type' => 'textarea',
                'help_text' => 'Areas where the school head can grow and develop.',
                'placeholder' => 'Identify specific areas for improvement...',
                'order' => 3,
                'column_map' => 'areas_for_improvement',
            ],
            [
                'key' => 'ideas_for_addressing_challenges',
                'label' => 'Ideas for Addressing Challenges',
                'type' => 'textarea',
                'help_text' => 'Collaboratively generate strategies to address challenges.',
                'placeholder' => 'Brainstorm strategies with the school head...',
                'order' => 4,
                'column_map' => 'ideas_for_addressing_challenges',
            ],
            [
                'key' => 'prioritized_next_steps',
                'label' => 'Prioritized Next Steps',
                'type' => 'textarea',
                'help_text' => 'Agreed actionable next steps with clear timelines.',
                'placeholder' => 'Next steps with timelines and responsibilities...',
                'order' => 5,
                'column_map' => 'prioritized_next_steps',
            ],
            [
                'key' => 'teacher_reflection',
                'label' => 'School Head Reflection',
                'type' => 'textarea',
                'help_text' => 'The school head\'s reflection on the observation and feedback.',
                'placeholder' => 'School head\'s personal reflection on the observation feedback...',
                'order' => 6,
                'column_map' => 'teacher_reflection',
            ],
            [
                'key' => 'feedback',
                'label' => 'Supervisor Feedback',
                'type' => 'textarea',
                'help_text' => 'Overall feedback summary for the school head.',
                'placeholder' => 'Provide overall feedback summarizing the observation...',
                'order' => 7,
                'column_map' => 'feedback',
            ],
            [
                'key' => 'supervisor_notes',
                'label' => 'Supervisor\'s Private Notes',
                'type' => 'textarea',
                'help_text' => 'Private notes for your reference only.',
                'placeholder' => 'Your private notes and reminders...',
                'order' => 8,
                'column_map' => 'supervisor_notes',
            ],
        ]);

        $this->command->info("Created school head form template: {$template->name}");
    }

    private function buildSections(FormTemplate $template, string $schoolYear): void
    {
        // ===== PRE-OBSERVATION PLANNING =====
        $planningSection = $template->sections()->create([
            'key' => 'pre_observation_planning',
            'label' => 'Pre-Observation Planning',
            'order' => 0,
        ]);

        $planningSection->fields()->createMany([
            [
                'key' => 'lesson_plan_file',
                'label' => 'Lesson Plan File',
                'type' => 'file',
                'help_text' => 'Upload the teacher\'s lesson plan for review.',
                'order' => 0,
                'column_map' => 'lesson_plan_file',
            ],
            [
                'key' => 'observation_tool',
                'label' => 'Observation Tool',
                'type' => 'select',
                'help_text' => 'Select the observation tool/rubric to be used.',
                'options' => [
                    ['value' => 'ppst', 'label' => 'PPST'],
                    ['value' => 'classroom_observation_tool', 'label' => 'Classroom Observation Tool (COT)'],
                    ['value' => 'tisuyon', 'label' => 'Tisuyon (Peer Observation)'],
                ],
                'order' => 1,
                'column_map' => 'observation_tool',
            ],
            [
                'key' => 'supervisor_notes',
                'label' => 'Supervisor Notes',
                'type' => 'textarea',
                'help_text' => 'Preliminary notes, things to watch for, or reminders before the class visit.',
                'placeholder' => 'Write your preliminary notes, things to watch for, or reminders before the class visit...',
                'order' => 2,
                'column_map' => 'supervisor_notes',
            ],
            [
                'key' => 'suggested_focus',
                'label' => 'Suggested Focus Areas',
                'type' => 'textarea',
                'help_text' => 'Based on previous observations and AI analysis.',
                'placeholder' => 'e.g. Classroom management, questioning techniques, learner engagement...',
                'order' => 3,
                'column_map' => 'suggested_focus',
            ],
        ]);

        // ===== PRE-CONFERENCE =====
        $preConfSection = $template->sections()->create([
            'key' => 'pre_conference',
            'label' => 'Pre-Conference',
            'order' => 1,
        ]);

        $preConfSection->fields()->createMany([
            [
                'key' => 'conference_date',
                'label' => 'Pre-Conference Date',
                'type' => 'date',
                'required' => false,
                'order' => 0,
                'column_map' => 'conference_date',
            ],
            [
                'key' => 'lesson_plan_review',
                'label' => 'Lesson Plan Review',
                'type' => 'textarea',
                'help_text' => 'Review the lesson plan objectives, activities, and assessment strategies.',
                'placeholder' => 'Summarize key points from the lesson plan review...',
                'order' => 1,
                'column_map' => 'lesson_plan_review',
            ],
            [
                'key' => 'instructional_materials',
                'label' => 'Instructional Materials',
                'type' => 'textarea',
                'help_text' => 'List the instructional materials, resources, and ICT tools to be used.',
                'placeholder' => 'e.g. PowerPoint presentation, worksheets, manipulatives, online resources...',
                'order' => 2,
                'column_map' => 'instructional_materials',
            ],
            [
                'key' => 'discussion_notes',
                'label' => 'Pre-Conference Discussion Notes',
                'type' => 'textarea',
                'help_text' => 'Document key discussion points including teaching strategies, learner diversity considerations, and assessment methods.',
                'placeholder' => 'Document the key discussion points from the pre-conference meeting...',
                'order' => 3,
                'column_map' => 'discussion_notes',
            ],
            [
                'key' => 'finalized_focus',
                'label' => 'Finalized Observation Focus',
                'type' => 'textarea',
                'help_text' => 'Areas agreed upon to be the focus of the classroom observation.',
                'placeholder' => 'e.g. Learner engagement strategies, differentiated instruction, classroom management...',
                'required' => false,
                'order' => 4,
                'column_map' => 'finalized_focus',
            ],
            [
                'key' => 'teacher_reflection',
                'label' => 'Teacher Reflection',
                'type' => 'textarea',
                'help_text' => 'The teacher\'s self-reflection on their lesson plan and anticipated challenges.',
                'placeholder' => 'Teacher\'s reflection on the lesson plan, teaching strategies, and expected outcomes...',
                'order' => 5,
                'column_map' => 'teacher_reflection',
            ],
        ]);

        // ===== OBSERVATION (COT) =====
        $obsSection = $template->sections()->create([
            'key' => 'observation',
            'label' => 'Classroom Observation (COT Ratings)',
            'order' => 2,
        ]);

        $cotIndicators = config('cot.versions.' . $schoolYear, config('cot.versions.' . config('cot.default_version')))['indicators'] ?? [];
        $domains = [];
        foreach ($cotIndicators as $i => $indicator) {
            $domains[$indicator['domain']][] = $indicator;
        }

        $fieldOrder = 0;
        foreach ($domains as $domainName => $indicators) {
            $obsSection->fields()->create([
                'key' => 'heading_' . Str::slug($domainName),
                'label' => $domainName,
                'type' => 'heading',
                'order' => $fieldOrder++,
            ]);

            foreach ($indicators as $indicator) {
                $obsSection->fields()->create([
                    'key' => 'rating_' . $indicator['code'],
                    'label' => $indicator['code'] . ' - ' . $indicator['description'],
                    'type' => 'rating',
                    'metadata' => [
                        'indicator_code' => $indicator['code'],
                        'domain' => $indicator['domain'],
                    ],
                    'required' => true,
                    'order' => $fieldOrder++,
                ]);
            }
        }

        $obsSection->fields()->createMany([
            [
                'key' => 'other_comments',
                'label' => 'Other Comments',
                'type' => 'textarea',
                'help_text' => 'Additional observations or notes.',
                'placeholder' => 'Any additional comments about the observation...',
                'order' => $fieldOrder++,
            ],
            [
                'key' => 'evidence_files',
                'label' => 'Evidence Files',
                'type' => 'file',
                'help_text' => 'Upload supporting evidence files.',
                'order' => $fieldOrder++,
                'column_map' => 'evidence_files',
            ],
        ]);

        // ===== POST-CONFERENCE =====
        $postConfSection = $template->sections()->create([
            'key' => 'post_conference',
            'label' => 'Post-Conference',
            'order' => 3,
        ]);

        $postConfSection->fields()->createMany([
            [
                'key' => 'conference_date',
                'label' => 'Post-Conference Date',
                'type' => 'date',
                'order' => 0,
                'column_map' => 'conference_date',
            ],
            [
                'key' => 'star_notes',
                'label' => 'STAR Notes - What\'s Going Well',
                'type' => 'textarea',
                'help_text' => 'Document specific observations of effective teaching practices observed.',
                'placeholder' => 'Describe what went well during the observation. Be specific and cite examples...',
                'order' => 1,
                'column_map' => 'star_notes',
            ],
            [
                'key' => 'challenges_facing_teacher',
                'label' => 'Identify Challenges',
                'type' => 'textarea',
                'help_text' => 'Identify challenges or difficulties observed during the lesson.',
                'placeholder' => 'What challenges did the teacher face during the lesson? e.g. time management, learner engagement, materials...',
                'order' => 2,
                'column_map' => 'challenges_facing_teacher',
            ],
            [
                'key' => 'areas_for_improvement',
                'label' => 'Areas for Improvement',
                'type' => 'textarea',
                'help_text' => 'Non-threatening areas where the teacher can grow and develop further.',
                'placeholder' => 'Identify specific areas where the teacher can improve. Frame these constructively...',
                'order' => 3,
                'column_map' => 'areas_for_improvement',
            ],
            [
                'key' => 'ideas_for_addressing_challenges',
                'label' => 'Ideas for Addressing Challenges',
                'type' => 'textarea',
                'help_text' => 'Collaboratively generate strategies and solutions to address the identified challenges.',
                'placeholder' => 'Brainstorm strategies with the teacher. What resources, training, or support can help address these challenges?',
                'order' => 4,
                'column_map' => 'ideas_for_addressing_challenges',
            ],
            [
                'key' => 'prioritized_next_steps',
                'label' => 'Prioritized Next Steps',
                'type' => 'textarea',
                'help_text' => 'Agree on actionable next steps with clear timelines and responsibilities.',
                'placeholder' => "1. ... (by when)\n2. ... (by when)\n3. ... (by when)",
                'order' => 5,
                'column_map' => 'prioritized_next_steps',
            ],
            [
                'key' => 'teacher_reflection',
                'label' => 'Teacher Reflection',
                'type' => 'textarea',
                'help_text' => 'The teacher\'s reflection on their observed lesson and the post-conference discussion.',
                'placeholder' => 'Teacher\'s personal reflection on the observation feedback and insights gained...',
                'order' => 6,
                'column_map' => 'teacher_reflection',
            ],
            [
                'key' => 'ai_comparison',
                'label' => 'AI Comparison (Plan vs Actual)',
                'type' => 'textarea',
                'help_text' => 'AI-generated comparison between the lesson plan and actual classroom observation.',
                'placeholder' => 'AI-generated comparison analysis will appear here...',
                'order' => 7,
                'column_map' => 'ai_comparison',
            ],
            [
                'key' => 'feedback',
                'label' => 'Supervisor Feedback',
                'type' => 'textarea',
                'help_text' => 'Overall feedback summary for the teacher.',
                'placeholder' => 'Provide overall feedback summarizing the observation and conference...',
                'order' => 8,
                'column_map' => 'feedback',
            ],
            [
                'key' => 'supervisor_notes',
                'label' => 'Supervisor\'s Private Notes',
                'type' => 'textarea',
                'help_text' => 'These notes are for your reference only and will not be visible to the teacher.',
                'placeholder' => 'Your private notes and reminders for future reference...',
                'order' => 9,
                'column_map' => 'supervisor_notes',
            ],
        ]);
    }
}
