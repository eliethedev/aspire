<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InvitationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_teacher_invitation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.invitations.store'), [
            'name' => 'Maria Santos',
            'email' => 'maria@example.com',
            'role' => 'teacher',
            'department' => 'Science',
        ]);

        $response->assertRedirect(route('admin.invitations.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'maria@example.com',
            'role' => 'teacher',
            'status' => 'invited',
        ]);
        $this->assertDatabaseHas('invitations', [
            'email' => 'maria@example.com',
            'role' => 'teacher',
        ]);
        $this->assertDatabaseHas('teachers', [
            'department' => 'Science',
        ]);
    }

    public function test_admin_invitation_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.invitations.store'), [
            'name' => 'Maria Santos',
            'email' => 'taken@example.com',
            'role' => 'teacher',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_non_admin_cannot_create_invitations(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($teacher)
            ->post(route('admin.invitations.store'), [
                'name' => 'Maria Santos',
                'email' => 'maria@example.com',
                'role' => 'teacher',
            ])
            ->assertForbidden();
    }

    public function test_invited_user_can_accept_invitation_and_set_password(): void
    {
        $user = User::factory()->create([
            'name' => 'Maria Santos',
            'email' => 'maria@example.com',
            'role' => 'teacher',
            'status' => 'invited',
        ]);
        $invitation = Invitation::factory()->create([
            'user_id' => $user->id,
            'email' => 'maria@example.com',
            'role' => 'teacher',
        ]);

        $response = $this->post(route('auth.set-password.store'), [
            'token' => $invitation->token,
            'password' => 'Str0ng!Pass',
            'password_confirmation' => 'Str0ng!Pass',
        ]);

        $response->assertRedirect(route('dashboard'));

        $this->assertSame('active', $user->fresh()->status);
        $this->assertTrue(Hash::check('Str0ng!Pass', $user->fresh()->password));
        $this->assertTrue($invitation->fresh()->is_used);
        $this->assertNotNull($invitation->fresh()->accepted_at);
    }

    public function test_set_password_requires_strong_password(): void
    {
        $user = User::factory()->create([
            'email' => 'maria@example.com',
            'role' => 'teacher',
            'status' => 'invited',
        ]);
        $invitation = Invitation::factory()->create([
            'user_id' => $user->id,
            'email' => 'maria@example.com',
            'role' => 'teacher',
        ]);

        $this->post(route('auth.set-password.store'), [
            'token' => $invitation->token,
            'password' => 'weak',
            'password_confirmation' => 'weak',
        ])->assertSessionHasErrors('password');

        $this->assertSame('invited', $user->fresh()->status);
    }

    public function test_expired_invitation_token_is_rejected(): void
    {
        $user = User::factory()->create([
            'email' => 'maria@example.com',
            'role' => 'teacher',
            'status' => 'invited',
        ]);
        $invitation = Invitation::factory()->expired()->create([
            'user_id' => $user->id,
            'email' => 'maria@example.com',
            'role' => 'teacher',
        ]);

        $this->post(route('auth.set-password.store'), [
            'token' => $invitation->token,
            'password' => 'Str0ng!Pass',
            'password_confirmation' => 'Str0ng!Pass',
        ])->assertSessionHasErrors('token');

        $this->assertSame('invited', $user->fresh()->status);
    }
}
