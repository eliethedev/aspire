@extends(match (auth()->user()->role) {
    'admin' => 'layouts.admin',
    'supervisor' => 'layouts.supervisor',
    default => 'layouts.teacher',
})

@section('title', 'Notifications')

@php
    $priorityClasses = [
        'critical' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
        'high' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
        'medium' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
        'low' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
    ];

    $filterLink = function (string $statusValue) use ($activeType, $activePriority) {
        return route('notifications.index', array_filter([
            'status' => $statusValue === 'all' ? null : $statusValue,
            'type' => $activeType?->value,
            'priority' => $activePriority?->value,
        ]));
    };
@endphp

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Notifications</h1>
            @if($notifications->total() > 0)
                <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                    @csrf
                    <button type="submit" class="text-sm font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        Mark all as read
                    </button>
                </form>
            @endif
        </div>

        {{-- Filters --}}
        <div class="px-6 py-3 border-b border-gray-200 dark:border-gray-700 flex flex-wrap items-center gap-x-4 gap-y-3">
            <nav class="flex gap-1" aria-label="Filter by read status">
                @foreach(['all' => 'All', 'unread' => 'Unread', 'read' => 'Read'] as $value => $label)
                    <a href="{{ $filterLink($value) }}"
                       class="px-3 py-1.5 text-sm rounded-lg transition-colors {{ $status === $value ? 'bg-indigo-100 text-indigo-700 font-medium dark:bg-indigo-900/40 dark:text-indigo-300' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' }}"
                       :aria-current="{{ $status === $value ? "'page'" : 'false' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </nav>

            <form method="GET" action="{{ route('notifications.index') }}" class="flex flex-wrap items-center gap-2 ms-auto">
                <input type="hidden" name="status" value="{{ $status !== 'all' ? $status : '' }}">
                <label class="sr-only" for="type-filter">Notification type</label>
                <select name="type" id="type-filter" onchange="this.form.submit()"
                        class="text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All types</option>
                    @foreach($types as $type)
                        <option value="{{ $type->value }}" @selected($activeType?->value === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </select>
                <label class="sr-only" for="priority-filter">Priority</label>
                <select name="priority" id="priority-filter" onchange="this.form.submit()"
                        class="text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All priorities</option>
                    @foreach($priorities as $priority)
                        <option value="{{ $priority->value }}" @selected($activePriority?->value === $priority->value)>{{ $priority->label() }}</option>
                    @endforeach
                </select>
                <noscript><button type="submit" class="px-3 py-1.5 text-sm font-medium text-white bg-indigo-600 rounded-lg">Apply</button></noscript>
                @if($activeType || $activePriority || $status !== 'all')
                    <a href="{{ route('notifications.index') }}" class="text-sm text-gray-500 hover:text-indigo-600 dark:text-gray-400">Clear</a>
                @endif
            </form>
        </div>

        <div class="divide-y divide-gray-200 dark:divide-gray-800">
            @forelse($notifications as $notification)
                <div class="px-6 py-4 flex items-start gap-4 {{ $notification->isUnread() ? 'bg-blue-50/50 dark:bg-blue-900/10' : '' }}">
                    <div class="mt-0.5 shrink-0 {{ $notification->isUnread() ? 'text-indigo-500' : 'text-gray-400 dark:text-gray-500' }}">
                        <x-notification-icon :type="$notification->type" class="h-6 w-6" />
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $notification->title }}</h3>
                            <span class="text-xs text-gray-400 dark:text-gray-500">{{ $notification->typeLabel() }}</span>
                            @if($notification->isUnread())
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">New</span>
                            @endif
                        </div>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $notification->message }}</p>
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-2 mt-2">
                            <span class="text-xs text-gray-400 dark:text-gray-500">{{ $notification->created_at?->diffForHumans() }}</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium {{ $priorityClasses[$notification->priorityEnum()?->value] ?? $priorityClasses['low'] }}">
                                {{ $notification->priorityEnum()?->label() ?? 'Medium priority' }}
                            </span>
                            @if($notification->link)
                                <a href="{{ $notification->link }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    View details &rarr;
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="shrink-0 flex flex-col items-end gap-2">
                        @if($notification->isUnread())
                            <form method="POST" action="{{ route('notifications.mark-read', $notification->id) }}">
                                @csrf
                                <button type="submit" class="text-xs font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    Mark as read
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('notifications.mark-unread', $notification->id) }}">
                                @csrf
                                <button type="submit" class="text-xs font-medium text-gray-500 hover:text-indigo-600 dark:text-gray-400 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    Mark as unread
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-6 py-16 text-center">
                    <x-notification-icon :type="$activeType?->value ?? 'system'" class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" />
                    <h3 class="mt-3 text-sm font-medium text-gray-900 dark:text-gray-100">No notifications</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        You don't have any notifications{{ $status !== 'all' || $activeType || $activePriority ? ' matching these filters' : '' }}.
                    </p>
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
@endsection
