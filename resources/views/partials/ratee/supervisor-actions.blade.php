@php
    $actions = $rateeProfile['actions'];
    $canSchedule = $canSchedule ?? true;
    $canViewHistory = $canViewHistory ?? true;
@endphp

<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
    <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-5">Supervisor Actions</h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        @if($canSchedule)
        <a href="{{ $actions['schedule_observation'] }}"
           class="flex items-center gap-3 rounded-xl border border-gray-200 dark:border-gray-700 p-4 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors group">
            <div class="w-10 h-10 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            </div>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">Schedule Observation</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Set up the next classroom / school observation.</p>
            </div>
        </a>
        @endif

        @if($canViewHistory)
        <a href="{{ $actions['observation_history'] }}"
           class="flex items-center gap-3 rounded-xl border border-gray-200 dark:border-gray-700 p-4 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors group">
            <div class="w-10 h-10 rounded-lg bg-slate-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">Observation History</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Review all recorded observations and notes.</p>
            </div>
        </a>
        @endif

        @if($actions['post_conference'])
        <a href="{{ $actions['post_conference']['url'] }}"
           class="flex items-center gap-3 rounded-xl border border-amber-200 dark:border-amber-900/40 bg-amber-50 dark:bg-amber-900/20 p-4 hover:bg-amber-100 dark:hover:bg-amber-900/30 transition-colors group sm:col-span-2">
            <div class="w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-400 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
            </div>
            <div class="min-w-0">
                <p class="text-sm font-semibold text-amber-900 dark:text-amber-300">Start / View Post-Observation Conference</p>
                <p class="text-xs text-amber-700 dark:text-amber-400 mt-0.5">{{ $actions['post_conference']['label'] }}</p>
            </div>
        </a>
        @endif
    </div>
</div>
