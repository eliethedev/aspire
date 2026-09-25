@extends('layouts.supervisor')

@section('title', 'Career Progression')
@include('partials.dashboard.mock-styles')

@section('content')
<div class="mock-wrap max-w-7xl mx-auto px-1 py-1">
    <div class="mock-topbar">
        <div class="mock-crumbs">Supervisor <span>/</span> <b>Career Progression</b></div>
        @if($statusFilter)<span class="mock-pill amber"><span class="pulse"></span>Filtered</span>@endif
    </div>

    <div class="mock-title">
        <div>
            <h1>Career Progression</h1>
            <p>
                Readiness overview across your ratees. Assessments are support-only &mdash; they never change a teacher's position or career stage.
            </p>
        </div>
        <time>{{ now()->format('l, F j, Y') }}</time>
    </div>

    <!-- Status summary cards -->
    <div class="mock-kpis" role="list" aria-label="Readiness summary">
        <a href="{{ route('supervisor.career.index', ['status' => 'ready_for_consideration']) }}"
           class="mock-kpi {{ $statusFilter === 'ready_for_consideration' ? 'hot' : '' }}" role="listitem">
            <label>Ready for Consideration</label>
            <div class="val">{{ $statusCounts['ready_for_consideration'] }}</div>
            <div class="delta mock-flat">Click to filter</div>
        </a>
        <a href="{{ route('supervisor.career.index', ['status' => 'for_review']) }}"
           class="mock-kpi {{ $statusFilter === 'for_review' ? 'hot' : '' }}" role="listitem">
            <label>For Review</label>
            <div class="val">{{ $statusCounts['for_review'] }}</div>
            <div class="delta mock-flat">Click to filter</div>
        </a>
        <a href="{{ route('supervisor.career.index', ['status' => 'needs_development']) }}"
           class="mock-kpi {{ $statusFilter === 'needs_development' ? 'hot' : '' }}" role="listitem">
            <label>Needs Development</label>
            <div class="val">{{ $statusCounts['needs_development'] }}</div>
            <div class="delta mock-flat">Click to filter</div>
        </a>
        <a href="{{ route('supervisor.career.index', ['status' => 'not_yet_assessed']) }}"
           class="mock-kpi {{ $statusFilter === 'not_yet_assessed' ? 'hot' : '' }}" role="listitem">
            <label>Not Yet Assessed</label>
            <div class="val">{{ $statusCounts['not_yet_assessed'] }}</div>
            <div class="delta mock-flat">Click to filter</div>
        </a>
    </div>

    <!-- Search + filter -->
    <section class="mock-panel" aria-label="Search">
        <div class="mock-panel-head"><h2>Search</h2></div>
        <div style="padding:14px 16px">
    <form method="GET" action="{{ route('supervisor.career.index') }}" class="flex flex-col sm:flex-row gap-2">
        @if ($statusFilter)
            <input type="hidden" name="status" value="{{ $statusFilter }}">
        @endif
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="search" value="{{ $search }}" placeholder="Search by name or email&hellip;"
                   class="w-full pl-9 pr-3 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500">
        </div>
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">Search</button>
    </form>
        </div>
    </section>

    <!-- List -->
    <section class="mock-panel" aria-label="Career progression list">
        <div class="mock-panel-head"><h2>Ratees</h2><span class="hint">Readiness overview</span></div>
        <div class="hidden md:grid grid-cols-12 gap-3 px-5 py-3 bg-gray-50 dark:bg-gray-800/60 border-b border-gray-200 dark:border-gray-700 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
            <div class="col-span-4">Teacher</div>
            <div class="col-span-2">Current Stage</div>
            <div class="col-span-2">Readiness</div>
            <div class="col-span-2">Target Stage</div>
            <div class="col-span-2 text-right">Evidence</div>
        </div>

        @forelse ($rows as $row)
            @php $teacher = $row['teacher']; $assessment = $row['assessment']; @endphp
            <div class="grid grid-cols-1 md:grid-cols-12 gap-2 md:gap-3 px-5 py-4 border-b border-gray-100 dark:border-gray-800 last:border-b-0 hover:bg-gray-50/60 dark:hover:bg-gray-800/30 transition-colors items-center">
                <!-- Teacher -->
                <div class="md:col-span-4 flex items-center gap-3 min-w-0">
                    <span class="w-9 h-9 rounded-full bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 flex items-center justify-center text-sm font-bold shrink-0">
                        {{ strtoupper(mb_substr($teacher->user->name, 0, 1)) }}
                    </span>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $teacher->user->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $teacher->position_label ?? 'Teacher' }} &middot; {{ $teacher->user->email }}</p>
                    </div>
                </div>
                <!-- Current stage -->
                <div class="md:col-span-2 text-sm text-gray-700 dark:text-gray-300">
                    {{ $row['current_stage_label'] }}
                </div>
                <!-- Readiness badge -->
                <div class="md:col-span-2">
                    @if ($assessment)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $assessment->statusBadgeClass() }}">
                            {{ $assessment->statusLabel() }}
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">
                            Not Yet Assessed
                        </span>
                    @endif
                    @if ($assessment && $assessment->assessed_at)
                        <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5">{{ $assessment->assessed_at->format('M d, Y') }}</p>
                    @endif
                </div>
                <!-- Target stage -->
                <div class="md:col-span-2 text-sm">
                    @if ($row['target_stage_label'])
                        <span class="inline-flex items-center gap-1 text-indigo-700 dark:text-indigo-300 font-medium">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                            {{ $row['target_stage_label'] }}
                        </span>
                    @else
                        <span class="text-gray-400 dark:text-gray-500">&mdash;</span>
                    @endif
                </div>
                <!-- Evidence + action -->
                <div class="md:col-span-2 md:text-right">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        @if ($row['observations_count'] > 0)
                            {{ $row['observations_count'] }} obs &middot;
                            <span class="font-semibold text-gray-700 dark:text-gray-300">{{ number_format($row['avg_score'], 2) }}/6</span>
                        @else
                            No COT data yet
                        @endif
                    </p>
                    <a href="{{ route('supervisor.teachers.show', $teacher) }}#readiness"
                       class="inline-flex items-center gap-1 mt-1 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors">
                        View Profile
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        @empty
            <div class="px-5 py-12 text-center">
                <svg class="mx-auto w-10 h-10 text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">No teachers found</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    @if ($search || $statusFilter)
                        Try adjusting your search or filter.
                    @else
                        Teachers assigned to your school will appear here.
                    @endif
                </p>
                @if ($search || $statusFilter)
                    <a href="{{ route('supervisor.career.index') }}" class="inline-block mt-3 text-sm font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">Clear filters</a>
                @endif
            </div>
        @endforelse
    </section>

    <p class="mt-3 text-xs text-gray-400 dark:text-gray-500">
        Readiness assessments support (never replace) DepEd's official promotion process. Click a status card to filter.
    </p>
</div>
@endsection
