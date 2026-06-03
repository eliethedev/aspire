<?php

namespace Database\Seeders;

use App\Models\SchoolHeadProfile;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;

class SchoolHeadProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first school
        $school = School::first();
        
        if (!$school) {
            $this->command->warn('No schools found. Please run SchoolSeeder first.');
            return;
        }

        // Create or update a school head user
        $schoolHeadUser = User::firstOrCreate(
            ['email' => 'maria.santos@school1.edu'],
            [
                'name' => 'Maria Santos',
                'password' => bcrypt('password'),
                'role' => 'school_head',
                'school_id' => $school->id,
                'email_verified_at' => now(),
            ]
        );

        // Create or update school head profile
        SchoolHeadProfile::updateOrCreate(
            ['user_id' => $schoolHeadUser->id],
            [
                'school_id' => $school->id,
                'position_level' => 'principal_i',
                'administrative_experience_years' => 10,
                'leadership_training' => 'DepEd Leadership Training Program',
                'current_designation' => 'principal',
                'number_of_teachers_supervised' => 25,
                'school_type' => 'integrated',
                'additional_roles' => 'Division Coordinator',
                'position' => 'Principal I',
                'subject' => 'Educational Management',
                'grade_level' => 'All Levels',
            ]
        );

        // Create another school head if there are multiple schools
        $school2 = School::skip(1)->first();
        if ($school2) {
            $schoolHeadUser2 = User::firstOrCreate(
                ['email' => 'juan.reyes@school2.edu'],
                [
                    'name' => 'Juan Reyes',
                    'password' => bcrypt('password'),
                    'role' => 'school_head',
                    'school_id' => $school2->id,
                    'email_verified_at' => now(),
                ]
            );

            SchoolHeadProfile::updateOrCreate(
                ['user_id' => $schoolHeadUser2->id],
                [
                    'school_id' => $school2->id,
                    'position_level' => 'head_teacher',
                    'administrative_experience_years' => 8,
                    'leadership_training' => 'School-Based Management Training',
                    'current_designation' => 'head_teacher',
                    'number_of_teachers_supervised' => 15,
                    'school_type' => 'secondary',
                    'additional_roles' => 'Subject Department Head',
                    'position' => 'Head Teacher III',
                    'subject' => 'Science',
                    'grade_level' => 'Secondary',
                ]
            );
        }

        $this->command->info('School head profiles seeded successfully.');
    }
}
