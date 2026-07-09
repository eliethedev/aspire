@extends('layouts.admin')

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
<div class="max-w-7xl mx-auto px-4 sm:px-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Observations</h1>
            <p class="text-gray-500 mt-1">Monitor all classroom observations and evaluations across the system.</p>
        </div>
    </div>

    <div class="grid grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                    <p class="text-xs text-gray-500">Total Observations</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['in_progress'] }}</p>
                    <p class="text-xs text-gray-500">In Progress</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['completed'] }}</p>
                    <p class="text-xs text-gray-500">Completed</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['cancelled'] }}</p>
                    <p class="text-xs text-gray-500">Cancelled</p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 mb-6">
        <form method="GET" action="{{ route('admin.observations.index') }}">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Search</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" name="search" value="{{ request('search') }}"
                               class="w-full pl-9 pr-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                               placeholder="Search by subject, grade level, notes...">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Type</label>
                    <select name="observation_type"
                            class="px-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        <option value="">All Types</option>
                        <option value="teacher_observation" {{ request('observation_type') == 'teacher_observation' ? 'selected' : '' }}>Teacher</option>
                        <option value="school_head_observation" {{ request('observation_type') == 'school_head_observation' ? 'selected' : '' }}>School Head</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Status</label>
                    <select name="status"
                            class="px-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        <option value="">All Statuses</option>
                        <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Stage</label>
                    <select name="stage"
                            class="px-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
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
                @if(request()->anyFilled(['search', 'observation_type', 'status', 'stage']))
                    <a href="{{ route('admin.observations.index') }}"
                       class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 transition-colors">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    @forelse($observations as $observation)
        @php
            $observee = $observation->observee;
            $observeeName = $observee?->user?->name ?? 'Unknown';
            $observeeInitial = strtoupper(substr($observeeName, 0, 1));
            $observer = $observation->observer;
            $observerName = $observer?->user?->name ?? $observer?->name ?? 'Unknown';
            $isTeacher = $observation->observation_type === 'teacher_observation';
            $stageLabel = str_replace('_', ' ', $observation->stage);
            $stageLabel = ucwords($stageLabel);
            $stageLabel = str_replace('Pre Observation Planning', 'Pre-Observation Planning', $stageLabel);
            $stageLabel = str_replace('Post Conference', 'Post-Conference', $stageLabel);
            $stageLabel = str_replace('Pre Conference', 'Pre-Conference', $stageLabel);
        @endphp

        <div class="obs-card bg-white rounded-xl border border-gray-100 shadow-sm p-5 mb-4">
            <div class="flex flex-col sm:flex-row sm:items-start gap-4">
                <div class="flex items-center gap-3 min-w-0 flex-1">
                    <div class="w-11 h-11 rounded-full {{ $isTeacher ? 'bg-indigo-100 text-indigo-700' : 'bg-emerald-100 text-emerald-700' }} flex items-center justify-center text-base font-bold shrink-0">
                        {{ $observeeInitial }}
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="font-semibold text-gray-900 truncate">{{ $observeeName }}</h3>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium {{ $isTeacher ? 'bg-indigo-100 text-indigo-700' : 'bg-emerald-100 text-emerald-700' }}">
                                {{ $isTeacher ? 'Teacher' : 'School Head' }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-500">
                            {{ $observation->subject ?? 'No subject' }}
                            @if($observation->grade_level)
                                &middot; {{ $observation->grade_level }}
                            @endif
                        </p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            Observer: {{ $observerName }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-4 text-sm text-gray-500 shrink-0">
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>{{ $observation->observation_date?->format('M d, Y') ?? 'No date' }}</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span class="capitalize">{{ str_replace('_', ' ', $observation->observation_mode) }}</span>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mt-4 pt-4 border-t border-gray-50">
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
                                <span class="{{ $done ? 'text-indigo-600 font-medium' : ($active ? 'text-gray-900 font-medium' : 'text-gray-400') }} whitespace-nowrap">
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

                <div class="flex items-center gap-3 shrink-0">
                    @if($observation->status === 'cancelled')
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                            Cancelled
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium
                            {{ $observation->status === 'completed' ? 'bg-green-100 text-green-700' : ($observation->status === 'scheduled' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $observation->status === 'completed' ? 'bg-green-500' : ($observation->status === 'scheduled' ? 'bg-amber-500' : 'bg-blue-500') }}"></span>
                            {{ ucwords(str_replace('_', ' ', $observation->status)) }}
                        </span>
                    @endif

                    <a href="{{ route('admin.observations.show', $observation) }}"
                       class="px-3 py-1.5 text-sm font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-lg transition-colors">
                        View Details
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-12 text-center">
            <div class="w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-1">No observations found</h3>
            <p class="text-sm text-gray-500 mb-6">There are no observations matching your criteria.</p>
            <a href="{{ route('admin.observations.index') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors">
                View All Observations
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
