<?php

/*
|--------------------------------------------------------------------------
| COT Indicator Versions by School Year & Career Stage
|--------------------------------------------------------------------------
|
| Maps school year ranges to per-career-stage indicator sets. Each career
| stage (TI-TIII, TIV-TVII, MTI-MTII) uses its OWN COT rating sheet with a
| distinct indicator set AND a distinct rating scale, matching the official
| DepEd Annex E-2 forms:
|
|   Teacher I-III         : 2-6 scale   (NO = 2)
|   Teacher IV-VII        : 3-7 scale   (NO = 3)
|   Master Teacher I-II   : 4-8 scale   (NO = 4)
|
| The system resolves the correct version by the observation's school year
| and the observee's career stage (see CotIndicatorService).
|
*/

$d1 = 'Domain 1: Content Knowledge and Pedagogy';
$d2 = 'Domain 2: Learning Environment';
$d3 = 'Domain 3: Diversity of Learners';
$d4 = 'Domain 4: Curriculum and Planning';
$d5 = 'Domain 5: Assessment and Reporting';

/*
|--------------------------------------------------------------------------
| Teacher I-III Indicators (Proficient Teacher A)
|--------------------------------------------------------------------------
*/

$ti2025 = [
    ['code' => '1.1.2', 'description' => 'Apply knowledge of content within and across curriculum teaching areas', 'domain' => $d1],
    ['code' => '1.4.2', 'description' => 'Use a range of teaching strategies that enhance learner achievement in literacy and numeracy skills', 'domain' => $d1],
    ['code' => '1.5.2', 'description' => 'Apply a range of teaching strategies to develop critical and creative thinking, as well as other higher-order thinking skills', 'domain' => $d1],
    ['code' => '2.3.2', 'description' => 'Manage classroom structure to engage learners, individually or in groups, in meaningful exploration, discovery and hands-on activities within a range of physical learning environments', 'domain' => $d2],
    ['code' => '2.6.2', 'description' => 'Manage learner behavior constructively by applying positive and non-violent discipline to ensure learning-focused environments', 'domain' => $d2],
    ['code' => '3.1.2', 'description' => "Use differentiated, developmentally appropriate learning experiences to address learners' gender, needs, strengths, interests and experiences", 'domain' => $d3],
    ['code' => '4.1.2', 'description' => 'Plan, manage and implement developmentally sequenced teaching and learning process to meet curriculum requirements and varied teaching contexts', 'domain' => $d4],
    ['code' => '4.5.2', 'description' => 'Select, develop, organize and use appropriate teaching and learning resources, including ICT, to address learning goals', 'domain' => $d4],
    ['code' => '5.1.2', 'description' => 'Design, select, organize and use diagnostic, formative and summative assessment strategies consistent with curriculum requirements', 'domain' => $d5],
];

$ti2026 = [
    ['code' => '1.1.2', 'description' => 'Apply knowledge of content within and across curriculum teaching areas', 'domain' => $d1],
    ['code' => '1.4.2', 'description' => 'Use a range of teaching strategies that enhance learner achievement in literacy and numeracy skills', 'domain' => $d1],
    ['code' => '1.5.2', 'description' => 'Apply a range of teaching strategies to develop critical and creative thinking, as well as other higher-order thinking skills', 'domain' => $d1],
    ['code' => '1.6.2', 'description' => 'Display proficient use of Mother Tongue, Filipino and English to facilitate teaching and learning', 'domain' => $d1],
    ['code' => '2.1.2', 'description' => 'Establish safe and secure learning environments to enhance learning through the consistent implementation of policies, guidelines and procedures', 'domain' => $d2],
    ['code' => '2.2.2', 'description' => 'Maintain learning environments that promote fairness, respect and care to encourage learning', 'domain' => $d2],
    ['code' => '3.2.2', 'description' => "Establish a learner-centered culture by using teaching strategies that respond to learners' linguistic, cultural, socio-economic and religious backgrounds", 'domain' => $d3],
    ['code' => '3.5.2', 'description' => 'Adapt and use culturally appropriate teaching strategies to address the needs of learners from indigenous groups', 'domain' => $d3],
    ['code' => '5.3.2', 'description' => 'Use strategies for providing timely, accurate and constructive feedback to improve learner performance', 'domain' => $d5],
];

$ti2027 = [
    ['code' => '1.1.2', 'description' => 'Apply knowledge of content within and across curriculum teaching areas', 'domain' => $d1],
    ['code' => '1.4.2', 'description' => 'Use a range of teaching strategies that enhance learner achievement in literacy and numeracy skills', 'domain' => $d1],
    ['code' => '1.3.2', 'description' => 'Ensure the positive use of ICT to facilitate the teaching and learning process', 'domain' => $d1],
    ['code' => '1.7.2', 'description' => 'Use effective verbal and non-verbal classroom communication strategies to support learner understanding, participation, engagement and achievement', 'domain' => $d1],
    ['code' => '2.4.2', 'description' => 'Maintain supportive learning environments that nurture and inspire learners to participate, cooperate and collaborate in continued learning', 'domain' => $d2],
    ['code' => '2.5.2', 'description' => 'Apply a range of successful strategies that maintain learning environments that motivate learners to work productively by assuming responsibility for their own learning', 'domain' => $d2],
    ['code' => '3.3.2', 'description' => 'Design, adapt and implement teaching strategies that are responsive to learners with disabilities, giftedness and talents', 'domain' => $d3],
    ['code' => '3.4.2', 'description' => 'Plan and deliver teaching strategies that are responsive to the special educational needs of learners in difficult circumstances, including: geographic isolation; chronic illness; displacement due to armed conflict, urban resettlement or disasters; child abuse and child labor practices', 'domain' => $d3],
];

/*
|--------------------------------------------------------------------------
| Master Teacher I-II Indicators (Highly Proficient)
|--------------------------------------------------------------------------
*/

$mt2025 = [
    ['code' => '1.1.3', 'description' => 'Model effective applications of content knowledge within and across curriculum teaching areas', 'domain' => $d1],
    ['code' => '1.4.3', 'description' => 'Evaluate with colleagues the effectiveness of teaching strategies that promote learner achievement in literacy and numeracy', 'domain' => $d1],
    ['code' => '1.5.3', 'description' => 'Develop and apply effective teaching strategies to promote critical and creative thinking, as well as other higher-order thinking skills', 'domain' => $d1],
    ['code' => '2.3.3', 'description' => 'Work with colleagues to model and share effective techniques in the management of classroom structure to engage learners, individually or in groups, in meaningful exploration, discovery and hands-on activities within a range of physical learning environments', 'domain' => $d2],
    ['code' => '2.6.3', 'description' => 'Exhibit effective and constructive behavior management skills by applying positive and non-violent discipline to ensure learning-focused environments', 'domain' => $d2],
    ['code' => '3.1.3', 'description' => "Work with colleagues to share differentiated, developmentally appropriate opportunities to address learners' differences in gender, needs, strengths, interests and experiences", 'domain' => $d3],
    ['code' => '4.1.3', 'description' => 'Develop and apply effective strategies in the planning and management of developmentally sequenced teaching and learning process to meet curriculum requirements and varied teaching contexts', 'domain' => $d4],
    ['code' => '4.5.3', 'description' => 'Advise and guide in the selection, organization, development and use of appropriate teaching and learning resources, including ICT, to address specific learning goals', 'domain' => $d4],
    ['code' => '5.1.3', 'description' => 'Work collaboratively with colleagues to review the design, selection, organization and use of a range of effective diagnostic, formative and summative assessment strategies consistent with curriculum requirements', 'domain' => $d5],
];

$mt2026 = [
    ['code' => '1.1.3', 'description' => 'Model effective applications of content knowledge within and across curriculum teaching areas', 'domain' => $d1],
    ['code' => '1.4.3', 'description' => 'Evaluate with colleagues the effectiveness of teaching strategies that promote learner achievement in literacy and numeracy', 'domain' => $d1],
    ['code' => '1.5.3', 'description' => 'Develop and apply effective teaching strategies to promote critical and creative thinking, as well as other higher-order thinking skills', 'domain' => $d1],
    ['code' => '1.6.3', 'description' => 'Model and support colleagues in the proficient use of Mother Tongue, Filipino and English to improve teaching and learning, as well as to develop the learners\' pride of their language, heritage and culture', 'domain' => $d1],
    ['code' => '2.1.3', 'description' => 'Exhibit effective strategies that ensure safe and secure learning environments that promote fairness, respect and care to encourage learning', 'domain' => $d2],
    ['code' => '2.2.3', 'description' => 'Exhibit effective practices to foster learning environments that promote fairness, respect and care to encourage learning', 'domain' => $d2],
    ['code' => '3.2.3', 'description' => "Exhibit a learner-centered culture that promotes success by using effective teaching strategies that respond to their linguistic, cultural, socio-economic and religious backgrounds", 'domain' => $d3],
    ['code' => '3.5.3', 'description' => 'Develop and apply teaching strategies to address effectively the needs of learners from indigenous groups', 'domain' => $d3],
    ['code' => '5.3.3', 'description' => 'Use effective strategies for providing timely, accurate and constructive feedback to encourage learners to reflect on and improve their own learning', 'domain' => $d5],
];

$mt2027 = [
    ['code' => '1.1.3', 'description' => 'Model effective applications of content knowledge within and across curriculum teaching areas', 'domain' => $d1],
    ['code' => '1.4.3', 'description' => 'Evaluate with colleagues the effectiveness of teaching strategies that promote learner achievement in literacy and numeracy', 'domain' => $d1],
    ['code' => '1.3.3', 'description' => 'Promote effective strategies in the positive use of ICT to facilitate the teaching and learning process', 'domain' => $d1],
    ['code' => '1.7.3', 'description' => 'Display a wide range of effective verbal and non-verbal classroom strategies to support learner understanding, participation, engagement and achievement', 'domain' => $d1],
    ['code' => '2.4.3', 'description' => 'Work with colleagues to share successful strategies that sustain supportive learning environments that nurture and inspire learners to participate, cooperate and collaborate in continued learning', 'domain' => $d2],
    ['code' => '2.5.3', 'description' => 'Model successful strategies and support colleagues in promoting learning environments that effectively motivate learners to work productively by assuming responsibility for their own learning', 'domain' => $d2],
    ['code' => '3.3.3', 'description' => 'Assist colleagues to design, adapt and implement teaching strategies that are responsive to learners with disabilities, giftedness and talents', 'domain' => $d3],
    ['code' => '3.4.3', 'description' => 'Evaluate with colleagues teaching strategies that are responsive to the special educational needs of learners in difficult circumstances, including: geographic isolation; chronic illness; displacement due to armed conflict, urban resettlement or disasters; child abuse and child labor practices', 'domain' => $d3],
];

/*
|--------------------------------------------------------------------------
| Rating scales (per stage)
|--------------------------------------------------------------------------
*/

$scale_ti = [6 => 'Outstanding', 5 => 'Very Satisfactory', 4 => 'Satisfactory', 3 => 'Unsatisfactory', 2 => 'Poor'];
$scale_tiv = [7 => 'Outstanding', 6 => 'Very Satisfactory', 5 => 'Satisfactory', 4 => 'Unsatisfactory', 3 => 'Poor'];
$scale_mt = [8 => 'Outstanding', 7 => 'Very Satisfactory', 6 => 'Satisfactory', 5 => 'Unsatisfactory', 4 => 'Poor'];

$css_ti = [6 => 'bg-green-600', 5 => 'bg-blue-500', 4 => 'bg-yellow-400', 3 => 'bg-orange-400', 2 => 'bg-red-500'];
$css_tiv = [7 => 'bg-green-600', 6 => 'bg-blue-500', 5 => 'bg-yellow-400', 4 => 'bg-orange-400', 3 => 'bg-red-500'];
$css_mt = [8 => 'bg-green-600', 7 => 'bg-blue-500', 6 => 'bg-yellow-400', 5 => 'bg-orange-400', 4 => 'bg-red-500'];

$stageVersion = function (string $label, string $stage, array $scale, array $css, array $indicators) {
    return [
        'label' => $label,
        'ratee_role' => 'teacher',
        'career_stage' => $stage,
        'rating_scale' => $scale,
        'rating_scale_css' => $css,
        'indicators' => $indicators,
    ];
};

return [

    'versions' => [
        '2025-2026' => [
            'teacher_i_iii' => $stageVersion('COT 2025-2026 (Teacher I-III)', 'teacher_i_iii', $scale_ti, $css_ti, $ti2025),
            'teacher_iv_vii' => $stageVersion('COT 2025-2026 (Teacher IV-VII)', 'teacher_iv_vii', $scale_tiv, $css_tiv, $ti2025),
            'master_teacher_i_ii' => $stageVersion('COT 2025-2026 (Master Teacher I-II)', 'master_teacher_i_ii', $scale_mt, $css_mt, $mt2025),
        ],
        '2026-2027' => [
            'teacher_i_iii' => $stageVersion('COT 2026-2027 (Teacher I-III)', 'teacher_i_iii', $scale_ti, $css_ti, $ti2026),
            'teacher_iv_vii' => $stageVersion('COT 2026-2027 (Teacher IV-VII)', 'teacher_iv_vii', $scale_tiv, $css_tiv, $ti2026),
            'master_teacher_i_ii' => $stageVersion('COT 2026-2027 (Master Teacher I-II)', 'master_teacher_i_ii', $scale_mt, $css_mt, $mt2026),
        ],
        '2027-2028' => [
            'teacher_i_iii' => $stageVersion('COT 2027-2028 (Teacher I-III)', 'teacher_i_iii', $scale_ti, $css_ti, $ti2027),
            'teacher_iv_vii' => $stageVersion('COT 2027-2028 (Teacher IV-VII)', 'teacher_iv_vii', $scale_tiv, $css_tiv, $ti2027),
            'master_teacher_i_ii' => $stageVersion('COT 2027-2028 (Master Teacher I-II)', 'master_teacher_i_ii', $scale_mt, $css_mt, $mt2027),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | COT Rating Scale (Fallback)
    |--------------------------------------------------------------------------
    |
    | Official COT rating scale per DepEd Order. Used as the fallback when a
    | version has no rating scale of its own (Teacher I-III default).
    |
    */

    'rating_scale' => $scale_ti,

    'rating_scale_css' => $css_ti,

    /*
    |--------------------------------------------------------------------------
    | Official Annex E-2 Template Files
    |--------------------------------------------------------------------------
    |
    | The official DepEd Classroom Observation Tool (COT) rating sheet per
    | career stage. When a target stage has a template, generated documents
    | are rendered from the template (single SY form filled in) instead of the
    | programmatic layout. Paths are relative to public/.
    |
    */

    'templates' => [
        'teacher_i_iii' => 'documents/Annex-E-2_COT-Rating-Sheet-for-Beginning-towards-Proficient-Teacher-TI-TIII (1).docx',
        'teacher_iv_vii' => 'documents/Annex-E-2_COT-Rating-Sheet-for-Proficient-Teacher-TIV-TVII.docx',
        'master_teacher_i_ii' => 'documents/Annex-E-2_COT-Rating-Sheet-for-Highly-Proficient-Teacher-MTI-MTII.docx',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Version
    |--------------------------------------------------------------------------
    |
    | Fallback school year version if observation has none set. `default_stage`
    | is the career stage that owns the default (fallback) instrument.
    |
    */

    'default_version' => '2025-2026',

    'default_stage' => 'teacher_i_iii',

];
