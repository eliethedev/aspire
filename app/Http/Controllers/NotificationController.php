<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        $notifications = $user->notifications()->take(10)->get();
        $unreadCount = $user->unreadNotifications()->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    public function markAsRead(Request $request, $id): RedirectResponse
    {
        $notification = Notification::findOrFail($id);
        
        if ($notification->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $notification->markAsRead();

        return redirect()->back();
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $user->unreadNotifications()->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return redirect()->back();
    }

    public function show($role)
    {
        $user = auth()->user();
        
        // Validate that the user has the requested role
        if ($user->role !== $role) {
            abort(403, 'Unauthorized access to notifications for this role');
        }
        
        $notifications = $user->notifications()->paginate(20);
        
        $viewMap = [
            'admin' => 'notifications.admin',
            'teacher' => 'notifications.teacher',
            'supervisor' => 'notifications.supervisor',
            'school_head' => 'notifications.school_head',
        ];
        
        $view = $viewMap[$role] ?? 'notifications.index';

        return view($view, compact('notifications'));
    }
}
