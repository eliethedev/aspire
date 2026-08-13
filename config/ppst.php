<?php

return [

    /*
    |--------------------------------------------------------------------------
    | PPST Standards Library
    |--------------------------------------------------------------------------
    |
    | The Philippine Professional Standards for Teachers (PPST) is the
    | standards foundation of the system. It defines the seven professional
    | domains and their indicators (the full framework a teacher is expected
    | to demonstrate). Classroom-observation instruments (COT forms) assemble
    | a subset of these standards into the observable rating sheet.
    |
    | This config seeds the database (`PpstStandardSeeder`) and is the
    | reference content for the admin-managed PPST standards library.
    |
    */

    'domains' => [
        [
            'number' => 1,
            'name' => 'Content Knowledge and Pedagogy',
            'indicators' => [
                ['code' => '1.1.1', 'description' => 'Content knowledge and its application within and across curriculum teaching areas'],
                ['code' => '1.1.2', 'description' => 'Apply knowledge of content within and across curriculum teaching areas'],
                ['code' => '1.2.1', 'description' => 'Research-based knowledge and principles of teaching and learning'],
                ['code' => '1.2.2', 'description' => 'Use research-based knowledge and principles of teaching and learning to enhance professional practice'],
                ['code' => '1.3.1', 'description' => 'Positive use of ICT in teaching and learning'],
                ['code' => '1.3.2', 'description' => 'Show skills in the positive use of ICT to facilitate teaching and learning'],
                ['code' => '1.4.1', 'description' => 'Strategies for promoting literacy and numeracy'],
                ['code' => '1.4.2', 'description' => 'Use strategies for promoting literacy and numeracy'],
                ['code' => '1.5.1', 'description' => 'Strategies for developing critical and creative thinking as well as other higher-order thinking skills'],
                ['code' => '1.5.2', 'description' => 'Apply strategies to develop critical and creative thinking as well as other higher-order thinking skills'],
                ['code' => '1.6.1', 'description' => 'Mother Tongue, Filipino and English in teaching and learning'],
                ['code' => '1.6.2', 'description' => 'Use Mother Tongue, Filipino and English to facilitate teaching and learning'],
            ],
        ],
        [
            'number' => 2,
            'name' => 'Learning Environment',
            'indicators' => [
                ['code' => '2.1.1', 'description' => 'Knowledge of policies, guidelines and procedures that provide safe and secure learning environments'],
                ['code' => '2.1.2', 'description' => 'Establish safe and secure learning environments'],
                ['code' => '2.2.1', 'description' => 'Knowledge of managing classroom structure that engages learners in various activities'],
                ['code' => '2.2.2', 'description' => 'Promote fairness in the classroom'],
                ['code' => '2.3.1', 'description' => 'Knowledge of classroom management and discipline'],
                ['code' => '2.3.2', 'description' => 'Manage classroom structure to engage learners'],
                ['code' => '2.4.1', 'description' => 'Knowledge of procedures that support learner participation'],
                ['code' => '2.4.2', 'description' => 'Manage classroom activities to maintain discipline'],
                ['code' => '2.5.1', 'description' => 'Knowledge of learning environments that support purposive learning'],
                ['code' => '2.5.2', 'description' => 'Use classroom procedures that support learner participation'],
                ['code' => '2.6.1', 'description' => 'Knowledge of positive and non-violent discipline'],
                ['code' => '2.6.2', 'description' => 'Manage learner behavior constructively'],
            ],
        ],
        [
            'number' => 3,
            'name' => 'Diversity of Learners',
            'indicators' => [
                ['code' => '3.1.1', 'description' => "Knowledge of learners' gender, needs, strengths, interests and experiences"],
                ['code' => '3.1.2', 'description' => 'Demonstrate knowledge of policies and guidelines on learner protection'],
                ['code' => '3.2.1', 'description' => "Knowledge of learners' linguistic, cultural, socioeconomic and religious backgrounds"],
                ['code' => '3.2.2', 'description' => "Adapt and use teaching strategies that are responsive to learners' linguistic and cultural background"],
                ['code' => '3.3.1', 'description' => 'Knowledge of learners with disabilities, giftedness and talents'],
                ['code' => '3.3.2', 'description' => "Adapt and use teaching strategies that are responsive to learners' socioeconomic background"],
                ['code' => '3.4.1', 'description' => 'Knowledge of learners in difficult circumstances'],
                ['code' => '3.4.2', 'description' => "Adapt and use teaching strategies that are responsive to learners' physical disabilities"],
                ['code' => '3.5.1', 'description' => 'Knowledge of learners with different backgrounds and abilities'],
                ['code' => '3.5.2', 'description' => "Adapt and use teaching strategies that are responsive to learners' giftedness and talents"],
            ],
        ],
        [
            'number' => 4,
            'name' => 'Curriculum and Planning',
            'indicators' => [
                ['code' => '4.1.1', 'description' => 'Knowledge of curriculum areas and curriculum goals'],
                ['code' => '4.1.2', 'description' => 'Plan and deliver lessons using appropriate teaching and learning resources'],
                ['code' => '4.2.1', 'description' => 'Knowledge of teaching and learning resources'],
                ['code' => '4.2.2', 'description' => 'Plan and deliver lessons using appropriate assessment strategies'],
                ['code' => '4.3.1', 'description' => 'Knowledge of instructional planning and lesson design'],
                ['code' => '4.3.2', 'description' => 'Plan and deliver lessons using appropriate instructional planning'],
                ['code' => '4.4.1', 'description' => 'Knowledge of learning activities that are developmentally appropriate'],
                ['code' => '4.4.2', 'description' => 'Plan and deliver lessons using appropriate learning activities'],
                ['code' => '4.5.1', 'description' => 'Knowledge of learning outcomes that are aligned with the curriculum'],
                ['code' => '4.5.2', 'description' => 'Plan and deliver lessons using appropriate learning outcomes'],
            ],
        ],
        [
            'number' => 5,
            'name' => 'Assessment and Reporting',
            'indicators' => [
                ['code' => '5.1.1', 'description' => 'Knowledge of assessment tools that are aligned with learning outcomes'],
                ['code' => '5.1.2', 'description' => 'Design and use assessment tools that are aligned with learning outcomes'],
                ['code' => '5.2.1', 'description' => 'Knowledge of monitoring and evaluation of learner progress and achievement'],
                ['code' => '5.2.2', 'description' => 'Monitor and evaluate learner progress'],
                ['code' => '5.3.1', 'description' => 'Knowledge of feedback to improve learning'],
                ['code' => '5.3.2', 'description' => 'Provide timely and accurate feedback to learners'],
                ['code' => '5.4.1', 'description' => 'Knowledge of communicating learner progress to stakeholders'],
                ['code' => '5.4.2', 'description' => 'Communicate learner progress to stakeholders'],
                ['code' => '5.5.1', 'description' => 'Knowledge of using assessment data to improve teaching and learning'],
                ['code' => '5.5.2', 'description' => 'Use assessment data to improve teaching and learning'],
            ],
        ],
        [
            'number' => 6,
            'name' => 'Community Linkages and Professional Engagement',
            'indicators' => [
                ['code' => '6.1.1', 'description' => 'Knowledge of learning environments that respond to community aspirations'],
                ['code' => '6.1.2', 'description' => 'Establish learning environments that respond to the aspirations of the community'],
                ['code' => '6.2.1', 'description' => 'Knowledge of engaging parents and guardians in the teaching-learning process'],
                ['code' => '6.2.2', 'description' => 'Engage parents and the wider school community in the educative process'],
                ['code' => '6.3.1', 'description' => 'Knowledge of professional ethics and responsibilities'],
                ['code' => '6.3.2', 'description' => 'Demonstrate professional ethics, responsibilities and obligations to the profession, learners and other stakeholders'],
                ['code' => '6.4.1', 'description' => 'Knowledge of school policies and procedures'],
                ['code' => '6.4.2', 'description' => 'Comply with school policies and procedures that foster harmonious relationships with learners, parents and other stakeholders'],
            ],
        ],
        [
            'number' => 7,
            'name' => 'Personal Growth and Professional Development',
            'indicators' => [
                ['code' => '7.1.1', 'description' => "Knowledge of one's philosophy of teaching"],
                ['code' => '7.1.2', 'description' => 'Articulate a clear personal philosophy of teaching that is reflected in practice'],
                ['code' => '7.2.1', 'description' => 'Knowledge of the dignity of teaching as a profession'],
                ['code' => '7.2.2', 'description' => 'Uphold the dignity of teaching by maintaining high standards of professionalism'],
                ['code' => '7.3.1', 'description' => 'Knowledge of professional links with colleagues'],
                ['code' => '7.3.2', 'description' => 'Establish professional links with colleagues to enrich teaching practice'],
                ['code' => '7.4.1', 'description' => 'Knowledge of professional reflection and learning'],
                ['code' => '7.4.2', 'description' => 'Participate in professional networks and engage in continuous learning to improve practice'],
            ],
        ],
    ],

];
