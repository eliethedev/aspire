@extends('layouts.supervisor')

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
            <li><a href="{{ route('supervisor.observations.index') }}" class="hover:text-indigo-600 dark:text-indigo-400 transition-colors">Evaluations</a></li>
            <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"/></svg></li>
            <li><a href="{{ route('supervisor.observations.show', $observation) }}" class="hover:text-indigo-600 dark:text-indigo-400 transition-colors">Observation Details</a></li>
            <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"/></svg></li>
            <li class="text-gray-900 dark:text-gray-100 font-medium">Prepare for the Observation</li>
        </ol>
    </nav>

    @include('partials.draft-banner')

    <!-- Progress Steps -->
    @include('partials.observation-stepper')

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Prepare for the Observation</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">{{ $observation->observee->user->name ?? 'Unknown' }} &middot; {{ $observation->observation_date?->format('M d, Y') ?? 'No date' }}</p>
        </div>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
            {{ ucfirst($observation->status) }}
        </span>
    </div>

    <form method="POST" action="{{ route('supervisor.observations.storePreObservationPlanning', $observation) }}"
          x-data="{ submitting: false }" @submit="setTimeout(() => submitting = true, 100)"
          id="planning-form" data-autosave-form>
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
                        <p class="text-gray-500 dark:text-gray-400 text-sm">{{ $isSchoolHeadObs ? ($observation->observee->current_designation_label ?? 'School Head') : ($observation->observee->position ?? 'Teacher') }}</p>
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
                        <span class="text-xs {{ $planning && $planning->ai_insights ? 'text-purple-700 font-semibold' : 'text-gray-400 dark:text-gray-500' }}">AI Analysis</span>
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
                        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 3v3m3-3v3m-9 14V10a1 1 0 011-1h16a1 1 0 011 1v10m-18 0a1 1 0 001 1h16a1 1 0 001-1m-18 0l3-3 3 3 3-3 3 3 3-3v3M6 7h12"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">EPOC Pre-Observation Context</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">EPOC evaluates school supervision &amp; leadership practices rather than a single lesson plan.</p>
                    </div>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                    This observation uses the <strong>Evaluation of Practices and Observation of Competencies (EPOC)</strong> instrument
                    to assess the school head's supervision, instructional leadership, and school management practices. Focus your
                    review on the school head's supervision targets and the leadership practices to be observed during the session.
                </p>
                @if($planning?->suggested_focus)
                <div class="mt-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg p-3 border border-purple-100">
                    <span class="text-xs font-semibold text-purple-800 dark:text-purple-300 uppercase tracking-wider">Suggested Focus Area</span>
                    <p class="text-sm text-gray-700 dark:text-gray-300 mt-1">{{ is_array($planning->suggested_focus) ? implode(', ', $planning->suggested_focus) : $planning->suggested_focus }}</p>
                </div>
                @endif
            </div>
            @endif

            @if(!$isSchoolHeadObs)
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">AI Observation Assistant</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Optional help reviewing the lesson plan and suggesting useful areas to focus on. You can ignore it and continue yourself.</p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 text-xs bg-gradient-to-r from-purple-500 to-indigo-500 text-white px-2.5 py-1 rounded-full font-medium shadow-sm">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                        Optional
                    </span>
                </div>

                <div id="ai-notice-slot" class="mb-3"></div>
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
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1 max-w-xs mx-auto">Get AI suggestions for an instant analysis, or click "Write Manually" to add your own — both are saved the same way.</p>
                        </div>
                    @endif
                </div>
                <input type="hidden" name="ai_insights" id="ai_insights_input" value="{{ $planning?->ai_insights ?? '' }}">

                <!-- Manual entry (supervisor-only alternative to AI) -->
                <div id="manual-insights-box" class="hidden mt-4 rounded-xl border-2 border-dashed border-indigo-300 dark:border-indigo-700 bg-indigo-50/50 dark:bg-indigo-900/10 p-4">
                    <label for="manual-insights-textarea" class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-1">
                        Write your own insights
                    </label>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">No AI needed — note down what you want to focus on during the observation based on the lesson plan and the teacher's history.</p>
                    <textarea id="manual-insights-textarea" rows="8"
                              class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"
                              placeholder="e.g. Focus on questioning techniques and learner engagement. Previous observation showed..."></textarea>
                    <div class="flex items-center gap-2 mt-3">
                        <button type="button" id="save-manual-insights-btn"
                                class="px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition-colors inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            Use These Notes
                        </button>
                        <button type="button" id="cancel-manual-insights-btn"
                                class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 rounded-xl transition-colors">Cancel</button>
                        <span id="manual-saved-flash" class="hidden text-xs font-medium text-green-600 ml-1">Saved ✓</span>
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-2">
                    <button type="button" id="generate-ai-insights-btn"
                            class="px-5 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 disabled:from-purple-300 disabled:to-indigo-300 text-white text-sm rounded-xl font-semibold transition-all shadow-sm shadow-purple-600/20 hover:shadow-md hover:shadow-purple-600/30 inline-flex items-center gap-2">
                        <svg id="ai-spinner" class="hidden w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span id="ai-btn-text">Get AI Suggestions</span>
                    </button>
                    <button type="button" id="write-manual-insights-btn"
                            class="px-4 py-2.5 text-sm font-semibold text-indigo-700 dark:text-indigo-300 hover:text-white bg-white dark:bg-gray-900 hover:bg-indigo-600 border border-indigo-200 hover:border-indigo-600 rounded-xl transition-all inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Write Manually
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
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Observation Preparation</h2>

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

                    <!-- Suggested Focus Areas -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Suggested Focus Areas</label>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Based on previous observations and AI analysis.</p>
                        <textarea name="suggested_focus" rows="3"
                                  class="w-full px-3 py-2 rounded-lg bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                  placeholder="{{ $isSchoolHeadObs ? 'e.g. Instructional supervision, staff development, resource management...' : 'e.g. Classroom management, questioning techniques, learner engagement...' }}">{{ old('suggested_focus', $planning?->suggested_focus) }}</textarea>
                        @error('suggested_focus')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Pre-Conference Details -->
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

                <!-- Action Buttons -->
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span class="text-xs text-gray-500 dark:text-gray-400">You can save notes and continue later.</span>
                            </div>
                            <p id="autosave-status" data-autosave-status class="text-xs font-medium text-gray-400 dark:text-gray-500 shrink-0"></p>
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
            <div class="flex flex-wrap items-center gap-4">
                <button type="submit" name="continue" value="pre_conference"
                        class="inline-flex items-center gap-3 px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-semibold shadow-lg shadow-indigo-600/20 transition-all hover:shadow-xl hover:shadow-indigo-600/30">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    Continue to Pre-Observation Conversation
                </button>
                @if($observation->canCancel())
                <a href="{{ route('supervisor.observations.cancel-form', $observation) }}"
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
            <a href="{{ route('supervisor.observations.show', $observation) }}"
               class="inline-flex items-center gap-2 px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:text-gray-100 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Details
            </a>
            <a href="{{ route('supervisor.observations.index') }}"
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
    fetch('{{ route("supervisor.observations.request-lesson-plan", $observation) }}', {
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

var aiNoticeSlot = document.getElementById('ai-notice-slot');
var insightsContainer = document.getElementById('ai-insights-container');

function escapeHtml(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

function removeEmptyState() {
    const empty = document.getElementById('ai-insights-empty');
    if (empty) empty.remove();
}

function renderInsightsCard(text, badgeText, badgeClass) {
    removeEmptyState();
    let existing = document.getElementById('ai-insights-card');
    if (!existing) {
        existing = document.createElement('div');
        existing.id = 'ai-insights-card';
        insightsContainer.appendChild(existing);
    }
    existing.innerHTML =
        '<div class="bg-gradient-to-br from-purple-50 to-indigo-50 dark:from-purple-900/10 dark:to-indigo-900/10 rounded-xl p-5 border border-purple-100 dark:border-purple-900/40">' +
            '<div class="flex items-center gap-2 mb-3">' +
                '<div class="w-2 h-2 rounded-full bg-purple-500 animate-pulse"></div>' +
                '<span class="text-xs font-semibold uppercase tracking-wider ' + badgeClass + '">' + escapeHtml(badgeText) + '</span>' +
            '</div>' +
            '<div class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap leading-relaxed" id="ai-insights-text"></div>' +
        '</div>';
    existing.querySelector('#ai-insights-text').textContent = text;
    document.getElementById('clear-ai-insights-btn')?.classList.remove('hidden');
}

function openManualInsights() {
    AINotice.hide(aiNoticeSlot);
    const box = document.getElementById('manual-insights-box');
    const ta = document.getElementById('manual-insights-textarea');
    if (!ta.value.trim()) {
        ta.value = document.getElementById('ai_insights_input').value || '';
    }
    document.getElementById('manual-saved-flash').classList.add('hidden');
    box.classList.remove('hidden');
    box.scrollIntoView({ behavior: 'smooth', block: 'center' });
    ta.focus();
}

function closeManualInsights() {
    document.getElementById('manual-insights-box').classList.add('hidden');
}

document.getElementById('write-manual-insights-btn')?.addEventListener('click', openManualInsights);
document.getElementById('cancel-manual-insights-btn')?.addEventListener('click', closeManualInsights);

document.getElementById('save-manual-insights-btn')?.addEventListener('click', function() {
    const value = document.getElementById('manual-insights-textarea').value.trim();
    if (!value) return;
    document.getElementById('ai_insights_input').value = value;
    renderInsightsCard(value, 'Added by you', 'text-indigo-700 dark:text-indigo-300');
    closeManualInsights();
    const flash = document.getElementById('manual-saved-flash');
    flash.classList.remove('hidden');
    setTimeout(() => flash.classList.add('hidden'), 2500);
});

var aiBtn = document.getElementById('generate-ai-insights-btn');
var aiSpinner = document.getElementById('ai-spinner');
var aiBtnText = document.getElementById('ai-btn-text');
var aiGenerating = false;

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
    AiLoading.start(insightsContainer, 'Generating AI insights', 'Reviewing the lesson plan and previous observations\u2026');
}

function finishGenerated(insights) {
    AiLoading.stop();
    closeManualInsights();
    document.getElementById('ai_insights_input').value = insights;
    renderInsightsCard(insights, 'AI Suggestions Ready', 'text-purple-700 dark:text-purple-300');
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
        AINotice.show(aiNoticeSlot, { error: 'AI insights are taking longer than expected. Please try again later.', reason: 'timeout', manual_available: true }, { onManual: openManualInsights });
        return;
    }

    setTimeout(function() {
        fetch('{{ route("supervisor.observations.ai-insights-status", $observation) }}')
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
    AINotice.hide(aiNoticeSlot);
    startGeneratingUi();

    fetch('{{ route("supervisor.observations.generate-ai-insights", $observation) }}', {
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
        AINotice.show(aiNoticeSlot, data, { onManual: openManualInsights });
    })
    .catch(() => {
        cancelGeneration();
        AINotice.show(aiNoticeSlot, { error: 'AI isn\'t available because your connection to the server was interrupted. Please try again.' }, { onManual: openManualInsights });
    });
});

document.getElementById('clear-ai-insights-btn')?.addEventListener('click', function() {
    if (!confirm('Clear these insights? This cannot be undone.')) return;

    fetch('{{ route("supervisor.observations.clear-ai-insights", $observation) }}', {
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
            document.getElementById('manual-insights-textarea').value = '';
            const card = document.getElementById('ai-insights-card');
            if (card) card.remove();
            const empty = document.getElementById('ai-insights-empty');
            if (empty) empty.remove();
            const container = document.getElementById('ai-insights-container');
            if (container) {
                const div = document.createElement('div');
                div.id = 'ai-insights-empty';
                div.className = 'bg-gray-50 dark:bg-gray-800 rounded-xl p-8 border-2 border-dashed border-gray-200 dark:border-gray-700 text-center';
                div.innerHTML = '<div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-purple-100 to-indigo-100 flex items-center justify-center mx-auto mb-4"><svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg></div><p class="text-sm font-medium text-gray-600 dark:text-gray-400">No suggestions yet</p><p class="text-xs text-gray-400 dark:text-gray-500 mt-1 max-w-xs mx-auto">Get AI suggestions or write them yourself — both work equally well.</p>';
                container.appendChild(div);
            }
            this.classList.add('hidden');
        }
    })
    .catch(err => {
        alert('Failed to clear insights.');
        console.error(err);
    });
});
</script>
@include('partials.autosave')
<script>
    (function () {
        var form = document.getElementById('planning-form');
        var status = document.getElementById('autosave-status');
        if (form && window.asAutoSave) {
            asAutoSave(form, 'pre_observation_planning', status, { wait: 1200 });
        }
    })();
</script>
@endpush
@endsection