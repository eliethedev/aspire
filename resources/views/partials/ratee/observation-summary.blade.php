@php $stats = $rateeProfile['stats']; @endphp
<div class="bg-white dark:bg-gray-900 rounded-2xl border border-slate-200 dark:border-gray-800 shadow-sm p-6 section-card">
    <div class="flex items-center justify-between gap-3 mb-5">
        <h2 class="text-sm font-bold tracking-widest uppercase text-slate-700 dark:text-gray-200 flex items-center gap-2"><span class="w-1.5 h-5 rounded-full bg-indigo-600"></span> Observation Summary</h2>
        <span class="text-xs px-2.5 py-1 rounded-full bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-600 dark:text-gray-300">{{ $stats['total'] }} total</span>
    </div>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <div class="rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50/50 dark:bg-gray-800 p-4">
            <p class="text-2xl font-extrabold text-slate-900 dark:text-gray-100">{{ $stats['total'] }}</p>
            <p class="text-xs font-medium text-slate-500 dark:text-gray-400">Total Observations</p>
        </div>
        <div class="rounded-xl border border-emerald-200 dark:border-emerald-500/30 bg-emerald-50 dark:bg-emerald-500/10 p-4">
            <p class="text-2xl font-extrabold text-emerald-700 dark:text-emerald-300">{{ $stats['completed'] }}</p>
            <p class="text-xs font-medium text-emerald-700/70 dark:text-emerald-300/70">Completed</p>
        </div>
        <div class="rounded-xl border border-amber-200 dark:border-amber-500/30 bg-amber-50 dark:bg-amber-500/10 p-4">
            <p class="text-2xl font-extrabold text-amber-700 dark:text-amber-300">{{ $stats['in_progress'] }}</p>
            <p class="text-xs font-medium text-amber-700/70 dark:text-amber-300/70">In Progress</p>
        </div>
        <div class="rounded-xl border border-indigo-200 dark:border-indigo-500/30 bg-indigo-50 dark:bg-indigo-500/10 p-4">
            <p class="text-xl font-extrabold text-indigo-700 dark:text-indigo-300">{{ $stats['average_rating'] !== null ? number_format($stats['average_rating'], 2) . ' / 6' : '—' }}</p>
            <p class="text-xs font-medium text-indigo-700/70 dark:text-indigo-300/70">Average Rating</p>
        </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div class="bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl p-4">
            <p class="text-xs font-semibold tracking-widest uppercase text-slate-500 dark:text-gray-400">Latest Observation</p>
            @if($stats['latest_observation'])
                <p class="mt-1 font-semibold text-slate-900 dark:text-gray-100">{{ $stats['latest_observation']['date'] }} @if($stats['latest_observation']['rating'])<span class="text-slate-500 dark:text-gray-400">· {{ number_format($stats['latest_observation']['rating'], 2) }} / 6</span>@endif</p>
            @else
                <p class="mt-1 text-sm text-slate-400 dark:text-gray-500">None yet</p>
            @endif
        </div>
        <div class="bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl p-4">
            <p class="text-xs font-semibold tracking-widest uppercase text-slate-500 dark:text-gray-400">Upcoming Observation</p>
            @if($stats['upcoming_observation'])
                <p class="mt-1 font-semibold text-slate-900 dark:text-gray-100">{{ $stats['upcoming_observation']['date'] }}</p>
            @else
                <p class="mt-1 text-sm text-slate-400 dark:text-gray-500">None scheduled</p>
            @endif
        </div>
    </div>
</div>
