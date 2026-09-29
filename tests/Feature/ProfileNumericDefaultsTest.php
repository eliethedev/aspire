<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\SchoolHeadProfile;
use App\Models\SupervisorProfile;
use App\Models\Teacher;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileNumericDefaultsTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_profile_null_integers_default_instead_of_crashing(): void
    {
        // Regression: SQLSTATE[23000] Column 'teacher_load' cannot be null.
        $user = User::factory()->create(['role' => 'teacher']);

        $profile = $user->teacherProfile()->updateOrCreate(
            ['user_id' => $user->id],
            ['grade_level' => null, 'teacher_load' => null, 'department' => 'General']
        );

        $this->assertSame(0, $profile->fresh()->teacher_load);
        $this->assertFalse((bool) $profile->fresh()->has_advisory_class);
    }

    public function test_teacher_profile_update_without_load_succeeds(): void
    {
        $user = User::factory()->create(['role' => 'teacher', 'email_verified_at' => now()]);
        Teacher::create([
            'user_id' => $user->id,
            'school_id' => $user->school_id,
            'department' => 'General',
            'years_of_service' => 1,
        ]);

        $this->actingAs($user)
            ->patch(route('teacher.profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
            ])
            ->assertRedirect(route('teacher.profile.edit'));

        $this->assertSame(0, $user->fresh()->teacherProfile->teacher_load);
    }

    public function test_supervisor_profile_null_years_default_instead_of_crashing(): void
    {
        $user = User::factory()->create(['role' => 'supervisor']);

        $profile = SupervisorProfile::create([
            'user_id' => $user->id,
            'previous_teaching_experience_years' => null,
            'administrative_experience_years' => null,
        ]);

        $this->assertSame(0, $profile->fresh()->previous_teaching_experience_years);
        $this->assertSame(0, $profile->fresh()->administrative_experience_years);
    }

    public function test_school_head_profile_null_integers_default_instead_of_crashing(): void
    {
        $school = School::factory()->create();
        $user = User::factory()->create(['role' => 'school_head', 'school_id' => $school->id]);

        $profile = SchoolHeadProfile::create([
            'user_id' => $user->id,
            'school_id' => $school->id,
            'administrative_experience_years' => null,
            'number_of_teachers_supervised' => null,
        ]);

        $this->assertSame(0, $profile->fresh()->administrative_experience_years);
        $this->assertSame(0, $profile->fresh()->number_of_teachers_supervised);
    }
}
