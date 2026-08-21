@extends('layouts.supervisor')

@section('title', 'My Evaluations')

@push('styles')
<style>
    /* Compact minimized cards for efficient browsing */
    .senior-card {
        transition: box-shadow 0.15s ease;
        border-width: 1px;
    }
    .senior-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }
    .stage-circle {
        width: 22px;
        height: 22px;
        border-radius: 9999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 700;
        flex-shrink: 0;
    }
    .stage-connector {
        height: 2px;
        border-radius: 9999px;
        flex: 1;
        min-width: 8px;
    }
    .obs-card { min-height: 172px; }
    #main-content { padding-top: 0.75rem !important; }
</style>
@endpush

@section('content')
@php $hasFilters = request()->anyFilled(['search', 'observation_type', 'status', 'stage', 'date_from', 'date_to']); @endphp
<div class="max-w-7xl mx-auto">
    {{-- Compact header — minimized --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2">
        <div class="min-w-0">
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100 leading-none">My Evaluations</h1>
            <p class="mt-0.5 text-md text-gray-500 dark:text-gray-400 leading-none">Track and manage observations.</p>
        </div>
        <a href="{{ route('supervisor.observations.create') }}"
           class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-md font-semibold shadow-sm shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            New Evaluation
        </a>
    </div>

    <!-- Stats — minimized -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 mb-2" role="list" aria-label="Evaluation summary">
        <div class="senior-card bg-white dark:bg-gray-900 rounded-lg border-gray-200 dark:border-gray-700 p-2.5 flex items-center gap-2.5">
            <div class="w-10 h-10 rounded-md bg-indigo-600 flex items-center justify-center shrink-0" aria-hidden="true">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <div class="min-w-0">
                <p class="text-2xl font-bold leading-none text-gray-900 dark:text-gray-100">{{ $stats['total'] }}</p>
                <p class="text-[15px] font-medium text-gray-600 dark:text-gray-400 leading-none mt-0.5">Total</p>
            </div>
        </div>
        <div class="senior-card bg-white dark:bg-gray-900 rounded-lg border-amber-200 dark:border-amber-900/40 p-2.5 flex items-center gap-2.5">
            <div class="w-10 h-10 rounded-md bg-amber-500 flex items-center justify-center shrink-0" aria-hidden="true">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="min-w-0">
                <p class="text-2xl font-bold leading-none text-gray-900 dark:text-gray-100">{{ $stats['in_progress'] }}</p>
                <p class="text-[15px] font-medium text-amber-700 dark:text-amber-300 leading-none mt-0.5">In Progress</p>
            </div>
        </div>
        <div class="senior-card bg-white dark:bg-gray-900 rounded-lg border-emerald-200 dark:border-emerald-900/40 p-2.5 flex items-center gap-2.5">
            <div class="w-10 h-10 rounded-md bg-emerald-600 flex items-center justify-center shrink-0" aria-hidden="true">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="min-w-0">
                <p class="text-2xl font-bold leading-none text-gray-900 dark:text-gray-100">{{ $stats['completed'] }}</p>
                <p class="text-[15px] font-medium text-emerald-700 dark:text-emerald-300 leading-none mt-0.5">Completed</p>
            </div>
        </div>
        <div class="senior-card bg-white dark:bg-gray-900 rounded-lg border-gray-200 dark:border-gray-700 p-2.5 flex items-center gap-2.5">
            <div class="w-10 h-10 rounded-md bg-gray-600 flex items-center justify-center shrink-0" aria-hidden="true">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </div>
            <div class="min-w-0">
                <p class="text-2xl font-bold leading-none text-gray-900 dark:text-gray-100">{{ $stats['cancelled'] }}</p>
                <p class="text-[15px] font-medium text-gray-500 dark:text-gray-400 leading-none mt-0.5">Cancelled</p>
            </div>
        </div>
    </div>

    <!-- Search & Filters — compact -->
    <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 mb-2 overflow-hidden" x-data="{ open: @json($hasFilters) }">
        <button type="button" @click="open = !open"
                class="w-full flex items-center justify-between gap-2 px-2.5 py-1.5 text-left hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
                :aria-expanded="open.toString()">
            <span class="flex items-center gap-1.5 text-md font-semibold text-gray-700 dark:text-gray-200">
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                Filters
                @if($hasFilters)
                    <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700">Active</span>
                @endif
            </span>
            <svg class="w-3 h-3 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div x-show="open" x-transition>
            <form method="GET" action="{{ route('supervisor.observations.index') }}" class="px-2.5 py-2 border-t border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/20">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-1.5">
                    <div class="md:col-span-5 relative">
                        <svg class="absolute left-2 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" name="search" value="{{ request('search') }}"
                               class="w-full pl-7 pr-2 py-1.5 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-xs text-gray-900 dark:text-gray-100 placeholder:text-gray-400 focus:ring-1 focus:ring-indigo-500 outline-none"
                               placeholder="Name, subject...">
                    </div>
                    <div class="md:col-span-2">
                        <select name="observation_type" class="w-full px-2 py-1.5 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-xs outline-none">
                            <option value="">All Types</option>
                            <option value="teacher_observation" {{ request('observation_type') == 'teacher_observation' ? 'selected' : '' }}>Teacher</option>
                            <option value="school_head_observation" {{ request('observation_type') == 'school_head_observation' ? 'selected' : '' }}>School Head</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <select name="status" class="w-full px-2 py-1.5 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-xs outline-none">
                            <option value="">All Status</option>
                            <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="md:col-span-3">
                        <select name="stage" class="w-full px-2 py-1.5 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-xs outline-none">
                            <option value="">All Stages</option>
                            <option value="pre_observation_planning" {{ request('stage') == 'pre_observation_planning' ? 'selected' : '' }}>Planning</option>
                            <option value="pre_conference" {{ request('stage') == 'pre_conference' ? 'selected' : '' }}>Pre-Conf</option>
                            <option value="observation" {{ request('stage') == 'observation' ? 'selected' : '' }}>Observation</option>
                            <option value="post_conference" {{ request('stage') == 'post_conference' ? 'selected' : '' }}>Post-Conf</option>
                        </select>
                    </div>
                    <div class="md:col-span-3">
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full px-2 py-1.5 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-xs outline-none">
                    </div>
                    <div class="md:col-span-3">
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full px-2 py-1.5 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-xs outline-none">
                    </div>
                    <div class="md:col-span-6 flex items-center gap-1.5 pt-1">
                        <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-md text-xs font-semibold">Filter</button>
                        @if($hasFilters)
                            <a href="{{ route('supervisor.observations.index') }}" class="px-2.5 py-1.5 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-md text-xs">Clear</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Legend — compact -->
    <div class="mb-2 flex flex-wrap gap-1 text-[12px] dark:text-white" aria-label="Stage legend">
        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full bg-white dark:bg-gray-900 border border-gray-200 font-medium"><span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span> Planning</span>
        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full bg-white dark:bg-gray-900 border border-gray-200 font-medium"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Pre-Conf</span>
        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full bg-white dark:bg-gray-900 border border-gray-200 font-medium"><span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span> Observation</span>
        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full bg-white dark:bg-gray-900 border border-gray-200 font-medium"><span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Post-Conf</span>
    </div>

    <!-- Evaluations List — compact 2-col -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5" role="list" aria-label="Evaluations">
    @forelse($observations as $observation)
        @php
            $observee = $observation->observee;
            $observeeName = $observee?->user?->name ?? 'Unknown';
            $observeeInitial = strtoupper(substr($observeeName, 0, 1));
            $isTeacher = $observation->observation_type === 'teacher_observation';
            $epocPending = $observation->schoolHead
                && !$observation->epocEvaluation
                && $observation->status !== 'cancelled'
                && !$observation->isFinalized();
            $statusConfig = match($observation->status) {
                'completed' => ['label' => 'Completed', 'bg' => 'bg-emerald-100 text-emerald-700 border-emerald-200'],
                'scheduled' => ['label' => 'Scheduled', 'bg' => 'bg-amber-100 text-amber-700 border-amber-200'],
                'cancelled' => ['label' => 'Cancelled', 'bg' => 'bg-red-100 text-red-700 border-red-200'],
                default => ['label' => ucwords(str_replace('_',' ', $observation->status)), 'bg' => 'bg-blue-100 text-blue-700 border-blue-200'],
            };
            $accent = $observation->status === 'cancelled' ? 'border-l-red-500' : ($observation->status === 'completed' ? 'border-l-emerald-500' : 'border-l-indigo-500');
            $stages = ['pre_observation_planning' => 'Planning','pre_conference' => 'Pre-Conf','observation' => 'Observe','post_conference' => 'Post-Conf'];
            $stageKeys = array_keys($stages);
            $currentIdx = array_search($observation->stage, $stageKeys);
            if($currentIdx===false) $currentIdx=0;
            $stagesForLabel = $stageKeys;
            $idxLabel = array_search($observation->stage, $stagesForLabel);
            $stepNum = $idxLabel!==false ? $idxLabel+1 : 1;
        @endphp

        <article class="senior-card obs-card bg-white dark:bg-gray-900 rounded-lg border-gray-200 dark:border-gray-700 overflow-hidden border-l-[3px] {{ $accent }} flex flex-col" role="listitem" aria-label="Evaluation for {{ $observeeName }}">
            <div class="p-3 flex flex-col gap-2 flex-1">
                {{-- header: avatar + name + badges + date --}}
                <div class="flex items-start justify-between gap-2">
                    <div class="flex gap-2.5 min-w-0 flex-1">
                        <div class="w-8 h-8 rounded-md {{ $isTeacher ? 'bg-indigo-600 text-white' : 'bg-emerald-600 text-white' }} flex items-center justify-center text-sm font-bold shrink-0" aria-hidden="true">{{ $observeeInitial }}</div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-1">
                                <h2 class="text-sm font-semibold leading-tight text-gray-900 dark:text-gray-100 truncate">{{ $observeeName }}</h2>
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium border {{ $isTeacher ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200' }}">{{ $isTeacher ? 'Teacher' : 'S.Head' }}</span>
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium border {{ $statusConfig['bg'] }}">{{ $statusConfig['label'] }}</span>
                            </div>
                            <p class="text-xs text-gray-600 dark:text-gray-400 truncate leading-tight mt-0.5">
                                {{ Str::limit($observation->subject ?? 'No subject', 28) }}
                                @if($observation->grade_level) <span class="mx-0.5">•</span> {{ $observation->grade_level }} @endif
                                <span class="mx-0.5">•</span> {{ ucwords(str_replace('_',' ',$observation->observation_mode)) }}
                            </p>
                            @if($observation->schoolHead)
                                <p class="text-xs text-purple-600 dark:text-purple-300 truncate leading-none mt-0.5">SH: {{ $observation->schoolHead->name }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <div class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-gray-900 dark:bg-white text-white dark:text-gray-900 text-xs font-semibold leading-none">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            {{ $observation->observation_date?->format('M d, Y') ?? 'No date' }}
                        </div>
                        @if($observation->has_time_schedule)
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 leading-none">{{ $observation->start_time_label }}@if($observation->end_time_label)-{{ $observation->end_time_label }}@endif</p>
                        @endif
                    </div>
                </div>

                @if($epocPending)
                <div class="flex items-center gap-2 rounded-md border border-amber-300 bg-amber-50 dark:bg-amber-900/20 px-2.5 py-1.5">
                    <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 3l7 4v5c0 5-3.5 7.5-7 8-3.5-.5-7-3-7-8V7l7-4z"/></svg>
                    <p class="text-xs font-medium text-amber-800 dark:text-amber-300 truncate flex-1">Enhanced Post Observation Conference
                         pending — evaluation </p>
                    <a href="{{ route('supervisor.observations.epoc', $observation) }}" class="inline-flex items-center gap-1 px-3 py-1 bg-amber-600 hover:bg-amber-700 text-white rounded-md text-xs font-semibold shadow-sm shrink-0">Complete now<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></a>
                </div>
                @endif

                {{-- Progress compact --}}
                <div class="pt-2 border-t border-gray-100 dark:border-gray-800">
                    <div class="flex items-center gap-1" role="progressbar" aria-valuenow="{{ $stepNum }}" aria-valuemin="1" aria-valuemax="4">
                        @foreach($stageKeys as $i => $key)
                            @php $done=$i < $currentIdx || ($i===$currentIdx && $observation->status==='completed'); $active=$i===$currentIdx && $observation->status!=='completed' && $observation->status!=='cancelled'; @endphp
                            <div class="flex items-center gap-0.5 flex-1">
                                <div class="flex flex-col items-center gap-1 min-w-0 flex-1">
                                    <div class="stage-circle border
                                        @if($done) bg-emerald-600 border-emerald-600 text-white
                                        @elseif($active) bg-indigo-600 border-indigo-600 text-white
                                        @elseif($observation->status==='cancelled') bg-gray-200 border-gray-300 text-gray-500
                                        @else bg-white dark:bg-gray-800 border-gray-300 text-gray-400
                                        @endif
                                    ">
                                        @if($done)<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>@else{{ $i+1 }}@endif
                                    </div>
                                    <span class="text-xs font-medium leading-none text-center truncate w-full @if($done) text-emerald-600 @elseif($active) text-indigo-600 @else text-gray-400 @endif">{{ $stages[$key] }}</span>
                                </div>
                                @if($i < count($stageKeys)-1)
                                    <div class="stage-connector -mt-3.5 {{ $i < $currentIdx || $done ? 'bg-emerald-500' : ($active ? 'bg-indigo-300' : 'bg-gray-200') }}"></div>
                                @endif
                            </div>
                        @endforeach
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400 ml-1 shrink-0">{{ $stepNum }}/4</span>
                    </div>
                </div>

                {{-- Actions — pushed to bottom for equal height --}}
                <div class="flex gap-1.5 mt-auto pt-1">
                    <a href="{{ route('supervisor.observations.show', $observation) }}" class="flex-1 inline-flex items-center justify-center gap-1 px-2.5 py-1.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 rounded-md text-sm font-medium hover:bg-gray-50 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        View
                    </a>
                    @php $continueRoute = match($observation->stage) { 'pre_observation_planning'=>'supervisor.observations.preObservationPlanning','pre_conference'=>'supervisor.observations.preConference','observation'=>'supervisor.observations.observation','post_conference'=>$observation->status!=='completed'?'supervisor.observations.postConference':null, default=>null }; @endphp
                    @if($continueRoute && $observation->status!=='cancelled' && $observation->status!=='completed')
                        <a href="{{ route($continueRoute, $observation) }}" class="flex-1 inline-flex items-center justify-center gap-1 px-2.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-sm font-medium">Continue <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg></a>
                    @elseif($observation->status==='completed')
                        <span class="flex-1 inline-flex items-center justify-center gap-1 px-2.5 py-1.5 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 text-emerald-700 rounded-md text-sm font-medium">Completed</span>
                    @endif
                    @if($observation->canCancel())
                        <a href="{{ route('supervisor.observations.cancel-form', $observation) }}" class="px-2.5 py-1.5 bg-white border border-red-200 text-red-600 rounded-md text-sm font-medium hover:bg-red-50">Cancel</a>
                    @endif
                </div>
            </div>
        </article>
    @empty
        <div class="col-span-full senior-card bg-white dark:bg-gray-900 rounded-lg border-gray-200 dark:border-gray-700 p-6 text-center">
            <div class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center mx-auto mb-2">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            @if($hasFilters)
                <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">No results for filters</h3>
                <p class="mt-1 text-xs text-gray-500">Try clearing filters.</p>
                <a href="{{ route('supervisor.observations.index') }}" class="mt-3 inline-flex px-3 py-1.5 bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-md text-xs font-semibold">Clear Filters</a>
            @else
                <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">No evaluations yet</h3>
                <p class="mt-1 text-xs text-gray-500">Schedule your first evaluation.</p>
                <a href="{{ route('supervisor.observations.create') }}" class="mt-3 inline-flex gap-1 px-3 py-1.5 bg-indigo-600 text-white rounded-md text-xs font-semibold">Create Evaluation</a>
            @endif
        </div>
    @endforelse
    </div>

    @if($observations->hasPages())
        <div class="mt-3 bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-2">
            {{ $observations->links() }}
        </div>
    @endif
</div>
@endsection
