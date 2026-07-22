@extends('layouts.supervisor')

@section('title', 'Teacher Profile - ' . $teacher->user->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <a href="{{ route('supervisor.teachers.index') }}" class="inline-flex items-center gap-1.5 text-sm text-indigo-400 hover:text-indigo-300 mb-2 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Teachers List
            </a>
            <h1 class="text-2xl font-bold text-dark-900">{{ $teacher->user->name }}</h1>
            <p class="text-dark-500 mt-1">{{ $teacher->position ?? 'Teacher' }} &middot; {{ $teacher->user->email }}</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('supervisor.observations.teacher-history', ['observeeId' => $teacher->id, 'type' => 'App\\Models\\Teacher']) }}"
               class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 dark:border-gray-600 text-dark-700 rounded-lg text-sm font-medium hover:bg-gray-50 dark:bg-gray-800 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                All Observations
            </a>
            <a href="{{ route('supervisor.observations.create') }}?teacher_id={{ $teacher->id }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                New Observation
            </a>
        </div>
    </div>

    <!-- Profile & Stats Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Teacher Info Card -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                <div class="flex flex-col items-center text-center mb-6">
                    <div class="w-20 h-20 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 flex items-center justify-center text-3xl font-bold mb-3">
                        {{ strtoupper(substr($teacher->user->name, 0, 1)) }}
                    </div>
                    <h2 class="text-lg font-bold text-dark-900">{{ $teacher->user->name }}</h2>
                    <p class="text-sm text-dark-500">{{ $teacher->user->email }}</p>
                    @if($teacher->position)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 mt-2">
                            {{ $teacher->position }}
                        </span>
                    @endif
                </div>

                <div class="space-y-3 text-sm">
                    @if($teacher->employee_number)
                    <div class="flex justify-between">
                        <span class="text-dark-400">Employee No.</span>
                        <span class="font-medium text-dark-700">{{ $teacher->employee_number }}</span>
                    </div>
                    @endif
                    @if($teacher->department)
                    <div class="flex justify-between">
                        <span class="text-dark-400">Department</span>
                        <span class="font-medium text-dark-700">{{ $teacher->department }}</span>
                    </div>
                    @endif
                    @if($teacher->subject)
                    <div class="flex justify-between">
                        <span class="text-dark-400">Subject</span>
                        <span class="font-medium text-dark-700">{{ $teacher->subject }}</span>
                    </div>
                    @endif
                    @if($teacher->grade_level)
                    <div class="flex justify-between">
                        <span class="text-dark-400">Grade Level</span>
                        <span class="font-medium text-dark-700">Grade {{ $teacher->grade_level }}</span>
                    </div>
                    @endif
                    @if($teacher->years_of_service)
                    <div class="flex justify-between">
                        <span class="text-dark-400">Years of Service</span>
                        <span class="font-medium text-dark-700">{{ $teacher->years_of_service }}</span>
                    </div>
                    @endif
                    @if($teacher->prc_license_number)
                    <div class="flex justify-between">
                        <span class="text-dark-400">PRC License</span>
                        <span class="font-medium text-dark-700">{{ $teacher->prc_license_number }}</span>
                    </div>
                    @endif
                    @if($teacher->mobile_number)
                    <div class="flex justify-between">
                        <span class="text-dark-400">Mobile No.</span>
                        <span class="font-medium text-dark-700">{{ $teacher->mobile_number }}</span>
                    </div>
                    @endif
                    @if($teacher->school)
                    <div class="flex justify-between">
                        <span class="text-dark-400">School</span>
                        <span class="font-medium text-dark-700 text-right">{{ $teacher->school->name }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-dark-400">User Since</span>
                        <span class="font-medium text-dark-700">{{ $teacher->user->created_at->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats & Recent Observations -->
        <div class="lg:col-span-2">
            <!-- Stats Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
                    <p class="text-2xl font-bold text-dark-900">{{ $stats['total'] }}</p>
                    <p class="text-xs text-dark-500">Total Observations</p>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['completed'] }}</p>
                    <p class="text-xs text-dark-500">Completed</p>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
                    <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $stats['in_progress'] }}</p>
                    <p class="text-xs text-dark-500">In Progress</p>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
                    <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $stats['avg_score'] ? number_format($stats['avg_score'], 2) : 'N/A' }}</p>
                    <p class="text-xs text-dark-500">Avg Score</p>
                </div>
            </div>

            <!-- Recent Observations -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-lg font-bold text-dark-900">Recent Observations</h2>
                    <a href="{{ route('supervisor.observations.teacher-history', ['observeeId' => $teacher->id, 'type' => 'App\\Models\\Teacher']) }}"
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
                    @endphp
                    <div class="flex items-center justify-between py-3 {{ !$loop->last ? 'border-b border-gray-50' : '' }}">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="text-center shrink-0">
                                <p class="text-xs font-bold text-dark-400 uppercase">{{ $observation->observation_date->format('M') }}</p>
                                <p class="text-lg font-bold text-indigo-600 dark:text-indigo-400 leading-tight">{{ $observation->observation_date->format('d') }}</p>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-medium text-dark-900 text-sm truncate">{{ $observation->subject ?? 'Observation' }}</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium {{ $statusBadge }}">
                                        {{ ucwords(str_replace('_', ' ', $observation->status)) }}
                                    </span>
                                </div>
                                <p class="text-xs text-dark-400 mt-0.5">
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
                            <svg class="w-6 h-6 text-dark-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                        <p class="text-sm text-dark-500">No observations recorded yet.</p>
                        <a href="{{ route('supervisor.observations.create') }}?teacher_id={{ $teacher->id }}"
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
    </div>
</div>
@endsection
