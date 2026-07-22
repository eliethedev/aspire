@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white dark:bg-gray-900 rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Notifications</h1>
            @if(auth()->user()->unreadNotifications()->count() > 0)
            <form method="POST" action="{{ route('notifications.mark-all-read') }}" class="inline">
                @csrf
                <button type="submit" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                    Mark all as read
                </button>
            </form>
            @endif
        </div>

        <div class="divide-y divide-gray-200">
            @forelse($notifications as $notification)
            <a href="{{ $notification->link ?? '#' }}" 
               class="block px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-800 {{ !$notification->is_read ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}"
               onclick="markAsRead({{ $notification->id }})">
                <div class="flex items-start">
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ $notification->title }}</h3>
                            @if(!$notification->is_read)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300">
                                New
                            </span>
                            @endif
                        </div>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $notification->message }}</p>
                        <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                </div>
            </a>
            @empty
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No notifications</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">You don't have any notifications yet.</p>
            </div>
            @endforelse
        </div>

        @if($notifications->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $notifications->links() }}
        </div>
        @endif
    </div>
</div>

<script>
function markAsRead(id) {
    fetch(`/notifications/${id}/mark-read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
        },
    });
}
</script>
@endsection
