<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserEmailEditTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_edit_a_users_email_with_confirmation(): void
    {
        $user = User::factory()->teacher()->create(['email' => 'old@example.com']);

        $this->actingAs($this->admin)->put(
            route('admin.users.update', $user),
            [
                'name' => $user->name,
                'email' => 'new@example.com',
                'role' => $user->role,
                'confirm_email_change' => '1',
            ]
        )->assertRedirect(route('admin.users.index'));

        $this->assertSame('new@example.com', $user->fresh()->email);
    }

    public function test_email_change_requires_confirmation(): void
    {
        $user = User::factory()->teacher()->create(['email' => 'old@example.com']);

        $this->actingAs($this->admin)
            ->from(route('admin.users.edit', $user))
            ->put(
                route('admin.users.update', $user),
                [
                    'name' => $user->name,
                    'email' => 'new@example.com',
                    'role' => $user->role,
                ]
            )
            ->assertSessionHasErrors('confirm_email_change');

        $this->assertSame('old@example.com', $user->fresh()->email);
    }

    public function test_an_admin_cannot_reuse_another_users_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);
        $user = User::factory()->teacher()->create(['email' => 'old@example.com']);

        $this->actingAs($this->admin)
            ->from(route('admin.users.edit', $user))
            ->put(
                route('admin.users.update', $user),
                [
                    'name' => $user->name,
                    'email' => 'taken@example.com',
                    'role' => $user->role,
                    'confirm_email_change' => '1',
                ]
            )
            ->assertSessionHasErrors('email');

        $this->assertSame('old@example.com', $user->fresh()->email);
    }

    public function test_changing_email_unverifies_the_new_address(): void
    {
        $user = User::factory()->teacher()->create([
            'email' => 'old@example.com',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($this->admin)->put(
            route('admin.users.update', $user),
            [
                'name' => $user->name,
                'email' => 'new@example.com',
                'role' => $user->role,
                'confirm_email_change' => '1',
            ]
        )->assertRedirect(route('admin.users.index'));

        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_editing_without_changing_email_does_not_require_confirmation(): void
    {
        $user = User::factory()->teacher()->create(['email' => 'same@example.com']);

        $this->actingAs($this->admin)->put(
            route('admin.users.update', $user),
            [
                'name' => 'Updated Name',
                'email' => 'same@example.com',
                'role' => $user->role,
            ]
        )->assertRedirect(route('admin.users.index'));

        $this->assertSame('Updated Name', $user->fresh()->name);
        $this->assertSame('same@example.com', $user->fresh()->email);
    }
}
