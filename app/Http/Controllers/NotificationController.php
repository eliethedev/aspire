<?php

namespace App\Http\Controllers;

use App\Enums\NotificationPriority;
use App\Enums\NotificationType;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(protected NotificationService $notificationService)
    {
    }

    /**
     * Full "view all" page with filters and pagination.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $status = $request->query('status', 'all');
        $status = in_array($status, ['all', 'unread', 'read'], true) ? $status : 'all';

        $type = NotificationType::tryFrom((string) $request->query('type', ''));
        $priority = NotificationPriority::tryFrom((string) $request->query('priority', ''));

        $notifications = $this->notificationService->paginate($user, 15, $status, $type, $priority);

        return view('notifications.index', [
            'notifications' => $notifications,
            'types' => NotificationType::cases(),
            'priorities' => NotificationPriority::cases(),
            'status' => $status,
            'activeType' => $type,
            'activePriority' => $priority,
        ]);
    }

    /**
     * JSON feed used by the header dropdown (unread first).
     */
    public function recent(Request $request): JsonResponse
    {
        $user = $request->user();

        $notifications = $this->notificationService->getRecent($user, 8);

        return response()->json([
            'notifications' => $notifications->map(fn (Notification $n) => $this->toArray($n)),
            'unread_count' => $this->notificationService->unreadCount($user),
        ]);
    }

    public function markAsRead(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $notification = Notification::findOrFail($id);
        $user = $request->user();

        if (! $this->notificationService->markAsRead($notification, $user)) {
            abort(403, 'Unauthorized');
        }

        return $this->stateResponse($request);
    }

    public function markAsUnread(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $notification = Notification::findOrFail($id);
        $user = $request->user();

        if (! $this->notificationService->markAsUnread($notification, $user)) {
            abort(403, 'Unauthorized');
        }

        return $this->stateResponse($request);
    }

    public function markAllAsRead(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        $this->notificationService->markAllAsRead($user);

        return $this->stateResponse($request);
    }

    public function destroy(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $notification = Notification::findOrFail($id);
        $user = $request->user();

        if (! $this->notificationService->delete($notification, $user)) {
            abort(403, 'Unauthorized');
        }

        return $this->stateResponse($request);
    }

    /**
     * Legacy per-role route — kept so old "view all" links keep working.
     */
    public function show(Request $request, string $role): RedirectResponse
    {
        $user = $request->user();

        if ($user->role !== $role) {
            abort(403, 'Unauthorized access to notifications for this role');
        }

        return redirect()->route('notifications.index');
    }

    protected function stateResponse(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'unread_count' => $this->notificationService->unreadCount($request->user()),
            ]);
        }

        return redirect()->back();
    }

    protected function toArray(Notification $notification): array
    {
        return [
            'id' => $notification->id,
            'type' => $notification->type,
            'type_label' => $notification->typeLabel(),
            'title' => $notification->title,
            'message' => $notification->message,
            'priority' => $notification->priorityEnum()?->value,
            'priority_label' => $notification->priorityEnum()?->label(),
            'link' => $notification->link,
            'is_read' => $notification->isRead(),
            'read_at' => $notification->read_at?->toIso8601String(),
            'created_at' => $notification->created_at?->toIso8601String(),
            'human_time' => optional($notification->created_at)->diffForHumans(),
        ];
    }
}
