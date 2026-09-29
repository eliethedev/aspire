<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileCompletionRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_incomplete_teacher_is_redirected_with_field_focus_targets(): void
    {
        $user = User::factory()->create([
            'role' => 'teacher',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('teacher.dashboard'));

        $response->assertRedirect(route('teacher.profile.edit'));
        $response->assertSessionHas('profile_incomplete', true);
        $response->assertSessionHas('profile_missing', function ($missing) {
            return in_array('grade_level', $missing, true)
                && in_array('default_room', $missing, true);
        });

        $focus = session('profile_focus');
        $this->assertNotEmpty($focus);

        $byField = collect($focus)->keyBy('field');
        $this->assertSame('teaching', $byField['grade_level']['tab']);
        $this->assertSame('grade_level', $byField['grade_level']['input']);
        $this->assertSame('teaching', $byField['default_room']['tab']);
        $this->assertSame('personal', $byField['mobile_number']['tab']);
    }

    public function test_profile_page_opens_on_first_missing_tab_with_jump_targets(): void
    {
        $user = User::factory()->create([
            'role' => 'teacher',
            'email_verified_at' => now(),
        ]);

        // Pass through the guarded route to flash the focus payload, then render.
        $this->actingAs($user)->get(route('teacher.dashboard'));

        $response = $this->actingAs($user)
            ->withSession([
                'profile_incomplete' => true,
                'status' => 'profile-incomplete',
                'profile_missing' => ['mobile_number', 'grade_level'],
                'profile_focus' => [
                    ['field' => 'mobile_number', 'label' => 'Mobile number', 'tab' => 'personal', 'input' => 'mobile_number'],
                    ['field' => 'grade_level', 'label' => 'Grade level', 'tab' => 'teaching', 'input' => 'grade_level'],
                ],
            ])
            ->get(route('teacher.profile.edit'));

        $response->assertOk();
        // Page boots on the first missing tab and exposes the jump helper.
        $response->assertSee("activeTab: 'personal'", false);
        $response->assertSee('goToField', false);
        $response->assertSee('Mobile number', false);
        $response->assertSee('Grade level', false);
    }

    public function test_complete_teacher_passes_through(): void
    {
        $user = User::factory()->create([
            'role' => 'teacher',
            'email_verified_at' => now(),
        ]);
        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            ['mobile_number' => '09123456789', 'employment_status' => 'permanent']
        );
        $teacher = \App\Models\Teacher::create([
            'user_id' => $user->id,
            'school_id' => $user->school_id,
            'department' => 'Science',
            'years_of_service' => 3,
            'grade_level' => 'junior_high',
        ]);
        $user->teacherProfile()->updateOrCreate(
            ['user_id' => $user->id],
            ['grade_level' => 'junior_high', 'default_room' => 'Room 201']
        );

        $this->actingAs($user)->get(route('teacher.dashboard'))->assertOk();
    }
}
