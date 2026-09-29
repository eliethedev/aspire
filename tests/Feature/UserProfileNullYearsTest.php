<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserProfileNullYearsTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_save_with_null_years_defaults_to_zero(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        // Regression: years_of_teaching_experience is NOT NULL DEFAULT 0 —
        // an explicit null (as sent by the profile forms) caused
        // "SQLSTATE[23000]: Column 'years_of_teaching_experience' cannot be null".
        $profile = $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            ['mobile_number' => '09708228109', 'years_of_teaching_experience' => null]
        );

        $this->assertSame(0, $profile->fresh()->years_of_teaching_experience);
    }

    public function test_admin_profile_update_without_years_succeeds(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->patch(route('admin.profile.update'), [
                'name' => $admin->name,
                'email' => $admin->email,
                'mobile_number' => '09708228109',
            ])
            ->assertRedirect(route('admin.profile.edit'));

        $this->assertSame(0, $admin->fresh()->profile->years_of_teaching_experience);
    }
}
