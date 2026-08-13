<?php

return [

    /*
    |--------------------------------------------------------------------------
    | COT Indicator Versions by School Year
    |--------------------------------------------------------------------------
    |
    | Maps school year ranges to their corresponding indicator sets.
    | The system uses the observation's school_year to determine
    | which set of indicators to display.
    |
    | Each version may carry optional keys (all nullable):
    |   - ratee_role    : 'teacher' (default) or 'school_head'
    |   - career_stage  : one of config('career_stages.stages') keys, or null
    |                     for a stage-agnostic instrument (applies to all
    |                     teacher stages). e.g. 'master_teacher_i_ii'
    |   - rating_scale  : per-version rating scale (value => label). Falls
    |                     back to the global 'rating_scale' below when omitted.
    |   - rating_scale_css : per-version Tailwind classes per rating value.
    |
    | Versions without a career_stage remain the fallback instrument used for
    | every teacher, preserving the current app-wide Teacher I-III behaviour.
    |
    */

    'versions' => [
        '2025-2026' => [
            'label' => 'COT 2025-2026',
            'indicators' => [
                [
                    'code' => '1.1.2',
                    'description' => 'Apply knowledge of content within and across curriculum teaching areas',
                    'domain' => 'Domain 1: Content Knowledge and Pedagogy',
                ],
                [
                    'code' => '1.2.2',
                    'description' => 'Use research-based knowledge and principles of teaching and learning to enhance professional practice',
                    'domain' => 'Domain 1: Content Knowledge and Pedagogy',
                ],
                [
                    'code' => '1.3.2',
                    'description' => 'Use developmentally appropriate teaching strategies to address learners\' developmental needs',
                    'domain' => 'Domain 1: Content Knowledge and Pedagogy',
                ],
                [
                    'code' => '1.4.2',
                    'description' => 'Plan and teach using standard- and competence-based learning',
                    'domain' => 'Dom ain 1: Content Knowledge and Pedagogy',
                ],
                [
                    'code' => '1.5.2',
                    'description' => 'Apply knowledge of learner diversity and individual differences',
                    'domain' => 'Domain 1: Content Knowledge and Pedagogy',
                ],
                [
                    'code' => '1.6.2',
                    'description' => 'Demonstrate mastery of subject matter',
                    'domain' => 'Domain 1: Content Knowledge and Pedagogy',
                ],
                [
                    'code' => '2.1.2',
                    'description' => 'Establish safe and secure learning environments',
                    'domain' => 'Domain 2: Learning Environment',
                ],
                [
                    'code' => '2.2.2',
                    'description' => 'Promote fairness in the classroom',
                    'domain' => 'Domain 2: Learning Environment',
                ],
                [
                    'code' => '2.3.2',
                    'description' => 'Manage classroom structure to engage learners',
                    'domain' => 'Domain 2: Learning Environment',
                ],
                [
                    'code' => '2.4.2',
                    'description' => 'Manage classroom activities to maintain discipline',
                    'domain' => 'Domain 2: Learning Environment',
                ],
                [
                    'code' => '2.5.2',
                    'description' => 'Use classroom procedures that support learner participation',
                    'domain' => 'Domain 2: Learning Environment',
                ],
                [
                    'code' => '2.6.2',
                    'description' => 'Manage learner behavior constructively',
                    'domain' => 'Domain 2: Learning Environment',
                ],
                [
                    'code' => '3.1.2',
                    'description' => 'Demonstrate knowledge of policies and guidelines on learner protection',
                    'domain' => 'Domain 3: Diversity of Learners',
                ],
                [
                    'code' => '3.2.2',
                    'description' => 'Adapt and use teaching strategies that are responsive to learners\' linguistic and cultural background',
                    'domain' => 'Domain 3: Diversity of Learners',
                ],
                [
                    'code' => '3.3.2',
                    'description' => 'Adapt and use teaching strategies that are responsive to learners\' socioeconomic background',
                    'domain' => 'Domain 3: Diversity of Learners',
                ],
                [
                    'code' => '3.4.2',
                    'description' => 'Adapt and use teaching strategies that are responsive to learners\' physical disabilities',
                    'domain' => 'Domain 3: Diversity of Learners',
                ],
                [
                    'code' => '3.5.2',
                    'description' => 'Adapt and use teaching strategies that are responsive to learners\' giftedness and talents',
                    'domain' => 'Domain 3: Diversity of Learners',
                ],
                [
                    'code' => '4.1.2',
                    'description' => 'Plan and deliver lessons using appropriate teaching and learning resources',
                    'domain' => 'Domain 4: Curriculum and Planning',
                ],
                [
                    'code' => '4.2.2',
                    'description' => 'Plan and deliver lessons using appropriate assessment strategies',
                    'domain' => 'Domain 4: Curriculum and Planning',
                ],
                [
                    'code' => '4.3.2',
                    'description' => 'Plan and deliver lessons using appropriate instructional planning',
                    'domain' => 'Domain 4: Curriculum and Planning',
                ],
                [
                    'code' => '4.4.2',
                    'description' => 'Plan and deliver lessons using appropriate learning activities',
                    'domain' => 'Domain 4: Curriculum and Planning',
                ],
                [
                    'code' => '4.5.2',
                    'description' => 'Plan and deliver lessons using appropriate learning outcomes',
                    'domain' => 'Domain 4: Curriculum and Planning',
                ],
                [
                    'code' => '5.1.2',
                    'description' => 'Design and use assessment tools that are aligned with learning outcomes',
                    'domain' => 'Domain 5: Assessment and Reporting',
                ],
                [
                    'code' => '5.2.2',
                    'description' => 'Monitor and evaluate learner progress',
                    'domain' => 'Domain 5: Assessment and Reporting',
                ],
                [
                    'code' => '5.3.2',
                    'description' => 'Provide timely and accurate feedback to learners',
                    'domain' => 'Domain 5: Assessment and Reporting',
                ],
                [
                    'code' => '5.4.2',
                    'description' => 'Communicate learner progress to stakeholders',
                    'domain' => 'Domain 5: Assessment and Reporting',
                ],
                [
                    'code' => '5.5.2',
                    'description' => 'Use assessment data to improve teaching and learning',
                    'domain' => 'Domain 5: Assessment and Reporting',
                ],
            ],
        ],

        '2026-2027' => [
            'label' => 'COT 2026-2027',
            'indicators' => [
                [
                    'code' => '1.1.2',
                    'description' => 'Apply knowledge of content within and across curriculum teaching areas',
                    'domain' => 'Domain 1: Content Knowledge and Pedagogy',
                ],
                [
                    'code' => '1.2.2',
                    'description' => 'Use research-based knowledge and principles of teaching and learning to enhance professional practice',
                    'domain' => 'Domain 1: Content Knowledge and Pedagogy',
                ],
                [
                    'code' => '1.3.2',
                    'description' => 'Use developmentally appropriate teaching strategies to address learners\' developmental needs',
                    'domain' => 'Domain 1: Content Knowledge and Pedagogy',
                ],
                [
                    'code' => '1.4.2',
                    'description' => 'Plan and teach using standard- and competence-based learning',
                    'domain' => 'Domain 1: Content Knowledge and Pedagogy',
                ],
                [
                    'code' => '1.5.2',
                    'description' => 'Apply knowledge of learner diversity and individual differences',
                    'domain' => 'Domain 1: Content Knowledge and Pedagogy',
                ],
                [
                    'code' => '1.6.2',
                    'description' => 'Demonstrate mastery of subject matter',
                    'domain' => 'Domain 1: Content Knowledge and Pedagogy',
                ],
                [
                    'code' => '2.1.2',
                    'description' => 'Establish safe and secure learning environments',
                    'domain' => 'Domain 2: Learning Environment',
                ],
                [
                    'code' => '2.2.2',
                    'description' => 'Promote fairness in the classroom',
                    'domain' => 'Domain 2: Learning Environment',
                ],
                [
                    'code' => '2.3.2',
                    'description' => 'Manage classroom structure to engage learners',
                    'domain' => 'Domain 2: Learning Environment',
                ],
                [
                    'code' => '2.4.2',
                    'description' => 'Manage classroom activities to maintain discipline',
                    'domain' => 'Domain 2: Learning Environment',
                ],
                [
                    'code' => '2.5.2',
                    'description' => 'Use classroom procedures that support learner participation',
                    'domain' => 'Domain 2: Learning Environment',
                ],
                [
                    'code' => '2.6.2',
                    'description' => 'Manage learner behavior constructively',
                    'domain' => 'Domain 2: Learning Environment',
                ],
                [
                    'code' => '3.1.2',
                    'description' => 'Demonstrate knowledge of policies and guidelines on learner protection',
                    'domain' => 'Domain 3: Diversity of Learners',
                ],
                [
                    'code' => '3.2.2',
                    'description' => 'Adapt and use teaching strategies that are responsive to learners\' linguistic and cultural background',
                    'domain' => 'Domain 3: Diversity of Learners',
                ],
                [
                    'code' => '3.3.2',
                    'description' => 'Adapt and use teaching strategies that are responsive to learners\' socioeconomic background',
                    'domain' => 'Domain 3: Diversity of Learners',
                ],
                [
                    'code' => '3.4.2',
                    'description' => 'Adapt and use teaching strategies that are responsive to learners\' physical disabilities',
                    'domain' => 'Domain 3: Diversity of Learners',
                ],
                [
                    'code' => '3.5.2',
                    'description' => 'Adapt and use teaching strategies that are responsive to learners\' giftedness and talents',
                    'domain' => 'Domain 3: Diversity of Learners',
                ],
                [
                    'code' => '4.1.2',
                    'description' => 'Plan and deliver lessons using appropriate teaching and learning resources',
                    'domain' => 'Domain 4: Curriculum and Planning',
                ],
                [
                    'code' => '4.2.2',
                    'description' => 'Plan and deliver lessons using appropriate assessment strategies',
                    'domain' => 'Domain 4: Curriculum and Planning',
                ],
                [
                    'code' => '4.3.2',
                    'description' => 'Plan and deliver lessons using appropriate instructional planning',
                    'domain' => 'Domain 4: Curriculum and Planning',
                ],
                [
                    'code' => '4.4.2',
                    'description' => 'Plan and deliver lessons using appropriate learning activities',
                    'domain' => 'Domain 4: Curriculum and Planning',
                ],
                [
                    'code' => '4.5.2',
                    'description' => 'Plan and deliver lessons using appropriate learning outcomes',
                    'domain' => 'Domain 4: Curriculum and Planning',
                ],
                [
                    'code' => '5.1.2',
                    'description' => 'Design and use assessment tools that are aligned with learning outcomes',
                    'domain' => 'Domain 5: Assessment and Reporting',
                ],
                [
                    'code' => '5.2.2',
                    'description' => 'Monitor and evaluate learner progress',
                    'domain' => 'Domain 5: Assessment and Reporting',
                ],
                [
                    'code' => '5.3.2',
                    'description' => 'Provide timely and accurate feedback to learners',
                    'domain' => 'Domain 5: Assessment and Reporting',
                ],
                [
                    'code' => '5.4.2',
                    'description' => 'Communicate learner progress to stakeholders',
                    'domain' => 'Domain 5: Assessment and Reporting',
                ],
                [
                    'code' => '5.5.2',
                    'description' => 'Use assessment data to improve teaching and learning',
                    'domain' => 'Domain 5: Assessment and Reporting',
                ],
            ],
        ],

        '2027-2028' => [
            'label' => 'COT 2027-2028',
            'indicators' => [
                [
                    'code' => '1.1.2',
                    'description' => 'Apply knowledge of content within and across curriculum teaching areas',
                    'domain' => 'Domain 1: Content Knowledge and Pedagogy',
                ],
                [
                    'code' => '1.2.2',
                    'description' => 'Use research-based knowledge and principles of teaching and learning to enhance professional practice',
                    'domain' => 'Domain 1: Content Knowledge and Pedagogy',
                ],
                [
                    'code' => '1.3.2',
                    'description' => 'Use developmentally appropriate teaching strategies to address learners\' developmental needs',
                    'domain' => 'Domain 1: Content Knowledge and Pedagogy',
                ],
                [
                    'code' => '1.4.2',
                    'description' => 'Plan and teach using standard- and competence-based learning',
                    'domain' => 'Domain 1: Content Knowledge and Pedagogy',
                ],
                [
                    'code' => '1.5.2',
                    'description' => 'Apply knowledge of learner diversity and individual differences',
                    'domain' => 'Domain 1: Content Knowledge and Pedagogy',
                ],
                [
                    'code' => '1.6.2',
                    'description' => 'Demonstrate mastery of subject matter',
                    'domain' => 'Domain 1: Content Knowledge and Pedagogy',
                ],
                [
                    'code' => '2.1.2',
                    'description' => 'Establish safe and secure learning environments',
                    'domain' => 'Domain 2: Learning Environment',
                ],
                [
                    'code' => '2.2.2',
                    'description' => 'Promote fairness in the classroom',
                    'domain' => 'Domain 2: Learning Environment',
                ],
                [
                    'code' => '2.3.2',
                    'description' => 'Manage classroom structure to engage learners',
                    'domain' => 'Domain 2: Learning Environment',
                ],
                [
                    'code' => '2.4.2',
                    'description' => 'Manage classroom activities to maintain discipline',
                    'domain' => 'Domain 2: Learning Environment',
                ],
                [
                    'code' => '2.5.2',
                    'description' => 'Use classroom procedures that support learner participation',
                    'domain' => 'Domain 2: Learning Environment',
                ],
                [
                    'code' => '2.6.2',
                    'description' => 'Manage learner behavior constructively',
                    'domain' => 'Domain 2: Learning Environment',
                ],
                [
                    'code' => '3.1.2',
                    'description' => 'Demonstrate knowledge of policies and guidelines on learner protection',
                    'domain' => 'Domain 3: Diversity of Learners',
                ],
                [
                    'code' => '3.2.2',
                    'description' => 'Adapt and use teaching strategies that are responsive to learners\' linguistic and cultural background',
                    'domain' => 'Domain 3: Diversity of Learners',
                ],
                [
                    'code' => '3.3.2',
                    'description' => 'Adapt and use teaching strategies that are responsive to learners\' socioeconomic background',
                    'domain' => 'Domain 3: Diversity of Learners',
                ],
                [
                    'code' => '3.4.2',
                    'description' => 'Adapt and use teaching strategies that are responsive to learners\' physical disabilities',
                    'domain' => 'Domain 3: Diversity of Learners',
                ],
                [
                    'code' => '3.5.2',
                    'description' => 'Adapt and use teaching strategies that are responsive to learners\' giftedness and talents',
                    'domain' => 'Domain 3: Diversity of Learners',
                ],
                [
                    'code' => '4.1.2',
                    'description' => 'Plan and deliver lessons using appropriate teaching and learning resources',
                    'domain' => 'Domain 4: Curriculum and Planning',
                ],
                [
                    'code' => '4.2.2',
                    'description' => 'Plan and deliver lessons using appropriate assessment strategies',
                    'domain' => 'Domain 4: Curriculum and Planning',
                ],
                [
                    'code' => '4.3.2',
                    'description' => 'Plan and deliver lessons using appropriate instructional planning',
                    'domain' => 'Domain 4: Curriculum and Planning',
                ],
                [
                    'code' => '4.4.2',
                    'description' => 'Plan and deliver lessons using appropriate learning activities',
                    'domain' => 'Domain 4: Curriculum and Planning',
                ],
                [
                    'code' => '4.5.2',
                    'description' => 'Plan and deliver lessons using appropriate learning outcomes',
                    'domain' => 'Domain 4: Curriculum and Planning',
                ],
                [
                    'code' => '5.1.2',
                    'description' => 'Design and use assessment tools that are aligned with learning outcomes',
                    'domain' => 'Domain 5: Assessment and Reporting',
                ],
                [
                    'code' => '5.2.2',
                    'description' => 'Monitor and evaluate learner progress',
                    'domain' => 'Domain 5: Assessment and Reporting',
                ],
                [
                    'code' => '5.3.2',
                    'description' => 'Provide timely and accurate feedback to learners',
                    'domain' => 'Domain 5: Assessment and Reporting',
                ],
                [
                    'code' => '5.4.2',
                    'description' => 'Communicate learner progress to stakeholders',
                    'domain' => 'Domain 5: Assessment and Reporting',
                ],
                [
                    'code' => '5.5.2',
                    'description' => 'Use assessment data to improve teaching and learning',
                    'domain' => 'Domain 5: Assessment and Reporting',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | COT Rating Scale
    |--------------------------------------------------------------------------
    |
    | Official COT rating scale per DepEd Order.
    |
    */

    'rating_scale' => [
        6 => 'Outstanding',
        5 => 'Very Satisfactory',
        4 => 'Satisfactory',
        3 => 'Unsatisfactory',
        2 => 'Poor',
    ],

    'rating_scale_css' => [
        6 => 'bg-green-600',
        5 => 'bg-blue-500',
        4 => 'bg-yellow-400',
        3 => 'bg-orange-400',
        2 => 'bg-red-500',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Version
    |--------------------------------------------------------------------------
    |
    | Fallback school year version if observation has none set.
    |
    */

    'default_version' => '2025-2026',

];
