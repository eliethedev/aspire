<?php

namespace Tests\Unit;

use App\Models\Invitation;
use App\Models\Teacher;
use App\Models\TeacherProfile;
use App\Models\User;
use App\Models\UserProfile;
use App\Services\InvitationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class InvitationServiceTest extends TestCase
{
    use RefreshDatabase;

    private InvitationService $service;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new InvitationService;
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_create_invitation_creates_user_invitation_and_teacher_profile(): void
    {
        $invitation = $this->service->createInvitation([
            'name' => 'Juan Dela Cruz',
            'email' => 'juan@example.com',
            'role' => 'teacher',
            'department' => 'Mathematics',
        ], $this->admin);

        $this->assertInstanceOf(Invitation::class, $invitation);
        $this->assertSame('juan@example.com', $invitation->email);
        $this->assertSame('teacher', $invitation->role);
        $this->assertSame($this->admin->id, $invitation->invited_by);
        $this->assertNotNull($invitation->token);
        $this->assertTrue($invitation->expires_at->isFuture());

        $user = $invitation->user;
        $this->assertSame('Juan Dela Cruz', $user->name);
        $this->assertSame('juan@example.com', $user->email);
        $this->assertSame('teacher', $user->role);
        $this->assertSame('invited', $user->status);

        $this->assertDatabaseHas('user_profiles', ['user_id' => $user->id]);
        $this->assertDatabaseHas('teachers', [
            'user_id' => $user->id,
            'department' => 'Mathematics',
        ]);
        $this->assertDatabaseHas('teacher_profiles', ['user_id' => $user->id]);
    }

    public function test_create_invitation_rejects_existing_user_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        try {
            $this->service->createInvitation([
                'name' => 'Test',
                'email' => 'existing@example.com',
                'role' => 'teacher',
            ], $this->admin);

            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('email', $e->errors());
        }

        $this->assertDatabaseCount('users', 2);
    }

    public function test_create_invitation_rejects_duplicate_pending_invitation(): void
    {
        $user = User::factory()->create([
            'email' => 'pending@example.com',
            'role' => 'teacher',
            'status' => 'invited',
        ]);

        Invitation::factory()->create([
            'user_id' => $user->id,
            'email' => 'pending@example.com',
            'role' => 'teacher',
        ]);

        try {
            $this->service->createInvitation([
                'name' => 'Test',
                'email' => 'pending@example.com',
                'role' => 'teacher',
            ], $this->admin);

            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('email', $e->errors());
        }

        $this->assertDatabaseCount('invitations', 1);
    }

    public function test_validate_token_returns_valid_invitation(): void
    {
        $invitation = Invitation::factory()->create();

        $result = $this->service->validateToken($invitation->token);

        $this->assertSame($invitation->id, $result->id);
    }

    public function test_validate_token_rejects_unknown_token(): void
    {
        try {
            $this->service->validateToken('not-a-real-token');
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('token', $e->errors());
        }
    }

    public function test_validate_token_rejects_used_invitation(): void
    {
        $invitation = Invitation::factory()->used()->create();

        try {
            $this->service->validateToken($invitation->token);
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('token', $e->errors());
        }
    }

    public function test_validate_token_rejects_expired_invitation(): void
    {
        $invitation = Invitation::factory()->expired()->create();

        try {
            $this->service->validateToken($invitation->token);
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('token', $e->errors());
        }
    }

    public function test_accept_invitation_sets_password_marks_used_and_logs_in(): void
    {
        $user = User::factory()->create([
            'email' => 'accept@example.com',
            'role' => 'teacher',
            'status' => 'invited',
        ]);
        $invitation = Invitation::factory()->create([
            'user_id' => $user->id,
            'email' => 'accept@example.com',
            'role' => 'teacher',
        ]);

        $acceptedUser = $this->service->acceptInvitation($invitation->token, 'StrongPass!123');

        $this->assertSame($user->id, $acceptedUser->id);
        $this->assertSame('active', $user->fresh()->status);
        $this->assertTrue(Hash::check('StrongPass!123', $user->fresh()->password));
        $this->assertNotNull($user->fresh()->password_set_at);

        $this->assertTrue($invitation->fresh()->is_used);
        $this->assertNotNull($invitation->fresh()->accepted_at);

        $this->assertSame($user->id, Auth::id());
    }

    public function test_accept_invitation_rejects_used_token(): void
    {
        $invitation = Invitation::factory()->used()->create();

        try {
            $this->service->acceptInvitation($invitation->token, 'StrongPass!123');
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('token', $e->errors());
        }
    }

    public function test_resend_invitation_regenerates_token_and_extends_expiry(): void
    {
        $invitation = Invitation::factory()->create([
            'expires_at' => now()->addMinutes(5),
        ]);
        $oldToken = $invitation->token;
        $oldExpiry = $invitation->expires_at;

        $updated = $this->service->resendInvitation($invitation);

        $this->assertNotSame($oldToken, $updated->token);
        $this->assertTrue($updated->expires_at->greaterThan($oldExpiry));
        $this->assertSame(1, $updated->resend_count);
        $this->assertNotNull($updated->last_sent_at);
    }

    public function test_resend_invitation_throws_when_limit_reached(): void
    {
        $invitation = Invitation::factory()->create(['resend_count' => 3]);

        try {
            $this->service->resendInvitation($invitation);
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('invitation', $e->errors());
        }
    }

    public function test_resend_invitation_rejects_used_invitation(): void
    {
        $invitation = Invitation::factory()->used()->create();

        try {
            $this->service->resendInvitation($invitation);
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('invitation', $e->errors());
        }
    }

    public function test_cancel_invitation_marks_used_and_deletes_user(): void
    {
        $user = User::factory()->create([
            'email' => 'cancel@example.com',
            'role' => 'teacher',
            'status' => 'invited',
        ]);
        $invitation = Invitation::factory()->create([
            'user_id' => $user->id,
            'email' => 'cancel@example.com',
            'role' => 'teacher',
        ]);

        $this->service->cancelInvitation($invitation);

        // Deleting the invited user cascades to the invitation row.
        $this->assertDatabaseMissing('invitations', ['id' => $invitation->id]);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('teachers', ['user_id' => $user->id]);
    }

    public function test_cancel_invitation_rejects_used_invitation(): void
    {
        $invitation = Invitation::factory()->used()->create();

        try {
            $this->service->cancelInvitation($invitation);
            $this->fail('Expected ValidationException was not thrown.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('invitation', $e->errors());
        }
    }

    public function test_get_statistics_counts_invitations(): void
    {
        Invitation::factory()->create();
        Invitation::factory()->used()->create();
        Invitation::factory()->expired()->create();

        $stats = $this->service->getStatistics();

        $this->assertSame(3, $stats['total']);
        $this->assertSame(1, $stats['pending']);
        $this->assertSame(1, $stats['used']);
        $this->assertSame(1, $stats['expired']);
    }
}
