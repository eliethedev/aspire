<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Teacher Career Stages
    |--------------------------------------------------------------------------
    |
    | Central catalogue of DepEd career stages used to resolve which COT
    | instrument (indicator set + rating scale) applies to a teacher.
    |
    | COT versions store one of these enum values in `career_stage`. A version
    | with a NULL career stage applies to every teacher stage (the "generic"
    | instrument, e.g. the current Teacher I-III COT which is used app-wide).
    |
    | Position strings on the teachers table are normalised into these stages
    | via App\Enums\TeacherCareerStage::fromPosition().
    |
    */

    'stages' => [
        'teacher_i_iii' => 'Teacher I-III',
        'teacher_iv_vii' => 'Teacher IV-VII',
        'master_teacher_i_ii' => 'Master Teacher I-II',
        'master_teacher_iii_v' => 'Master Teacher III-V',
    ],

    /*
    |--------------------------------------------------------------------------
    | Position Aliases per Stage
    |--------------------------------------------------------------------------
    |
    | Free-text position strings that map onto each career stage. Used when
    | normalising existing records (`php artisan teachers:normalize-career-stages`)
    | and by TeacherCareerStage::fromPosition(). Matching is case-insensitive
    | and ignores whitespace/punctuation.
    |
    */

    'position_aliases' => [
        'teacher_i_iii' => [
            'teacher i',
            'teacher ii',
            'teacher iii',
            'teacher 1',
            'teacher 2',
            'teacher 3',
            'teacher i-iii',
            'teacher i to iii',
        ],
        'teacher_iv_vii' => [
            'teacher iv',
            'teacher v',
            'teacher vi',
            'teacher vii',
            'teacher 4',
            'teacher 5',
            'teacher 6',
            'teacher 7',
            'teacher iv-vii',
            'teacher iv to vii',
        ],
        'master_teacher_i_ii' => [
            'master teacher i',
            'master teacher ii',
            'master teacher 1',
            'master teacher 2',
            'master teacher i-ii',
            'master teacher i to ii',
        ],
        'master_teacher_iii_v' => [
            'master teacher iii',
            'master teacher iv',
            'master teacher v',
            'master teacher 3',
            'master teacher 4',
            'master teacher 5',
            'master teacher iii-v',
            'master teacher iii to v',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Standards Frameworks, Career Tracks and Ratee Positions
    |--------------------------------------------------------------------------
    |
    | The version context an administrator picks on the Create Version form.
    | Each framework (PPST for teaching personnel, PPSSH for school heads)
    | exposes its career tracks and, per track, the ratee positions that a
    | versioned instrument may target.
    |
    | The career stage is NEVER chosen manually. It is derived from the
    | selected framework + career track + ratee position by
    | App\Services\CareerStageResolver. The server re-resolves the stage on
    | every store/update and ignores whatever the browser sent.
    |
    | PPST positions reuse the teacher career-stage keys so a version's
    | `career_stage` column stays compatible with App\Enums\TeacherCareerStage
    | and the observation resolver in App\Services\CotIndicatorService.
    |
    */

    'frameworks' => [
        'ppst' => [
            'label' => 'PPST',
            'tracks' => [
                'classroom_teaching' => [
                    'label' => 'Classroom Teaching',
                    'positions' => [
                        'teacher_i_iii' => ['label' => 'Teacher I-III', 'career_stage' => 'teacher_i_iii'],
                        'teacher_iv_vii' => ['label' => 'Teacher IV-VII', 'career_stage' => 'teacher_iv_vii'],
                        'master_teacher_i_ii' => ['label' => 'Master Teacher I-II', 'career_stage' => 'master_teacher_i_ii'],
                        'master_teacher_iii_v' => ['label' => 'Master Teacher III-V', 'career_stage' => 'master_teacher_iii_v'],
                    ],
                ],
            ],
        ],
        'ppssh' => [
            'label' => 'PPSSH',
            'tracks' => [
                'school_administration' => [
                    'label' => 'School Administration',
                    'positions' => [
                        'aspiring_school_head' => ['label' => 'Aspiring School Head', 'career_stage' => 'career_stage_i'],
                        'school_principal_i_ii' => ['label' => 'School Principal I-II', 'career_stage' => 'career_stage_ii'],
                        'school_principal_iii' => ['label' => 'School Principal III', 'career_stage' => 'career_stage_iii'],
                        'school_principal_iv' => ['label' => 'School Principal IV', 'career_stage' => 'career_stage_iv'],
                    ],
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Career Stage Display Labels
    |--------------------------------------------------------------------------
    |
    | Read-only label shown on the Create/Edit Version forms. PPST teacher
    | stages keep their existing position-group keys; PPSSH stages use the
    | generic stage keys so the same stage number never collides across
    | frameworks.
    |
    */

    'career_stage_labels' => [
        'teacher_i_iii' => 'Career Stage I',
        'teacher_iv_vii' => 'Career Stage II',
        'master_teacher_i_ii' => 'Career Stage III',
        'master_teacher_iii_v' => 'Career Stage IV',
        'career_stage_i' => 'Career Stage I',
        'career_stage_ii' => 'Career Stage II',
        'career_stage_iii' => 'Career Stage III',
        'career_stage_iv' => 'Career Stage IV',
    ],

    /*
    |--------------------------------------------------------------------------
    | Instruments
    |--------------------------------------------------------------------------
    |
    | Versioned observation instruments. A version currently always stores the
    | classroom observation tool ('cot'); kept as data so future instruments
    | can be added without schema changes.
    |
    */

    'instruments' => [
        'cot' => 'COT',
    ],
];
