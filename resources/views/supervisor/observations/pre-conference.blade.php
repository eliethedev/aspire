@extends('layouts.supervisor')

@section('title', 'Pre-Observation Conversation')
@include('partials.dashboard.mock-styles')

@push('styles')
<style>
    select option { background-color: #1f2937; color: #ffffff; }
    .ai-panel-enter { animation: slideDown 0.3s ease-out; }
    @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    .sidebar-scroll::-webkit-scrollbar { width: 4px; }
    .sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
    .sidebar-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    .insight-card { transition: all 0.2s ease; }
    .insight-card:hover { border-color: #a5b4fc; }
    .copy-btn { opacity: 0; transition: opacity 0.15s ease; }
    .insight-card:hover .copy-btn { opacity: 1; }
    .agenda-item { transition: all 0.15s ease; }
    .agenda-item:has(input:checked) label { text-decoration: line-through; color: #9ca3af; }
    .modal-overlay { background: rgba(0,0,0,0.3); backdrop-filter: blur(2px); }
</style>
@endpush

@php
    $currentStage = $observation->stage;
    $aiInsights = $planning?->ai_insights;
    $aiReviewed = $planning?->ai_insights_reviewed ?? false;
    $lessonPlanMissing = $planning && !$planning->lesson_plan_file;
@endphp

@section('content')
<div class="mock-wrap max-w-7xl mx-auto px-1 py-1">
    <div class="mock-topbar">
        <div class="mock-crumbs">Supervisor <span>/</span> <b>Pre-Observation Conversation</b></div>
        <div class="mock-actions">
            <a class="mock-btn" href="{{ route('supervisor.observations.show', $observation) }}">Back to Details</a>
            <a class="mock-btn" href="{{ route('supervisor.observations.index') }}">All Evaluations</a>
        </div>
    </div>

    <div class="mock-title">
        <div>
            <h1>Pre-Observation Conversation</h1>
            <p>{{ $observation->observee->user->name ?? 'Unknown' }} · {{ $observation->observation_date?->format('M d, Y') ?? 'No date' }}</p>
        </div>
        <time>{{ ucfirst($observation->status) }} · Step 2 of 4</time>
    </div>

    @include('partials.draft-banner')

    <!-- Progress Steps -->
    @include('partials.observation-stepper')

    {{-- Page header lives in the mock shell above --}}
    @if($lessonPlanMissing)
    <div class="mb-6 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 rounded-lg flex items-start gap-3">
        <svg class="w-5 h-5 text-amber-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
        <div>
            <p class="text-sm font-medium text-amber-800 dark:text-amber-300">Lesson Plan Not Uploaded</p>
            <p class="text-xs text-amber-700 mt-1">The teacher has not uploaded a lesson plan for this observation. Consider requesting one before the pre-conference meeting.</p>
        </div>
    </div>
    @endif

    <form method="POST" action="{{ route('supervisor.observations.storePreConference', $observation) }}" id="pre-conference-form" class="space-y-4 sm:space-y-6" data-autosave-form
          x-data="{ submitting: false }" x-on:submit="submitting = true">
    @csrf
    <input type="hidden" name="ai_insights_reviewed" id="ai_insights_reviewed_input" value="{{ $aiReviewed ? '1' : '0' }}">

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6 items-start">

        <!-- Left Column -->
        <div class="min-w-0 md:col-span-2 space-y-4 sm:space-y-6">

            <!-- AI Pre-Observation Insights Panel -->
            <div id="ai-insights-panel"
                 class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-purple-200 dark:border-purple-900/40 ai-panel-enter {{ $aiReviewed ? 'opacity-75' : '' }}">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between p-4 sm:p-5 border-b border-purple-100 dark:border-purple-900/40 bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-purple-950/40 dark:to-indigo-950/20 rounded-t-xl">
                    <div class="flex min-w-0 flex-wrap items-center gap-2">
                        <svg class="w-5 h-5 shrink-0 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                        <h2 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-gray-100">AI Observation Assistant</h2>
                        <span class="shrink-0 text-[11px] bg-purple-100 dark:bg-purple-900/30 text-purple-700 px-2 py-0.5 rounded-full font-medium">Optional</span>
                        @if($aiReviewed)
                            <span class="shrink-0 text-[11px] bg-green-100 dark:bg-green-900/30 text-green-700 px-2 py-0.5 rounded-full font-medium">Reviewed</span>
                        @endif
                    </div>
                    <button type="button" id="regenerate-ai-btn"
                            class="w-full sm:w-auto justify-center min-h-[44px] sm:min-h-0 px-3 py-2 sm:py-1.5 text-xs font-medium text-purple-700 bg-purple-100 dark:bg-purple-900/30 hover:bg-purple-200 rounded-lg transition-colors inline-flex items-center gap-1.5 disabled:opacity-50">
                        <svg id="regenerate-spinner" class="hidden w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span class="generate-btn-text">Regenerate</span>
                    </button>
                </div>

                <div class="p-4 sm:p-5 space-y-4" id="ai-panel-body">
                    @include('partials.ai-engine-selector')
                    <div class="flex items-start gap-2 text-xs text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 rounded-lg p-3 border border-gray-200 dark:border-gray-700">
                        <svg class="w-4 h-4 text-gray-400 dark:text-gray-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p>AI-generated analysis of the submitted lesson plan. Optional — review, accept, or ignore these suggestions.</p>
                    </div>

                    <div id="ai-panel-notice"></div>

                    <div id="ai-result-slot">
                    @if($aiInsights)
                        @include('partials.ai-insights-result', ['insights' => $aiInsights])
                    @else
                        <div class="text-center py-6" id="ai-empty-state">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">No AI insights generated yet.</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mb-4">Generate insights from the lesson plan — or skip AI entirely and complete the form below yourself.</p>
                            <button type="button" onclick="generateAiInsights(event)"
                                    class="generate-ai-btn w-full sm:w-auto px-4 py-2 min-h-[44px] justify-center text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 disabled:bg-purple-300 rounded-lg transition-colors inline-flex items-center gap-2">
                                <svg class="generate-spinner hidden w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                <span class="generate-btn-text">Get AI Suggestions</span>
                            </button>
                        </div>
                    @endif
                    </div>
                </div>
            </div>

            <!-- Pre-Observation Planning Summary (collapsible) -->
            @if($planning)
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm" x-data="{ open: {{ $observation->stage === 'pre_conference' ? 'true' : 'false' }} }">
                <button type="button" @click="open = !open"
                        class="w-full flex items-center justify-between p-4 text-left">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Preparation Summary</h2>
                    <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" x-collapse>
                    <div class="p-4 pt-0">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @if($planning->lesson_plan_file)
                            <div class="bg-blue-50 rounded-lg p-3 border border-blue-100">
                                <span class="text-xs text-gray-500 dark:text-dark uppercase tracking-wider font-medium">Lesson Plan</span>
                                <div class="mt-1 flex items-center justify-between">
                                    <span class="text-sm text-gray-700 dark:text-dark truncate min-w-0">{{ preg_replace('/^\d+_/', '', basename($planning->lesson_plan_file)) }}</span>
                                    <a href="{{ asset('storage/' . $planning->lesson_plan_file) }}" target="_blank" class="text-sm text-blue-600 hover:text-blue-700 font-medium shrink-0 ml-2">View</a>
                                </div>
                            </div>
                            @endif
                            @if($planning->supervisor_notes)
                            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3 border border-gray-200 dark:border-gray-700">
                                <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider font-medium">Supervisor's Notes</span>
                                <p class="text-sm text-gray-700 dark:text-gray-300 mt-1">{{ Str::limit($planning->supervisor_notes, 120) }}</p>
                            </div>
                            @endif
                            @if($planning->suggested_focus)
                            <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-3 border border-purple-100">
                                <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider font-medium">Suggested Focus</span>
                                <p class="text-sm text-gray-700 dark:text-gray-300 mt-1">{{ Str::limit(is_array($planning->suggested_focus) ? implode(', ', $planning->suggested_focus) : $planning->suggested_focus, 120) }}</p>
                            </div>
                            @endif
                            @if($planning->observation_tool)
                            <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3 border border-green-100">
                                <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider font-medium">Observation Form</span>
                                <p class="text-sm text-gray-700 dark:text-gray-300 mt-1 font-medium">{{ str_replace('_', ' ', ucfirst($planning->observation_tool)) }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Section 1: About the Lesson -->
            <section class="mock-panel" aria-label="About the lesson" style="padding:16px 20px">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">About the Lesson</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subject</label>
                        <p class="text-gray-900 dark:text-gray-100 font-semibold">{{ $observation->subject ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Grade / Section</label>
                        <p class="text-gray-900 dark:text-gray-100 font-semibold">{{ $observation->grade_level_label ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" for="conference_date">Date / Time *</label>
                        <input type="date" name="conference_date" id="conference_date"
                               value="{{ old('conference_date', $preConference?->conference_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                               class="w-full px-3 py-2 rounded-lg bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                        @error('conference_date')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </section>

            <!-- Section 2: Lesson Plan & Strategy -->
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm p-4 sm:p-6 border border-gray-100 dark:border-gray-800">
                <div class="flex items-center gap-2 mb-1">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Things to Think About</h2>
                    <span class="text-xs bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 px-2 py-0.5 rounded-full font-medium">Optional</span>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">A few prompts to guide your pre-observation conversation with the teacher.</p>
                <div id="suggestions-notice" class="mb-3"></div>
                <div class="space-y-4">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Teaching Strategies</label>
                            <button type="button" class="things-suggest-btn shrink-0 inline-flex items-center gap-1 px-2.5 py-1.5 min-h-[36px] text-xs font-semibold text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-900/20 hover:bg-purple-100 dark:hover:bg-purple-900/30 border border-purple-200 dark:border-purple-800 rounded-lg transition-colors" data-field="teaching_strategies" data-textarea="teaching_strategies" data-container="things-suggest-teaching_strategies">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                <span>Suggest with AI</span>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">What teaching strategies will be used in this lesson?</p>
                        <div id="things-suggest-teaching_strategies" class="hidden mb-2"></div>
                        <textarea name="teaching_strategies" id="teaching_strategies" rows="3"
                                  class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                                  placeholder="e.g. Direct instruction, collaborative learning, inquiry-based, differentiated instruction...">{{ old('teaching_strategies', $preConference?->teaching_strategies) }}</textarea>
                    </div>
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Instructional Materials</label>
                            <button type="button" class="things-suggest-btn shrink-0 inline-flex items-center gap-1 px-2.5 py-1.5 min-h-[36px] text-xs font-semibold text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-900/20 hover:bg-purple-100 dark:hover:bg-purple-900/30 border border-purple-200 dark:border-purple-800 rounded-lg transition-colors" data-field="instructional_materials" data-textarea="instructional_materials" data-container="things-suggest-instructional_materials">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                <span>Suggest with AI</span>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">What materials and resources will be used?</p>
                        <div id="things-suggest-instructional_materials" class="hidden mb-2"></div>
                        <textarea name="instructional_materials" id="instructional_materials" rows="2"
                                  class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                                  placeholder="e.g. PowerPoint, worksheets, manipulatives, online resources...">{{ old('instructional_materials', $preConference?->instructional_materials) }}</textarea>
                    </div>
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Assessment / Activity</label>
                            <button type="button" class="things-suggest-btn shrink-0 inline-flex items-center gap-1 px-2.5 py-1.5 min-h-[36px] text-xs font-semibold text-purple-700 dark:text-purple-300 bg-purple-50 dark:bg-purple-900/20 hover:bg-purple-100 dark:hover:bg-purple-900/30 border border-purple-200 dark:border-purple-800 rounded-lg transition-colors" data-field="assessment_activity" data-textarea="assessment_activity" data-container="things-suggest-assessment_activity">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                <span>Suggest with AI</span>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">How will learning be assessed? What activities are planned?</p>
                        <div id="things-suggest-assessment_activity" class="hidden mb-2"></div>
                        <textarea name="assessment_activity" id="assessment_activity" rows="2"
                                  class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                                  placeholder="e.g. Formative assessment, group activity, quiz, performance task...">{{ old('assessment_activity', $preConference?->assessment_activity) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm p-4 sm:p-6 border border-gray-100 dark:border-gray-800">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between sm:gap-3 mb-4">
                    <div class="flex items-start gap-2">
                        <svg class="w-4 h-4 mt-0.5 shrink-0 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Save and continue to the Classroom Observation when the conversation is complete.</span>
                    </div>
                    <p id="autosave-status" data-autosave-status class="text-xs font-medium text-gray-400 dark:text-gray-500 shrink-0"></p>
                </div>
                <div class="flex flex-col sm:flex-row sm:flex-wrap lg:flex-nowrap gap-2 sm:gap-3">
                    <button type="submit" name="save_draft" value="1" :disabled="submitting"
                            :class="submitting ? 'opacity-60 cursor-not-allowed' : ''"
                            class="flex-1 min-w-0 sm:min-w-[140px] min-h-[44px] px-3 sm:px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:bg-gray-800 font-medium text-[13px] lg:text-sm text-center leading-tight transition-colors">
                        <span x-show="!submitting" class="flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Save Draft
                        </span>
                        <span x-show="submitting" class="flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Saving...
                        </span>
                    </button>
                    <button type="submit" :disabled="submitting"
                            :class="submitting ? 'opacity-60 cursor-not-allowed' : ''"
                            class="flex-[2_2_0%] min-w-0 sm:min-w-[200px] lg:min-w-0 min-h-[44px] px-3 sm:px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold text-[13px] lg:text-sm leading-tight text-center shadow-sm transition-colors">
                        <span x-show="!submitting" class="flex items-center justify-center gap-1.5 text-center leading-tight">
                            <span class="sm:hidden lg:inline">Save &amp; Continue to Classroom Observation</span>
                            <span class="hidden sm:inline lg:hidden">Save &amp; Continue</span>
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </span>
                        <span x-show="submitting" class="flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Saving...
                        </span>
                    </button>
                    <button type="button" onclick="openCancelModal()"
                            class="sm:flex-none lg:flex-initial min-h-[44px] px-3 sm:px-4 py-2.5 rounded-lg border border-red-200 text-red-600 dark:text-red-400 hover:bg-red-50 dark:bg-red-900/20 font-medium text-[13px] lg:text-sm leading-tight text-center transition-colors">
                        Cancel Observation
                    </button>
                </div>
            </div>
        </div>

        <!-- Right Column - stacks below main content on mobile, sticky sidebar on md+ -->
        <aside class="min-w-0 space-y-4 sm:space-y-6 md:sticky md:top-6 md:self-start md:max-h-[calc(100vh-3rem)] md:overflow-y-auto sidebar-scroll">
            <!-- Teacher Information -->
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm p-4 sm:p-5 border border-gray-100 dark:border-gray-800">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider mb-3">Teacher Information</h3>
                <div class="space-y-3">
                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Name</span>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $observation->observee->user->name ?? 'Unknown' }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Position</span>
                        <p class="text-sm text-gray-900 dark:text-gray-100">{{ $observation->observee->position_label ?? 'Teacher' }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">School</span>
                        <p class="text-sm text-gray-900 dark:text-gray-100">{{ $observation->observee->school_name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Status</span>
                        <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">{{ ucfirst($observation->status) }}</span>
                    </div>
                </div>
            </div>

            <!-- Conversation Checklist -->
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm p-4 sm:p-5 border border-gray-100 dark:border-gray-800">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider mb-3">Conversation Checklist</h3>
                <ul class="space-y-2" id="agenda-checklist" data-saved="{{ json_encode($preConference?->form_responses['agenda_checklist'] ?? []) }}">
                    <li class="agenda-item flex items-center gap-2.5 min-h-[36px]">
                        <input type="checkbox" data-index="0" class="w-4 h-4 shrink-0 rounded border-gray-300 dark:border-gray-600 text-indigo-600 dark:text-indigo-400 focus:ring-indigo-500 cursor-pointer">
                        <label class="flex-1 text-xs text-gray-600 dark:text-gray-400 cursor-pointer select-none">Lesson information reviewed</label>
                    </li>
                    <li class="agenda-item flex items-center gap-2.5 min-h-[36px]">
                        <input type="checkbox" data-index="1" class="w-4 h-4 shrink-0 rounded border-gray-300 dark:border-gray-600 text-indigo-600 dark:text-indigo-400 focus:ring-indigo-500 cursor-pointer">
                        <label class="flex-1 text-xs text-gray-600 dark:text-gray-400 cursor-pointer select-none">Teaching strategies discussed</label>
                    </li>
                    <li class="agenda-item flex items-center gap-2.5 min-h-[36px]">
                        <input type="checkbox" data-index="2" class="w-4 h-4 shrink-0 rounded border-gray-300 dark:border-gray-600 text-indigo-600 dark:text-indigo-400 focus:ring-indigo-500 cursor-pointer">
                        <label class="flex-1 text-xs text-gray-600 dark:text-gray-400 cursor-pointer select-none">Instructional materials confirmed</label>
                    </li>
                    <li class="agenda-item flex items-center gap-2.5 min-h-[36px]">
                        <input type="checkbox" data-index="3" class="w-4 h-4 shrink-0 rounded border-gray-300 dark:border-gray-600 text-indigo-600 dark:text-indigo-400 focus:ring-indigo-500 cursor-pointer">
                        <label class="flex-1 text-xs text-gray-600 dark:text-gray-400 cursor-pointer select-none">Teacher concerns addressed</label>
                    </li>
                    <li class="agenda-item flex items-center gap-2.5 min-h-[36px]">
                        <input type="checkbox" data-index="4" class="w-4 h-4 shrink-0 rounded border-gray-300 dark:border-gray-600 text-indigo-600 dark:text-indigo-400 focus:ring-indigo-500 cursor-pointer">
                        <label class="flex-1 text-xs text-gray-600 dark:text-gray-400 cursor-pointer select-none">Assessment and activities confirmed</label>
                    </li>
                    <li class="agenda-item flex items-center gap-2.5 min-h-[36px]">
                        <input type="checkbox" data-index="5" class="w-4 h-4 shrink-0 rounded border-gray-300 dark:border-gray-600 text-indigo-600 dark:text-indigo-400 focus:ring-indigo-500 cursor-pointer">
                        <label class="flex-1 text-xs text-gray-600 dark:text-gray-400 cursor-pointer select-none">Observation schedule confirmed</label>
                    </li>
                </ul>
            </div>

            <!-- Observation Form Info -->
            @if($planning?->observation_tool)
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm p-4 sm:p-6 border border-gray-100 dark:border-gray-800">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider mb-3">Observation Form</h3>
                <div class="bg-blue-50 rounded-lg p-3 border border-blue-100">
                    <p class="text-sm font-medium text-blue-800">{{ str_replace('_', ' ', ucfirst($planning->observation_tool)) }}</p>
                    @if($planning->observation_tool === 'ppst')
                        <p class="text-xs text-blue-600 mt-1">5 Domains &middot; 27 Indicators</p>
                    @elseif($planning->observation_tool === 'classroom_observation_tool')
                        <p class="text-xs text-blue-600 mt-1">9 Performance Indicators</p>
                    @elseif($planning->observation_tool === 'tisuyon')
                        <p class="text-xs text-blue-600 mt-1">Peer Observation &middot; Collaborative</p>
                    @endif
                </div>
            </div>
            @endif

            <!-- Previous Observation Performance Summary -->
            @if(isset($prevStrengths) && $prevStrengths->isNotEmpty())
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm p-4 sm:p-6 border border-gray-100 dark:border-gray-800">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider mb-3">Previous Observation Performance</h3>
                <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3 border border-green-100 mb-3">
                    <p class="text-xs font-semibold text-green-800 dark:text-green-300 uppercase tracking-wider mb-2">Strengths</p>
                    <ul class="space-y-1.5">
                        @foreach($prevStrengths as $strength)
                        <li class="flex items-center justify-between text-xs">
                            <span class="text-green-700">{{ $strength->domain }}</span>
                            <span class="font-medium text-green-600 dark:text-green-400">{{ number_format($strength->avg_rating, 1) }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @if(isset($prevWeaknesses) && $prevWeaknesses->isNotEmpty())
                <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-3 border border-red-100">
                    <p class="text-xs font-semibold text-red-800 dark:text-red-300 uppercase tracking-wider mb-2">Areas for Improvement</p>
                    <ul class="space-y-1.5">
                        @foreach($prevWeaknesses as $weakness)
                        <li class="flex items-center justify-between text-xs">
                            <span class="text-red-700">{{ $weakness->domain }}</span>
                            <span class="font-medium text-red-600 dark:text-red-400">{{ number_format($weakness->avg_rating, 1) }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endif
            </div>
            @endif

            <!-- Focus Areas Summary -->
            @if($planning?->suggested_focus)
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm p-4 sm:p-6 border border-gray-100 dark:border-gray-800">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider mb-3">AI Suggested Focus</h3>
                <p class="text-sm text-gray-700 dark:text-gray-300">{{ is_array($planning->suggested_focus) ? implode(', ', $planning->suggested_focus) : $planning->suggested_focus }}</p>
            </div>
            @endif

        </aside>

    </div>
    </form>

    <!-- Bottom Navigation -->
    <div class="mt-6 sm:mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
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

<!-- Cancel Observation Modal -->
<div id="cancel-modal" class="fixed inset-0 z-50 hidden items-center justify-center modal-overlay">
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-2xl max-w-md w-full mx-4 p-6">
        <div class="flex items-center gap-2 mb-4">
            <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Cancel Observation</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">This action cannot be undone.</p>
            </div>
        </div>
        <form method="POST" action="{{ route('supervisor.observations.cancel', $observation) }}">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Reason for cancellation *</label>
                <select name="cancellation_reason" required
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-red-500 text-sm">
                    <option value="">Select a reason...</option>
                    <option value="teacher_request">Teacher Request</option>
                    <option value="supervisor_initiative">Supervisor Initiative</option>
                    <option value="conflict_in_schedule">Conflict in Schedule</option>
                    <option value="health_reason">Health Reason</option>
                    <option value="insufficient_documentation">Insufficient Documentation</option>
                    <option value="technical_issues">Technical Issues</option>
                    <option value="weather_emergency">Weather / Emergency</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="mb-4 hidden" id="other-reason-container">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Please specify</label>
                <input type="text" name="cancellation_other_reason"
                       class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-red-500 text-sm"
                       placeholder="Describe the reason...">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Internal Note (optional)</label>
                <textarea name="internal_note" rows="2"
                          class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-red-500 text-sm"
                          placeholder="Additional notes for your reference..."></textarea>
            </div>
            <p class="text-xs text-gray-400 dark:text-gray-500 mb-4">This will cancel the observation for <strong>{{ $observation->observee->user->name ?? 'this teacher' }}</strong> and notify them.</p>
            <div class="flex gap-3">
                <button type="button" onclick="closeCancelModal()"
                        class="flex-1 px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:bg-gray-800 font-medium text-sm transition-colors">
                    Keep Observation
                </button>
                <button type="submit"
                        class="flex-1 px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white font-medium text-sm transition-colors">
                    Confirm Cancellation
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
@include('partials.ai-notice')
@include('partials.ai-loading-state')
<script>
// ===================== AI Insights Actions =====================

function acceptAiInsights() {
    var text = document.getElementById('ai-insights-text');
    if (!text) return;
    var insights = text.textContent || text.innerText;
    var ts = document.getElementById('teaching_strategies');
    var aa = document.getElementById('assessment_activity');
    if (ts) ts.value = insights;
    document.getElementById('ai_insights_reviewed_input').value = '1';
    markReviewed();
    showToast('AI insights applied to Lesson Plan & Strategy.');
}

function modifyAiInsights() {
    document.getElementById('modify-ai-container').classList.remove('hidden');
    document.getElementById('modify-ai-container').scrollIntoView({ behavior: 'smooth', block: 'center' });
    document.getElementById('ai-action-buttons').classList.add('hidden');
}

function applyModifiedInsights() {
    var modified = document.getElementById('modify-ai-textarea').value;
    var textEl = document.getElementById('ai-insights-text');
    if (textEl) textEl.textContent = modified;
    var ts = document.getElementById('teaching_strategies');
    if (ts) ts.value = modified;
    document.getElementById('ai_insights_reviewed_input').value = '1';
    document.getElementById('modify-ai-container').classList.add('hidden');
    document.getElementById('ai-action-buttons').classList.remove('hidden');
    markReviewed();
    showToast('Modified insights applied.');
}

function cancelModify() {
    document.getElementById('modify-ai-container').classList.add('hidden');
    document.getElementById('ai-action-buttons').classList.remove('hidden');
}

function rejectAiInsights() {
    document.getElementById('ai-insights-panel').classList.add('hidden');
    document.getElementById('ai_insights_reviewed_input').value = '1';
    showToast('AI insights dismissed.');
}

// Empty-state template reused after clearing insights — no page reload.
// Mirrors the server-rendered #ai-empty-state above (ids/classes kept so the
// generate flow rebinds through the same global functions).
var EMPTY_AI_TEMPLATE = ''
    + '<div class="text-center py-6" id="ai-empty-state">'
    + '<svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>'
    + '<p class="text-sm text-gray-500 dark:text-gray-400 mb-2">No AI insights generated yet.</p>'
    + '<p class="text-xs text-gray-400 dark:text-gray-500 mb-4">Generate insights from the lesson plan — or skip AI entirely and complete the form below yourself.</p>'
    + '<button type="button" onclick="generateAiInsights(event)" class="generate-ai-btn w-full sm:w-auto px-4 py-2 min-h-[44px] justify-center text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 disabled:bg-purple-300 rounded-lg transition-colors inline-flex items-center gap-2">'
    + '<svg class="generate-spinner hidden w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>'
    + '<span class="generate-btn-text">Get AI Suggestions</span>'
    + '</button></div>';

// Swaps freshly generated, server-organized insights into the result slot.
// The server renders the exact same partial used on first paint, so the
// organized sections (Lesson Focus, Key Things to Watch, Pre-Conference
// Talking Points, Potential Challenges) appear instantly — no reload.
function renderGeneratedInsights(data, btn, btnText, spinner, isRegenerate) {
    hideAiLoading();
    aiGenerating = false;
    var slot = document.getElementById('ai-result-slot');
    if (slot && data.panel_html) {
        slot.innerHTML = data.panel_html;
        slot.scrollIntoView({ behavior: 'smooth', block: 'center' });
        showToast('AI suggestions ready — review the organized sections below.');
    }
    restoreAiBtn(btn, btnText, spinner, isRegenerate);
}

function clearAiInsights() {
    if (!confirm('Clear AI insights from the database? This cannot be undone.')) return;

    fetch('{{ route("supervisor.observations.clear-ai-insights", $observation) }}', {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
        },
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (data.success) {
            var slot = document.getElementById('ai-result-slot');
            if (slot) slot.innerHTML = EMPTY_AI_TEMPLATE;
            var reviewed = document.getElementById('ai_insights_reviewed_input');
            if (reviewed) reviewed.value = '0';
            showToast('AI insights cleared.');
        }
    })
    .catch(function(err) {
        alert('Failed to clear AI insights.');
        console.error(err);
    });
}

function useAiSuggestions(btn) {
    if (btn) { btn.disabled = true; btn.classList.add('opacity-60'); }
    AINotice.hide(document.getElementById('suggestions-notice'));
    AINotice.hide(document.getElementById('ai-panel-notice'));

    fetch('{{ route("supervisor.observations.generate-ai-suggestions", $observation) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
        },
    })
    .then(function(res) {
        return res.json().catch(function() { return {}; }).then(function(data) { return { ok: res.ok, data: data }; });
    })
    .then(function(result) {
        if (result.ok && (result.data.discussion_notes || result.data.finalized_focus)) {
            var ts = document.getElementById('teaching_strategies');
            var text = result.data.discussion_notes || result.data.finalized_focus;
            if (ts) ts.value = text;
            document.getElementById('ai_insights_reviewed_input').value = '1';
            showToast('AI suggestions applied to Teaching Strategies — review and edit freely.');
        } else {
            AINotice.show('suggestions-notice', result.data, {
                onManual: focusManualEntry,
                manualLabel: 'Write them myself',
            });
        }
    })
    .catch(function() {
        AINotice.show('suggestions-notice', { error: 'AI isn\'t available because your connection to the server was interrupted. Please try again.' }, {
            onManual: focusManualEntry,
            manualLabel: 'Write them myself',
        });
    })
    .finally(function() {
        if (btn) { btn.disabled = false; btn.classList.remove('opacity-60'); }
    });
}

function focusManualEntry() {
    var ts = document.getElementById('teaching_strategies');
    document.getElementById('suggestions-notice').scrollIntoView({ behavior: 'smooth', block: 'center' });
    if (ts) ts.focus();
    showToast('No problem — you can write the teaching strategies yourself.');
}

function markReviewed() {
    var panel = document.getElementById('ai-insights-panel');
    if (panel) panel.classList.add('opacity-75');
}

// ===================== Things to Think About — per-field AI suggestions =====================
// Advisory only: suggestions are tailored to the uploaded lesson plan and
// PPST cues, but the supervisor picks what (if anything) goes in the box.

function renderThingsSuggestions(container, payload, textareaId) {
    container.innerHTML = '';
    container.classList.remove('hidden');

    var card = document.createElement('div');
    card.className = 'rounded-xl border border-purple-200 dark:border-purple-800 bg-purple-50/60 dark:bg-purple-900/10 p-3 space-y-2';

    var head = document.createElement('div');
    head.className = 'flex items-center gap-2';
    var badge = document.createElement('span');
    badge.className = 'text-[11px] font-semibold px-2 py-0.5 rounded-full ' +
        (payload.fallback
            ? 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300'
            : 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300');
    badge.textContent = payload.fallback ? 'Built-in guide' : 'AI suggestions';
    head.appendChild(badge);
    var hint = document.createElement('span');
    hint.className = 'text-[11px] text-gray-500 dark:text-gray-400';
    hint.textContent = 'Advisory only — tap Use to add, then review and edit freely.';
    head.appendChild(hint);
    card.appendChild(head);

    if (payload.analysis) {
        var analysis = document.createElement('p');
        analysis.className = 'text-xs text-gray-600 dark:text-gray-300 leading-relaxed italic';
        analysis.textContent = payload.analysis;
        card.appendChild(analysis);
    }

    var list = document.createElement('ul');
    list.className = 'space-y-1.5';
    (payload.suggestions || []).forEach(function (text) {
        var li = document.createElement('li');
        li.className = 'flex items-start gap-2 rounded-lg bg-white dark:bg-gray-900 border border-purple-100 dark:border-purple-900/40 px-2.5 py-2';
        var span = document.createElement('span');
        span.className = 'flex-1 text-xs text-gray-700 dark:text-gray-200 leading-relaxed';
        span.textContent = text;
        var useBtn = document.createElement('button');
        useBtn.type = 'button';
        useBtn.className = 'shrink-0 px-2.5 py-1 min-h-[32px] text-[11px] font-semibold text-white bg-purple-600 hover:bg-purple-700 rounded-lg transition-colors';
        useBtn.textContent = 'Use';
        useBtn.addEventListener('click', function () {
            var ta = document.getElementById(textareaId);
            if (!ta) return;
            var current = ta.value.trim();
            ta.value = current ? current + '\n' + text : text;
            ta.dispatchEvent(new Event('input', { bubbles: true }));
            ta.dispatchEvent(new Event('change', { bubbles: true }));
            showToast('Suggestion added — review and edit freely.');
        });
        li.appendChild(span);
        li.appendChild(useBtn);
        list.appendChild(li);
    });
    card.appendChild(list);
    container.appendChild(card);
    container.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

document.querySelectorAll('.things-suggest-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var field = btn.dataset.field;
        var textareaId = btn.dataset.textarea;
        var container = document.getElementById(btn.dataset.container);
        if (!field || !container) return;

        var label = btn.querySelector('span');
        var originalLabel = label ? label.textContent : '';
        btn.disabled = true;
        if (label) label.textContent = 'Thinking…';
        AINotice.hide(document.getElementById('suggestions-notice'));

        fetch('{{ route("supervisor.observations.generate-things-suggestions", $observation) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ field: field }),
        })
        .then(function (res) {
            return res.json().catch(function () { return {}; }).then(function (data) { return { ok: res.ok, data: data }; });
        })
        .then(function (result) {
            if (result.ok && result.data.suggestions && result.data.suggestions.length) {
                renderThingsSuggestions(container, result.data, textareaId);
            } else {
                AINotice.show('suggestions-notice', result.data, {
                    onManual: focusManualEntry,
                    manualLabel: 'Write them myself',
                    onRetry: function () { btn.click(); },
                });
            }
        })
        .catch(function () {
            AINotice.show('suggestions-notice', { error: 'AI isn\'t available because your connection to the server was interrupted. Please try again.' }, {
                onManual: focusManualEntry,
                manualLabel: 'Write them myself',
                onRetry: function () { btn.click(); },
            });
        })
        .finally(function () {
            btn.disabled = false;
            if (label) label.textContent = originalLabel;
        });
    });
});

// ===================== Regenerate AI Insights =====================

var aiPanelBody = document.getElementById('ai-panel-body');
var aiGenerating = false;

function showAiLoading(message) {
    if (aiPanelBody) {
        var card = aiPanelBody.querySelector('.insight-card');
        var actions = document.getElementById('ai-action-buttons');
        var empty = document.getElementById('ai-empty-state');
        if (card) card.style.display = 'none';
        if (actions) actions.style.display = 'none';
        if (empty) empty.style.display = 'none';
    }
    AiLoading.start(aiPanelBody || document.getElementById('ai-panel-notice'), message, 'Reviewing the lesson plan and previous observations\u2026');
}

function hideAiLoading() {
    AiLoading.stop();
    if (aiPanelBody) {
        var card = aiPanelBody.querySelector('.insight-card');
        var actions = document.getElementById('ai-action-buttons');
        var empty = document.getElementById('ai-empty-state');
        if (card) card.style.display = '';
        if (actions) actions.style.display = '';
        if (empty) empty.style.display = '';
    }
}

function restoreAiBtn(btn, btnText, spinner, isRegenerate) {
    if (btn) btn.disabled = false;
    if (spinner) spinner.classList.add('hidden');
    if (btnText) btnText.textContent = isRegenerate ? 'Regenerate' : 'Get AI Suggestions';
}

document.getElementById('regenerate-ai-btn')?.addEventListener('click', function(e) {
    generateAiInsights(e, true);
});

function generateAiInsights(e, isRegenerate) {
    if (aiGenerating) return;
    aiGenerating = true;

    var btn = (e && e.currentTarget) || document.querySelector('.generate-ai-btn');
    var spinner = btn ? btn.querySelector('.generate-spinner, #regenerate-spinner') : document.getElementById('regenerate-spinner');
    var btnText = btn ? btn.querySelector('.generate-btn-text, #ai-btn-text') : null;

    if (btn) btn.disabled = true;
    if (spinner) spinner.classList.remove('hidden');
    if (btnText) btnText.textContent = isRegenerate ? 'Regenerating\u2026' : 'Generating\u2026';
    AINotice.hide(document.getElementById('ai-panel-notice'));
    showAiLoading(isRegenerate ? 'Regenerating AI insights' : 'Generating AI insights');

    fetch('{{ route("supervisor.observations.generate-ai-insights", $observation) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ mode: window.aiEngineSelectedMode ? window.aiEngineSelectedMode() : 'auto' }),
    })
    .then(function(res) {
        return res.json().catch(function() { return {}; }).then(function(data) { return { ok: res.ok, status: res.status, data: data }; });
    })
    .then(function(result) {
        if (result.ok && result.data.ai_insights) {
            renderGeneratedInsights(result.data, btn, btnText, spinner, isRegenerate);
            return;
        }
        if (result.status === 202 && result.data.status === 'processing') {
            pollAiInsightsStatus(btn, btnText, spinner, isRegenerate);
            return;
        }
        hideAiLoading();
        aiGenerating = false;
        restoreAiBtn(btn, btnText, spinner, isRegenerate);
        AINotice.show('ai-panel-notice', result.data, {
            onManual: function () {
                showToast('No problem — continue with the form below; AI insights are optional.');
                var ts = document.getElementById('teaching_strategies');
                if (ts) ts.focus();
            },
            onRetry: function() { generateAiInsights(null, isRegenerate); },
            manualLabel: 'Continue without AI',
        });
    })
    .catch(function() {
        hideAiLoading();
        aiGenerating = false;
        restoreAiBtn(btn, btnText, spinner, isRegenerate);
        AINotice.show('ai-panel-notice', { error: 'AI isn\'t available because your connection to the server was interrupted. Please try again.' }, {
            onManual: function () {
                showToast('No problem — continue with the form below; AI insights are optional.');
                var ts = document.getElementById('teaching_strategies');
                if (ts) ts.focus();
            },
            onRetry: function() { generateAiInsights(null, isRegenerate); },
            manualLabel: 'Continue without AI',
        });
    });
}

function pollAiInsightsStatus(btn, btnText, spinner, isRegenerate) {
    var attempts = 0;
    var maxAttempts = 60; // 5 minutes max (5s intervals)

    function check() {
        attempts++;
        if (attempts > maxAttempts) {
            hideAiLoading();
            aiGenerating = false;
            restoreAiBtn(btn, btnText, spinner, isRegenerate);
            AINotice.show('ai-panel-notice', { error: 'AI insights are taking longer than expected. Please try again later.' }, {
                onRetry: function() { generateAiInsights(null, isRegenerate); },
                manualLabel: 'Continue without AI',
                onManual: function () {
                    showToast('No problem — continue with the form below; AI insights are optional.');
                    var ts = document.getElementById('teaching_strategies');
                    if (ts) ts.focus();
                },
            });
            return;
        }

        fetch('{{ route("supervisor.observations.ai-insights-status", $observation) }}', {
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.status === 'completed' && data.ai_insights) {
                renderGeneratedInsights(data, btn, btnText, spinner, isRegenerate);
            } else {
                setTimeout(check, 5000);
            }
        })
        .catch(function() {
            setTimeout(check, 5000);
        });
    }

    setTimeout(check, 3000);
}

// ===================== Copy to Clipboard =====================

function copyToClipboard(btn, elementId) {
    var el = document.getElementById(elementId);
    if (!el) return;
    var text = el.textContent || el.innerText;
    navigator.clipboard.writeText(text).then(function() {
        var original = btn.innerHTML;
        btn.innerHTML = '<svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.5 12.75l6 6 9-13.5"/></svg>';
        setTimeout(function() { btn.innerHTML = original; }, 2000);
    }).catch(function() {
        var ta = document.createElement('textarea');
        ta.value = text;
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        alert('Copied!');
    });
}

// ===================== Cancel Modal =====================

function openCancelModal() {
    document.getElementById('cancel-modal').classList.remove('hidden');
    document.getElementById('cancel-modal').classList.add('flex');
}

function closeCancelModal() {
    document.getElementById('cancel-modal').classList.add('hidden');
    document.getElementById('cancel-modal').classList.remove('flex');
}

document.querySelector('[name="cancellation_reason"]')?.addEventListener('change', function() {
    var container = document.getElementById('other-reason-container');
    if (container) {
        container.classList.toggle('hidden', this.value !== 'other');
    }
});

document.getElementById('cancel-modal')?.addEventListener('click', function(e) {
    if (e.target === this) closeCancelModal();
});

// ===================== Form Validation =====================

document.getElementById('pre-conference-form')?.addEventListener('submit', function(e) {
    var date = document.getElementById('conference_date');
    if (date && !date.value) {
        alert('Please select a conference date for your records, but you may continue.');
        // Allow submission to proceed - the controller will handle stage advancement
    }
});

// ===================== Toast Notification =====================

function showToast(message) {
    var existing = document.querySelector('.toast-notification');
    if (existing) existing.remove();

    var toast = document.createElement('div');
    toast.className = 'toast-notification fixed bottom-6 right-6 z-50 bg-gray-900 text-white px-5 py-3 rounded-lg shadow-xl text-sm font-medium animate-bounce';
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(function() {
        toast.style.transition = 'opacity 0.5s';
        toast.style.opacity = '0';
        setTimeout(function() { toast.remove(); }, 500);
    }, 3000);
}
</script>
@include('partials.autosave')
<script>
    (function () {
        var form = document.getElementById('pre-conference-form');
        var status = document.getElementById('autosave-status');
        if (form && window.asAutoSave) {
            asAutoSave(form, 'pre_conference', status, { wait: 1200 });
        }
    })();
</script>
<script>
    (function () {
        var list = document.getElementById('agenda-checklist');
        if (!list) return;

        var saved = [];
        try { saved = JSON.parse(list.dataset.saved || '[]'); } catch (e) {}

        var checkboxes = list.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(function (cb) {
            var idx = parseInt(cb.dataset.index, 10);
            if (saved.indexOf(idx) !== -1) cb.checked = true;
        });

        var token = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
        var url = '{{ route("supervisor.observations.agenda-checklist", $observation) }}';
        var timer = null;

        function collectChecked() {
            var checked = [];
            checkboxes.forEach(function (cb) {
                if (cb.checked) checked.push(parseInt(cb.dataset.index, 10));
            });
            return checked;
        }

        function save() {
            clearTimeout(timer);
            timer = setTimeout(function () {
                fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                    body: JSON.stringify({ checked: collectChecked() }),
                });
            }, 400);
        }

        list.addEventListener('change', function (e) {
            if (e.target.type === 'checkbox') save();
        });
    })();
</script>
@endpush
@endsection