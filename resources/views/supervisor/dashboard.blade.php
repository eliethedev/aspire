@extends('layouts.supervisor')

@section('title', 'Supervisor Dashboard')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    @php
        $user = Auth::user();
    @endphp

    <!-- Welcome -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Welcome back, {{ $user->name }}!</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ now()->format('l, F j, Y') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700 dark:text-indigo-300">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                Supervisor
            </span>
            <a href="{{ route('supervisor.observations.index') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 font-medium">All Observations &rarr;</a>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Teachers</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ $stats['total_teachers'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Obs</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ $stats['total_observations'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2 text-xs">
                <span class="text-gray-500 dark:text-gray-400">{{ $stats['scheduled'] }} scheduled</span>
                <span class="text-gray-300 dark:text-gray-600">&middot;</span>
                <span class="text-gray-500 dark:text-gray-400">{{ $stats['in_progress'] }} in progress</span>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Completed</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ $stats['completed'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5">
                    <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $stats['total_observations'] > 0 ? ($stats['completed'] / $stats['total_observations']) * 100 : 0 }}%"></div>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $stats['total_observations'] > 0 ? round(($stats['completed'] / $stats['total_observations']) * 100) : 0 }}% completion rate</p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Avg Score</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ number_format($stats['average_score'], 1) }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-green-50 dark:bg-green-900/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-xs">
                @if($trend > 0)
                    <svg class="w-3.5 h-3.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                    <span class="text-green-600 dark:text-green-400 font-medium">+{{ number_format($trend, 1) }}</span>
                    <span class="text-gray-400 dark:text-gray-500">trend</span>
                @elseif($trend < 0)
                    <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                    <span class="text-red-600 dark:text-red-400 font-medium">{{ number_format($trend, 1) }}</span>
                    <span class="text-gray-400 dark:text-gray-500">trend</span>
                @else
                    <span class="text-gray-400 dark:text-gray-500">No trend data</span>
                @endif
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pending</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ $stats['in_progress'] + $stats['stage_pre_planning'] + $stats['stage_pre_conference'] + $stats['stage_observation'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">{{ $stats['stage_post_conference'] }} in post-conference stage</div>
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
                    <canvas id="cotChart"></canvas>
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <p class="text-sm text-gray-400 dark:text-gray-500">No scored observations yet to show trend data.</p>
                </div>
            @endif
        </div>

        <!-- Quick Actions -->
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <div class="flex items-center gap-2 mb-4">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider">Quick Actions</h2>
            </div>
            <div class="grid grid-cols-1 gap-3">
                <a href="{{ route('supervisor.observations.create') }}" class="flex items-center gap-3 p-3 rounded-lg bg-indigo-50 hover:bg-indigo-100 dark:hover:bg-indigo-800/30 transition-colors group">
                    <div class="w-9 h-9 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-indigo-700 dark:text-indigo-300">New Observation</p>
                        <p class="text-xs text-indigo-500 dark:text-indigo-400">Schedule a new classroom observation</p>
                    </div>
                </a>
                <a href="{{ route('supervisor.teachers.index') }}" class="flex items-center gap-3 p-3 rounded-lg bg-emerald-50 hover:bg-emerald-100 dark:hover:bg-emerald-800/30 transition-colors group">
                    <div class="w-9 h-9 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-emerald-700 dark:text-emerald-400">Teachers</p>
                        <p class="text-xs text-emerald-500 dark:text-emerald-400">Manage your teachers</p>
                    </div>
                </a>
                <a href="{{ route('supervisor.reports.index') }}" class="flex items-center gap-3 p-3 rounded-lg bg-purple-50 hover:bg-purple-100 dark:hover:bg-purple-800/30 transition-colors group">
                    <div class="w-9 h-9 rounded-lg bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-purple-700">Reports</p>
                        <p class="text-xs text-purple-500 dark:text-purple-400">Generate observation reports</p>
                    </div>
                </a>
                <a href="{{ route('supervisor.feedback.center') }}" class="flex items-center gap-3 p-3 rounded-lg bg-amber-50 hover:bg-amber-100 dark:hover:bg-amber-800/30 transition-colors group">
                    <div class="w-9 h-9 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-amber-700 dark:text-amber-400">Feedback Center</p>
                        <p class="text-xs text-amber-500 dark:text-amber-400">Review AI feedback</p>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Continue Where You Left Off -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider">Continue Where You Left Off</h2>
            </div>
            @if($todoObservations->count() > 0)
                <a href="{{ route('supervisor.observations.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 font-medium">View All &rarr;</a>
            @endif
        </div>
        @if($todoObservations->count() > 0)
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($todoObservations as $observation)
                    @php
                        $continueRoute = match($observation->stage) {
                            'pre_observation_planning' => 'supervisor.observations.preObservationPlanning',
                            'pre_conference' => 'supervisor.observations.preConference',
                            'observation' => 'supervisor.observations.observation',
                            'post_conference' => 'supervisor.observations.postConference',
                            default => null,
                        };
                        $stageShort = match($observation->stage) {
                            'pre_observation_planning' => 'Pre-Observation Planning',
                            'pre_conference' => 'Pre-Conference',
                            'observation' => 'Observation',
                            'post_conference' => 'Post-Conference',
                        };
                    @endphp
                    <div class="flex items-center justify-between py-3 first:pt-0 last:pb-0">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-full bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center shrink-0">
                                <span class="text-sm font-bold text-indigo-600 dark:text-indigo-400">{{ strtoupper(substr($observation->observee->user->name ?? '?', 0, 1)) }}</span>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ $observation->observee->user->name ?? 'Unknown' }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $stageShort }} &middot; {{ $observation->observation_date?->format('M d, Y') ?? 'No date' }}</p>
                            </div>
                        </div>
                        @if($continueRoute)
                            <a href="{{ route($continueRoute, $observation) }}"
                               class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors shrink-0">
                                Continue
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="flex flex-col items-center py-12 text-center">
                <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-sm text-gray-400 dark:text-gray-500">You're all caught up. No pending evaluations need your attention.</p>
            </div>
        @endif
    </div>

    <!-- Recent Activity -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider">Recent Activity</h2>
            </div>
            @if($recentObservations->count() > 0)
                <a href="{{ route('supervisor.observations.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 font-medium">View All &rarr;</a>
            @endif
        </div>
        @if($recentObservations->count() > 0)
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($recentObservations as $observation)
                    <div class="flex items-center justify-between py-3 first:pt-0 last:pb-0">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center">
                                <svg class="w-4 h-4 text-indigo-500 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $observation->observee->user->name ?? 'Unknown' }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $observation->observation_date->format('M d, Y') }} &middot; {{ $observation->subject ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium
                                {{ $observation->status === 'completed' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : ($observation->status === 'scheduled' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : ($observation->status === 'cancelled' ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400' : 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400')) }}">
                                {{ ucwords(str_replace('_', ' ', $observation->status)) }}
                            </span>
                            <a href="{{ route('supervisor.observations.show', $observation) }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 font-medium">View</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="flex flex-col items-center py-12 text-center">
                <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-sm text-gray-400 dark:text-gray-500">No recent activity to display.</p>
                <a href="{{ route('supervisor.observations.create') }}" class="mt-3 text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 font-medium">Create your first observation &rarr;</a>
            </div>
        @endif
    </div>
</div>

@if(count($cotScores) > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    new Chart(document.getElementById('cotChart'), {
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
                pointBorderColor: '#fff',
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
                    ticks: { stepSize: 1, color: '#6b7280', font: { size: 10 } },
                    grid: { color: 'rgba(0,0,0,0.05)' }
                },
                x: {
                    ticks: { color: '#6b7280', font: { size: 10 } },
                    grid: { display: false }
                }
            }
        }
    });
</script>
@endif
@endsection