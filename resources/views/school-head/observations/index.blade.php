@extends('layouts.teacher')

@section('title', 'Observations')

@push('styles')
<style>
    .obs-card {
        transition: all 0.2s ease;
    }
    .obs-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06);
    }
</style>
@endpush

@section('content')
@php $hasFilters = request()->anyFilled(['search', 'status']); @endphp
<div class="max-w-7xl mx-auto px-4 sm:px-6">
    <!-- Header -->
    <x-page-header title="Observations" subtitle="Manage teacher observations you've scheduled and your own performance observations.">
        <x-slot name="actions">
            <a href="{{ route('school-head.observations.create') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium text-sm transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Schedule Observation
            </a>
        </x-slot>
    </x-page-header>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 mb-3">
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-3">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-gray-100 mt-0.5">{{ $stats['total'] }}</p>
                </div>
                <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-3">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Upcoming</p>
                    <p class="text-lg font-bold text-amber-600 dark:text-amber-400 mt-0.5">{{ $stats['upcoming'] }}</p>
                </div>
                <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-3">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Completed</p>
                    <p class="text-lg font-bold text-green-600 dark:text-green-400 mt-0.5">{{ $stats['completed'] }}</p>
                </div>
                <div class="w-8 h-8 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm mb-3" x-data="{ open: @json($hasFilters) }">
        <button type="button" @click="open = !open" :aria-expanded="open.toString()"
                class="w-full flex items-center justify-between gap-2 px-3 py-2 text-left hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors rounded-t-xl">
            <span class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-200">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                Filters
                @if($hasFilters)
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400">Active</span>
                @endif
            </span>
            <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div x-show="open" x-cloak x-transition>
            <div class="px-3 py-3 border-t border-gray-100 dark:border-gray-800">
                <form method="GET" class="flex flex-wrap items-center gap-2">
                    <div class="relative flex-1 min-w-[200px]">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by subject, school year..."
                               class="w-full pl-9 pr-4 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm text-gray-900 dark:text-gray-100 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                    </div>
                    <select name="status" class="px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        <option value="">All Status</option>
                        <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    <button type="submit" class="px-4 py-1.5 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">Filter</button>
                    @if($hasFilters)
                        <a href="{{ route('school-head.observations.index') }}" class="px-3 py-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:text-gray-100 transition-colors">Clear</a>
                    @endif
                </form>
            </div>
        </div>

        <!-- Observation List -->
        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($observations as $observation)
                @php
                    $observee = $observation->observee;
                    $isObserver = $observation->observer_id === Auth::id();
                    $isCoObserver = $observation->school_head_id === Auth::id() && !$isObserver;
                    $statusColors = [
                        'scheduled' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
                        'in_progress' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                        'cot_completed' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
                        'completed' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                        'cancelled' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                    ];
                    $friendlyStatuses = [
                        'scheduled' => 'Scheduled',
                        'in_progress' => 'In Progress',
                        'cot_completed' => 'Ratings Completed',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ];
                    $stageLabels = [
                        'pre_observation_planning' => 'Pre-Observation Planning',
                        'pre_conference' => 'Pre-Conference',
                        'observation' => 'Observation',
                        'post_conference' => 'Post-Conference',
                    ];
                    $epocPending = $observation->schoolHead
                        && !$observation->epocEvaluation
                        && $observation->status !== 'cancelled'
                        && !$observation->isFinalized();
                @endphp
                <a href="{{ route('school-head.observations.show', $observation) }}"
                   class="obs-card block p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-4 min-w-0">
                            <div class="w-11 h-11 rounded-full {{ $isObserver ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400' }} flex items-center justify-center text-sm font-bold shrink-0">
                                {{ $observee?->user?->name ? strtoupper(substr($observee->user->name, 0, 1)) : '?' }}
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <p class="font-semibold text-gray-900 dark:text-gray-100 text-sm">{{ $observee?->user?->name ?? 'Unknown' }}</p>
                                    @if($isObserver)
                                        <span class="text-[10px] font-medium px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">You scheduled</span>
                                    @elseif($isCoObserver)
                                        <span class="text-[10px] font-medium px-1.5 py-0.5 rounded bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300">Assigned School Head</span>
                                    @endif
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium {{ $statusColors[$observation->status] ?? 'bg-gray-100 text-gray-600' }}">
                                        {{ $friendlyStatuses[$observation->status] ?? ucfirst(str_replace('_', ' ', $observation->status)) }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2 mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    @if($observation->subject)
                                        <span>{{ $observation->subject }}</span>
                                        <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                                    @endif
                                    @if($observation->grade_level)
                                        <span>Grade {{ $observation->grade_level }}</span>
                                        <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                                    @endif
                                    <span>{{ $observation->school_year ?? 'N/A' }}</span>
                                    <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                                    <span>{{ $observation->observation_date?->format('M d, Y') ?? 'No date' }}</span>
                                    @if($observation->has_time_schedule)
                                        <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                                        <span>{{ $observation->start_time_label }}@if($observation->end_time_label) - {{ $observation->end_time_label }}@endif</span>
                                    @endif
                                    @if($observation->location)
                                        <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                                        <span>{{ $observation->location }}</span>
                                    @endif
                                </div>
                                @if(isset($stageLabels[$observation->stage]))
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Stage: {{ $stageLabels[$observation->stage] }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            @if($observation->overall_score)
                                <div class="text-right">
                                    <p class="text-lg font-bold text-indigo-600 dark:text-indigo-400">{{ number_format($observation->overall_score, 2) }}</p>
                                    <p class="text-[10px] text-gray-400 uppercase tracking-wider">Score</p>
                                </div>
                            @endif
                            <svg class="w-4 h-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </div>
                @if($epocPending)
                    <div class="mt-3 flex items-center gap-2 rounded-lg border border-amber-200 dark:border-amber-900/50 bg-amber-50 dark:bg-amber-900/20 px-3 py-2">
                        <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-xs text-amber-800 dark:text-amber-300">
                            <span class="font-semibold">Post-Observation Conference</span>
                            (School Head Evaluation) is not yet completed.
                        </p>
                    </div>
                @endif
        </a>
            @empty
                <div class="text-center py-16">
                    <svg class="w-16 h-16 text-gray-300 dark:text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <p class="text-gray-500 dark:text-gray-400 font-medium">No observations found</p>
                    <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Schedule your first teacher observation to get started.</p>
                    <a href="{{ route('school-head.observations.create') }}" class="inline-flex items-center gap-2 mt-4 px-5 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        Schedule Observation
                    </a>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Pagination -->
    @if($observations->hasPages())
        <div class="mt-4">
            {{ $observations->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
