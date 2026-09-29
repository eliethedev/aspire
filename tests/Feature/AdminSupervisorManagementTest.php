<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\Supervisor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSupervisorManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_supervisors_index_lists_supervisor_users(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $school = School::factory()->create(['is_active' => true]);
        $user = User::factory()->create([
            'role' => 'supervisor',
            'school_id' => $school->id,
        ]);
        Supervisor::create([
            'user_id' => $user->id,
            'school_id' => $school->id,
            'position' => 'Supervisor',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.supervisors.index'));

        $response->assertOk();
        $response->assertSee($user->name);
        $response->assertSee($user->email);
    }

    public function test_admin_supervisors_index_backfills_missing_profile_rows(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $school = School::factory()->create(['is_active' => true]);
        $user = User::factory()->create([
            'role' => 'supervisor',
            'school_id' => $school->id,
        ]);

        $this->assertDatabaseMissing('supervisors', ['user_id' => $user->id]);

        $response = $this->actingAs($admin)->get(route('admin.supervisors.index'));

        $response->assertOk();
        $response->assertSee($user->name);
        $this->assertDatabaseHas('supervisors', ['user_id' => $user->id]);
    }

    public function test_admin_supervisors_show_renders(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $school = School::factory()->create(['is_active' => true]);
        $user = User::factory()->create([
            'role' => 'supervisor',
            'school_id' => $school->id,
        ]);
        $supervisor = Supervisor::create([
            'user_id' => $user->id,
            'school_id' => $school->id,
            'position' => 'Principal',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.supervisors.show', $supervisor));

        $response->assertOk();
        $response->assertSee($user->name);
        $response->assertSee('Principal');
    }

    public function test_admin_supervisors_edit_renders_and_updates(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $school = School::factory()->create(['is_active' => true]);
        $user = User::factory()->create([
            'role' => 'supervisor',
            'school_id' => $school->id,
        ]);
        $supervisor = Supervisor::create([
            'user_id' => $user->id,
            'school_id' => $school->id,
            'position' => 'Supervisor',
            'status' => 'active',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.supervisors.edit', $supervisor))
            ->assertOk()
            ->assertSee($user->name);

        $this->actingAs($admin)
            ->put(route('admin.supervisors.update', $supervisor), [
                'fullName' => 'Updated Name',
                'email' => $user->email,
                'schoolId' => $school->id,
                'position' => 'Principal',
                'status' => 'active',
            ])
            ->assertRedirect(route('admin.supervisors.index'));

        $this->assertSame('Updated Name', $user->fresh()->name);
        $this->assertSame('Principal', $supervisor->fresh()->position);
    }
}
