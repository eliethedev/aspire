@extends('layouts.teacher')

@section('title', 'Observation Details')

@push('styles')
<style>
    .stage-card {
        transition: all 0.2s ease;
    }
    .stage-card:hover {
        transform: translateY(-2px);
    }
    .progress-step {
        transition: all 0.2s ease;
    }
    .progress-step:hover .step-circle {
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Observation Details</h1>
                @if($observation->status === 'cancelled')
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-700">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                        Cancelled
                    </span>
                @endif
            </div>
            <p class="text-gray-500 dark:text-gray-400 mt-1">{{ $observation->observee->user->name ?? 'Unknown' }} - {{ $observation->observation_date?->format('M d, Y') ?? 'No date' }}
                @if($observation->start_time_label)
                    @ {{ $observation->start_time_label }}@if($observation->end_time_label) - {{ $observation->end_time_label }}@endif
                @endif
                @if($observation->location) &middot; {{ $observation->location }} @endif
            </p>
            <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">
                {{ $observation->isTeacherObservation() ? 'Teacher Observation' : 'School Head Observation' }}
                @if($observation->isTeacherObservation() && $observation->subject)
                    | {{ $observation->subject }} - {{ $observation->grade_level }}
                @endif
            </p>
        </div>
        <a href="{{ route('school-head.observations.index') }}" 
           class="px-6 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:bg-gray-800 transition-colors">
            Back to List
        </a>
    </div>

    @php
        $stageRoutes = [
            'pre_observation_planning' => 'school-head.observations.preObservationPlanning',
            'pre_conference' => 'school-head.observations.preConference',
            'observation' => 'school-head.observations.observation',
            'post_conference' => 'school-head.observations.postConference',
        ];
        $stageLabels = [
            'pre_observation_planning' => 'Pre-Observation Planning',
            'pre_conference' => 'Pre-Conference',
            'observation' => 'Observation',
            'post_conference' => 'Post-Conference',
        ];
        $stageCompleted = [
            'pre_observation_planning' => (bool) $observation->preObservationPlanning,
            'pre_conference' => (bool) $observation->preConference,
            'observation' => $observation->cotRatings && $observation->cotRatings->count() > 0,
            'post_conference' => (bool) $observation->postConference,
        ];
        $stageKeys = ['pre_observation_planning', 'pre_conference', 'observation', 'post_conference'];
        $currentIdx = array_search($observation->stage, $stageKeys);
    @endphp

    <!-- Clickable Progress Steps -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            @foreach($stageKeys as $i => $key)
                @php
                    $done = $stageCompleted[$key];
                    $active = $i === $currentIdx && !$done;
                    $isCurrentStage = $key === $observation->stage;
                    $canAccess = $done || $isCurrentStage || ($i > 0 && $stageCompleted[$stageKeys[$i - 1]]);
                @endphp

                {{-- connector line --}}
                @if($i > 0)
                    <div class="flex-1 mx-4 h-1 {{ $stageCompleted[$stageKeys[$i - 1]] ? 'bg-green-400' : 'bg-gray-200' }}"></div>
                @endif

                {{-- step --}}
                @if($canAccess)
                    <a href="{{ route($stageRoutes[$key], $observation) }}"
                       class="flex items-center group cursor-pointer">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full {{ $done ? 'bg-green-600 text-white' : ($active ? 'bg-indigo-600 text-white ring-2 ring-indigo-200' : 'bg-gray-200 text-gray-500 dark:text-gray-400') }} font-semibold transition-colors group-hover:shadow-md text-sm">
                            {{ $done ? '✓' : ($i + 1) }}
                        </div>
                        <span class="ml-2 {{ $done ? 'text-gray-600 dark:text-gray-400 font-medium' : ($active ? 'text-indigo-600 dark:text-indigo-400 font-medium' : 'text-gray-400 dark:text-gray-500') }} group-hover:text-indigo-600 dark:text-indigo-400 transition-colors text-sm">{{ $stageLabels[$key] }}</span>
                    </a>
                @else
                    <div class="flex items-center opacity-50">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-200 text-gray-400 dark:text-gray-500 font-semibold text-sm">
                            {{ $i + 1 }}
                        </div>
                        <span class="ml-2 text-gray-400 dark:text-gray-500 text-sm">{{ $stageLabels[$key] }}</span>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    <!-- Stage Navigation Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        @foreach($stageKeys as $i => $key)
            @php
                $done = $stageCompleted[$key];
                $active = $key === $observation->stage;
                $canAccess = $done || $active || ($i > 0 && $stageCompleted[$stageKeys[$i - 1]]);
                $icon = match($key) {
                    'pre_observation_planning' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
                    'pre_conference' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/></svg>',
                    'observation' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>',
                    'post_conference' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                };
                $desc = match($key) {
                    'pre_observation_planning' => 'Lesson plan review & AI insights',
                    'pre_conference' => 'Pre-observation discussion',
                    'observation' => 'Classroom ratings & notes',
                    'post_conference' => 'Feedback & action plan',
                };
            @endphp

            @if($canAccess)
                <a href="{{ route($stageRoutes[$key], $observation) }}"
                   class="bg-white dark:bg-gray-900 rounded-xl border {{ $active ? 'border-indigo-300 ring-2 ring-indigo-100' : 'border-gray-100' }} shadow-sm p-4 hover:shadow-md transition-all group">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-9 h-9 rounded-lg {{ $done ? 'bg-green-100 dark:bg-green-900/30 text-green-700' : ($active ? 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700' : 'bg-gray-50 dark:bg-gray-800 text-dark-400') }} flex items-center justify-center">
                            {!! $icon !!}
                        </div>
                        <span class="text-xs font-semibold {{ $done ? 'text-green-600 dark:text-green-400' : ($active ? 'text-indigo-600 dark:text-indigo-400' : 'text-dark-400') }} uppercase tracking-wide">
                            {{ $done ? 'Completed' : ($active ? 'Current' : 'Available') }}
                        </span>
                    </div>
                    <h4 class="font-semibold text-dark-900 text-sm mb-0.5">{{ $stageLabels[$key] }}</h4>
                    <p class="text-xs text-dark-400">{{ $desc }}</p>
                </a>
            @else
                <div class="bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 opacity-60">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-9 h-9 rounded-lg bg-gray-200 text-gray-400 dark:text-gray-500 flex items-center justify-center">
                            {!! $icon !!}
                        </div>
                        <span class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wide">Locked</span>
                    </div>
                    <h4 class="font-semibold text-gray-500 dark:text-gray-400 text-sm mb-0.5">{{ $stageLabels[$key] }}</h4>
                    <p class="text-xs text-gray-400 dark:text-gray-500">{{ $desc }}</p>
                </div>
            @endif
        @endforeach
    </div>

    <!-- Confirmation Status -->
    @if($observation->confirmation_status === 'confirmed')
    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 mb-6">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                <p class="text-sm text-emerald-800">
                    <strong>Teacher confirmed</strong> this observation on {{ $observation->confirmed_at?->format('M d, Y \a\t h:i A') }}.
                </p>
            </div>
        </div>
    </div>
    @elseif($observation->confirmation_status === 'rejected')
    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 rounded-xl p-4 mb-6">
        <div class="flex gap-3">
            <svg class="w-5 h-5 text-red-600 dark:text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                <p class="text-sm text-red-800 dark:text-red-300">
                    <strong>Teacher rejected</strong> this observation on {{ $observation->rejected_at?->format('M d, Y \a\t h:i A') }}.
                </p>
                @if($observation->rejection_reason)
                <p class="text-sm text-red-700 mt-1">
                    <strong>Reason:</strong> {{ str_replace('_', ' ', ucwords($observation->rejection_reason)) }}
                </p>
                @endif
                @if($observation->rejection_notes)
                <p class="text-sm text-red-700 mt-1">
                    <strong>Notes:</strong> {{ $observation->rejection_notes }}
                </p>
                @endif
                <div class="mt-3">
                    <a href="{{ route('school-head.observations.cancel-form', $observation) }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Cancel & Reschedule
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Stage Details -->
    <div class="space-y-6">
        <!-- Pre-Observation Planning -->
        @if($observation->preObservationPlanning)
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Pre-Observation Planning</h2>
            <div class="space-y-3">
                @if($observation->preObservationPlanning->lesson_plan_file)
                <div>
                    <span class="text-gray-500 dark:text-gray-400 text-sm">Lesson Plan:</span>
                    <a href="{{ asset('storage/' . $observation->preObservationPlanning->lesson_plan_file) }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 ml-2 text-sm font-medium">View File</a>
                </div>
                @endif
                @if($observation->preObservationPlanning->ai_insights)
                <div>
                    <span class="text-gray-500 dark:text-gray-400 text-sm">AI Insights:</span>
                    <p class="text-gray-900 dark:text-gray-100 mt-1">{{ $observation->preObservationPlanning->ai_insights }}</p>
                </div>
                @endif
                @if($observation->preObservationPlanning->suggested_focus)
                <div>
                    <span class="text-gray-500 dark:text-gray-400 text-sm">Suggested Focus:</span>
                    <p class="text-gray-900 dark:text-gray-100 mt-1">{{ $observation->preObservationPlanning->suggested_focus }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Pre-Conference -->
        @if($observation->preConference)
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/></svg>
                </div>
                <div class="flex-1">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Pre-Conference</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Pre-observation discussion between teacher and supervisor</p>
                </div>
                @if($observation->preObservationPlanning?->ai_insights_reviewed)
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-purple-100 dark:bg-purple-900/30 text-purple-700">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                        AI Reviewed
                    </span>
                @endif
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @if($observation->preObservationPlanning?->ai_insights)
                <div class="md:col-span-2 p-4 rounded-xl bg-gradient-to-br from-purple-50 to-indigo-50 border border-purple-100">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-2 h-2 rounded-full bg-purple-500"></div>
                        <span class="text-xs font-semibold text-purple-700 uppercase tracking-wider">AI Pre-Observation Insights</span>
                    </div>
                    <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ is_array($observation->preObservationPlanning->ai_insights) ? (json_encode($observation->preObservationPlanning->ai_insights) ?: '') : $observation->preObservationPlanning->ai_insights }}</p>
                </div>
                @endif
                @if($observation->preConference->conference_date)
                <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Conference Date</span>
                    <p class="text-gray-900 dark:text-gray-100 font-medium mt-1">{{ $observation->preConference->conference_date->format('M d, Y') }}</p>
                </div>
                @endif
                @if($observation->preConference->lesson_plan_review)
                <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Lesson Plan Review</span>
                    <p class="text-gray-900 dark:text-gray-100 mt-1">{{ $observation->preConference->lesson_plan_review }}</p>
                </div>
                @endif
                @if($observation->preConference->discussion_notes)
                <div class="md:col-span-2 p-4 rounded-xl bg-gradient-to-br from-amber-50 to-yellow-50 border border-amber-100">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        <span class="text-sm font-semibold text-amber-800 dark:text-amber-300">Discussion Notes</span>
                    </div>
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $observation->preConference->discussion_notes }}</p>
                </div>
                @endif
                @if($observation->preConference->finalized_focus)
                <div class="md:col-span-2 p-4 rounded-xl bg-gradient-to-br from-blue-50 to-cyan-50 border border-blue-100">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        <span class="text-sm font-semibold text-blue-800">Finalized Focus</span>
                    </div>
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $observation->preConference->finalized_focus }}</p>
                </div>
                @endif
                @if($observation->preConference->teacher_reflection)
                <div class="md:col-span-2 p-4 rounded-xl bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-100">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span class="text-sm font-semibold text-emerald-800">Teacher Reflection</span>
                    </div>
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $observation->preConference->teacher_reflection }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Observation (COT Ratings) -->
        @if($observation->cotRatings && $observation->cotRatings->count() > 0)
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Observation Ratings</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">COT-based performance assessment</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider font-medium">Overall Score</p>
                    <div class="flex items-end gap-1">
                        <p class="text-gray-900 dark:text-gray-100 font-bold text-3xl tracking-tight">{{ number_format($observation->overall_score, 1) }}</p>
                        <p class="text-gray-400 dark:text-gray-500 font-medium text-lg mb-0.5">/ 6</p>
                    </div>
                    @php
                        $scorePct = ($observation->overall_score / 6) * 100;
                        $scoreBg = $scorePct >= 80 ? 'bg-emerald-500' : ($scorePct >= 60 ? 'bg-amber-500' : 'bg-red-500');
                    @endphp
                    <div class="w-24 h-1.5 bg-gray-100 dark:bg-gray-800 rounded-full mt-1 ml-auto">
                        <div class="{{ $scoreBg }} h-1.5 rounded-full" style="width: {{ $scorePct }}%"></div>
                    </div>
                </div>
            </div>
            <div class="space-y-2.5">
                @foreach($observation->cotRatings as $rating)
                    @php
                        $r = $rating->not_observed ? null : $rating->rating;
                        $rPct = $r ? ($r / 6) * 100 : 0;
                        $rColor = !$r ? 'bg-gray-100 dark:bg-gray-800 border-gray-200 dark:border-gray-700' : ($r >= 5 ? 'border-emerald-200 bg-emerald-50' : ($r >= 4 ? 'border-blue-200 bg-blue-50' : ($r >= 3 ? 'border-amber-200 bg-amber-50 dark:bg-amber-900/20' : 'border-red-200 bg-red-50 dark:bg-red-900/20')));
                        $rBadge = !$r ? 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400' : ($r >= 5 ? 'bg-emerald-100 text-emerald-700' : ($r >= 4 ? 'bg-blue-100 text-blue-700' : ($r >= 3 ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700' : 'bg-red-100 dark:bg-red-900/30 text-red-700')));
                    @endphp
                    <div class="rounded-xl p-4 border {{ $rColor }}">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $rating->indicator }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $rating->domain }}</p>
                                @if($rating->comments)
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2 pt-2 border-t border-gray-200/60">{{ $rating->comments }}</p>
                                @endif
                            </div>
                            <div class="text-center shrink-0">
                                <div class="w-14 h-14 rounded-xl {{ $rBadge }} flex items-center justify-center">
                                    <span class="text-lg font-bold">{{ $r ? number_format($r, 1) : 'NO' }}</span>
                                </div>
                                <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-0.5">/ 6</p>
                            </div>
                        </div>
                        @if($r)
                        <div class="mt-2 w-full h-1 bg-gray-100 dark:bg-gray-800 rounded-full">
                            <div class="h-1 rounded-full {{ $r >= 5 ? 'bg-emerald-500' : ($r >= 4 ? 'bg-blue-500' : ($r >= 3 ? 'bg-amber-500' : 'bg-red-500')) }}" style="width: {{ $rPct }}%"></div>
                        </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Post-Conference -->
        @if($observation->postConference)
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Post-Conference</h2>
            <div class="space-y-3">
                @if($observation->postConference->conference_date)
                <div>
                    <span class="text-gray-500 dark:text-gray-400 text-sm">Conference Date:</span>
                    <p class="text-gray-900 dark:text-gray-100">{{ $observation->postConference->conference_date->format('M d, Y') }}</p>
                </div>
                @endif
                @if($observation->postConference->ai_comparison)
                <div>
                    <span class="text-gray-500 dark:text-gray-400 text-sm">AI Comparison (Plan vs Actual):</span>
                    <p class="text-gray-900 dark:text-gray-100 mt-1">{{ $observation->postConference->ai_comparison }}</p>
                </div>
                @endif
                @if($observation->postConference->feedback)
                <div>
                    <span class="text-gray-500 dark:text-gray-400 text-sm">Feedback:</span>
                    <p class="text-gray-900 dark:text-gray-100 mt-1">{{ $observation->postConference->feedback }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    <!-- Cancellation Info -->
    @if($observation->status === 'cancelled')
    <div class="mt-8 bg-red-50 dark:bg-red-900/20 border border-red-200 rounded-xl p-6">
        <div class="flex gap-3">
            <svg class="w-5 h-5 text-red-600 dark:text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M4.293 4.293a1 1 0 011.414 0L12 10.586l6.293-6.293a1 1 0 111.414 1.414L13.414 12l6.293 6.293a1 1 0 01-1.414 1.414L12 13.414l-6.293 6.293a1 1 0 01-1.414-1.414L10.586 12 4.293 5.707a1 1 0 010-1.414z"/></svg>
            <div>
                <h3 class="font-semibold text-red-800 dark:text-red-300">Observation Cancelled</h3>
                <p class="text-sm text-red-700 mt-1">
                    Cancelled on {{ $observation->cancelled_at?->format('M d, Y \a\t h:i A') }} by {{ $observation->cancelledBy?->name ?? 'Unknown' }}
                </p>
                <p class="text-sm text-red-700 mt-1">
                    <strong>Reason:</strong> {{ ucwords(str_replace('_', ' ', $observation->cancellation_reason)) }}
                </p>
            </div>
        </div>
    </div>
    @endif

    <!-- Feedback & Coaching Actions -->
    <div class="mt-8 mb-4 flex justify-center gap-4">
        <a href="{{ route('supervisor.feedback.index', $observation) }}"
           class="inline-flex items-center gap-3 px-8 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-xl font-semibold shadow-lg shadow-purple-600/20 transition-all hover:shadow-xl hover:shadow-purple-600/30">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/></svg>
            Feedback Management
        </a>
        @if($observation->postConference)
        <a href="{{ route('supervisor.coaching.create', $observation) }}"
           class="inline-flex items-center gap-3 px-8 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-semibold shadow-lg shadow-emerald-600/20 transition-all hover:shadow-xl hover:shadow-emerald-600/30">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Create Coaching Agreement
        </a>
        @endif
    </div>

    <!-- Actions -->
    @if($observation->status !== 'cancelled' && $observation->status !== 'completed')
    <div class="flex justify-center gap-4">
        @php
            $continueLabel = match($observation->stage) {
                'pre_observation_planning' => 'Continue to Pre-Observation Planning',
                'pre_conference' => 'Continue to Pre-Conference',
                'observation' => 'Continue to Observation',
                'post_conference' => 'Continue to Post-Conference',
                default => null,
            };
            $continueRoute = match($observation->stage) {
                'pre_observation_planning' => 'school-head.observations.preObservationPlanning',
                'pre_conference' => 'school-head.observations.preConference',
                'observation' => 'school-head.observations.observation',
                'post_conference' => 'school-head.observations.postConference',
                default => null,
            };
        @endphp
        @if($continueRoute)
            <a href="{{ route($continueRoute, $observation) }}"
               class="inline-flex items-center gap-3 px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-semibold shadow-lg shadow-indigo-600/20 transition-all hover:shadow-xl hover:shadow-indigo-600/30">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                {{ $continueLabel }}
            </a>
        @endif

        @if($observation->canCancel())
            <a href="{{ route('school-head.observations.cancel-form', $observation) }}"
               class="inline-flex items-center gap-3 px-8 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl font-semibold shadow-lg shadow-red-600/20 transition-all hover:shadow-xl hover:shadow-red-600/30">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                Cancel Observation
            </a>
        @endif
    </div>
    @endif

    <!-- Download Report (only when completed) -->
    @if($observation->status === 'completed')
    <div class="mt-6 flex flex-wrap justify-center gap-3">
        <a href="{{ route('school-head.observations.report-pdf', $observation) }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium text-sm shadow-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Download PDF Report
        </a>
        <a href="{{ route('school-head.observations.report', $observation) }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg font-medium text-sm shadow-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Download Markdown Report
        </a>
        <a href="{{ route('school-head.observations.indicator-trends', $observation) }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium text-sm shadow-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            Indicator Trends
        </a>
        <a href="{{ route('school-head.observations.progress-comparison', $observation) }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium text-sm shadow-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
            Progress Comparison
        </a>
        <a href="{{ route('school-head.observations.pd-recommendations', $observation) }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-medium text-sm shadow-sm transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            PD Recommendations
        </a>
    </div>
    @endif

    <!-- Observation History -->
    @if($observation->observee)
    <div class="mt-6 text-center">
        <a href="{{ route('school-head.observations.teacher-history', $observation->observee_id) }}?type={{ $observation->observee_type }}"
           class="inline-flex items-center gap-2 text-sm text-indigo-400 hover:text-indigo-300 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            View all observations for {{ $observation->observee->user?->name ?? 'this teacher' }}
        </a>
    </div>
    @endif
</div>
@endsection
