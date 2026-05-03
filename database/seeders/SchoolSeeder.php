<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\User;
use App\Services\SchoolService;
use Illuminate\Database\Seeder;

class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        $schoolService = app(SchoolService::class);

        // Get or create demo school
        $demoSchool = School::firstOrCreate(
            ['slug' => 'demo-school'],
            [
                'name' => 'Demo School',
                'subdomain' => 'demo',
                'is_active' => true,
            ]
        );

        // Create admin user
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@aspire.edu'],
            [
                'name' => 'System Admin',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );

        // Create demo users for the school
        $schoolHead = User::firstOrCreate(
            ['email' => 'head@demo.edu'],
            [
                'name' => 'School Head',
                'password' => bcrypt('password'),
                'role' => 'school_head',
                'school_id' => $demoSchool->id,
            ]
        );

        $supervisor = User::firstOrCreate(
            ['email' => 'supervisor@demo.edu'],
            [
                'name' => 'Supervisor',
                'password' => bcrypt('password'),
                'role' => 'supervisor',
                'school_id' => $demoSchool->id,
            ]
        );

        $teacher = User::firstOrCreate(
            ['email' => 'teacher@demo.edu'],
            [
                'name' => 'Teacher',
                'password' => bcrypt('password'),
                'role' => 'teacher',
                'school_id' => $demoSchool->id,
            ]
        );

        // Add users to school with roles
        $schoolService->addUserToSchool($demoSchool, $schoolHead, 'school_head');
        $schoolService->addUserToSchool($demoSchool, $supervisor, 'supervisor');
        $schoolService->addUserToSchool($demoSchool, $teacher, 'teacher');

        $this->command->info('Demo school and users created successfully!');
        $this->command->info('Login credentials:');
        $this->command->info('Admin: admin@aspire.edu / password');
        $this->command->info('School Head: head@demo.edu / password');
        $this->command->info('Supervisor: supervisor@demo.edu / password');
        $this->command->info('Teacher: teacher@demo.edu / password');
    }
}
