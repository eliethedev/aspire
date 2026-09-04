@extends('layouts.supervisor')

@section('title', 'Observation History - ' . $observeeName)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6">
    <!-- Header — minimized -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
        <div>
            <a href="{{ route('supervisor.observations.index') }}" class="inline-flex items-center gap-1 text-xs text-indigo-500 hover:text-indigo-600 mb-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Evaluations
            </a>
            <h1 class="text-base font-bold text-gray-900 dark:text-gray-100 leading-none">{{ $observeeName }}</h1>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 leading-none">Observation history</p>
        </div>
    </div>

    <!-- Stats — compact -->
    <div class="grid grid-cols-3 gap-2 mb-3">
        <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-2.5">
            <p class="text-sm font-bold text-gray-900 dark:text-gray-100 leading-none">{{ $stats['total'] }}</p>
            <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">Total</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-2.5">
            <p class="text-sm font-bold text-gray-900 dark:text-gray-100 leading-none">{{ $stats['completed'] }}</p>
            <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">Completed</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-2.5">
            <p class="text-sm font-bold text-gray-900 dark:text-gray-100 leading-none">{{ $stats['avg_score'] ? number_format($stats['avg_score'], 2) : 'N/A' }}</p>
            <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-0.5">Avg Score</p>
        </div>
    </div>

    <!-- Observations List — 2-col compact -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5">
    @forelse($observations as $observation)
        @php
            $stageLabels = ['pre_observation_planning' => 'Prepare', 'pre_conference' => 'Pre-Observation Conversation', 'observation' => 'Classroom Observation', 'post_conference' => 'Post-Observation Conference'];
            $stageBadgeColor = match($observation->status) {
                'completed' => 'bg-green-100 dark:bg-green-900/30 text-green-700',
                'scheduled' => 'bg-amber-100 dark:bg-amber-900/30 text-amber-700',
                default => 'bg-blue-100 text-blue-700',
            };
        @endphp
        <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-3 hover:shadow-sm transition-shadow flex flex-col gap-2 min-h-[140px] h-full">
            <div class="flex items-start justify-between gap-2">
                <div class="flex gap-2 min-w-0">
                    <div class="text-center shrink-0 leading-none">
                        <p class="text-xs font-bold text-gray-900 dark:text-gray-100 leading-none">{{ $observation->observation_date->format('M') }}</p>
                        <p class="text-base font-bold text-indigo-600 dark:text-indigo-400 leading-none">{{ $observation->observation_date->format('d') }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 leading-none">{{ $observation->observation_date->format('Y') }}</p>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-1 flex-wrap">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">{{ Str::limit($observation->subject ?? 'No subject', 20) }}</h3>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium {{ $stageBadgeColor }}">{{ ucwords(str_replace('_',' ',$observation->status)) }}</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate leading-tight">
                            {{ $stageLabels[$observation->stage] ?? ucwords(str_replace('_',' ',$observation->stage)) }}
                            @if($observation->grade_level) · Grade {{ $observation->grade_level }} @endif
                        </p>
                        @if($observation->overall_score)
                            <p class="text-xs text-gray-400 leading-none mt-0.5">{{ number_format($observation->overall_score,1) }}/6.0</p>
                        @endif
                    </div>
                </div>
                <span class="text-xs text-gray-400 capitalize shrink-0">{{ str_replace('_',' ',$observation->observation_mode) }}</span>
            </div>
            <div class="flex gap-1.5 pt-2 border-t border-gray-100 dark:border-gray-800 mt-auto">
                <a href="{{ route('supervisor.observations.show', $observation) }}" class="flex-1 inline-flex items-center justify-center px-2.5 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50">View</a>
                @php $continueRoute = match($observation->stage) { 'pre_observation_planning'=>'supervisor.observations.preObservationPlanning','pre_conference'=>'supervisor.observations.preConference','observation'=>'supervisor.observations.observation','post_conference'=>$observation->status!=='completed'?'supervisor.observations.postConference':null, default=>null }; @endphp
                @if($continueRoute)
                    <a href="{{ route($continueRoute, $observation) }}" class="flex-1 inline-flex items-center justify-center px-2.5 py-1.5 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700">Continue</a>
                @endif
            </div>
        </div>
    @empty
        <div class="col-span-full bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-6 text-center">
            <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">No observations found</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400">No records yet.</p>
        </div>
    @endforelse
    </div>

    @if($observations->hasPages())
        <div class="mt-3">
            {{ $observations->links() }}
        </div>
    @endif
</div>
@endsection