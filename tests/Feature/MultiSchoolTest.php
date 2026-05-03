<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use App\Services\SchoolService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiSchoolTest extends TestCase
{
    use RefreshDatabase;

    protected $schoolService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->schoolService = app(SchoolService::class);
    }

    public function test_school_can_be_created(): void
    {
        $school = $this->schoolService->createSchool([
            'name' => 'Test School',
            'slug' => 'test-school',
            'subdomain' => 'test',
            'is_active' => true,
        ]);

        $this->assertInstanceOf(School::class, $school);
        $this->assertEquals('Test School', $school->name);
        $this->assertEquals('test-school', $school->slug);
        $this->assertTrue($school->isActive());
    }

    public function test_user_can_be_added_to_school(): void
    {
        $school = School::create([
            'name' => 'Test School',
            'slug' => 'test-school',
        ]);
        
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);

        $this->schoolService->addUserToSchool($school, $user, 'teacher');

        $this->assertTrue($user->canAccessSchool($school));
        $this->assertTrue($user->hasSchoolRole($school, 'teacher'));
        $this->assertEquals('teacher', $this->schoolService->getUserRoleInSchool($school, $user));
    }

    public function test_user_role_can_be_changed_in_school(): void
    {
        $school = School::create([
            'name' => 'Test School',
            'slug' => 'test-school',
        ]);
        
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);

        $this->schoolService->addUserToSchool($school, $user, 'teacher');
        $this->schoolService->changeUserRole($school, $user, 'supervisor');

        $this->assertFalse($user->hasSchoolRole($school, 'teacher'));
        $this->assertTrue($user->hasSchoolRole($school, 'supervisor'));
    }

    public function test_user_can_be_deactivated_in_school(): void
    {
        $school = School::create([
            'name' => 'Test School',
            'slug' => 'test-school',
        ]);
        
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);

        $this->schoolService->addUserToSchool($school, $user, 'teacher');
        $this->assertTrue($user->canAccessSchool($school));

        $this->schoolService->deactivateUser($school, $user);
        $this->assertFalse($user->canAccessSchool($school));
    }

    public function test_school_has_default_settings(): void
    {
        $school = $this->schoolService->createSchool([
            'name' => 'Test School',
            'slug' => 'test-school',
        ]);

        $this->assertTrue($this->schoolService->isFeatureEnabled($school, 'observations'));
        $this->assertTrue($this->schoolService->isFeatureEnabled($school, 'cot_ratings'));
        $this->assertTrue($this->schoolService->isFeatureEnabled($school, 'predictions'));
        $this->assertTrue($this->schoolService->isFeatureEnabled($school, 'feedback'));
    }

    public function test_user_role_methods_work(): void
    {
        $teacher = User::create([
            'name' => 'Teacher User',
            'email' => 'teacher@example.com',
            'password' => bcrypt('password'),
            'role' => 'teacher',
        ]);

        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->assertTrue($teacher->isTeacher());
        $this->assertFalse($teacher->isAdmin());
        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isTeacher());
    }
}
