<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\Teacher;
use App\Models\TeacherProfile;
use App\Models\User;
use App\Services\SchoolService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TeacherSeeder extends Seeder
{
    /**
     * Template of the five dummy teachers seeded into every school.
     * The email slug is used to keep accounts unique per school.
     *
     * @var array<int, array<string, mixed>>
     */
    private array $templates = [
        [
            'name' => 'Alicia Domingo',
            'position' => 'Teacher I',
            'teaching_position' => 'teacher_i',
            'career_stage' => 'teacher_i_iii',
            'subject' => 'Mathematics',
            'grade_level' => 'junior_high',
            'department' => 'Mathematics',
            'years_of_service' => 3,
            'teacher_load' => 6,
        ],
        [
            'name' => 'Bryan Villanueva',
            'position' => 'Teacher III',
            'teaching_position' => 'teacher_iii',
            'career_stage' => 'teacher_i_iii',
            'subject' => 'Science',
            'grade_level' => 'junior_high',
            'department' => 'Science',
            'years_of_service' => 5,
            'teacher_load' => 6,
        ],
        [
            'name' => 'Carla Magsaysay',
            'position' => 'Teacher IV',
            'teaching_position' => null,
            'career_stage' => 'teacher_iv_vii',
            'subject' => 'English',
            'grade_level' => 'senior_high',
            'department' => 'English',
            'years_of_service' => 8,
            'teacher_load' => 5,
        ],
        [
            'name' => 'Daniel Reyes',
            'position' => 'Teacher V',
            'teaching_position' => null,
            'career_stage' => 'teacher_iv_vii',
            'subject' => 'Filipino',
            'grade_level' => 'junior_high',
            'department' => 'Filipino',
            'years_of_service' => 12,
            'teacher_load' => 5,
        ],
        [
            'name' => 'Elena Castillo',
            'position' => 'Master Teacher I',
            'teaching_position' => 'master_teacher_i',
            'career_stage' => 'master_teacher_i_ii',
            'subject' => 'Mathematics',
            'grade_level' => 'senior_high',
            'department' => 'Mathematics',
            'years_of_service' => 15,
            'teacher_load' => 4,
        ],
    ];

    /**
     * Seed five dummy teachers into every school.
     */
    public function run(): void
    {
        $schoolService = app(SchoolService::class);

        $schools = School::orderBy('id')->get();

        if ($schools->isEmpty()) {
            $this->command->warn('No schools found. Please run SchoolSeeder first.');
            return;
        }

        foreach ($schools as $school) {
            foreach ($this->templates as $i => $template) {
                $slug = Str::slug($template['name']);
                $email = "{$slug}" . ($i + 1) . "@{$school->slug}.edu";

                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => $template['name'],
                        'password' => bcrypt('password'),
                        'role' => 'teacher',
                        'school_id' => $school->id,
                        'status' => 'active',
                        'email_verified_at' => now(),
                    ]
                );

                // Keep the user attached to the school and role current.
                $user->update([
                    'name' => $template['name'],
                    'role' => 'teacher',
                    'school_id' => $school->id,
                    'status' => 'active',
                ]);
                $schoolService->addUserToSchool($school, $user, 'teacher');

                Teacher::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'school_id' => $school->id,
                        'department' => $template['department'],
                        'years_of_service' => $template['years_of_service'],
                        'employee_number' => 'EMP-' . strtoupper(substr($school->slug, 0, 4) . '-' . str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT)),
                        'mobile_number' => '09' . str_pad((string) random_int(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                        'prc_license_number' => 'PRC-' . strtoupper(Str::random(6)),
                        'position' => $template['position'],
                        'career_stage' => $template['career_stage'],
                        'subject' => $template['subject'],
                        'grade_level' => $template['grade_level'],
                    ]
                );

                TeacherProfile::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'grade_level' => $template['grade_level'],
                        'subject_area_taught' => $template['subject'],
                        'teaching_position' => $template['teaching_position'],
                        'department' => $template['department'],
                        'teacher_load' => $template['teacher_load'],
                        'has_advisory_class' => true,
                        'advisory_section' => 'Section ' . (($i % 3) + 1),
                    ]
                );
            }
        }

        $this->command->info('Five teachers seeded into every school.');
    }
}
