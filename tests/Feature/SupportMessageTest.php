<?php

namespace Tests\Feature;

use App\Enums\NotificationType;
use App\Models\Notification;
use App\Models\SupportMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('support.create'))->assertRedirect('/login');
        $this->post(route('support.store'), [])->assertRedirect('/login');
    }

    public function test_user_can_submit_a_bug_report(): void
    {
        $user = User::factory()->teacher()->create();

        $response = $this->actingAs($user)->post(route('support.store'), [
            'type' => 'bug',
            'subject' => 'Cannot download COT template',
            'message' => 'Word says the file has a problem with its content.',
        ]);

        $message = SupportMessage::latest('id')->first();

        $response->assertRedirect(route('support.show', $message));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('support_messages', [
            'user_id' => $user->id,
            'type' => 'bug',
            'subject' => 'Cannot download COT template',
            'message' => 'Word says the file has a problem with its content.',
            'status' => SupportMessage::STATUS_OPEN,
            'admin_note' => null,
            'resolved_by' => null,
            'resolved_at' => null,
        ]);
    }

    public function test_submission_validates_required_fields(): void
    {
        $user = User::factory()->teacher()->create();

        $this->actingAs($user)->post(route('support.store'), [])
            ->assertSessionHasErrors(['type', 'subject', 'message']);

        $this->assertDatabaseCount('support_messages', 0);
    }

    public function test_submission_rejects_invalid_type(): void
    {
        $user = User::factory()->teacher()->create();

        $this->actingAs($user)->post(route('support.store'), [
            'type' => 'nonsense',
            'subject' => 'Subject',
            'message' => 'Message',
        ])->assertSessionHasErrors('type');
    }

    public function test_admins_are_notified_when_a_message_is_submitted(): void
    {
        $admin = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();
        $user = User::factory()->supervisor()->create();

        $this->actingAs($user)->post(route('support.store'), [
            'type' => 'feedback',
            'subject' => 'Great feature',
            'message' => 'The new COT template download works great.',
        ]);

        $message = SupportMessage::latest('id')->first();

        foreach ([$admin, $otherAdmin] as $adminUser) {
            $this->assertDatabaseHas('notifications', [
                'user_id' => $adminUser->id,
                'type' => NotificationType::SUPPORT_MESSAGE->value,
                'title' => 'New Feedback',
                'link' => route('admin.support-messages.show', $message),
                'is_read' => false,
            ]);
        }

        $this->assertDatabaseMissing('notifications', ['user_id' => $user->id]);
    }

    public function test_user_can_see_only_their_own_messages(): void
    {
        $user = User::factory()->teacher()->create();
        SupportMessage::factory()->count(2)->for($user)->create();
        SupportMessage::factory()->count(3)->create();

        $response = $this->actingAs($user)->get(route('support.index'));

        $response->assertOk();
        $response->assertViewHas('messages', function ($paginator) {
            return $paginator->total() === 2;
        });
    }

    public function test_user_cannot_view_another_users_message(): void
    {
        $owner = User::factory()->teacher()->create();
        $other = User::factory()->teacher()->create();
        $message = SupportMessage::factory()->for($owner)->create();

        $this->actingAs($other)->get(route('support.show', $message))->assertForbidden();
        $this->actingAs($owner)->get(route('support.show', $message))->assertOk();
    }

    public function test_user_can_see_admin_response_on_their_message(): void
    {
        $owner = User::factory()->teacher()->create();
        $admin = User::factory()->admin()->create();
        $message = SupportMessage::factory()->for($owner)->create([
            'status' => SupportMessage::STATUS_RESOLVED,
            'admin_note' => 'This was fixed in the latest release.',
            'resolved_by' => $admin->id,
            'resolved_at' => now(),
        ]);

        $this->actingAs($owner)
            ->get(route('support.show', $message))
            ->assertOk()
            ->assertSee('This was fixed in the latest release.')
            ->assertSee('Resolved');
    }

    public function test_non_admin_cannot_access_admin_support_routes(): void
    {
        $teacher = User::factory()->teacher()->create();
        $message = SupportMessage::factory()->create();

        $this->actingAs($teacher)->get(route('admin.support-messages.index'))->assertForbidden();
        $this->actingAs($teacher)->get(route('admin.support-messages.show', $message))->assertForbidden();
        $this->actingAs($teacher)->patch(route('admin.support-messages.update', $message), ['status' => 'resolved'])->assertForbidden();
        $this->actingAs($teacher)->delete(route('admin.support-messages.destroy', $message))->assertForbidden();
    }

    public function test_admin_can_list_and_filter_messages(): void
    {
        $admin = User::factory()->admin()->create();
        SupportMessage::factory()->bug()->open()->create(['subject' => 'Broken export button']);
        SupportMessage::factory()->feedback()->resolved()->create(['subject' => 'Nice dashboard']);

        $this->actingAs($admin)->get(route('admin.support-messages.index'))
            ->assertOk()
            ->assertSee('Broken export button')
            ->assertSee('Nice dashboard');

        $this->actingAs($admin)->get(route('admin.support-messages.index', ['type' => 'bug', 'status' => 'open']))
            ->assertOk()
            ->assertSee('Broken export button')
            ->assertDontSee('Nice dashboard');
    }

    public function test_admin_can_update_status_and_leave_a_note(): void
    {
        $admin = User::factory()->admin()->create();
        $message = SupportMessage::factory()->open()->create();

        $this->actingAs($admin)
            ->patch(route('admin.support-messages.update', $message), [
                'status' => 'resolved',
                'admin_note' => 'Fixed in the latest release.',
            ])
            ->assertRedirect(route('admin.support-messages.show', $message));

        $message->refresh();

        $this->assertSame(SupportMessage::STATUS_RESOLVED, $message->status);
        $this->assertSame('Fixed in the latest release.', $message->admin_note);
        $this->assertSame($admin->id, $message->resolved_by);
        $this->assertNotNull($message->resolved_at);

        $this->assertDatabaseHas('audit_logs', [
            'module' => 'support_messages',
            'record_id' => (string) $message->id,
            'action' => 'updated',
        ]);
    }

    public function test_admin_can_reopen_a_resolved_message(): void
    {
        $admin = User::factory()->admin()->create();
        $message = SupportMessage::factory()->resolved()->create();

        $this->actingAs($admin)
            ->patch(route('admin.support-messages.update', $message), [
                'status' => 'in_progress',
                'admin_note' => null,
            ])
            ->assertRedirect();

        $message->refresh();

        $this->assertSame(SupportMessage::STATUS_IN_PROGRESS, $message->status);
        $this->assertNull($message->resolved_by);
        $this->assertNull($message->resolved_at);
    }

    public function test_admin_can_delete_a_message(): void
    {
        $admin = User::factory()->admin()->create();
        $message = SupportMessage::factory()->create();

        $this->actingAs($admin)
            ->delete(route('admin.support-messages.destroy', $message))
            ->assertRedirect(route('admin.support-messages.index'));

        $this->assertDatabaseMissing('support_messages', ['id' => $message->id]);

        $this->assertDatabaseHas('audit_logs', [
            'module' => 'support_messages',
            'record_id' => (string) $message->id,
            'action' => 'deleted',
        ]);
    }

    public function test_deleting_a_user_cascades_their_messages(): void
    {
        $user = User::factory()->teacher()->create();
        SupportMessage::factory()->for($user)->create();

        $this->assertDatabaseCount('support_messages', 1);

        $user->delete();

        $this->assertDatabaseCount('support_messages', 0);
    }
}
