@extends('layouts.supervisor')

@section('title', 'Observation History - ' . $observeeName)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <a href="{{ route('supervisor.observations.index') }}" class="inline-flex items-center gap-1.5 text-sm text-indigo-400 hover:text-indigo-300 mb-2 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Evaluations
            </a>
            <h1 class="text-2xl font-bold text-dark-900">{{ $observeeName }}</h1>
            <p class="text-dark-500 mt-1">Complete observation history</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-3 gap-4 mb-8">
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <p class="text-2xl font-bold text-dark-900">{{ $stats['total'] }}</p>
            <p class="text-xs text-dark-500">Total Observations</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <p class="text-2xl font-bold text-dark-900">{{ $stats['completed'] }}</p>
            <p class="text-xs text-dark-500">Completed</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <p class="text-2xl font-bold text-dark-900">{{ $stats['avg_score'] ? number_format($stats['avg_score'], 2) : 'N/A' }}</p>
            <p class="text-xs text-dark-500">Average Score</p>
        </div>
    </div>

    <!-- Observations List -->
    @forelse($observations as $observation)
        @php
            $stageLabels = ['pre_observation_planning' => 'Pre-Observation Planning', 'pre_conference' => 'Pre-Conference', 'observation' => 'Observation', 'post_conference' => 'Post-Conference'];
            $stageBadgeColor = match($observation->status) {
                'completed' => 'bg-green-100 dark:bg-green-900/30 text-green-700',
                'scheduled' => 'bg-amber-100 dark:bg-amber-900/30 text-amber-700',
                default => 'bg-blue-100 text-blue-700',
            };
        @endphp
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5 mb-4 hover:shadow-md transition-shadow">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-4">
                    <div class="text-center">
                        <p class="text-lg font-bold text-dark-900">{{ $observation->observation_date->format('M') }}</p>
                        <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $observation->observation_date->format('d') }}</p>
                        <p class="text-xs text-dark-400">{{ $observation->observation_date->format('Y') }}</p>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="font-semibold text-dark-900">{{ $observation->subject ?? 'No subject' }}</h3>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium {{ $stageBadgeColor }}">
                                {{ ucwords(str_replace('_', ' ', $observation->status)) }}
                            </span>
                        </div>
                        <p class="text-sm text-dark-500 mt-0.5">
                            {{ $stageLabels[$observation->stage] ?? ucwords(str_replace('_', ' ', $observation->stage)) }}
                            @if($observation->grade_level) &middot; Grade {{ $observation->grade_level }} @endif
                            &middot; <span class="capitalize">{{ str_replace('_', ' ', $observation->observation_mode) }}</span>
                        </p>
                        @if($observation->overall_score)
                            <p class="text-sm text-dark-400 mt-0.5">Score: {{ number_format($observation->overall_score, 2) }} / 6.00</p>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('supervisor.observations.show', $observation) }}"
                       class="px-3 py-1.5 text-sm font-medium text-dark-600 hover:text-dark-900 hover:bg-gray-50 dark:bg-gray-800 rounded-lg transition-colors">
                        View Details
                    </a>
                    @php
                        $continueRoute = match($observation->stage) {
                            'pre_observation_planning' => 'supervisor.observations.preObservationPlanning',
                            'pre_conference' => 'supervisor.observations.preConference',
                            'observation' => 'supervisor.observations.observation',
                            'post_conference' => $observation->status !== 'completed' ? 'supervisor.observations.postConference' : null,
                            default => null,
                        };
                    @endphp
                    @if($continueRoute)
                        <a href="{{ route($continueRoute, $observation) }}"
                           class="px-4 py-1.5 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
                            Continue
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-12 text-center">
            <h3 class="text-lg font-semibold text-dark-900 mb-1">No observations found</h3>
            <p class="text-sm text-dark-500">This teacher has no observation records yet.</p>
        </div>
    @endforelse

    @if($observations->hasPages())
        <div class="mt-8">
            {{ $observations->links() }}
        </div>
    @endif
</div>
@endsection