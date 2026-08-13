@extends('layouts.teacher')

@section('title', 'Teacher Dashboard')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    @php
        $user = Auth::user();
        $roleColors = ['teacher' => 'bg-indigo-100 text-indigo-700', 'supervisor' => 'bg-emerald-100 text-emerald-700', 'school_head' => 'bg-amber-100 text-amber-700'];
        $roleLabel = ucwords(str_replace('_', ' ', $user->role));
    @endphp

    <!-- Welcome -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Welcome back, {{ $user->name }}!</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ now()->format('l, F j, Y') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium {{ $roleColors[$user->role] ?? 'bg-gray-100 text-gray-700' }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    {{ $roleLabel }}
                </span>
                <a href="{{ route('teacher.observations.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">View All Observations &rarr;</a>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ $stats['total'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2 text-xs">
                <span class="text-gray-500 dark:text-gray-400">{{ $stats['completed'] }} completed</span>
                <span class="text-gray-300 dark:text-gray-600">&middot;</span>
                <span class="text-gray-500 dark:text-gray-400">{{ $stats['in_progress'] }} in progress</span>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Avg Score</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ number_format($stats['average_cot_score'], 1) }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-green-50 dark:bg-green-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-xs">
                @if($trend > 0)
                    <svg class="w-3.5 h-3.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                    <span class="text-green-600 dark:text-green-400 font-medium">+{{ number_format($trend, 1) }}</span>
                    <span class="text-gray-400 dark:text-gray-500">from previous</span>
                @elseif($trend < 0)
                    <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                    <span class="text-red-600 dark:text-red-400 font-medium">{{ number_format($trend, 1) }}</span>
                    <span class="text-gray-400 dark:text-gray-500">from previous</span>
                @else
                    <span class="text-gray-400 dark:text-gray-500">No trend data yet</span>
                @endif
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Completed</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ $stats['completed'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5">
                    <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $stats['total'] > 0 ? ($stats['completed'] / $stats['total']) * 100 : 0 }}%"></div>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $stats['total'] > 0 ? round(($stats['completed'] / $stats['total']) * 100) : 0 }}% completion rate</p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Scheduled</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ $stats['scheduled'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-xs">
                <span class="text-gray-500 dark:text-gray-400">{{ $stats['stage_pre_planning'] + $stats['stage_pre_conference'] + $stats['stage_observation'] }} pending stages</span>
            </div>
            @if($stats['pending_confirmation'] > 0)
            <div class="mt-2">
                <a href="{{ route('teacher.observations.index') }}"
                   class="inline-flex items-center gap-1.5 text-xs font-medium text-amber-600 dark:text-amber-400 hover:text-amber-800 dark:hover:text-amber-300">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ $stats['pending_confirmation'] }} need{{ $stats['pending_confirmation'] > 1 ? '' : 's' }} your confirmation
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Stage Progress -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
        <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider mb-4">Observation Cycle Progress</h2>
        <div class="grid grid-cols-4 gap-3">
            @foreach($stageStatus as $key => $stage)
                <div class="relative flex flex-col items-center text-center p-3 rounded-lg {{ $stage['done'] ? 'bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800' : 'bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700' }}">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center mb-2 {{ $stage['done'] ? 'bg-green-100 dark:bg-green-800/40 text-green-600 dark:text-green-400' : 'bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500' }}">
                        @if($stage['done'])
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        @else
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $stage['icon'] }}"/></svg>
                        @endif
                    </div>
                    <p class="text-xs font-medium {{ $stage['done'] ? 'text-green-700 dark:text-green-300' : 'text-gray-500 dark:text-gray-400' }}">{{ $stage['label'] }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Main grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- COT Chart -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider">COT Score Trend</h2>
                @if(count($cotScores) > 0)
                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ count($cotScores) }} observations</span>
                @endif
            </div>
            @if(count($cotScores) > 0)
                <div class="max-h-48">
                    <canvas id="growthChart"></canvas>
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <p class="text-sm text-gray-400 dark:text-gray-500">No completed observations yet to show trend data.</p>
                </div>
            @endif
        </div>

        <!-- Recent / Next -->
        <div class="space-y-6">
            <!-- Recent -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider">Recent Observation</h2>
                    @if($recentObservation)
                        <a href="{{ route('teacher.observations.show', $recentObservation) }}" class="text-xs text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">View &rarr;</a>
                    @endif
                </div>
                @if($recentObservation)
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-14 h-14 rounded-full bg-indigo-50 dark:bg-indigo-900/40 flex items-center justify-center">
                            <span class="text-xl font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($recentObservation->overall_score, 1) }}</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $recentObservation->observation_date?->format('M d, Y') ?? 'No date' }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $recentObservation->observer?->name ?? 'Unknown' }}</p>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium {{ $recentObservation->status === 'completed' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' }}">
                                {{ ucwords(str_replace('_', ' ', $recentObservation->status)) }}
                            </span>
                        </div>
                    </div>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Subject</span>
                            <span class="text-gray-900 dark:text-gray-100 font-medium">{{ $recentObservation->subject ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Grade Level</span>
                            <span class="text-gray-900 dark:text-gray-100 font-medium">{{ $recentObservation->grade_level ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Stage</span>
                            <span class="text-gray-900 dark:text-gray-100 font-medium capitalize">{{ str_replace('_', ' ', $recentObservation->stage) }}</span>
                        </div>
                    </div>
                @else
                    <div class="flex flex-col items-center py-8 text-center">
                        <svg class="w-10 h-10 text-gray-300 dark:text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <p class="text-sm text-gray-400 dark:text-gray-500">No observation data available yet.</p>
                    </div>
                @endif
            </div>

            <!-- Next -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider">Next Observation</h2>
                    @if($nextObservation)
                        <a href="{{ route('teacher.observations.show', $nextObservation) }}" class="text-xs text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">View &rarr;</a>
                    @endif
                </div>
                @if($nextObservation)
                    @php
                        $daysUntil = $nextObservation->observation_date ? now()->diffInDays($nextObservation->observation_date, false) : 0;
                    @endphp
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-14 h-14 rounded-full {{ $daysUntil <= 0 ? 'bg-red-50 dark:bg-red-900/20' : ($daysUntil <= 3 ? 'bg-amber-50 dark:bg-amber-900/20' : 'bg-blue-50 dark:bg-blue-900/20') }} flex items-center justify-center">
                            <svg class="w-6 h-6 {{ $daysUntil <= 0 ? 'text-red-500' : ($daysUntil <= 3 ? 'text-amber-500' : 'text-blue-500') }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $nextObservation->observation_date?->format('M d, Y') ?? 'No date' }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $nextObservation->observer?->name ?? 'Unknown' }}</p>
                            @if($daysUntil > 0)
                                <span class="text-xs font-medium text-amber-600 dark:text-amber-400">{{ $daysUntil }} day{{ $daysUntil > 1 ? 's' : '' }} away</span>
                            @elseif($daysUntil == 0)
                                <span class="text-xs font-medium text-red-600 dark:text-red-400">Today</span>
                            @else
                                <span class="text-xs font-medium text-red-600 dark:text-red-400">{{ abs($daysUntil) }} day{{ abs($daysUntil) > 1 ? 's' : '' }} overdue</span>
                            @endif
                        </div>
                    </div>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Subject</span>
                            <span class="text-gray-900 dark:text-gray-100 font-medium">{{ $nextObservation->subject ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Stage</span>
                            <span class="text-gray-900 dark:text-gray-100 font-medium capitalize">{{ str_replace('_', ' ', $nextObservation->stage) }}</span>
                        </div>
                    </div>
                @else
                    <div class="flex flex-col items-center py-8 text-center">
                        <svg class="w-10 h-10 text-gray-300 dark:text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <p class="text-sm text-gray-400 dark:text-gray-500">No upcoming observations scheduled.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Bottom row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Post-Conference Feedback -->
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <div class="flex items-center gap-2 mb-4">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider">Post-Conference Feedback</h2>
            </div>
            @if($recentFeedback)
                <div class="space-y-3">
                    @if($recentFeedback->feedback)
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Feedback</p>
                            <p class="text-sm text-gray-800 dark:text-gray-200">{{ Str::limit($recentFeedback->feedback, 250) }}</p>
                        </div>
                    @endif
                    @if($recentFeedback->action_plan)
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Action Plan</p>
                            <p class="text-sm text-gray-800 dark:text-gray-200">{{ Str::limit($recentFeedback->action_plan, 250) }}</p>
                        </div>
                    @endif
                    @if(!$recentFeedback->feedback && !$recentFeedback->action_plan)
                        <div class="flex flex-col items-center py-8 text-center">
                            <svg class="w-10 h-10 text-gray-300 dark:text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                            <p class="text-sm text-gray-400 dark:text-gray-500">No feedback recorded yet.</p>
                        </div>
                    @endif
                </div>
            @else
                <div class="flex flex-col items-center py-8 text-center">
                    <svg class="w-10 h-10 text-gray-300 dark:text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                    <p class="text-sm text-gray-400 dark:text-gray-500">No post-conference feedback available yet.</p>
                </div>
            @endif
            @if($latestFeedbacks->isNotEmpty())
                <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Latest Published Feedback</p>
                    <div class="space-y-2">
                        @foreach($latestFeedbacks as $fb)
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-700 dark:text-gray-300">{{ $fb->feedbackTypeLabel() }}</span>
                                <span class="text-[10px] text-gray-400 dark:text-gray-500">{{ $fb->created_at->format('M d') }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Quick Links -->
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <div class="flex items-center gap-2 mb-4">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider">Quick Links</h2>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <a href="{{ route('teacher.observations.index') }}" class="flex items-center gap-3 p-3 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 hover:bg-indigo-100 dark:hover:bg-indigo-900/30 transition-colors group">
                    <div class="w-9 h-9 rounded-lg bg-indigo-100 dark:bg-indigo-800/40 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-indigo-700 dark:text-indigo-300">Observations</p>
                        <p class="text-xs text-indigo-500 dark:text-indigo-400">View history & details</p>
                    </div>
                </a>
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 p-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 hover:bg-emerald-100 dark:hover:bg-emerald-900/30 transition-colors group">
                    <div class="w-9 h-9 rounded-lg bg-emerald-100 dark:bg-emerald-800/40 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-emerald-700 dark:text-emerald-300">My Profile</p>
                        <p class="text-xs text-emerald-500 dark:text-emerald-400">Update your details</p>
                    </div>
                </a>
                <a href="{{ route('notifications.index') }}" class="flex items-center gap-3 p-3 rounded-lg bg-amber-50 dark:bg-amber-900/20 hover:bg-amber-100 dark:hover:bg-amber-900/30 transition-colors group">
                    <div class="w-9 h-9 rounded-lg bg-amber-100 dark:bg-amber-800/40 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-amber-700 dark:text-amber-300">Notifications</p>
                        <p class="text-xs text-amber-500 dark:text-amber-400">View all alerts</p>
                    </div>
                </a>
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 p-3 rounded-lg bg-purple-50 dark:bg-purple-900/20 hover:bg-purple-100 dark:hover:bg-purple-900/30 transition-colors group">
                    <div class="w-9 h-9 rounded-lg bg-purple-100 dark:bg-purple-800/40 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-purple-700 dark:text-purple-300">Main Dashboard</p>
                        <p class="text-xs text-purple-500 dark:text-purple-400">Go to overview</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

@if(count($cotScores) > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const isDark = document.documentElement.classList.contains('dark');
    const ctx = document.getElementById('growthChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($cotLabels) !!},
            datasets: [{
                label: 'COT Score',
                data: {!! json_encode($cotScores) !!},
                borderColor: '#4f46e5',
                backgroundColor: 'rgba(79, 70, 229, 0.08)',
                borderWidth: 2,
                tension: 0.35,
                fill: true,
                pointBackgroundColor: '#4f46e5',
                pointBorderColor: isDark ? '#1f2937' : '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            aspectRatio: 2.5,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: false,
                    min: 1,
                    max: 7,
                    ticks: { stepSize: 1, color: isDark ? '#9ca3af' : '#6b7280', font: { size: 10 } },
                    grid: { color: isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.05)' }
                },
                x: {
                    ticks: { color: isDark ? '#9ca3af' : '#6b7280', font: { size: 10 } },
                    grid: { display: false }
                }
            }
        }
    });
</script>
@endif
@endsection