@extends('layouts.teacher')

@section('title', 'Prepare for the Observation')

@push('styles')
<style>
    select option {
        background-color: #1f2937;
        color: #ffffff;
    }
</style>
@endpush

@php
    $currentStage = $observation->stage;
    $isSchoolHeadObs = $observation->isSchoolHeadObservation();
@endphp

@section('content')
<div class="max-w-7xl mx-auto px-6">
    <!-- Breadcrumb -->
    <nav class="mb-6 text-sm">
        <ol class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
            <li><a href="{{ route('school-head.observations.index') }}" class="hover:text-indigo-600 dark:text-indigo-400 transition-colors">Evaluations</a></li>
            <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"/></svg></li>
            <li><a href="{{ route('school-head.observations.show', $observation) }}" class="hover:text-indigo-600 dark:text-indigo-400 transition-colors">Observation Details</a></li>
            <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"/></svg></li>
            <li class="text-gray-900 dark:text-gray-100 font-medium">Prepare for the Observation</li>
        </ol>
    </nav>

    <!-- Progress Steps -->
    @include('partials.observation-stepper', ['routeBase' => 'school-head'])

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Prepare for the Observation</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">{{ $observation->observee->user->name ?? 'Unknown' }} &middot; {{ $observation->observation_date?->format('M d, Y') ?? 'No date' }}</p>
        </div>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
            {{ $stageLabels[$observation->status] ?? ucfirst($observation->status) }}
        </span>
    </div>

    <form method="POST" action="{{ route('school-head.observations.storePreObservationPlanning', $observation) }}"
          x-data="{ submitting: false }" @submit="setTimeout(() => submitting = true, 100)">
        @csrf

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left Column - Main Content -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Observer Information Card -->
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm p-6 border border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ $isSchoolHeadObs ? 'School Head Information' : 'Teacher Information' }}</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div class="col-span-2">
                        <span class="text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider font-medium">Full Name</span>
                        <p class="text-gray-900 dark:text-gray-100 font-semibold mt-1 text-lg">{{ $observation->observee->user->name ?? 'Unknown' }}</p>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">{{ $isSchoolHeadObs ? ($observation->observee->current_designation_label ?? 'School Head') : ($observation->observee->position_label ?? 'Teacher') }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider font-medium">School</span>
                        <p class="text-gray-900 dark:text-gray-100 font-semibold mt-1">{{ $observation->observee->school->name ?? 'N/A' }}</p>
                    </div>
                    @if(!$isSchoolHeadObs)
                    <div>
                        <span class="text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider font-medium">Subject / Grade</span>
                        <p class="text-gray-900 dark:text-gray-100 font-semibold mt-1">{{ $observation->subject ?? 'N/A' }}</p>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Grade {{ $observation->grade_level ?? 'N/A' }}</p>
                    </div>
                    @else
                    <div>
                        <span class="text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider font-medium">Focus Area</span>
                        <p class="text-gray-900 dark:text-gray-100 font-semibold mt-1">School Supervision &amp; Leadership</p>
                    </div>
                    @endif
                </div>
            </div>

            @if(!$isSchoolHeadObs)
            <!-- Lesson Plan -->
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Lesson Plan</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Uploaded by the teacher for your review</p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 text-xs bg-amber-100 dark:bg-amber-900/30 text-amber-700 px-2.5 py-1 rounded-full font-medium">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Teacher's Responsibility
                    </span>
                </div>

                <!-- Status Timeline -->
                <div class="flex items-center gap-0 mb-5">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full {{ $planning && $planning->lesson_plan_file ? 'bg-emerald-100 text-emerald-600' : 'bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500' }} flex items-center justify-center">
                            @if($planning && $planning->lesson_plan_file)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            @endif
                        </div>
                        <span class="text-xs {{ $planning && $planning->lesson_plan_file ? 'text-emerald-700 font-semibold' : 'text-gray-400 dark:text-gray-500' }}">Uploaded</span>
                    </div>
                    <div class="flex-1 mx-2 h-px {{ $planning && $planning->lesson_plan_file ? 'bg-emerald-300' : 'bg-gray-200' }}"></div>
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full {{ $planning && $planning->ai_insights ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400' : 'bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500' }} flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                        </div>
                        <span class="text-xs {{ $planning && $planning->ai_insights ? 'text-purple-700 font-semibold' : 'text-gray-400 dark:text-gray-500' }}">AI Suggestions</span>
                    </div>
                    <div class="flex-1 mx-2 h-px {{ $planning && $planning->supervisor_notes ? 'bg-blue-300' : 'bg-gray-200' }}"></div>
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full {{ $planning && $planning->supervisor_notes ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500' }} flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </div>
                        <span class="text-xs {{ $planning && $planning->supervisor_notes ? 'text-blue-700 font-semibold' : 'text-gray-400 dark:text-gray-500' }}">Review</span>
                    </div>
                </div>

                @if($planning && $planning->lesson_plan_file)
                    <div class="flex items-center justify-between bg-gradient-to-r from-blue-50 to-emerald-50 rounded-xl p-4 border border-blue-100">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 bg-white dark:bg-gray-900 rounded-xl flex items-center justify-center shadow-sm">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ preg_replace('/^\d+_/', '', basename($planning->lesson_plan_file)) }}</p>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="inline-flex items-center gap-1 text-xs text-emerald-600 font-medium">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Uploaded
                                    </span>
                                    <span class="text-gray-300">&middot;</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Ready for review</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ asset('storage/' . $planning->lesson_plan_file) }}" target="_blank"
                               class="px-4 py-2 bg-white dark:bg-gray-900 hover:bg-blue-50 text-blue-700 border border-blue-200 text-sm rounded-lg font-medium transition-colors inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                View
                            </a>
                        </div>
                    </div>
                @else
                    <div class="relative overflow-hidden bg-gradient-to-r from-yellow-50 to-orange-50 rounded-xl p-5 border border-yellow-200">
                        <div class="absolute top-0 right-0 w-32 h-32 bg-yellow-100/50 rounded-full -translate-y-12 translate-x-12"></div>
                        <div class="relative flex items-start gap-4">
                            <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center shrink-0 shadow-sm">
                                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-yellow-800">No lesson plan uploaded yet</p>
                                <p class="text-xs text-yellow-700 mt-0.5">The teacher has not submitted a lesson plan for this observation.</p>
                                <button type="button" onclick="requestLessonPlan(this)"
                                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white text-xs rounded-lg font-semibold transition-colors shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                    Request Lesson Plan
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            @else
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Enhanced Post Observation Conference &mdash; Pre-Observation Context</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">The Enhanced Post Observation Conference evaluates school supervision &amp; leadership practices rather than a single lesson plan.</p>
                    </div>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                    This observation uses the <strong>Enhanced Post Observation Conference</strong> instrument
                    to assess the school head's supervision, instructional leadership, and school management practices. Focus your
                    review on the school head's supervision targets and the leadership practices to be observed during the session.
                </p>
                </div>
            @endif

            @if(!$isSchoolHeadObs)
            <!-- AI Observation Assistant -->
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">AI Observation Assistant</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Optional AI suggestions based on the lesson plan and previous observation data.</p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 text-xs bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 px-2.5 py-1 rounded-full font-medium">
                        Optional
                    </span>
                </div>

                <div id="ai-insights-container">
                    @if($planning && $planning->ai_insights)
                        <div id="ai-insights-card" class="bg-gradient-to-br from-purple-50 to-indigo-50 rounded-xl p-5 border border-purple-100">
                            <div class="flex items-center gap-2 mb-3">
                                <div class="w-2 h-2 rounded-full bg-purple-500 animate-pulse"></div>
                                <span class="text-xs font-semibold text-purple-700 uppercase tracking-wider">Suggestions Ready</span>
                            </div>
                            @php $insightSections = $planning->insightsSections(); @endphp
                            @if(isset($insightSections['raw']))
                                <div id="ai-insights-text" class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap leading-relaxed">{{ $insightSections['raw'] }}</div>
                            @else
                                {!! view('partials.ai-insights-display', ['sections' => $insightSections])->render() !!}
                            @endif
                        </div>
                    @else
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-xl p-8 border-2 border-dashed border-gray-200 dark:border-gray-700 text-center" id="ai-insights-empty">
                            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-purple-100 to-indigo-100 flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">No suggestions yet</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1 max-w-xs mx-auto">Click "Get AI Suggestions" to analyze the lesson plan and generate suggestions based on previous observation data.</p>
                        </div>
                    @endif
                </div>
                <input type="hidden" name="ai_insights" id="ai_insights_input" value="{{ $planning?->ai_insights ?? '' }}">
                <div class="mt-4 flex items-center gap-2">
                    <button type="button" id="generate-ai-insights-btn"
                            class="px-5 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 disabled:from-purple-300 disabled:to-indigo-300 text-white text-sm rounded-xl font-semibold transition-all shadow-sm shadow-purple-600/20 hover:shadow-md hover:shadow-purple-600/30 inline-flex items-center gap-2">
                        <svg id="ai-spinner" class="hidden w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span id="ai-btn-text">Get AI Suggestions</span>
                    </button>
                    <button type="button" id="clear-ai-insights-btn"
                            class="px-4 py-2.5 text-sm font-semibold text-red-600 dark:text-red-400 hover:text-white bg-red-50 dark:bg-red-900/20 hover:bg-red-600 border border-red-200 hover:border-red-600 rounded-xl transition-all inline-flex items-center gap-1.5 {{ $planning?->ai_insights ? '' : 'hidden' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Clear
                    </button>
                </div>
            </div>
            @endif

            <!-- Previous Observation Highlights (from past data) -->
            @if($previousObservations->isNotEmpty())
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Previous Observation Highlights</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Based on {{ $previousObservations->count() }} previous observation(s).</p>
                    </div>
                    <span class="inline-flex items-center gap-1 text-xs bg-blue-100 text-blue-700 px-2.5 py-1 rounded-full font-medium">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        Trend Data
                    </span>
                </div>

                @if($prevStrengths->isNotEmpty())
                <div class="mb-4">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Strengths</h3>
                    </div>
                    <div class="space-y-2">
                        @foreach($prevStrengths as $strength)
                            @php
                                $pct = min(($strength->avg_rating / 6) * 100, 100);
                            @endphp
                            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3 border border-gray-100">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $strength->domain }}</span>
                                    <span class="text-xs font-semibold text-green-600 dark:text-green-400">{{ number_format($strength->avg_rating, 1) }}/6</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-1.5">
                                    <div class="bg-green-500 h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($prevWeaknesses->isNotEmpty())
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                            <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Areas for Improvement</h3>
                    </div>
                    <div class="space-y-2">
                        @foreach($prevWeaknesses as $weakness)
                            @php
                                $pct = min(($weakness->avg_rating / 6) * 100, 100);
                            @endphp
                            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3 border border-gray-100">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $weakness->domain }}</span>
                                    <span class="text-xs font-semibold text-red-600 dark:text-red-400">{{ number_format($weakness->avg_rating, 1) }}/6</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-1.5">
                                    <div class="bg-red-400 h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($prevStrengths->isEmpty() && $prevWeaknesses->isEmpty())
                    <div class="flex flex-col items-center py-8 text-center">
                        <svg class="w-10 h-10 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <p class="text-sm text-gray-500 dark:text-gray-400">No detailed rating data available from previous observations.</p>
                    </div>
                @endif
            </div>
            @endif

        </div>

        <!-- Right Column - Sidebar -->
        <div class="space-y-6">

                <!-- Observation Preparation -->
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Observation Setup</h2>

                    <!-- Observation Tool -->
                    @if($isSchoolHeadObs)
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Observation Form / Rubric</label>
                        <div class="bg-indigo-50 dark:bg-indigo-900/30 rounded-lg p-3 border border-indigo-200 dark:border-indigo-900/50">
                            <p class="text-sm font-semibold text-indigo-800 dark:text-indigo-300">Enhanced Post Observation Conference</p>
                            <p class="text-xs text-indigo-600 dark:text-indigo-400 mt-1">Used for School Head observations.</p>
                        </div>
                    </div>
                    @else
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Observation Form / Rubric</label>
                        <select name="observation_tool"
                                class="w-full px-3 py-2 rounded-lg bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="">Select tool...</option>
                            {{-- PPST is hidden for school heads — they use COT only --}}
                            @if(!isset($observerRole) || $observerRole !== 'school_head')
                                <option value="ppst" {{ old('observation_tool', $planning?->observation_tool) === 'ppst' ? 'selected' : '' }}>PPST</option>
                            @endif
                            <option value="classroom_observation_tool" {{ old('observation_tool', $planning?->observation_tool) === 'classroom_observation_tool' ? 'selected' : '' }}>Classroom Observation Tool (COT)</option>
                            <option value="tisuyon" {{ old('observation_tool', $planning?->observation_tool) === 'tisuyon' ? 'selected' : '' }}>Tisuyon (Peer Observation)</option>
                        </select>
                        @error('observation_tool')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Selected Tool Preview -->
                    @if($planning?->observation_tool === 'ppst')
                    <div class="bg-blue-50 rounded-lg p-3 border border-blue-100 mb-4">
                        <p class="text-xs font-semibold text-blue-800 mb-1">PPST - 5 Domains</p>
                        <p class="text-xs text-blue-600">Content Knowledge, Learning Environment, Diversity of Learners, Curriculum & Planning, Assessment & Reporting</p>
                    </div>
                    @elseif($planning?->observation_tool === 'tisuyon')
                    <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3 border border-green-100 mb-4">
                        <p class="text-xs font-semibold text-green-800 dark:text-green-300 mb-1">Tisuyon - Peer Observation</p>
                        <p class="text-xs text-green-600 dark:text-green-400">Collaborative peer observation focused on professional dialogue and shared learning.</p>
                    </div>
                    @elseif($planning?->observation_tool === 'classroom_observation_tool')
                    <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-3 border border-purple-100 mb-4">
                        <p class="text-xs font-semibold text-purple-800 dark:text-purple-300 mb-1">9 Indicators</p>
                        <p class="text-xs text-purple-600 dark:text-purple-400">Standard classroom observation tool with 9 performance indicators.</p>
                    </div>
                    @endif
                    @endif

                    <!-- Supervisor's Notes -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">My Pre-Observation Notes</label>
                        <textarea name="supervisor_notes" rows="5"
                                  class="w-full px-3 py-2 rounded-lg bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                  placeholder="{{ $isSchoolHeadObs ? 'Write your preliminary notes, things to watch for, or reminders about the school head\'s supervision practices before the session...' : 'Write your preliminary notes, things to watch for, or reminders before the class visit...' }}">{{ old('supervisor_notes', $planning?->supervisor_notes) }}</textarea>
                        @error('supervisor_notes')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    </div>

                <!-- Pre-Observation Conversation Details -->
                @if(!$isSchoolHeadObs)
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Pre-Observation Conversation</h2>
                    @if($preConference && $preConference->conference_date)
                        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4 border border-green-100">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-green-800 dark:text-green-300">Scheduled</span>
                                <span class="text-xs bg-green-100 dark:bg-green-900/30 text-green-700 px-2 py-0.5 rounded-full">Set</span>
                            </div>
                            <p class="text-sm text-green-700 font-medium">{{ $preConference->conference_date->format('M d, Y') }}</p>
                            <p class="text-xs text-green-600 dark:text-green-400 mt-1">The pre-observation conversation has been scheduled.</p>
                        </div>
                    @else
                        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 border border-dashed border-gray-300 dark:border-gray-600 text-center">
                            <svg class="w-8 h-8 text-gray-400 dark:text-gray-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">No pre-observation conversation scheduled</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Continue to Pre-Observation Conversation to set the date.</p>
                        </div>
                    @endif
                </div>
                @endif

                <!-- Action Buttons -->
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="space-y-3">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="text-xs text-gray-500 dark:text-gray-400">You can save notes and continue later.</span>
                        </div>
                        <button type="submit"
                                class="block w-full px-4 py-2.5 bg-white dark:bg-gray-900 border-2 border-indigo-600 text-indigo-700 hover:bg-indigo-50 dark:bg-indigo-900/20 rounded-lg font-semibold text-sm transition-colors">
                            <span class="flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                                Save Notes
                            </span>
                        </button>
                    </div>
                </div>

        </div>

    </div>

    <!-- Bottom Actions -->
    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <button type="submit" name="continue" value="pre_conference"
                        class="inline-flex items-center gap-3 px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-semibold shadow-lg shadow-indigo-600/20 transition-all hover:shadow-xl hover:shadow-indigo-600/30">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    {{ $isSchoolHeadObs ? 'Continue to School Head Observation' : 'Continue to Pre-Observation Conversation' }}
                </button>
                @if($observation->canCancel())
                <a href="{{ route('school-head.observations.cancel-form', $observation) }}"
                   class="inline-flex items-center gap-3 px-8 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl font-semibold shadow-lg shadow-red-600/20 transition-all hover:shadow-xl hover:shadow-red-600/30">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Cancel Observation
                </a>
                @endif
            </div>
        </div>
    </div>

    </form>

    <!-- Bottom Navigation -->
    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <a href="{{ route('school-head.observations.show', $observation) }}"
               class="inline-flex items-center gap-2 px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:text-gray-100 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Details
            </a>
            <a href="{{ route('school-head.observations.index') }}"
               class="text-sm text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:text-gray-400 transition-colors">
                All Evaluations
            </a>
        </div>
    </div>
</div>
@push('scripts')
@include('partials.ai-notice')
@include('partials.ai-loading-state')
<script>
function requestLessonPlan(btn) {
    btn.disabled = true;
    btn.innerHTML = '<svg class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Sending...';
    fetch('{{ route("school-head.observations.request-lesson-plan", $observation) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
        },
    })
    .then(res => {
        if (res.redirected) window.location.href = res.url;
    })
    .catch(err => {
        alert('Failed to request lesson plan. Please try again.');
        btn.disabled = false;
        btn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg> Request Lesson Plan';
    });
}

var aiBtn = document.getElementById('generate-ai-insights-btn');
var aiSpinner = document.getElementById('ai-spinner');
var aiBtnText = document.getElementById('ai-btn-text');
var aiNoticeSlot = document.getElementById('ai-notice-slot');
var insightsContainer = document.getElementById('ai-insights-container');
var aiGenerating = false;

function removeEmptyState() {
    var empty = document.getElementById('ai-insights-empty');
    if (empty) empty.remove();
}

function renderInsightsCard(text) {
    var container = insightsContainer;
    var existing = document.getElementById('ai-insights-card');
    if (!existing) {
        existing = document.createElement('div');
        existing.id = 'ai-insights-card';
        container.appendChild(existing);
    }
    existing.className = 'bg-gradient-to-br from-purple-50 to-indigo-50 dark:from-purple-900/10 dark:to-indigo-900/10 rounded-xl p-5 border border-purple-100 dark:border-purple-900/40';
    existing.innerHTML = '<div class="flex items-center gap-2 mb-3"><div class="w-2 h-2 rounded-full bg-purple-500 animate-pulse"></div><span class="text-xs font-semibold text-purple-700 dark:text-purple-300 uppercase tracking-wider">Suggestions Ready</span></div><div class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap leading-relaxed" id="ai-insights-text"></div>';
    existing.querySelector('#ai-insights-text').textContent = text;
    document.getElementById('clear-ai-insights-btn')?.classList.remove('hidden');
}

function restoreBtn() {
    if (!aiBtn) return;
    aiBtn.disabled = false;
    if (aiSpinner) aiSpinner.classList.add('hidden');
    if (aiBtnText) aiBtnText.textContent = 'Get AI Suggestions';
}

function startGeneratingUi() {
    removeEmptyState();
    var existing = document.getElementById('ai-insights-card');
    if (existing) existing.remove();
    AiLoading.start(insightsContainer, 'Generating AI suggestions', 'Reviewing the lesson plan and previous observations\u2026');
}

function finishGenerated(insights) {
    AiLoading.stop();
    document.getElementById('ai_insights_input').value = insights;
    renderInsightsCard(insights);
    restoreBtn();
    aiGenerating = false;
}

function cancelGeneration() {
    AiLoading.stop();
    restoreBtn();
    aiGenerating = false;
}

function pollAiInsightsStatus(attempt) {
    attempt = attempt || 1;
    var maxAttempts = 40;
    var pollInterval = 3000;

    if (attempt > maxAttempts) {
        cancelGeneration();
        alert('AI insights are taking longer than expected. Please try again later.');
        return;
    }

    setTimeout(function() {
        fetch('{{ route("school-head.observations.ai-insights-status", $observation) }}')
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.status === 'completed' && data.ai_insights) {
                    finishGenerated(data.ai_insights);
                } else {
                    pollAiInsightsStatus(attempt + 1);
                }
            })
            .catch(function() {
                pollAiInsightsStatus(attempt + 1);
            });
    }, pollInterval);
}

document.getElementById('generate-ai-insights-btn')?.addEventListener('click', function() {
    if (aiGenerating) return;
    aiGenerating = true;

    if (aiBtn) aiBtn.disabled = true;
    if (aiSpinner) aiSpinner.classList.remove('hidden');
    if (aiBtnText) aiBtnText.textContent = 'Generating\u2026';
    startGeneratingUi();

    fetch('{{ route("school-head.observations.generate-ai-insights", $observation) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
        },
    })
    .then(async res => {
        const data = await res.json().catch(() => ({}));
        return { ok: res.ok, data };
    })
    .then(({ ok, data }) => {
        if (ok && data.ai_insights) {
            finishGenerated(data.ai_insights);
            return;
        }
        if (ok && data.status === 'processing') {
            pollAiInsightsStatus(1);
            return;
        }
        cancelGeneration();
        alert((data && data.error) || 'AI isn\'t available right now. Please try again.');
    })
    .catch(() => {
        cancelGeneration();
        alert('Failed to generate AI insights. Please try again.');
    });
});

document.getElementById('clear-ai-insights-btn')?.addEventListener('click', function() {
    if (!confirm('Clear AI insights? This cannot be undone.')) return;

    fetch('{{ route("school-head.observations.clear-ai-insights", $observation) }}', {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
        },
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById('ai_insights_input').value = '';
            const card = document.getElementById('ai-insights-card');
            if (card) card.remove();
            const empty = document.getElementById('ai-insights-empty');
            if (empty) empty.remove();
            const container = document.getElementById('ai-insights-container');
            if (container) {
                const div = document.createElement('div');
                div.id = 'ai-insights-empty';
                div.className = 'bg-gray-50 dark:bg-gray-800 rounded-xl p-8 border-2 border-dashed border-gray-200 dark:border-gray-700 text-center';
                div.innerHTML = '<div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-purple-100 to-indigo-100 flex items-center justify-center mx-auto mb-4"><svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg></div><p class="text-sm font-medium text-gray-600 dark:text-gray-400">No suggestions yet</p><p class="text-xs text-gray-400 dark:text-gray-500 mt-1 max-w-xs mx-auto">Click "Get AI Suggestions" to analyze the lesson plan and generate suggestions.</p>';
                container.appendChild(div);
            }
            document.getElementById('clear-ai-insights-btn').classList.add('hidden');
        }
    })
    .catch(err => {
        alert('Failed to clear AI insights.');
        console.error(err);
    });
});
</script>
@endpush
@endsection