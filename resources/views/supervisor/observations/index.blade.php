@extends('layouts.supervisor')

@section('title', 'My Evaluations')

@push('styles')
<style>
    .eval-card {
        transition: all 0.2s ease;
    }
    .eval-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06);
    }
    .stage-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
    }
    .stage-line {
        flex: 1;
        height: 2px;
        border-radius: 1px;
    }
</style>
@endpush

@section('content')
@php $hasFilters = request()->anyFilled(['search', 'observation_type', 'status', 'stage', 'date_from', 'date_to']); @endphp
<div class="max-w-7xl mx-auto px-4 sm:px-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
        <div>
            <h1 class="text-lg font-bold text-gray-900 dark:text-gray-100">My Evaluations</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Manage and track all your classroom observations and leadership evaluations.</p>
        </div>
        <a href="{{ route('supervisor.observations.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            New Evaluation
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2.5 mb-3">
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-3">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div>
                    <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $stats['total'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total Evaluations</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-3">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $stats['in_progress'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">In Progress</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-3">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $stats['completed'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Completed</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-3">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-red-100 dark:bg-red-900/30 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <div>
                    <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $stats['cancelled'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Cancelled</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm mb-3" x-data="{ open: @json($hasFilters) }">
        <button type="button" @click="open = !open"
                class="w-full flex items-center justify-between gap-2 px-3 py-2 text-left">
            <span class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-200">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                Filters
                @if($hasFilters)
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400">Active</span>
                @endif
            </span>
            <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div x-show="open">
            <form method="GET" action="{{ route('supervisor.observations.index') }}">
                <div class="px-3 pb-3 pt-3 border-t border-gray-100 dark:border-gray-800 flex flex-wrap items-end gap-2">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Search</label>
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <input type="text" name="search" value="{{ request('search') }}"
                                   class="w-full pl-9 pr-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                                   placeholder="Search by observee name, subject, grade level...">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Type</label>
                        <select name="observation_type"
                                class="px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                            <option value="">All Types</option>
                            <option value="teacher_observation" {{ request('observation_type') == 'teacher_observation' ? 'selected' : '' }}>Teacher</option>
                            <option value="school_head_observation" {{ request('observation_type') == 'school_head_observation' ? 'selected' : '' }}>School Head</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Status</label>
                        <select name="status"
                                class="px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                            <option value="">All Statuses</option>
                            <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Stage</label>
                        <select name="stage"
                                class="px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                            <option value="">All Stages</option>
                            <option value="pre_observation_planning" {{ request('stage') == 'pre_observation_planning' ? 'selected' : '' }}>Pre-Observation Planning</option>
                            <option value="pre_conference" {{ request('stage') == 'pre_conference' ? 'selected' : '' }}>Pre-Conference</option>
                            <option value="observation" {{ request('stage') == 'observation' ? 'selected' : '' }}>Observation</option>
                            <option value="post_conference" {{ request('stage') == 'post_conference' ? 'selected' : '' }}>Post-Conference</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">From</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                               class="px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">To</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                               class="px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                    </div>
                    <button type="submit"
                            class="px-4 py-1.5 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
                        Filter
                    </button>
                    @if($hasFilters)
                        <a href="{{ route('supervisor.observations.index') }}"
                           class="px-3 py-1.5 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Evaluations List -->
    @forelse($observations as $observation)
        @php
            $observee = $observation->observee;
            $observeeName = $observee?->user?->name ?? 'Unknown';
            $observeeInitial = strtoupper(substr($observeeName, 0, 1));
            $isTeacher = $observation->observation_type === 'teacher_observation';
            $stageLabel = str_replace('_', ' ', $observation->stage);
            $stageLabel = ucwords($stageLabel);
            $stageLabel = str_replace('Pre Observation Planning', 'Pre-Observation Planning', $stageLabel);
            $stageLabel = str_replace('Post Conference', 'Post-Conference', $stageLabel);
            $stageLabel = str_replace('Pre Conference', 'Pre-Conference', $stageLabel);
        @endphp

        <div class="eval-card bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 mb-3">
            <div class="flex flex-col sm:flex-row sm:items-start gap-4">
                <!-- Observee Avatar + Info -->
                <div class="flex items-center gap-3 min-w-0 flex-1">
                    <div class="w-11 h-11 rounded-full {{ $isTeacher ? 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700' : 'bg-emerald-100 text-emerald-700' }} flex items-center justify-center text-base font-bold shrink-0">
                        {{ $observeeInitial }}
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $observeeName }}</h3>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium {{ $isTeacher ? 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700' : 'bg-emerald-100 text-emerald-700' }}">
                                {{ $isTeacher ? 'Teacher' : 'School Head' }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $observation->subject ?? 'No subject' }}
                            @if($observation->grade_level)
                                &middot; {{ $observation->grade_level }}
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Date + Schedule -->
                <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500 dark:text-gray-400 shrink-0">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>{{ $observation->observation_date?->format('M d, Y') ?? 'No date' }}</span>
                    </div>
                    @if($observation->has_time_schedule)
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ $observation->start_time_label }}
                            @if($observation->end_time_label) - {{ $observation->end_time_label }} @endif</span>
                    </div>
                    @endif
                    @if($observation->location)
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span class="truncate max-w-[180px]">{{ $observation->location }}</span>
                    </div>
                    @endif
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span class="capitalize">{{ str_replace('_', ' ', $observation->observation_mode) }}</span>
                    </div>
                </div>
            </div>

            <!-- Stage Progress + Status + Actions -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mt-4 pt-4 border-t border-gray-50">
                <!-- Stage Progress Bar -->
                <div class="flex items-center gap-2 text-xs">
                    @php
                        $stages = ['pre_observation_planning', 'pre_conference', 'observation', 'post_conference'];
                        $currentIdx = array_search($observation->stage, $stages);
                        $isCompleted = $observation->stage === 'post_conference';
                    @endphp
                    @foreach($stages as $i => $s)
                        @php
                            $done = $i < $currentIdx || ($i === $currentIdx && $isCompleted);
                            $active = $i === $currentIdx && !$isCompleted;
                        @endphp
                        <div class="flex items-center gap-2">
                            <div class="flex items-center gap-1.5">
                                <div class="stage-dot {{ $done ? 'bg-indigo-500' : ($active ? 'bg-indigo-400 ring-2 ring-indigo-100' : 'bg-gray-200') }}"></div>
                                <span class="{{ $done ? 'text-indigo-600 dark:text-indigo-400 font-medium' : ($active ? 'text-gray-900 dark:text-gray-100 font-medium' : 'text-gray-400 dark:text-gray-500') }} whitespace-nowrap">
                                    {{ match($s) {
                                        'pre_observation_planning' => 'Planning',
                                        'pre_conference' => 'Pre-Conf',
                                        'observation' => 'Observation',
                                        'post_conference' => 'Post-Conf',
                                    } }}
                                </span>
                            </div>
                            @if($i < count($stages) - 1)
                                <div class="stage-line {{ $done ? 'bg-indigo-300' : 'bg-gray-200' }}"></div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Status + Actions -->
                <div class="flex items-center gap-3 shrink-0">
                    @if($observation->status === 'cancelled')
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                            Cancelled
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium
                            {{ $observation->status === 'completed' ? 'bg-green-100 dark:bg-green-900/30 text-green-700' : ($observation->status === 'scheduled' ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700' : 'bg-blue-100 text-blue-700') }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $observation->status === 'completed' ? 'bg-green-500' : ($observation->status === 'scheduled' ? 'bg-amber-500' : 'bg-blue-500') }}"></span>
                            {{ ucwords(str_replace('_', ' ', $observation->status)) }}
                        </span>
                    @endif

                    <a href="{{ route('supervisor.observations.show', $observation) }}"
                       class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-50 dark:bg-gray-800 rounded-lg transition-colors min-h-[44px] inline-flex items-center">
                        View
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
                    @if($continueRoute && $observation->status !== 'cancelled')
                        <a href="{{ route($continueRoute, $observation) }}"
                           class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors min-h-[44px] inline-flex items-center">
                            Continue
                        </a>
                    @endif

                    @if($observation->canCancel())
                        <a href="{{ route('supervisor.observations.cancel-form', $observation) }}"
                           class="px-4 py-2 text-sm font-medium text-red-600 dark:text-red-400 hover:text-red-800 dark:text-red-300 hover:bg-red-50 dark:bg-red-900/20 rounded-lg transition-colors min-h-[44px] inline-flex items-center">
                            Cancel
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <!-- Empty State -->
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-12 text-center">
            <div class="w-16 h-16 rounded-full bg-gray-50 dark:bg-gray-800 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            @if($hasFilters)
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">No evaluations match your filters</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Try adjusting your search or clearing the filters to see more evaluations.</p>
                <a href="{{ route('supervisor.observations.index') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 rounded-lg font-medium hover:bg-gray-50 transition-colors">
                    Clear Filters
                </a>
            @else
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">No evaluations yet</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Create your first evaluation to get started.</p>
                <a href="{{ route('supervisor.observations.create') }}"
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Create Evaluation
                </a>
            @endif
        </div>
    @endforelse

    <!-- Pagination -->
    @if($observations->hasPages())
        <div class="mt-4">
            {{ $observations->links() }}
        </div>
    @endif
</div>
@endsection
