@extends('layouts.supervisor')

@section('title', 'Pre-Conference')

@push('styles')
<style>
    select option { background-color: #1f2937; color: #ffffff; }
    .ai-panel-enter { animation: slideDown 0.3s ease-out; }
    @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    .sticky-sidebar { position: sticky; top: 1.5rem; }
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
    $stageKeys = ['pre_observation_planning', 'pre_conference', 'observation', 'post_conference'];
    $stageLabels = [
        'pre_observation_planning' => 'Pre-Observation Planning',
        'pre_conference' => 'Pre-Conference',
        'observation' => 'Observation',
        'post_conference' => 'Post-Conference',
    ];
    $stageRoutes = [
        'pre_observation_planning' => 'supervisor.observations.preObservationPlanning',
        'pre_conference' => 'supervisor.observations.preConference',
        'observation' => 'supervisor.observations.observation',
        'post_conference' => 'supervisor.observations.postConference',
    ];
    $currentStage = 'pre_conference';
    $currentIdx = array_search($currentStage, $stageKeys);
    $aiInsights = $planning?->ai_insights;
    $aiReviewed = $planning?->ai_insights_reviewed ?? false;
    $lessonPlanMissing = $planning && !$planning->lesson_plan_file;
@endphp

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8">
    <!-- Breadcrumb -->
    <nav class="mb-6 text-sm">
        <ol class="flex items-center gap-2 text-gray-500">
            <li><a href="{{ route('supervisor.observations.index') }}" class="hover:text-indigo-600 transition-colors">Evaluations</a></li>
            <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"/></svg></li>
            <li><a href="{{ route('supervisor.observations.show', $observation) }}" class="hover:text-indigo-600 transition-colors">Observation Details</a></li>
            <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"/></svg></li>
            <li class="text-gray-900 font-medium">Pre-Conference</li>
        </ol>
    </nav>

    <!-- Progress Steps -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            @foreach($stageKeys as $i => $key)
                @php
                    $isCurrent = $key === $currentStage;
                    $isCompleted = $i < $currentIdx;
                    $canAccess = $isCurrent || $isCompleted;
                @endphp
                @if($i > 0)
                    <div class="flex-1 mx-4 h-1 {{ $isCompleted ? 'bg-green-400' : 'bg-gray-200' }}"></div>
                @endif
                @if($canAccess)
                    <a href="{{ $isCurrent ? '#' : route($stageRoutes[$key], $observation) }}"
                       class="flex items-center group {{ $isCurrent ? 'cursor-default' : 'cursor-pointer' }}">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full {{ $isCompleted ? 'bg-green-600 text-white' : 'bg-indigo-600 text-white ring-2 ring-indigo-200' }} font-semibold transition-colors group-hover:shadow-md text-sm">
                            @if($isCompleted)
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            @else
                                {{ $i + 1 }}
                            @endif
                        </div>
                        <span class="ml-2 {{ $isCompleted ? 'text-gray-600' : 'text-gray-900 font-medium' }} text-sm group-hover:text-indigo-600 transition-colors">{{ $stageLabels[$key] }}</span>
                    </a>
                @else
                    <div class="flex items-center opacity-50">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-200 text-gray-400 font-semibold text-sm">{{ $i + 1 }}</div>
                        <span class="ml-2 text-gray-400 text-sm">{{ $stageLabels[$key] }}</span>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Pre-Conference</h1>
        <p class="text-gray-500 mt-1">{{ $observation->observee->user->name ?? 'Unknown' }} &middot; {{ $observation->observation_date->format('M d, Y') }}</p>
    </div>

    @if($lessonPlanMissing)
    <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-lg flex items-start gap-3">
        <svg class="w-5 h-5 text-amber-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
        <div>
            <p class="text-sm font-medium text-amber-800">Lesson Plan Not Uploaded</p>
            <p class="text-xs text-amber-700 mt-1">The teacher has not uploaded a lesson plan for this observation. Consider requesting one before the pre-conference meeting.</p>
        </div>
    </div>
    @endif

    <form method="POST" action="{{ route('supervisor.observations.storePreConference', $observation) }}" id="pre-conference-form" class="space-y-6">
    @csrf
    <input type="hidden" name="ai_insights_reviewed" id="ai_insights_reviewed_input" value="{{ $aiReviewed ? '1' : '0' }}">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left Column -->
        <div class="lg:col-span-2 space-y-6">

            <!-- AI Pre-Observation Insights Panel -->
            <div id="ai-insights-panel"
                 class="bg-white rounded-xl shadow-sm border border-purple-200 ai-panel-enter {{ $aiReviewed ? 'opacity-75' : '' }}">
                <div class="flex items-center justify-between p-4 border-b border-purple-100 bg-gradient-to-r from-purple-50 to-indigo-50 rounded-t-xl">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                        <h2 class="text-lg font-semibold text-gray-900">AI Pre-Observation Insights</h2>
                        <span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full font-medium">AI-Powered</span>
                        @if($aiReviewed)
                            <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-medium">Reviewed</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" id="regenerate-ai-btn"
                                class="px-3 py-1.5 text-xs font-medium text-purple-700 bg-purple-100 hover:bg-purple-200 rounded-lg transition-colors inline-flex items-center gap-1.5 disabled:opacity-50">
                            <svg id="regenerate-spinner" class="hidden w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span class="generate-btn-text">Regenerate</span>
                        </button>
                    </div>
                </div>

                <div class="p-4 space-y-4">
                    <div class="flex items-start gap-2 text-xs text-gray-500 bg-gray-50 rounded-lg p-3 border border-gray-200">
                        <svg class="w-4 h-4 text-gray-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p>AI-generated analysis of the submitted lesson plan only. Review and customize before finalizing observation focus areas.</p>
                    </div>

                    @if($aiInsights)
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 insight-card">
                            <div class="flex items-start justify-between gap-3">
                                <div class="prose prose-sm max-w-none text-gray-700 whitespace-pre-wrap flex-1" id="ai-insights-text">{{ is_array($aiInsights) ? (json_encode($aiInsights) ?: '') : $aiInsights }}</div>
                                <button type="button" onclick="copyToClipboard(this, 'ai-insights-text')"
                                        class="copy-btn p-1.5 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-200 transition-colors shrink-0" title="Copy AI Insights">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                </button>
                            </div>
                        </div>

                        <!-- AI Action Buttons -->
                        <div class="flex flex-wrap items-center gap-2" id="ai-action-buttons">
                            <button type="button" onclick="acceptAiInsights()"
                                    class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                Accept &amp; Apply
                            </button>
                            <button type="button" onclick="modifyAiInsights()"
                                    class="px-4 py-2 text-sm font-medium text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200 rounded-lg transition-colors inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                                Modify
                            </button>
                            <button type="button" onclick="rejectAiInsights()"
                                    class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-red-600 bg-gray-50 hover:bg-red-50 border border-gray-200 hover:border-red-200 rounded-lg transition-colors inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                Dismiss
                            </button>
                            <button type="button" id="clear-ai-insights-btn"
                                    class="px-4 py-2 text-sm font-medium text-red-600 hover:text-white bg-red-50 hover:bg-red-600 border border-red-200 hover:border-red-600 rounded-lg transition-colors inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                Clear
                            </button>
                        </div>

                        <!-- Modify AI Textarea (hidden by default) -->
                        <div id="modify-ai-container" class="hidden space-y-3">
                            <textarea id="modify-ai-textarea" rows="6"
                                      class="w-full px-3 py-2 rounded-lg border border-amber-300 text-gray-900 focus:outline-none focus:ring-2 focus:ring-amber-500 text-sm">{{ is_array($aiInsights) ? (json_encode($aiInsights) ?: '') : $aiInsights }}</textarea>
                            <div class="flex gap-2">
                                <button type="button" onclick="applyModifiedInsights()"
                                        class="px-4 py-2 text-sm font-medium text-white bg-amber-600 hover:bg-amber-700 rounded-lg transition-colors">Apply Modified Insights</button>
                                <button type="button" onclick="cancelModify()"
                                        class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">Cancel</button>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-6">
                            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                            <p class="text-sm text-gray-500 mb-2">No AI insights generated yet.</p>
                            <p class="text-xs text-gray-400 mb-4">Generate insights from the lesson plan to help focus the pre-conference discussion.</p>
                            <button type="button" onclick="generateAiInsights(event)"
                                    class="generate-ai-btn px-4 py-2 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 disabled:bg-purple-300 rounded-lg transition-colors inline-flex items-center gap-2">
                                <svg class="generate-spinner hidden w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                <span class="generate-btn-text">Generate AI Insights</span>
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Pre-Observation Planning Summary (collapsible) -->
            @if($planning)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100" x-data="{ open: {{ $observation->stage === 'pre_conference' ? 'true' : 'false' }} }">
                <button type="button" @click="open = !open"
                        class="w-full flex items-center justify-between p-4 text-left">
                    <h2 class="text-lg font-semibold text-gray-900">Pre-Observation Planning Summary</h2>
                    <svg class="w-5 h-5 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" x-collapse>
                    <div class="p-4 pt-0 border-t border-gray-100">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @if($planning->lesson_plan_file)
                            <div class="bg-blue-50 rounded-lg p-3 border border-blue-100">
                                <span class="text-xs text-gray-500 uppercase tracking-wider font-medium">Lesson Plan</span>
                                <div class="mt-1 flex items-center justify-between">
                                    <span class="text-sm text-gray-700 truncate">{{ preg_replace('/^\d+_/', '', basename($planning->lesson_plan_file)) }}</span>
                                    <a href="{{ asset('storage/' . $planning->lesson_plan_file) }}" target="_blank" class="text-sm text-blue-600 hover:text-blue-700 font-medium shrink-0 ml-2">View</a>
                                </div>
                            </div>
                            @endif
                            @if($planning->supervisor_notes)
                            <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                                <span class="text-xs text-gray-500 uppercase tracking-wider font-medium">Supervisor Notes</span>
                                <p class="text-sm text-gray-700 mt-1">{{ Str::limit($planning->supervisor_notes, 120) }}</p>
                            </div>
                            @endif
                            @if($planning->suggested_focus)
                            <div class="bg-purple-50 rounded-lg p-3 border border-purple-100">
                                <span class="text-xs text-gray-500 uppercase tracking-wider font-medium">Suggested Focus</span>
                                <p class="text-sm text-gray-700 mt-1">{{ Str::limit($planning->suggested_focus, 120) }}</p>
                            </div>
                            @endif
                            @if($planning->observation_tool)
                            <div class="bg-green-50 rounded-lg p-3 border border-green-100">
                                <span class="text-xs text-gray-500 uppercase tracking-wider font-medium">Observation Tool</span>
                                <p class="text-sm text-gray-700 mt-1 font-medium">{{ str_replace('_', ' ', ucfirst($planning->observation_tool)) }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Section 1: Conference Schedule -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Conference Schedule</h2>
                    <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-full font-medium">Pre-Conference</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Teacher</label>
                        <p class="text-gray-900 font-semibold">{{ $observation->observee->user->name ?? 'Unknown' }}</p>
                        <p class="text-sm text-gray-500">{{ $observation->observee->position ?? 'Teacher' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Subject / Grade</label>
                        <p class="text-gray-900 font-semibold">{{ $observation->subject ?? 'N/A' }}</p>
                        <p class="text-sm text-gray-500">Grade {{ $observation->grade_level ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Observation Date</label>
                        <p class="text-gray-900 font-semibold">{{ $observation->observation_date->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="conference_date">Pre-Conference Date *</label>
                        <input type="date" name="conference_date" id="conference_date"
                               value="{{ old('conference_date', $preConference?->conference_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                               class="w-full px-3 py-2 rounded-lg bg-white border border-gray-300 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" required>
                        @error('conference_date')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Section 2: Lesson Plan Review & Instructional Materials -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Lesson Plan Review & Instructional Materials</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Lesson Plan Review</label>
                        <p class="text-xs text-gray-500 mb-2">Review the lesson plan objectives, activities, and assessment strategies.</p>
                        <textarea name="lesson_plan_review" rows="3"
                                  class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                                  placeholder="Summarize key points from the lesson plan review...">{{ old('lesson_plan_review', $preConference?->lesson_plan_review) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Instructional Materials</label>
                        <p class="text-xs text-gray-500 mb-2">List the instructional materials, resources, and ICT tools to be used.</p>
                        <textarea name="instructional_materials" rows="3"
                                  class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                                  placeholder="e.g. PowerPoint presentation, worksheets, manipulatives, online resources...">{{ old('instructional_materials', $preConference?->instructional_materials) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Section 3: Discussion Notes & Finalized Focus -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Discussion & Finalized Focus</h2>
                <div class="space-y-4">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-sm font-medium text-gray-700">Pre-Conference Discussion Notes</label>
                            <button type="button" onclick="useAiSuggestions()"
                                    class="text-xs font-medium text-purple-600 hover:text-purple-700 bg-purple-50 hover:bg-purple-100 px-2 py-1 rounded transition-colors inline-flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                Use AI Suggestions
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mb-2">Document key discussion points including teaching strategies, learner diversity considerations, and assessment methods.</p>
                        <textarea name="discussion_notes" id="discussion_notes" rows="5"
                                  class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                                  placeholder="Document the key discussion points from the pre-conference meeting...">{{ old('discussion_notes', $preConference?->discussion_notes) }}</textarea>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-sm font-medium text-gray-700">Finalized Observation Focus *</label>
                            <button type="button" onclick="useAiSuggestions()"
                                    class="text-xs font-medium text-purple-600 hover:text-purple-700 bg-purple-50 hover:bg-purple-100 px-2 py-1 rounded transition-colors inline-flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                Use AI Suggestions
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 mb-2">Areas agreed upon to be the focus of the classroom observation.</p>
                        <textarea name="finalized_focus" id="finalized_focus" rows="4"
                                  class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                                  placeholder="e.g. Learner engagement strategies, differentiated instruction, classroom management..." required>{{ old('finalized_focus', $preConference?->finalized_focus) }}</textarea>
                        @error('finalized_focus')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Section 4: Teacher Reflection -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Teacher Reflection</h2>
                <p class="text-xs text-gray-500 mb-2">The teacher's self-reflection on their lesson plan and anticipated challenges.</p>
                <textarea name="teacher_reflection" rows="4"
                          class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                          placeholder="Teacher's reflection on the lesson plan, teaching strategies, and expected outcomes...">{{ old('teacher_reflection', $preConference?->teacher_reflection) }}</textarea>
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center gap-2 mb-4">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-xs text-gray-500">Save and continue to the Observation stage when the pre-conference is complete.</span>
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="submit" name="save_draft" value="1"
                            class="flex-1 px-4 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium text-sm text-center transition-colors">
                        <span class="flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Save Draft
                        </span>
                    </button>
                    <button type="submit"
                            class="flex-[2] px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold text-sm shadow-sm transition-colors">
                        <span class="flex items-center justify-center gap-2">
                            Save &amp; Continue to Observation
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </span>
                    </button>
                    <button type="button" onclick="openCancelModal()"
                            class="px-4 py-2.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 font-medium text-sm transition-colors">
                        Cancel Observation
                    </button>
                </div>
            </div>
        </div>

        <!-- Right Column - Sticky Sidebar -->
        <div class="space-y-6 sticky-sidebar">
            <div class="space-y-6">
                <!-- Teacher Info Card -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-3">Teacher Information</h3>
                    <div class="space-y-3">
                        <div>
                            <span class="text-xs text-gray-500">Name</span>
                            <p class="text-sm font-medium text-gray-900">{{ $observation->observee->user->name ?? 'Unknown' }}</p>
                        </div>
                        <div>
                            <span class="text-xs text-gray-500">Position</span>
                            <p class="text-sm text-gray-900">{{ $observation->observee->position ?? 'Teacher' }}</p>
                        </div>
                        <div>
                            <span class="text-xs text-gray-500">School</span>
                            <p class="text-sm text-gray-900">{{ $observation->observee->school->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <span class="text-xs text-gray-500">Status</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">{{ ucfirst($observation->status) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Observation Tool Info -->
                @if($planning?->observation_tool)
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-3">Observation Tool</h3>
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

                <!-- Previous COT Performance Summary -->
                @if(isset($prevStrengths) && $prevStrengths->isNotEmpty())
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-3">Previous COT Performance</h3>
                    <div class="bg-green-50 rounded-lg p-3 border border-green-100 mb-3">
                        <p class="text-xs font-semibold text-green-800 uppercase tracking-wider mb-2">Strengths</p>
                        <ul class="space-y-1.5">
                            @foreach($prevStrengths as $strength)
                            <li class="flex items-center justify-between text-xs">
                                <span class="text-green-700">{{ $strength->domain }}</span>
                                <span class="font-medium text-green-600">{{ number_format($strength->avg_rating, 1) }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @if(isset($prevWeaknesses) && $prevWeaknesses->isNotEmpty())
                    <div class="bg-red-50 rounded-lg p-3 border border-red-100">
                        <p class="text-xs font-semibold text-red-800 uppercase tracking-wider mb-2">Areas for Improvement</p>
                        <ul class="space-y-1.5">
                            @foreach($prevWeaknesses as $weakness)
                            <li class="flex items-center justify-between text-xs">
                                <span class="text-red-700">{{ $weakness->domain }}</span>
                                <span class="font-medium text-red-600">{{ number_format($weakness->avg_rating, 1) }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
                @endif

                <!-- Focus Areas Summary -->
                @if($planning?->suggested_focus)
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-3">Focus Areas</h3>
                    <p class="text-sm text-gray-700">{{ $planning->suggested_focus }}</p>
                </div>
                @endif

                <!-- Agenda Checklist -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-3">Pre-Conference Agenda</h3>
                    <ul class="space-y-2.5" id="agenda-checklist">
                        <li class="agenda-item flex items-start gap-2.5">
                            <input type="checkbox" class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                            <label class="text-xs text-gray-600 cursor-pointer select-none">Review lesson plan objectives and alignment</label>
                        </li>
                        <li class="agenda-item flex items-start gap-2.5">
                            <input type="checkbox" class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                            <label class="text-xs text-gray-600 cursor-pointer select-none">Discuss teaching strategies and methodologies</label>
                        </li>
                        <li class="agenda-item flex items-start gap-2.5">
                            <input type="checkbox" class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                            <label class="text-xs text-gray-600 cursor-pointer select-none">Address learner diversity considerations</label>
                        </li>
                        <li class="agenda-item flex items-start gap-2.5">
                            <input type="checkbox" class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                            <label class="text-xs text-gray-600 cursor-pointer select-none">Review instructional materials and resources</label>
                        </li>
                        <li class="agenda-item flex items-start gap-2.5">
                            <input type="checkbox" class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                            <label class="text-xs text-gray-600 cursor-pointer select-none">Discuss assessment methods and feedback</label>
                        </li>
                        <li class="agenda-item flex items-start gap-2.5">
                            <input type="checkbox" class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                            <label class="text-xs text-gray-600 cursor-pointer select-none">Agree on observation focus areas</label>
                        </li>
                        <li class="agenda-item flex items-start gap-2.5">
                            <input type="checkbox" class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                            <label class="text-xs text-gray-600 cursor-pointer select-none">Address teacher concerns and questions</label>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

    </div>
    </form>

    <!-- Bottom Navigation -->
    <div class="mt-8 pt-6 border-t border-gray-200">
        <div class="flex items-center justify-between">
            <a href="{{ route('supervisor.observations.show', $observation) }}"
               class="inline-flex items-center gap-2 px-4 py-2 text-sm text-gray-600 hover:text-gray-900 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Details
            </a>
            <a href="{{ route('supervisor.observations.index') }}"
               class="text-sm text-gray-400 hover:text-gray-600 transition-colors">
                All Evaluations
            </a>
        </div>
    </div>
</div>

<!-- Cancel Observation Modal -->
<div id="cancel-modal" class="fixed inset-0 z-50 hidden items-center justify-center modal-overlay">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full mx-4 p-6">
        <div class="flex items-center gap-2 mb-4">
            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Cancel Observation</h3>
                <p class="text-sm text-gray-500">This action cannot be undone.</p>
            </div>
        </div>
        <form method="POST" action="{{ route('supervisor.observations.cancel', $observation) }}">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Reason for cancellation *</label>
                <select name="cancellation_reason" required
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 focus:outline-none focus:ring-2 focus:ring-red-500 text-sm">
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
                <label class="block text-sm font-medium text-gray-700 mb-1">Please specify</label>
                <input type="text" name="cancellation_other_reason"
                       class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 focus:outline-none focus:ring-2 focus:ring-red-500 text-sm"
                       placeholder="Describe the reason...">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Internal Note (optional)</label>
                <textarea name="internal_note" rows="2"
                          class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 focus:outline-none focus:ring-2 focus:ring-red-500 text-sm"
                          placeholder="Additional notes for your reference..."></textarea>
            </div>
            <p class="text-xs text-gray-400 mb-4">This will cancel the observation for <strong>{{ $observation->observee->user->name ?? 'this teacher' }}</strong> and notify them.</p>
            <div class="flex gap-3">
                <button type="button" onclick="closeCancelModal()"
                        class="flex-1 px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium text-sm transition-colors">
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
<script>
// ===================== AI Insights Actions =====================

function acceptAiInsights() {
    const text = document.getElementById('ai-insights-text');
    if (!text) return;
    const insights = text.textContent || text.innerText;
    document.getElementById('discussion_notes').value = insights;
    document.getElementById('finalized_focus').value = extractFocusAreas(insights);
    document.getElementById('ai_insights_reviewed_input').value = '1';
    markReviewed();
    showToast('AI insights applied to Discussion Notes and Finalized Focus.');
}

function modifyAiInsights() {
    document.getElementById('modify-ai-container').classList.remove('hidden');
    document.getElementById('modify-ai-container').scrollIntoView({ behavior: 'smooth', block: 'center' });
    document.getElementById('ai-action-buttons').classList.add('hidden');
}

function applyModifiedInsights() {
    const modified = document.getElementById('modify-ai-textarea').value;
    const textEl = document.getElementById('ai-insights-text');
    if (textEl) textEl.textContent = modified;
    document.getElementById('discussion_notes').value = modified;
    document.getElementById('finalized_focus').value = extractFocusAreas(modified);
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

document.getElementById('clear-ai-insights-btn')?.addEventListener('click', function() {
    if (!confirm('Clear AI insights from the database? This cannot be undone.')) return;

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
            location.reload();
        }
    })
    .catch(err => {
        alert('Failed to clear AI insights.');
        console.error(err);
    });
});

function useAiSuggestions() {
    fetch('{{ route("supervisor.observations.generate-ai-suggestions", $observation) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
        },
    })
    .then(res => res.json())
    .then(data => {
        if (data.discussion_notes) {
            document.getElementById('discussion_notes').value = data.discussion_notes;
        }
        if (data.finalized_focus) {
            document.getElementById('finalized_focus').value = data.finalized_focus;
        }
        document.getElementById('ai_insights_reviewed_input').value = '1';
        showToast('AI suggestions applied to form fields.');
    })
    .catch(err => {
        alert('Failed to generate AI suggestions. Please try again.');
        console.error(err);
    });
}

function markReviewed() {
    const panel = document.getElementById('ai-insights-panel');
    if (panel) panel.classList.add('opacity-75');
}

// ===================== Regenerate AI Insights =====================

document.getElementById('regenerate-ai-btn')?.addEventListener('click', function(e) {
    generateAiInsights(e, true);
});

function generateAiInsights(e, isRegenerate = false) {
    const btn = e?.currentTarget || document.querySelector('.generate-ai-btn');
    const spinner = btn ? btn.querySelector('.generate-spinner, #regenerate-spinner') : document.getElementById('regenerate-spinner');
    const btnText = btn ? btn.querySelector('.generate-btn-text, #ai-btn-text') : null;

    if (btn) btn.disabled = true;
    if (spinner) spinner.classList.remove('hidden');
    if (btnText) btnText.textContent = isRegenerate ? 'Regenerating...' : 'Generating...';

    fetch('{{ route("supervisor.observations.generate-ai-insights", $observation) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
        },
    })
    .then(res => res.json())
    .then(data => {
        if (data.ai_insights) {
            location.reload();
        } else if (data.error) {
            alert(data.error);
        }
    })
    .catch(err => {
        alert('Failed to generate AI insights. Please try again.');
        console.error(err);
    })
    .finally(() => {
        if (btn) btn.disabled = false;
        if (spinner) spinner.classList.add('hidden');
        if (btnText) btnText.textContent = isRegenerate ? 'Regenerate' : 'Generate AI Insights';
    });
}

// ===================== Copy to Clipboard =====================

function copyToClipboard(btn, elementId) {
    const el = document.getElementById(elementId);
    if (!el) return;
    const text = el.textContent || el.innerText;
    navigator.clipboard.writeText(text).then(() => {
        const original = btn.innerHTML;
        btn.innerHTML = '<svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.5 12.75l6 6 9-13.5"/></svg>';
        setTimeout(() => btn.innerHTML = original, 2000);
    }).catch(() => {
        // Fallback
        const ta = document.createElement('textarea');
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

// Show/hide "other reason" field
document.querySelector('[name="cancellation_reason"]')?.addEventListener('change', function() {
    const container = document.getElementById('other-reason-container');
    if (container) {
        container.classList.toggle('hidden', this.value !== 'other');
    }
});

// Close modal on backdrop click
document.getElementById('cancel-modal')?.addEventListener('click', function(e) {
    if (e.target === this) closeCancelModal();
});

// ===================== Form Validation =====================

document.getElementById('pre-conference-form')?.addEventListener('submit', function(e) {
    const focus = document.getElementById('finalized_focus');
    const date = document.getElementById('conference_date');
    if (focus && !focus.value.trim()) {
        e.preventDefault();
        alert('Please enter the Finalized Observation Focus before saving.');
        focus.focus();
        return;
    }
    if (date && !date.value) {
        e.preventDefault();
        alert('Please select a Pre-Conference Date.');
        date.focus();
        return;
    }
});

// ===================== Toast Notification =====================

function showToast(message) {
    const existing = document.querySelector('.toast-notification');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.className = 'toast-notification fixed bottom-6 right-6 z-50 bg-gray-900 text-white px-5 py-3 rounded-lg shadow-xl text-sm font-medium animate-bounce';
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.transition = 'opacity 0.5s';
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 500);
    }, 3000);
}
</script>
@endpush
@endsection