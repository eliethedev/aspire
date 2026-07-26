<?php

namespace App\Services;

use Illuminate\Support\Collection;

class ProfessionalDevelopmentService
{
    private array $pdCatalog = [
        'Domain 1: Content Knowledge and Pedagogy' => [
            '1.1.2' => [
                'activities' => [
                    'Content Integration Workshop',
                    'Cross-Curricular Mapping Training',
                    'Subject-Specific Content Deepening Seminar',
                ],
                'strategies' => [
                    'Review DepEd curriculum guides for content alignment',
                    'Create cross-curricular lesson plans connecting related topics',
                    'Engage in content-specific reading and journal review',
                ],
            ],
            '1.2.2' => [
                'activities' => [
                    'Research-Based Teaching Methods Seminar',
                    'Action Research in the Classroom Workshop',
                    'Evidence-Based Practice Training',
                ],
                'strategies' => [
                    'Read current educational research journals',
                    'Design and implement a classroom-based action research project',
                    'Apply findings from recent pedagogical studies to lesson planning',
                ],
            ],
            '1.3.2' => [
                'activities' => [
                    'Developmentally Appropriate Practice (DAP) Training',
                    'Differentiated Instruction Workshop',
                    'Learner-Centered Teaching Strategies Seminar',
                ],
                'strategies' => [
                    'Use varied instructional strategies for diverse developmental levels',
                    'Incorporate play-based and experiential learning activities',
                    'Design tasks appropriate to learners\' cognitive stages',
                ],
            ],
            '1.4.2' => [
                'activities' => [
                    'Standards-Based Lesson Planning Workshop',
                    'Competence-Based Curriculum Design Training',
                    'Learning Competency Alignment Workshop',
                ],
                'strategies' => [
                    'Align lesson objectives with MELCs and learning standards',
                    'Design formative assessments tied to specific competencies',
                    'Use backward design principles in lesson planning',
                ],
            ],
            '1.5.2' => [
                'activities' => [
                    'Inclusive Education Training',
                    'Understanding Learner Diversity Workshop',
                    'Multiple Intelligences in the Classroom Seminar',
                ],
                'strategies' => [
                    'Conduct learner profiling to understand individual differences',
                    'Design activities catering to various learning styles',
                    'Implement flexible grouping strategies in the classroom',
                ],
            ],
            '1.6.2' => [
                'activities' => [
                    'Subject Matter Expertise Enhancement Program',
                    'Advanced Content Knowledge Training',
                    'Pedagogical Content Knowledge Workshop',
                ],
                'strategies' => [
                    'Pursue advanced study in teaching subject area',
                    'Participate in subject-specific professional learning communities',
                    'Engage in continuous content mastery through self-study',
                ],
            ],
        ],
        'Domain 2: Learning Environment' => [
            '2.1.2' => [
                'activities' => [
                    'Classroom Safety and Well-Being Training',
                    'Creating Positive Learning Environments Workshop',
                    'Child Protection Policy Orientation',
                ],
                'strategies' => [
                    'Establish and consistently implement classroom rules',
                    'Create physically and psychologically safe learning spaces',
                    'Practice empathy and active listening with learners',
                ],
            ],
            '2.2.2' => [
                'activities' => [
                    'Fairness and Equity in Education Seminar',
                    'Anti-Bias Teaching Practices Workshop',
                    'Gender-Responsive Pedagogy Training',
                ],
                'strategies' => [
                    'Use equitable participation strategies in class discussions',
                    'Apply consistent and fair grading practices',
                    'Address biases in instructional materials and interactions',
                ],
            ],
            '2.3.2' => [
                'activities' => [
                    'Classroom Structure and Organization Workshop',
                    'Engaging Classroom Layout Design Training',
                    'Active Learning Space Management Seminar',
                ],
                'strategies' => [
                    'Optimize classroom layout for collaborative learning',
                    'Use strategic seating arrangements for engagement',
                    'Create learning stations and activity centers',
                ],
            ],
            '2.4.2' => [
                'activities' => [
                    'Classroom Management Strategies Training',
                    'Positive Behavior Support Workshop',
                    'Discipline and Order in the Classroom Seminar',
                ],
                'strategies' => [
                    'Implement consistent routines and transitions',
                    'Use positive reinforcement techniques',
                    'Establish clear expectations and consequences',
                ],
            ],
            '2.5.2' => [
                'activities' => [
                    'Learner Participation Strategies Workshop',
                    'Interactive Teaching Methods Training',
                    'Cooperative Learning Structures Seminar',
                ],
                'strategies' => [
                    'Use think-pair-share, gallery walks, and other participation structures',
                    'Implement random calling and wait time techniques',
                    'Create opportunities for student-led discussions',
                ],
            ],
            '2.6.2' => [
                'activities' => [
                    'Constructive Behavior Management Training',
                    'Restorative Practices in Education Workshop',
                    'Positive Discipline Seminar',
                ],
                'strategies' => [
                    'Use restorative conversations instead of punitive measures',
                    'Implement behavior contracts and goal-setting with learners',
                    'Apply de-escalation techniques for challenging behaviors',
                ],
            ],
        ],
        'Domain 3: Diversity of Learners' => [
            '3.1.2' => [
                'activities' => [
                    'Child Protection and Learner Safety Orientation',
                    'DepEd Policies and Guidelines Seminar',
                    'Learner Rights and Welfare Training',
                ],
                'strategies' => [
                    'Familiarize with DepEd Order on child protection',
                    'Implement reporting mechanisms for learner concerns',
                    'Apply learner-centered policies in daily practice',
                ],
            ],
            '3.2.2' => [
                'activities' => [
                    'Mother Tongue-Based Multilingual Education (MTB-MLE) Training',
                    'Culturally Responsive Teaching Workshop',
                    'Linguistic Diversity in the Classroom Seminar',
                ],
                'strategies' => [
                    'Incorporate learners\' home languages in instruction',
                    'Use culturally relevant examples and materials',
                    'Design activities that celebrate cultural diversity',
                ],
            ],
            '3.3.2' => [
                'activities' => [
                    'Socioeconomically Responsive Teaching Workshop',
                    'Teaching in Under-Resourced Contexts Seminar',
                    'Community-Based Learning Strategies Training',
                ],
                'strategies' => [
                    'Use locally available instructional materials',
                    'Design activities requiring minimal resources',
                    'Connect lessons to learners\' real-life contexts',
                ],
            ],
            '3.4.2' => [
                'activities' => [
                    'Inclusive Education for Learners with Disabilities Training',
                    'Universal Design for Learning (UDL) Workshop',
                    'Adaptive Teaching Strategies Seminar',
                ],
                'strategies' => [
                    'Apply Universal Design for Learning principles',
                    'Provide multiple means of representation and engagement',
                    'Use assistive technologies where available',
                ],
            ],
            '3.5.2' => [
                'activities' => [
                    'Gifted and Talented Learners Enrichment Training',
                    'Differentiated Instruction for Advanced Learners Workshop',
                    'Talent Development and Recognition Seminar',
                ],
                'strategies' => [
                    'Provide enrichment activities for advanced learners',
                    'Use compacting and acceleration strategies',
                    'Create opportunities for creative and critical thinking',
                ],
            ],
        ],
        'Domain 4: Curriculum and Planning' => [
            '4.1.2' => [
                'activities' => [
                    'Instructional Materials Development Workshop',
                    'Technology-Enhanced Teaching Training',
                    'Resource-Based Learning Seminar',
                ],
                'strategies' => [
                    'Select and use diverse teaching and learning resources',
                    'Integrate technology tools for interactive instruction',
                    'Create and adapt materials suited to learner needs',
                ],
            ],
            '4.2.2' => [
                'activities' => [
                    'Assessment Strategies for Diverse Learners Workshop',
                    'Formative Assessment Techniques Training',
                    'Performance-Based Assessment Design Seminar',
                ],
                'strategies' => [
                    'Use varied assessment methods (oral, written, performance)',
                    'Design assessments aligned with learning objectives',
                    'Implement peer and self-assessment strategies',
                ],
            ],
            '4.3.2' => [
                'activities' => [
                    'Instructional Planning and Lesson Design Workshop',
                    'Backward Design and UBD Training',
                    'Strategic Lesson Planning Seminar',
                ],
                'strategies' => [
                    'Use the 4Ds framework: Display, Discover, Deepen, Defend',
                    'Plan lessons with clear objectives, activities, and assessments',
                    'Align instructional plans with curriculum standards',
                ],
            ],
            '4.4.2' => [
                'activities' => [
                    'Active Learning Strategies Workshop',
                    'Engaging Learning Activities Design Training',
                    'Experiential Learning Seminar',
                ],
                'strategies' => [
                    'Design collaborative and inquiry-based activities',
                    'Use problem-solving and critical thinking tasks',
                    'Incorporate real-world applications in learning activities',
                ],
            ],
            '4.5.2' => [
                'activities' => [
                    'Learning Outcomes Design and Alignment Workshop',
                    'Outcome-Based Education Training',
                    'Curriculum Alignment and Mapping Seminar',
                ],
                'strategies' => [
                    'Write clear, measurable, and achievable learning outcomes',
                    'Align learning outcomes with MELCs and assessments',
                    'Use blooms taxonomy to design cognitive-level-appropriate outcomes',
                ],
            ],
        ],
        'Domain 5: Assessment and Reporting' => [
            '5.1.2' => [
                'activities' => [
                    'Assessment Tool Development Workshop',
                    'Constructing Valid and Reliable Tests Training',
                    'Authentic Assessment Design Seminar',
                ],
                'strategies' => [
                    'Create rubrics aligned with learning outcomes',
                    'Design tests with varied item types (MC, essay, performance)',
                    'Validate assessment tools for reliability and validity',
                ],
            ],
            '5.2.2' => [
                'activities' => [
                    'Learner Progress Monitoring Workshop',
                    'Data-Driven Instruction Training',
                    'Formative Assessment and Feedback Seminar',
                ],
                'strategies' => [
                    'Maintain systematic records of learner progress',
                    'Use assessment data to inform instructional decisions',
                    'Implement regular check-ins and progress monitoring',
                ],
            ],
            '5.3.2' => [
                'activities' => [
                    'Effective Feedback Strategies Workshop',
                    'Timely and Constructive Feedback Training',
                    'Feed-Forward Assessment Seminar',
                ],
                'strategies' => [
                    'Provide specific, actionable, and timely feedback',
                    'Use written and oral feedback effectively',
                    'Guide learners to use feedback for improvement',
                ],
            ],
            '5.4.2' => [
                'activities' => [
                    'Stakeholder Communication Workshop',
                    'Parent-Teacher Conference Training',
                    'Learner Progress Reporting Seminar',
                ],
                'strategies' => [
                    'Prepare clear and comprehensive progress reports',
                    'Communicate regularly with parents/guardians',
                    'Use multiple channels for stakeholder communication',
                ],
            ],
            '5.5.2' => [
                'activities' => [
                    'Assessment-Driven Improvement Workshop',
                    'Using Data to Improve Teaching Practice Training',
                    'Reflective Practice and Action Research Seminar',
                ],
                'strategies' => [
                    'Analyze assessment results to identify learning gaps',
                    'Adjust instruction based on assessment findings',
                    'Document and share best practices from data analysis',
                ],
            ],
        ],
    ];

    public function getRecommendations(array $lowIndicators): array
    {
        $recommendations = [];
        $prioritized = collect($lowIndicators)->sortBy('average_rating');

        foreach ($prioritized as $indicator) {
            $domain = $indicator['domain'] ?? '';
            $code = $indicator['code'] ?? '';

            $catalog = $this->pdCatalog[$domain][$code] ?? null;

            if ($catalog) {
                $recommendations[] = [
                    'indicator_code' => $code,
                    'indicator' => $indicator['indicator'] ?? '',
                    'domain' => $domain,
                    'current_average' => $indicator['average_rating'],
                    'severity' => $this->getSeverity($indicator['average_rating']),
                    'activities' => $catalog['activities'],
                    'strategies' => $catalog['strategies'],
                ];
            } else {
                $recommendations[] = [
                    'indicator_code' => $code,
                    'indicator' => $indicator['indicator'] ?? '',
                    'domain' => $domain,
                    'current_average' => $indicator['average_rating'],
                    'severity' => $this->getSeverity($indicator['average_rating']),
                    'activities' => [
                        'Professional development workshop for ' . $domain,
                        'Peer coaching and mentoring sessions',
                        'Self-directed learning and reflection',
                    ],
                    'strategies' => [
                        'Review PPST indicator requirements and rubric criteria',
                        'Observe proficient peers demonstrating this competency',
                        'Create an individual development plan with specific goals',
                    ],
                ];
            }
        }

        return $recommendations;
    }

    public function generatePDPlan(array $lowIndicators, string $teacherName): array
    {
        $recommendations = $this->getRecommendations($lowIndicators);

        $shortTerm = array_filter($recommendations, fn($r) => $r['severity'] === 'critical' || $r['severity'] === 'high');
        $longTerm = array_filter($recommendations, fn($r) => $r['severity'] === 'medium');

        return [
            'teacher' => $teacherName,
            'generated_at' => now()->format('F d, Y'),
            'short_term_goals' => array_map(fn($r) => [
                'indicator' => "{$r['indicator_code']}: {$r['indicator']}",
                'target' => "Improve from {$r['current_average']}/6 to at least 3.5/6 within one grading period",
                'activities' => array_slice($r['activities'], 0, 2),
                'timeline' => '1-2 months',
                'support_needed' => 'Supervisory coaching and targeted workshop',
            ], array_values($shortTerm)),
            'long_term_goals' => array_map(fn($r) => [
                'indicator' => "{$r['indicator_code']}: {$r['indicator']}",
                'target' => "Achieve satisfactory (4.0/6) or above within one school year",
                'activities' => array_slice($r['activities'], 0, 2),
                'timeline' => '3-6 months',
                'support_needed' => 'Professional development seminars and peer mentoring',
            ], array_values($longTerm)),
            'all_recommendations' => $recommendations,
        ];
    }

    private function getSeverity(float $rating): string
    {
        return match (true) {
            $rating < 2 => 'critical',
            $rating < 3 => 'high',
            $rating < 3.5 => 'medium',
            default => 'low',
        };
    }
}
