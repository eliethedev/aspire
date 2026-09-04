@extends('layouts.admin')

@section('title', 'Observation Details')

@push('styles')
<style>
    .stage-card {
        transition: all 0.2s ease;
    }
    .stage-card:hover {
        transform: translateY(-2px);
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-6">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Observation Details</h1>
                @if($observation->status === 'cancelled')
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-50 dark:bg-red-900/200"></span>
                        Cancelled
                    </span>
                @endif
            </div>
            <p class="text-gray-500 dark:text-gray-400 dark:text-gray-500 mt-1">{{ $observation->observee->user->name ?? 'Unknown' }} - {{ $observation->observation_date?->format('M d, Y') ?? 'No date' }}</p>
            <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">
                {{ $observation->isTeacherObservation() ? 'Teacher Observation' : 'School Head Observation' }}
                @if($observation->isTeacherObservation() && $observation->subject)
                    | {{ $observation->subject }} - {{ $observation->grade_level }}
                @endif
            </p>
        </div>
        <div class="flex items-center gap-3">
            @if($observation->status === 'completed')
            <a href="{{ route('admin.observations.cot-document', $observation) }}"
               class="inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium shadow-sm transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                Download COT Document
            </a>
            @endif
            @if($observation->isSchoolHeadObservation() && $observation->epocEvaluation)
            <a href="{{ route('admin.observations.epoc-document', $observation) }}"
               class="inline-flex items-center gap-2 px-5 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium shadow-sm transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                Download EPOC Document
            </a>
            @endif
            <a href="{{ route('admin.observations.index') }}"
               class="px-6 py-2 rounded-lg border border-gray-300 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:bg-gray-800 transition-colors">
                Back to List
            </a>
        </div>
    </div>

    @php
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

    {{-- Progress Steps (static, read-only) --}}
    <div class="mb-8">
        <div class="flex items-center justify-between">
            @foreach($stageKeys as $i => $key)
                @php
                    $done = $stageCompleted[$key];
                    $active = $i === $currentIdx && !$done;
                @endphp

                @if($i > 0)
                    <div class="flex-1 mx-4 h-1 {{ $stageCompleted[$stageKeys[$i - 1]] ? 'bg-green-400' : 'bg-gray-200' }}"></div>
                @endif

                <div class="flex items-center {{ $active ? 'cursor-default' : '' }}">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full {{ $done ? 'bg-green-600 text-white' : ($active ? 'bg-indigo-600 text-white ring-2 ring-indigo-200' : 'bg-gray-200 text-gray-500 dark:text-gray-400 dark:text-gray-500') }} font-semibold text-sm">
                        {{ $done ? 'âœ“' : ($i + 1) }}
                    </div>
                    <span class="ml-2 {{ $done ? 'text-gray-600 dark:text-gray-400 dark:text-gray-500 font-medium' : ($active ? 'text-indigo-600 dark:text-indigo-400 font-medium' : 'text-gray-400 dark:text-gray-500') }} text-sm">{{ $stageLabels[$key] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Stage Navigation Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        @foreach($stageKeys as $i => $key)
            @php
                $done = $stageCompleted[$key];
                $active = $key === $observation->stage;
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

            <div class="bg-white dark:bg-gray-900 rounded-xl border {{ $active ? 'border-indigo-300 ring-2 ring-indigo-100' : 'border-gray-100 dark:border-gray-700' }} shadow-sm p-4 stage-card">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-9 h-9 rounded-lg {{ $done ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : ($active ? 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300' : 'bg-gray-50 dark:bg-gray-800 text-gray-400 dark:text-gray-500') }} flex items-center justify-center">
                        {!! $icon !!}
                    </div>
                    <span class="text-xs font-semibold {{ $done ? 'text-green-600 dark:text-green-400' : ($active ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400 dark:text-gray-500') }} uppercase tracking-wide">
                        {{ $done ? 'Completed' : ($active ? 'Current' : ($i < $currentIdx ? 'Available' : 'Locked')) }}
                    </span>
                </div>
                <h4 class="font-semibold text-gray-900 dark:text-gray-100 text-sm mb-0.5">{{ $stageLabels[$key] }}</h4>
                <p class="text-xs text-gray-400 dark:text-gray-500">{{ $desc }}</p>
            </div>
        @endforeach
    </div>

    {{-- Observer Info --}}
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400 dark:text-gray-500">Observer</p>
                <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $observation->observer?->user?->name ?? $observation->observer?->name ?? 'Unknown' }}</p>
            </div>
        </div>
    </div>

    {{-- Confirmation Status --}}
    @if($observation->confirmation_status === 'confirmed')
    <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl p-4 mb-6">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                <p class="text-sm text-emerald-800 dark:text-emerald-300">
                    <strong>Teacher confirmed</strong> this observation on {{ $observation->confirmed_at?->format('M d, Y \a\t h:i A') }}.
                </p>
            </div>
        </div>
    </div>
    @elseif($observation->confirmation_status === 'rejected')
    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4 mb-6">
        <div class="flex gap-3">
            <svg class="w-5 h-5 text-red-600 dark:text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                <p class="text-sm text-red-800">
                    <strong>Teacher rejected</strong> this observation on {{ $observation->rejected_at?->format('M d, Y \a\t h:i A') }}.
                </p>
                @if($observation->rejection_reason)
                <p class="text-sm text-red-700 dark:text-red-400 mt-1">
                    <strong>Reason:</strong> {{ str_replace('_', ' ', ucwords($observation->rejection_reason)) }}
                </p>
                @endif
                @if($observation->rejection_notes)
                <p class="text-sm text-red-700 dark:text-red-400 mt-1">
                    <strong>Notes:</strong> {{ $observation->rejection_notes }}
                </p>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- Stage Details --}}
    <div class="space-y-6">
        @if($observation->preObservationPlanning)
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Pre-Observation Planning</h2>
            <div class="space-y-3">
                @if($observation->preObservationPlanning->lesson_plan_file)
                <div>
                    <span class="text-gray-500 dark:text-gray-400 dark:text-gray-500 text-sm">Lesson Plan:</span>
                    <a href="{{ asset('storage/' . $observation->preObservationPlanning->lesson_plan_file) }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:text-indigo-300 ml-2 text-sm font-medium">View File</a>
                </div>
                @endif
                @if($observation->preObservationPlanning->ai_insights)
                <div>
                    <span class="text-gray-500 dark:text-gray-400 dark:text-gray-500 text-sm">AI Insights:</span>
                    <div class="mt-2">
                        @php $insightSections = $observation->preObservationPlanning->insightsSections(); @endphp
                        @if(isset($insightSections['raw']))
                            <p class="text-gray-900 dark:text-gray-100 mt-1 text-sm whitespace-pre-wrap">{{ $insightSections['raw'] }}</p>
                        @else
                            {!! view('partials.ai-insights-display', ['sections' => $insightSections])->render() !!}
                        @endif
                    </div>
                </div>
                @endif
                @if($observation->preObservationPlanning->suggested_focus)
                <div>
                    <span class="text-gray-500 dark:text-gray-400 dark:text-gray-500 text-sm">Suggested Focus:</span>
                    <p class="text-gray-900 dark:text-gray-100 mt-1">{{ $observation->preObservationPlanning->suggested_focus }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif

        @if($observation->preConference)
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Pre-Conference</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @if($observation->preConference->conference_date)
                <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Conference Date</span>
                    <p class="text-gray-900 dark:text-gray-100 font-medium mt-1">{{ $observation->preConference->conference_date->format('M d, Y') }}</p>
                </div>
                @endif
                @if($observation->preConference->topic)
                <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Topic</span>
                    <p class="text-gray-900 dark:text-gray-100 font-medium mt-1">{{ $observation->preConference->topic }}</p>
                </div>
                @endif
                @if($observation->preConference->learning_objectives)
                <div class="md:col-span-2 p-4 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Learning Objectives</span>
                    <p class="text-sm text-gray-700 dark:text-gray-300 mt-1 whitespace-pre-wrap">{{ $observation->preConference->learning_objectives }}</p>
                </div>
                @endif
                @if($observation->preConference->teaching_strategies)
                <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Teaching Strategies</span>
                    <p class="text-sm text-gray-700 dark:text-gray-300 mt-1 whitespace-pre-wrap">{{ $observation->preConference->teaching_strategies }}</p>
                </div>
                @endif
                @if($observation->preConference->assessment_activity)
                <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Assessment/Activity</span>
                    <p class="text-sm text-gray-700 dark:text-gray-300 mt-1 whitespace-pre-wrap">{{ $observation->preConference->assessment_activity }}</p>
                </div>
                @endif
                @if($observation->preConference->discussion_notes)
                <div class="md:col-span-2 p-4 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800">
                    <span class="text-xs font-semibold text-amber-700 dark:text-amber-400 uppercase tracking-wider">Discussion Notes</span>
                    <p class="text-gray-900 dark:text-gray-100 mt-1">{{ $observation->preConference->discussion_notes }}</p>
                </div>
                @endif
                @if($observation->preConference->finalized_focus)
                <div class="md:col-span-2 p-4 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800">
                    <span class="text-xs font-semibold text-blue-700 dark:text-blue-400 uppercase tracking-wider">Finalized Focus</span>
                    <p class="text-gray-900 dark:text-gray-100 mt-1">{{ $observation->preConference->finalized_focus }}</p>
                </div>
                @endif
                @if($observation->preConference->expected_challenges)
                <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Expected Challenges</span>
                    <p class="text-sm text-gray-700 dark:text-gray-300 mt-1 whitespace-pre-wrap">{{ $observation->preConference->expected_challenges }}</p>
                </div>
                @endif
                @if($observation->preConference->feedback_areas)
                <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Feedback Areas</span>
                    <p class="text-sm text-gray-700 dark:text-gray-300 mt-1 whitespace-pre-wrap">{{ $observation->preConference->feedback_areas }}</p>
                </div>
                @endif
                @if($observation->preConference->teacher_reflection)
                <div class="md:col-span-2 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800">
                    <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">Teacher Reflection</span>
                    <p class="text-gray-900 dark:text-gray-100 mt-1">{{ $observation->preConference->teacher_reflection }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif

        @if($observation->cotRatings && $observation->cotRatings->count() > 0)
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Observation Ratings</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500">COT-based performance assessment</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider font-medium">Overall Score</p>
                    <div class="flex items-end gap-1">
                        <p class="text-gray-900 dark:text-gray-100 font-bold text-3xl tracking-tight">{{ number_format($observation->overall_score, 1) }}</p>
                        <p class="text-gray-400 dark:text-gray-500 font-medium text-lg mb-0.5">/ 6</p>
                    </div>
                    @php
                        $scorePct = $observation->overall_score ? ($observation->overall_score / 6) * 100 : 0;
                        $scoreBg = $scorePct >= 80 ? 'bg-emerald-50 dark:bg-emerald-900/200' : ($scorePct >= 60 ? 'bg-amber-50 dark:bg-amber-900/200' : 'bg-red-50 dark:bg-red-900/200');
                    @endphp
                    <div class="w-24 h-1.5 bg-gray-100 rounded-full mt-1 ml-auto">
                        <div class="{{ $scoreBg }} h-1.5 rounded-full" style="width: {{ $scorePct }}%"></div>
                    </div>
                </div>
            </div>
            <div class="space-y-2.5">
                @foreach($observation->cotRatings as $rating)
                    @php
                        $r = $rating->not_observed ? null : $rating->rating;
                        $rPct = $r ? ($r / 6) * 100 : 0;
                        $rColor = !$r ? 'bg-gray-100 border-gray-200 dark:border-gray-700' : ($r >= 5 ? 'border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/20' : ($r >= 4 ? 'border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20' : ($r >= 3 ? 'border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20' : 'border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20')));
                        $rBadge = !$r ? 'bg-gray-100 text-gray-500 dark:text-gray-400 dark:text-gray-500' : ($r >= 5 ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : ($r >= 4 ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : ($r >= 3 ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400' : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400')));
                    @endphp
                    <div class="rounded-xl p-4 border {{ $rColor }}">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $rating->indicator }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500 mt-0.5">{{ $rating->domain }}</p>
                                @if($rating->comments)
                                <p class="text-sm text-gray-600 dark:text-gray-400 dark:text-gray-500 mt-2 pt-2 border-t border-gray-200 dark:border-gray-700/60">{{ $rating->comments }}</p>
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
                        <div class="mt-2 w-full h-1 bg-gray-100 rounded-full">
                            <div class="h-1 rounded-full {{ $r >= 5 ? 'bg-emerald-50 dark:bg-emerald-900/200' : ($r >= 4 ? 'bg-blue-50 dark:bg-blue-900/200' : ($r >= 3 ? 'bg-amber-50 dark:bg-amber-900/200' : 'bg-red-50 dark:bg-red-900/200')) }}" style="width: {{ $rPct }}%"></div>
                        </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($observation->isSchoolHeadObservation() && $observation->epocEvaluation)
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Post-Observation Conference Evaluation</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500">EPOC &middot; 23 indicators &middot; 1–5 scale</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider font-medium">Overall Score</p>
                    <div class="flex items-end gap-1">
                        <p class="text-gray-900 dark:text-gray-100 font-bold text-3xl tracking-tight">{{ number_format($observation->epocEvaluation->overall_score, 1) }}</p>
                        <p class="text-gray-400 dark:text-gray-500 font-medium text-lg mb-0.5">/ 5</p>
                    </div>
                </div>
            </div>

            @if($observation->epocEvaluation->narrative_observation)
            <div class="mb-4 p-4 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Narrative Observation</span>
                <p class="text-sm text-gray-700 dark:text-gray-300 mt-1 whitespace-pre-wrap">{{ $observation->epocEvaluation->narrative_observation }}</p>
            </div>
            @endif

            @if($observation->epocEvaluation->agreement)
            <div class="mb-4 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800">
                <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">Agreement</span>
                <p class="text-sm text-gray-700 dark:text-gray-300 mt-1 whitespace-pre-wrap">{{ $observation->epocEvaluation->agreement }}</p>
            </div>
            @endif

            <div class="space-y-2.5">
                @foreach($observation->epocEvaluation->ratings as $rating)
                    @php
                        $r = $rating->rating;
                        $rColor = !$r ? 'bg-gray-100 border-gray-200 dark:border-gray-700' : ($r >= 4 ? 'border-indigo-200 dark:border-indigo-800 bg-indigo-50 dark:bg-indigo-900/20' : ($r >= 3 ? 'border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20' : 'border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20'));
                        $rBadge = !$r ? 'bg-gray-100 text-gray-500 dark:text-gray-400 dark:text-gray-500' : ($r >= 4 ? 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400' : ($r >= 3 ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400'));
                    @endphp
                    <div class="rounded-xl p-4 border {{ $rColor }}">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $rating->indicator }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500 mt-0.5">{{ $rating->domain }}</p>
                                @if($rating->comments)
                                <p class="text-sm text-gray-600 dark:text-gray-400 dark:text-gray-500 mt-2 pt-2 border-t border-gray-200 dark:border-gray-700/60">{{ $rating->comments }}</p>
                                @endif
                            </div>
                            <div class="text-center shrink-0">
                                <div class="w-14 h-14 rounded-xl {{ $rBadge }} flex items-center justify-center">
                                    <span class="text-lg font-bold">{{ $r ? number_format($r, 1) : '—' }}</span>
                                </div>
                                <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-0.5">/ 5</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($observation->postConference)
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Post-Conference</h2>
            <div class="space-y-3">
                @if($observation->postConference->conference_date)
                <div>
                    <span class="text-gray-500 dark:text-gray-400 dark:text-gray-500 text-sm">Conference Date:</span>
                    <p class="text-gray-900 dark:text-gray-100">{{ $observation->postConference->conference_date->format('M d, Y') }}</p>
                </div>
                @endif
                @if($observation->postConference->ai_comparison)
                <div>
                    <span class="text-gray-500 dark:text-gray-400 dark:text-gray-500 text-sm">AI Comparison (Plan vs Actual):</span>
                    <p class="text-gray-900 dark:text-gray-100 mt-1">{{ $observation->postConference->ai_comparison }}</p>
                </div>
                @endif
                @if($observation->postConference->feedback)
                <div>
                    <span class="text-gray-500 dark:text-gray-400 dark:text-gray-500 text-sm">Feedback:</span>
                    <p class="text-gray-900 dark:text-gray-100 mt-1">{{ $observation->postConference->feedback }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    @if($observation->status === 'cancelled')
    <div class="mt-8 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-6">
        <div class="flex gap-3">
            <svg class="w-5 h-5 text-red-600 dark:text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M4.293 4.293a1 1 0 011.414 0L12 10.586l6.293-6.293a1 1 0 111.414 1.414L13.414 12l6.293 6.293a1 1 0 01-1.414 1.414L12 13.414l-6.293 6.293a1 1 0 01-1.414-1.414L10.586 12 4.293 5.707a1 1 0 010-1.414z"/></svg>
            <div>
                <h3 class="font-semibold text-red-800">Observation Cancelled</h3>
                <p class="text-sm text-red-700 dark:text-red-400 mt-1">
                    Cancelled on {{ $observation->cancelled_at?->format('M d, Y \a\t h:i A') }} by {{ $observation->cancelledBy?->name ?? 'Unknown' }}
                </p>
                <p class="text-sm text-red-700 dark:text-red-400 mt-1">
                    <strong>Reason:</strong> {{ ucwords(str_replace('_', ' ', $observation->cancellation_reason)) }}
                </p>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
