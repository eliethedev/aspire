@extends('layouts.admin')

@section('title', 'System Calendar')
@include('partials.dashboard.mock-styles')

@section('content')
<div class="mock-wrap max-w-7xl mx-auto px-1 py-1">
    {{-- Admin header --}}
    <div class="mock-topbar"><div class="mock-crumbs">Admin <span>/</span> <b>System Calendar</b></div><div class="mock-actions"><div>
            
            
            
        </div>
        <a href="{{ route('admin.observations.index') }}"
           class="mock-btn primary">
            <i class="fa-solid fa-clipboard-list text-xs"></i>All Observations
        </a></div></div>
<div class="mock-title"><div><h1>System Calendar</h1><p class="text-indigo-600 dark:text-indigo-400 text-xs tracking-widest uppercase font-semibold">System Oversight</p>
<p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Every observation and conference across all schools — filter by school, status, or type.</p></div><time>{{ now()->format('l, F j, Y') }}</time></div>

    {{-- Stats row --}}
    <div class="mock-kpis">
        <div class="mock-kpi">
            <div class="flex justify-between items-start gap-2">
                <div class="min-w-0">
                    <p class="text-[11px] tracking-widest uppercase font-semibold text-slate-500 dark:text-gray-400">Today</p>
                    <p class="text-2xl sm:text-3xl font-extrabold mt-1 text-slate-900 dark:text-gray-100">{{ $stats['today'] }}</p>
                    <p class="text-xs text-slate-500 dark:text-gray-400 mt-1">Scheduled for today</p>
                </div>
                <div class="w-9 h-9 sm:w-11 sm:h-11 shrink-0 rounded-xl bg-indigo-50 border border-indigo-100 dark:bg-indigo-500/10 dark:border-indigo-500/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400"><i class="fas fa-calendar-day"></i></div>
            </div>
        </div>
        <div class="mock-kpi">
            <div class="flex justify-between items-start gap-2">
                <div class="min-w-0">
                    <p class="text-[11px] tracking-widest uppercase font-semibold text-slate-500 dark:text-gray-400">Next 7 Days</p>
                    <p class="text-2xl sm:text-3xl font-extrabold mt-1 text-slate-900 dark:text-gray-100">{{ $stats['this_week'] }}</p>
                    <p class="text-xs text-slate-500 dark:text-gray-400 mt-1">Upcoming activities</p>
                </div>
                <div class="w-9 h-9 sm:w-11 sm:h-11 shrink-0 rounded-xl bg-sky-50 border border-sky-200 dark:bg-sky-500/10 dark:border-sky-500/30 flex items-center justify-center text-sky-600 dark:text-sky-400"><i class="fas fa-calendar-week"></i></div>
            </div>
        </div>
        <div class="mock-kpi">
            <div class="flex justify-between items-start gap-2">
                <div class="min-w-0">
                    <p class="text-[11px] tracking-widest uppercase font-semibold text-slate-500 dark:text-gray-400">Pending Cycles</p>
                    <p class="text-2xl sm:text-3xl font-extrabold mt-1 text-slate-900 dark:text-gray-100">{{ $stats['pending'] }}</p>
                    <p class="text-xs text-slate-500 dark:text-gray-400 mt-1">Not yet at post-conference</p>
                </div>
                <div class="w-9 h-9 sm:w-11 sm:h-11 shrink-0 rounded-xl bg-amber-50 border border-amber-200 dark:bg-amber-500/10 dark:border-amber-500/30 flex items-center justify-center text-amber-600 dark:text-amber-400"><i class="fas fa-hourglass-half"></i></div>
            </div>
        </div>
        <div class="mock-kpi hot">
            <div class="flex justify-between items-start gap-2">
                <div class="min-w-0">
                    <p class="text-[11px] tracking-widest uppercase font-semibold text-slate-500 dark:text-gray-400">Completion</p>
                    <p class="text-2xl sm:text-3xl font-extrabold mt-1 text-slate-900 dark:text-gray-100">{{ $stats['completion_rate'] }}%</p>
                    <p class="text-xs text-slate-500 dark:text-gray-400 mt-1">Cycles finished</p>
                </div>
                <div class="w-9 h-9 sm:w-11 sm:h-11 shrink-0 rounded-xl bg-emerald-50 border border-emerald-200 dark:bg-emerald-500/10 dark:border-emerald-500/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400"><i class="fas fa-circle-check"></i></div>
            </div>
            <div class="mt-3 h-2 bg-slate-100 dark:bg-gray-800 rounded-full overflow-hidden"><div class="h-2 bg-emerald-500 rounded-full" style="width: {{ $stats['completion_rate'] }}%"></div></div>
        </div>
    </div>

    <div class="grid xl:grid-cols-3 gap-4 sm:gap-6 items-start">
        {{-- Calendar with admin filters --}}
        <div class="xl:col-span-2 min-w-0">
            @include('partials.calendar', [
                'intro' => 'System-wide schedule across all schools.',
                'scheduleRoute' => null,
                'scheduleLabel' => null,
                'hideHeader' => true,
                'showFilters' => true,
                'schools' => $schools,
                'stackedSidebar' => true,
            ])
        </div>

        {{-- Admin sidebar --}}
        <div class="space-y-4 sm:space-y-6 min-w-0">
            {{-- Needs attention --}}
            <section class="mock-panel bg-white dark:bg-gray-900 rounded-2xl border border-slate-200 dark:border-gray-800 shadow-sm p-4 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xs font-bold tracking-widest uppercase flex items-center gap-2 text-slate-700 dark:text-gray-200"><span class="w-1.5 h-5 bg-rose-500 rounded-full"></span><i class="fas fa-triangle-exclamation text-rose-500"></i> Needs Attention</h2>
                    <span class="inline-flex items-center justify-center min-w-[22px] h-[22px] px-1.5 text-[11px] font-bold text-rose-700 dark:text-rose-300 bg-rose-50 dark:bg-rose-500/10 rounded-full">{{ $attentionCount }}</span>
                </div>
                <div class="space-y-2.5">
                    @forelse($attention as $item)
                        <a href="{{ $item['link'] }}" class="block p-3 rounded-xl border border-slate-200 dark:border-gray-700 hover:border-rose-200 dark:hover:border-rose-500/50 hover:bg-rose-50/40 dark:hover:bg-rose-900/10 transition-colors">
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-sm font-semibold text-slate-900 dark:text-gray-100 truncate">{{ $item['observee_name'] }}</p>
                                <span class="shrink-0 inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold {{ $item['reason'] === 'Overdue' ? 'bg-rose-100 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-500/30' : 'bg-amber-100 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-500/30' }}">{{ $item['reason'] }}</span>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-gray-400 mt-1 truncate"><i class="fa-solid fa-school mr-1"></i>{{ $item['school_name'] }} · {{ $item['observation_date_label'] }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $item['stage_label'] }}</p>
                        </a>
                    @empty
                        <p class="text-sm text-slate-400 text-center py-8 border-2 border-dashed border-slate-200 dark:border-gray-700 rounded-xl">Nothing overdue. All clear.</p>
                    @endforelse
                </div>
                @if($attentionCount > count($attention))
                    <a href="{{ route('admin.observations.index') }}" class="mt-3 block text-center text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700">View all {{ $attentionCount }} →</a>
                @endif
            </section>

            {{-- Schools to watch --}}
            <section class="mock-panel bg-white dark:bg-gray-900 rounded-2xl border border-slate-200 dark:border-gray-800 shadow-sm p-4 sm:p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xs font-bold tracking-widest uppercase flex items-center gap-2 text-slate-700 dark:text-gray-200"><span class="w-1.5 h-5 bg-violet-500 rounded-full"></span><i class="fas fa-school text-violet-600"></i> Busiest Schools · 30 Days</h2>
                    <a href="{{ route('admin.schools.index') }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">View all →</a>
                </div>
                <div class="space-y-2.5">
                    @forelse($schoolBreakdown as $idx => $school)
                        <div class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 dark:border-gray-700">
                            <div class="w-8 h-8 rounded-lg bg-violet-50 dark:bg-violet-500/10 border border-violet-200 dark:border-violet-500/30 text-violet-700 dark:text-violet-400 flex items-center justify-center text-sm font-extrabold shrink-0">{{ $idx + 1 }}</div>
                            <p class="flex-1 min-w-0 text-sm font-semibold text-slate-900 dark:text-gray-100 truncate">{{ $school['name'] }}</p>
                            <span class="shrink-0 text-xs font-bold px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-gray-800 text-slate-600 dark:text-gray-300">{{ $school['total'] }} upcoming</span>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400 text-center py-8 border-2 border-dashed border-slate-200 dark:border-gray-700 rounded-xl">No upcoming school load.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
