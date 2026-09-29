<?php

namespace Tests\Feature;

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSidebarTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_schools_show_renders_sidebar_without_type_error(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $school = School::factory()->create(['is_active' => true]);

        // Regression: sidebar compared route-model-bound School to int with (int) cast,
        // throwing "Object of class App\Models\School could not be converted to int".
        $this->actingAs($admin)
            ->get(route('admin.schools.show', $school))
            ->assertOk()
            ->assertSee($school->name);
    }
}
