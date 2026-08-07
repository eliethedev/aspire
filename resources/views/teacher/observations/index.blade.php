@extends('layouts.teacher')

@section('title', 'My Observations')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6">
    <!-- Header -->
    <x-page-header title="My Observations" subtitle="View all your classroom observations and evaluation results." />

    <!-- Stats Cards -->
    <div class="grid grid-cols-3 gap-4 mb-8">
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['total'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total Observations</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['upcoming'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Upcoming</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['completed'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Completed</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5 mb-6">
        <form method="GET" action="{{ route('teacher.observations.index') }}">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Search</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" name="search" value="{{ request('search') }}"
                               class="w-full pl-9 pr-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                               placeholder="Search by subject, grade level...">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Status</label>
                    <select name="status"
                            class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        <option value="">All Statuses</option>
                        <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Stage</label>
                    <select name="stage"
                            class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        <option value="">All Stages</option>
                        <option value="pre_observation_planning" {{ request('stage') == 'pre_observation_planning' ? 'selected' : '' }}>Pre-Observation Planning</option>
                        <option value="pre_conference" {{ request('stage') == 'pre_conference' ? 'selected' : '' }}>Pre-Conference</option>
                        <option value="observation" {{ request('stage') == 'observation' ? 'selected' : '' }}>Observation</option>
                        <option value="post_conference" {{ request('stage') == 'post_conference' ? 'selected' : '' }}>Post-Conference</option>
                    </select>
                </div>
                <button type="submit"
                        class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
                    Filter
                </button>
                @if(request()->anyFilled(['search', 'status', 'stage']))
                    <a href="{{ route('teacher.observations.index') }}"
                       class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Observations List -->
    @forelse($observations as $observation)
        @php
            $supervisorName = $observation->observer?->name ?? 'Unknown';
            $stageLabel = str_replace('_', ' ', $observation->stage);
            $stageLabel = ucwords($stageLabel);
            $stageLabel = str_replace('Pre Observation Planning', 'Pre-Observation Planning', $stageLabel);
            $stageLabel = str_replace('Post Conference', 'Post-Conference', $stageLabel);
            $stageLabel = str_replace('Pre Conference', 'Pre-Conference', $stageLabel);
            $confirmationBadge = '';
            if ($observation->stage === 'pre_observation_planning' && $observation->status !== 'cancelled') {
                $confirmationBadge = match($observation->confirmation_status) {
                    'confirmed' => '<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 ml-1">Confirmed</span>',
                    'rejected' => '<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 ml-1">Rejected</span>',
                    default => '<span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 ml-1">Awaiting Confirmation</span>',
                };
            }
        @endphp

        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5 mb-4 hover:shadow-md transition-shadow">
            <div class="flex flex-col sm:flex-row sm:items-start gap-4">
                <!-- Date Badge -->
                <div class="hidden sm:block text-center shrink-0 w-16">
                    <p class="text-sm font-bold text-gray-400 dark:text-gray-500 uppercase">{{ $observation->observation_date?->format('M') ?? 'N/A' }}</p>
                    <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ $observation->observation_date?->format('d') ?? '--' }}</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">{{ $observation->observation_date?->format('Y') ?? '----' }}</p>
                </div>

                <!-- Info -->
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ $observation->subject ?? 'No subject' }}</h3>
                        {!! $confirmationBadge !!}
                        @if($observation->status === 'cancelled')
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">
                            Cancelled
                        </span>
                        @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium
                            {{ $observation->status === 'completed' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : ($observation->status === 'scheduled' ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400' : 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400') }}">
                            {{ ucwords(str_replace('_', ' ', $observation->status)) }}
                        </span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                        {{ $stageLabel }}
                        @if($observation->grade_level) &middot; Grade {{ $observation->grade_level }} @endif
                        &middot; <span class="capitalize">{{ str_replace('_', ' ', $observation->observation_mode) }}</span>
                    </p>
                    <div class="flex items-center gap-4 mt-2 text-sm text-gray-500 dark:text-gray-400">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            {{ $supervisorName }}
                        </span>
                        @if($observation->overall_score)
                        <span class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                            {{ number_format($observation->overall_score, 2) }}
                        </span>
                        @endif
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-2 shrink-0">
                    @if($observation->canConfirm())
                        <a href="{{ route('teacher.observations.show', $observation) }}"
                           class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors">
                            Confirm Schedule
                        </a>
                    @else
                        <a href="{{ route('teacher.observations.show', $observation) }}"
                           class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
                            View Details
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-12 text-center">
            <div class="w-16 h-16 rounded-full bg-gray-50 dark:bg-gray-800 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">No observations yet</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Your supervisor hasn't scheduled any observations yet.</p>
            <a href="{{ route('teacher.dashboard') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors">
                Back to Dashboard
            </a>
        </div>
    @endforelse

    @if($observations->hasPages())
        <div class="mt-8">
            {{ $observations->links() }}
        </div>
    @endif
</div>
@endsection
