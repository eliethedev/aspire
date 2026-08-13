<?php

namespace Tests\Feature;

use App\Enums\NotificationPriority;
use App\Enums\NotificationType;
use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function service(): NotificationService
    {
        return app(NotificationService::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Factory
    |--------------------------------------------------------------------------
    */

    public function test_factory_creates_a_valid_notification(): void
    {
        $user = User::factory()->create();
        $notification = Notification::factory()->for($user)->create();

        $this->assertInstanceOf(Notification::class, $notification);
        $this->assertNotNull(NotificationType::tryFrom($notification->type));
        $this->assertInstanceOf(NotificationPriority::class, $notification->priority);
        $this->assertFalse($notification->isRead());
    }

    /*
    |--------------------------------------------------------------------------
    | Service: create
    |--------------------------------------------------------------------------
    */

    public function test_service_creates_notification_with_type_default_priority(): void
    {
        $user = User::factory()->create();

        $notification = $this->service()->notify(
            $user,
            NotificationType::OBSERVATION,
            'Observation Scheduled',
            'Your observation is scheduled.',
            null,
            '/dashboard'
        );

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
            'user_id' => $user->id,
            'type' => NotificationType::OBSERVATION->value,
            'priority' => NotificationPriority::HIGH->value,
            'title' => 'Observation Scheduled',
            'message' => 'Your observation is scheduled.',
            'link' => '/dashboard',
            'is_read' => false,
        ]);
    }

    public function test_service_accepts_explicit_priority(): void
    {
        $user = User::factory()->create();

        $notification = $this->service()->notify(
            $user,
            NotificationType::ANNOUNCEMENT,
            'New Announcement',
            'Message',
            NotificationPriority::HIGH
        );

        $this->assertSame(NotificationPriority::HIGH, $notification->priority);
    }

    public function test_service_normalizes_raw_type_strings(): void
    {
        $user = User::factory()->create();

        $notification = $this->service()->notify(
            $user,
            'feedback',
            'New Feedback',
            'Message'
        );

        $this->assertSame(NotificationType::FEEDBACK->value, $notification->type);
        $this->assertSame(NotificationPriority::HIGH, $notification->priority);
    }

    public function test_service_rejects_unknown_type(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->service()->notify(
            User::factory()->create(),
            'not-a-real-type',
            'Title',
            'Message'
        );
    }

    public function test_service_rejects_unknown_priority(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->service()->notify(
            User::factory()->create(),
            NotificationType::SYSTEM,
            'Title',
            'Message',
            'ultra'
        );
    }

    public function test_service_notifies_all_users_of_a_role(): void
    {
        User::factory()->count(3)->create(['role' => 'teacher']);
        User::factory()->create(['role' => 'supervisor']);

        $created = $this->service()->notifyByRole(
            'teacher',
            NotificationType::ANNOUNCEMENT,
            'Announcement',
            'Message'
        );

        $this->assertCount(3, $created);
        $this->assertSame(3, Notification::query()->ofType(NotificationType::ANNOUNCEMENT)->count());
    }

    public function test_service_notifies_multiple_users_at_once(): void
    {
        $users = User::factory()->count(2)->create();

        $created = $this->service()->notifyUsers(
            $users,
            NotificationType::SYSTEM,
            'Maintenance',
            'System will be down tonight.'
        );

        $this->assertCount(2, $created);
        $this->assertSame(2, Notification::count());
    }

    /*
    |--------------------------------------------------------------------------
    | Service: read state
    |--------------------------------------------------------------------------
    */

    public function test_service_counts_and_marks_notifications(): void
    {
        $user = User::factory()->create();
        $unread = Notification::factory()->count(3)->for($user)->unread()->create();
        Notification::factory()->count(1)->for($user)->read()->create();

        $this->assertSame(3, $this->service()->unreadCount($user));

        $this->assertTrue($this->service()->markAsRead($unread->first(), $user));
        $this->assertSame(2, $this->service()->unreadCount($user));

        $this->assertTrue($this->service()->markAsUnread($unread->first(), $user));
        $this->assertSame(3, $this->service()->unreadCount($user));

        $this->service()->markAllAsRead($user);
        $this->assertSame(0, $this->service()->unreadCount($user));
    }

    public function test_service_refuses_to_mark_another_users_notification(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $notification = Notification::factory()->for($owner)->create();

        $this->assertFalse($this->service()->markAsRead($notification, $other));
        $this->assertFalse($notification->fresh()->isRead());
    }

    /*
    |--------------------------------------------------------------------------
    | Service: retrieval & pagination
    |--------------------------------------------------------------------------
    */

    public function test_service_paginates_with_status_type_and_priority_filters(): void
    {
        $user = User::factory()->create();

        Notification::factory()->for($user)->ofType(NotificationType::FEEDBACK)->unread()->create();
        Notification::factory()->for($user)->ofType(NotificationType::ANNOUNCEMENT)->unread()->create();
        Notification::factory()->for($user)->ofType(NotificationType::ANNOUNCEMENT)->read()->create();

        $unread = $this->service()->paginate($user, 15, 'unread');
        $this->assertSame(2, $unread->total());

        $read = $this->service()->paginate($user, 15, 'read');
        $this->assertSame(1, $read->total());

        $feedbackOnly = $this->service()->paginate($user, 15, 'all', NotificationType::FEEDBACK);
        $this->assertSame(1, $feedbackOnly->total());

        $highOnly = $this->service()->paginate($user, 15, 'all', null, NotificationPriority::HIGH);
        $this->assertSame(1, $highOnly->total());
    }

    /*
    |--------------------------------------------------------------------------
    | HTTP
    |--------------------------------------------------------------------------
    */

    public function test_guest_is_redirected_from_notifications_pages(): void
    {
        $this->get(route('notifications.index'))->assertRedirect(route('login'));
        $this->get(route('notifications.recent'))->assertRedirect(route('login'));
    }

    public function test_user_can_view_their_notifications_page(): void
    {
        $user = User::factory()->create(['role' => 'teacher']);
        $notification = Notification::factory()->for($user)->create([
            'title' => 'Your observation is scheduled',
        ]);

        $response = $this->actingAs($user)->get(route('notifications.index'));

        $response->assertOk();
        $response->assertSee('Notifications');
        $response->assertSee('Your observation is scheduled');
    }

    public function test_recent_endpoint_returns_unread_count_json(): void
    {
        $user = User::factory()->create();
        Notification::factory()->count(3)->for($user)->unread()->create();

        $response = $this->actingAs($user)->getJson(route('notifications.recent'));

        $response->assertOk();
        $response->assertJson([
            'unread_count' => 3,
        ]);
        $response->assertJsonCount(3, 'notifications');
    }

    public function test_user_can_mark_own_notification_as_read(): void
    {
        $user = User::factory()->create();
        $notification = Notification::factory()->for($user)->create();

        $response = $this->actingAs($user)->postJson(
            route('notifications.mark-read', $notification->id)
        );

        $response->assertOk()->assertJson(['unread_count' => 0]);
        $this->assertTrue($notification->fresh()->isRead());
    }

    public function test_user_can_mark_own_notification_as_unread(): void
    {
        $user = User::factory()->create();
        $notification = Notification::factory()->for($user)->read()->create();

        $response = $this->actingAs($user)->postJson(
            route('notifications.mark-unread', $notification->id)
        );

        $response->assertOk()->assertJson(['unread_count' => 1]);
        $this->assertFalse($notification->fresh()->isRead());
    }

    public function test_user_cannot_mark_another_users_notification(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $notification = Notification::factory()->for($owner)->create();

        $this->actingAs($other)
            ->postJson(route('notifications.mark-read', $notification->id))
            ->assertForbidden();

        $this->assertFalse($notification->fresh()->isRead());
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $user = User::factory()->create();
        Notification::factory()->count(4)->for($user)->unread()->create();

        $response = $this->actingAs($user)->postJson(route('notifications.mark-all-read'));

        $response->assertOk()->assertJson(['unread_count' => 0]);
        $this->assertSame(0, $this->service()->unreadCount($user));
    }

    public function test_legacy_role_route_redirects_when_role_matches(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($teacher)
            ->get(route('notifications.show', 'teacher'))
            ->assertRedirect(route('notifications.index'));
    }

    public function test_legacy_role_route_forbids_other_roles(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);

        $this->actingAs($teacher)
            ->get(route('notifications.show', 'admin'))
            ->assertForbidden();
    }
}
