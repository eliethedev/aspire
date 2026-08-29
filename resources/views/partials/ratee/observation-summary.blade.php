@php $stats = $rateeProfile['stats']; @endphp
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 section-card">
    <div class="flex items-center justify-between gap-3 mb-5">
        <h2 class="text-sm font-bold tracking-widest uppercase text-slate-700 flex items-center gap-2"><span class="w-1.5 h-5 rounded-full bg-indigo-600"></span> Observation Summary</h2>
        <span class="text-xs px-2.5 py-1 rounded-full bg-slate-50 border border-slate-200 text-slate-600">{{ $stats['total'] }} total</span>
    </div>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4">
            <p class="text-2xl font-extrabold text-slate-900">{{ $stats['total'] }}</p>
            <p class="text-xs font-medium text-slate-500">Total Observations</p>
        </div>
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
            <p class="text-2xl font-extrabold text-emerald-700">{{ $stats['completed'] }}</p>
            <p class="text-xs font-medium text-emerald-700/70">Completed</p>
        </div>
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
            <p class="text-2xl font-extrabold text-amber-700">{{ $stats['in_progress'] }}</p>
            <p class="text-xs font-medium text-amber-700/70">In Progress</p>
        </div>
        <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-4">
            <p class="text-xl font-extrabold text-indigo-700">{{ $stats['average_rating'] !== null ? number_format($stats['average_rating'], 2) . ' / 6' : '—' }}</p>
            <p class="text-xs font-medium text-indigo-700/70">Average Rating</p>
        </div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
            <p class="text-xs font-semibold tracking-widest uppercase text-slate-500">Latest Observation</p>
            @if($stats['latest_observation'])
                <p class="mt-1 font-semibold text-slate-900">{{ $stats['latest_observation']['date'] }} @if($stats['latest_observation']['rating'])<span class="text-slate-500">· {{ number_format($stats['latest_observation']['rating'], 2) }} / 6</span>@endif</p>
            @else
                <p class="mt-1 text-sm text-slate-400">None yet</p>
            @endif
        </div>
        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
            <p class="text-xs font-semibold tracking-widest uppercase text-slate-500">Upcoming Observation</p>
            @if($stats['upcoming_observation'])
                <p class="mt-1 font-semibold text-slate-900">{{ $stats['upcoming_observation']['date'] }}</p>
            @else
                <p class="mt-1 text-sm text-slate-400">None scheduled</p>
            @endif
        </div>
    </div>
</div>
