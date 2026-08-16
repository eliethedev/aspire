@extends('layouts.supervisor')

@section('title', 'School Head Profile - ' . $schoolHead->user->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <a href="{{ route('supervisor.school-heads.index') }}" class="inline-flex items-center gap-1.5 text-sm text-indigo-400 hover:text-indigo-300 mb-2 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to School Heads List
            </a>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $rateeProfile['name'] }}</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">{{ $rateeProfile['position'] }} &middot; {{ $schoolHead->user->email }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('supervisor.school-heads.observations', $schoolHead) }}"
               class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-50 dark:bg-gray-800 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                All Observations
            </a>
            <a href="{{ route('supervisor.observations.create', ['school_head' => $schoolHead->id]) }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                New Observation
            </a>
        </div>
    </div>

    <!-- Profile Header -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 mb-8">
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
                    <p class="mt-0.5 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $schoolHead->school?->name ?? '—' }}</p>
                </div>
                <div class="bg-slate-50 dark:bg-gray-800 rounded-lg px-4 py-3">
                    <p class="text-[11px] font-medium text-gray-500 dark:text-gray-400 uppercase">Total Observations</p>
                    <p class="mt-0.5 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $rateeProfile['stats']['total'] }}</p>
                </div>
            </div>
        </div>
    </div>

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
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">Recent Observations</h2>
                <a href="{{ route('supervisor.school-heads.observations', $schoolHead) }}"
                   class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 transition-colors">
                    View All &rarr;
                </a>
            </div>

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
    </div>

    <!-- Supervisor Actions -->
    <div class="mt-8">
        @include('partials.ratee.supervisor-actions', ['rateeProfile' => $rateeProfile])
    </div>
</div>
@endsection
