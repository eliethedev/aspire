@extends('layouts.supervisor')

@section('title', 'School Head Profile - ' . $schoolHead->user->name)
@include('partials.dashboard.mock-styles')

@section('content')
<div class="mock-wrap max-w-7xl mx-auto px-1 py-1">
    <div class="mock-topbar">
        <div class="mock-crumbs">Supervisor <span>/</span> <b>School Head Profile</b></div>
        <div class="mock-actions">
            <a class="mock-btn" href="{{ route('supervisor.school-heads.index') }}">← Back to School Heads</a>
            <a class="mock-btn" href="{{ route('supervisor.school-heads.observations', $schoolHead) }}">All Observations</a>
            <a class="mock-btn primary" href="{{ route('supervisor.observations.create', ['school_head' => $schoolHead->id]) }}">＋ New Observation</a>
        </div>
    </div>

    <div class="mock-title">
        <div>
            <h1>{{ $rateeProfile['name'] }}</h1>
            <p>{{ $rateeProfile['position'] }} &middot; {{ $schoolHead->user->email }}</p>
        </div>
        <time>{{ $schoolHead->school_name ?? '' }}</time>
    </div>

    <!-- Profile Header -->
    <section class="mock-panel" style="padding:20px 24px" aria-label="Profile">
        <div class="flex flex-col md:flex-row md:items-center gap-6">
            <div class="flex items-center gap-5">
                <div class="w-20 h-20 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 flex items-center justify-center text-3xl font-bold shrink-0">
                    {{ strtoupper(substr($schoolHead->user->name, 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $rateeProfile['name'] }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $schoolHead->user->email }}</p>
                    <div class="flex flex-wrap items-center gap-2 mt-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700">
                            {{ $rateeProfile['position'] }}
                        </span>
                        @if($rateeProfile['career_stage_label'])
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700">
                                {{ $rateeProfile['career_stage_label'] }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 md:ml-auto w-full md:w-auto">
                <div class="bg-slate-50 dark:bg-gray-800 rounded-lg px-4 py-3">
                    <p class="text-[11px] font-medium text-gray-500 dark:text-gray-400 uppercase">Role</p>
                    <p class="mt-0.5 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $rateeProfile['role_label'] }}</p>
                </div>
                <div class="bg-slate-50 dark:bg-gray-800 rounded-lg px-4 py-3">
                    <p class="text-[11px] font-medium text-gray-500 dark:text-gray-400 uppercase">School</p>
                    <p class="mt-0.5 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $schoolHead->school_name ?? '—' }}</p>
                </div>
                <div class="bg-slate-50 dark:bg-gray-800 rounded-lg px-4 py-3">
                    <p class="text-[11px] font-medium text-gray-500 dark:text-gray-400 uppercase">Total Observations</p>
                    <p class="mt-0.5 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $rateeProfile['stats']['total'] }}</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Observation Summary -->
    @include('partials.ratee.observation-summary', ['rateeProfile' => $rateeProfile])

    <!-- Performance by Domain -->
    <div class="mt-8">
        @include('partials.ratee.performance-by-domain', ['rateeProfile' => $rateeProfile])
    </div>

    <!-- Areas Requiring Attention -->
    <div class="mt-8">
        @include('partials.ratee.areas-attention', ['rateeProfile' => $rateeProfile])
    </div>

    <!-- Recent Observations -->
    <div class="mt-8">
        <section class="mock-panel" aria-label="Recent observations">
            <div class="mock-panel-head"><h2>Recent Observations</h2><a class="link" href="{{ route('supervisor.school-heads.observations', $schoolHead) }}">View All &rarr;</a></div>
            <div style="padding:4px 16px 14px">

            @forelse($observations as $observation)
                @php
                    $stageLabels = ['pre_observation_planning' => 'Pre-Observation Planning', 'pre_conference' => 'Pre-Conference', 'observation' => 'Observation', 'post_conference' => 'Post-Conference'];
                    $statusBadge = match($observation->status) {
                        'completed' => 'bg-green-100 dark:bg-green-900/30 text-green-700',
                        'scheduled' => 'bg-amber-100 dark:bg-amber-900/30 text-amber-700',
                        'cancelled' => 'bg-red-100 dark:bg-red-900/30 text-red-700',
                        default => 'bg-blue-100 text-blue-700',
                    };
                    $instrumentLabel = $observation->cotIndicatorVersion?->label;
                @endphp
                <div class="flex items-center justify-between py-3 {{ !$loop->last ? 'border-b border-gray-50' : '' }}">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="text-center shrink-0">
                            <p class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase">{{ $observation->observation_date?->format('M') ?? '—' }}</p>
                            <p class="text-lg font-bold text-indigo-600 dark:text-indigo-400 leading-tight">{{ $observation->observation_date?->format('d') ?? '—' }}</p>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-medium text-gray-900 dark:text-gray-100 text-sm truncate">{{ $observation->subject ?? 'Observation' }}</span>
                                @if($instrumentLabel)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">{{ $instrumentLabel }}</span>
                                @endif
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium {{ $statusBadge }}">
                                    {{ ucwords(str_replace('_', ' ', $observation->status)) }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                                {{ $stageLabels[$observation->stage] ?? ucwords(str_replace('_', ' ', $observation->stage)) }}
                                @if($observation->overall_score)
                                    &middot; Score: {{ number_format($observation->overall_score, 2) }}
                                @endif
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('supervisor.observations.show', $observation) }}"
                       class="px-3 py-1.5 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:bg-indigo-900/20 rounded-lg transition-colors shrink-0">
                        View
                    </a>
                </div>
            @empty
                <div class="text-center py-8">
                    <div class="w-12 h-12 rounded-full bg-gray-50 dark:bg-gray-800 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">No observations recorded yet.</p>
                    <a href="{{ route('supervisor.observations.create', ['school_head' => $schoolHead->id]) }}"
                       class="inline-flex items-center gap-1.5 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 mt-2 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Start an Observation
                    </a>
                </div>
            @endforelse

            @if($observations->hasPages())
                <div class="mt-4">
                    {{ $observations->links() }}
                </div>
            @endif
            </div>
        </section>
    </div>

    <!-- Supervisor Actions -->
    <div class="mt-8">
        @include('partials.ratee.supervisor-actions', ['rateeProfile' => $rateeProfile])
    </div>
</div>
@endsection
