@php
    $stats = $rateeProfile['stats'];
@endphp

<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
    <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-5">Observation Summary</h2>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['total'] }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Total Observations</p>
        </div>
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['completed'] }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Completed</p>
        </div>
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $stats['in_progress'] }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">In Progress</p>
        </div>
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                {{ $stats['average_rating'] !== null ? number_format($stats['average_rating'], 2) . ' / 6' : 'N/A' }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Average Rating</p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-slate-50 dark:bg-gray-800 rounded-lg p-4">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Latest Observation</p>
            @if($stats['latest_observation'])
                <p class="mt-1 font-semibold text-gray-900 dark:text-gray-100">
                    {{ $stats['latest_observation']['date'] }}
                    @if($stats['latest_observation']['rating'])
                        &middot; {{ number_format($stats['latest_observation']['rating'], 2) }} / 6
                    @endif
                </p>
            @else
                <p class="mt-1 text-sm text-gray-400 dark:text-gray-500">None yet</p>
            @endif
        </div>
        <div class="bg-slate-50 dark:bg-gray-800 rounded-lg p-4">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Upcoming Observation</p>
            @if($stats['upcoming_observation'])
                <p class="mt-1 font-semibold text-gray-900 dark:text-gray-100">{{ $stats['upcoming_observation']['date'] }}</p>
            @else
                <p class="mt-1 text-sm text-gray-400 dark:text-gray-500">None scheduled</p>
            @endif
        </div>
    </div>
</div>
