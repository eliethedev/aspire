@php $actions = $rateeProfile['actions']; $canSchedule = $canSchedule ?? true; $canViewHistory = $canViewHistory ?? true; @endphp
<div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 section-card">
    <h2 class="text-sm font-bold tracking-widest uppercase text-slate-700 flex items-center gap-2 mb-5"><span class="w-1.5 h-5 rounded-full bg-indigo-600"></span> Supervisor Actions</h2>
    <div class="grid grid-cols-1 gap-3">
        @if($canSchedule)
        <a href="{{ $actions['schedule_observation'] }}" class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 hover:border-indigo-200 hover:shadow-sm transition-all group">
            <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                <i class="fas fa-plus text-sm"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-slate-900 group-hover:text-indigo-600">Schedule Observation</p>
                <p class="text-xs text-slate-500 mt-0.5">Set up the next classroom observation cycle.</p>
            </div>
            <i class="fas fa-chevron-right text-xs text-slate-300 group-hover:text-indigo-400"></i>
        </a>
        @endif
        @if($canViewHistory)
        <a href="{{ $actions['observation_history'] }}" class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 hover:border-slate-300 hover:shadow-sm transition-all group">
            <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 text-slate-600 flex items-center justify-center shrink-0">
                <i class="fas fa-clock-rotate-left text-sm"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-slate-900 group-hover:text-slate-700">Observation History</p>
                <p class="text-xs text-slate-500 mt-0.5">Review all recorded observations and notes.</p>
            </div>
            <i class="fas fa-chevron-right text-xs text-slate-300"></i>
        </a>
        @endif
        @if($actions['post_conference'])
        <a href="{{ $actions['post_conference']['url'] }}" class="flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 hover:bg-amber-100 hover:border-amber-300 transition-all group">
            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                <i class="fas fa-comments text-sm"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-amber-900">Post-Observation Conference</p>
                <p class="text-xs text-amber-800/80 mt-0.5">{{ $actions['post_conference']['label'] }}</p>
            </div>
            <i class="fas fa-chevron-right text-xs text-amber-700/50"></i>
        </a>
        @endif
    </div>
</div>
