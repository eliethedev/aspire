@extends('layouts.supervisor')

@section('title', 'Pre-Observation Planning')

@push('styles')
<style>
    select option {
        background-color: #1f2937;
        color: #ffffff;
    }
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
    $currentStage = 'pre_observation_planning';
    $currentIdx = array_search($currentStage, $stageKeys);
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
            <li class="text-gray-900 font-medium">Pre-Observation Planning</li>
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

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Pre-Observation Planning</h1>
            <p class="text-gray-500 mt-1">{{ $observation->observee->user->name ?? 'Unknown' }} &middot; {{ $observation->observation_date->format('M d, Y') }}</p>
        </div>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
            {{ ucfirst($observation->status) }}
        </span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left Column - Main Content -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Teacher Information Card -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Teacher Information</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div class="col-span-2">
                        <span class="text-gray-500 text-xs uppercase tracking-wider font-medium">Full Name</span>
                        <p class="text-gray-900 font-semibold mt-1 text-lg">{{ $observation->observee->user->name ?? 'Unknown' }}</p>
                        <p class="text-gray-500 text-sm">{{ $observation->observee->position ?? 'Teacher' }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500 text-xs uppercase tracking-wider font-medium">School</span>
                        <p class="text-gray-900 font-semibold mt-1">{{ $observation->observee->school->name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <span class="text-gray-500 text-xs uppercase tracking-wider font-medium">Subject / Grade</span>
                        <p class="text-gray-900 font-semibold mt-1">{{ $observation->subject ?? 'N/A' }}</p>
                        <p class="text-gray-500 text-sm">Grade {{ $observation->grade_level ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <!-- Lesson Plan -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">Lesson Plan</h2>
                        <p class="text-sm text-gray-500">Uploaded by the teacher for your review</p>
                    </div>
                    <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">Teacher's Responsibility</span>
                </div>

                @if($planning && $planning->lesson_plan_file)
                    <div class="flex items-center justify-between bg-blue-50 rounded-lg p-4 border border-blue-100">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ preg_replace('/^\d+_/', '', basename($planning->lesson_plan_file)) }}</p>
                                <p class="text-xs text-green-600 font-medium">Lesson Plan Uploaded</p>
                            </div>
                        </div>
                        <a href="{{ asset('storage/' . $planning->lesson_plan_file) }}" target="_blank"
                           class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg font-medium">
                            View Lesson Plan
                        </a>
                    </div>
                @else
                    <div class="flex items-center justify-between bg-yellow-50 rounded-lg p-4 border border-yellow-100">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">No lesson plan uploaded yet</p>
                                <p class="text-xs text-gray-500">The teacher has not submitted a lesson plan for this observation.</p>
                            </div>
                        </div>
                        <button type="button" onclick="alert('Request sent to teacher.')"
                                class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white text-sm rounded-lg font-medium">
                            Request Lesson Plan
                        </button>
                    </div>
                @endif
            </div>

            <!-- AI-Generated Insights -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">AI-Generated Insights</h2>
                    <span class="text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded-full font-medium">AI-Powered</span>
                </div>
                <p class="text-sm text-gray-500 mb-4">Review AI-generated insights based on the lesson plan and previous observation data.</p>

                <div id="ai-insights-container">
                    @if($planning && $planning->ai_insights)
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <p class="text-sm text-gray-700 whitespace-pre-wrap" id="ai-insights-text">{{ $planning->ai_insights }}</p>
                        </div>
                    @else
                        <div class="bg-gray-50 rounded-lg p-4 border border-dashed border-gray-300 text-center" id="ai-insights-empty">
                            <svg class="w-10 h-10 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                            <p class="text-sm text-gray-500">No AI insights available yet.</p>
                            <p class="text-xs text-gray-400 mt-1">Click "Generate AI Insights" to analyze the lesson plan and generate recommendations.</p>
                        </div>
                    @endif
                </div>
                <input type="hidden" name="ai_insights" id="ai_insights_input" value="{{ $planning?->ai_insights ?? '' }}">
                <div class="mt-3 flex items-center gap-2">
                    <button type="button" id="generate-ai-insights-btn"
                            class="px-4 py-2 bg-purple-600 hover:bg-purple-700 disabled:bg-purple-300 text-white text-sm rounded-lg font-medium transition-colors inline-flex items-center gap-2">
                        <svg id="ai-spinner" class="hidden w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span id="ai-btn-text">Generate AI Insights</span>
                    </button>
                    <button type="button" id="clear-ai-insights-btn"
                            class="px-4 py-2 text-sm font-medium text-red-600 hover:text-white bg-red-50 hover:bg-red-600 border border-red-200 hover:border-red-600 rounded-lg transition-colors inline-flex items-center gap-1.5 {{ $planning?->ai_insights ? '' : 'hidden' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        Clear Insights
                    </button>
                </div>
            </div>

            <!-- Previous Observation Highlights (from past data) -->
            @if($previousObservations->isNotEmpty())
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Previous Observation Highlights</h2>
                <p class="text-sm text-gray-500 mb-4">Based on {{ $previousObservations->count() }} previous observation(s).</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if($prevStrengths->isNotEmpty())
                    <div class="bg-green-50 rounded-lg p-4 border border-green-100">
                        <h3 class="text-sm font-semibold text-green-800 mb-3 flex items-center">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                            </svg>
                            Strengths
                        </h3>
                        <ul class="space-y-2">
                            @foreach($prevStrengths as $strength)
                                <li class="text-sm text-green-700 flex items-start">
                                    <span class="mr-2 mt-0.5">&#9679;</span>
                                    <span>{{ $strength->domain }} <span class="text-green-500 font-medium">({{ number_format($strength->avg_rating, 1) }})</span></span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    @if($prevWeaknesses->isNotEmpty())
                    <div class="bg-red-50 rounded-lg p-4 border border-red-100">
                        <h3 class="text-sm font-semibold text-red-800 mb-3 flex items-center">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                            </svg>
                            Areas for Improvement
                        </h3>
                        <ul class="space-y-2">
                            @foreach($prevWeaknesses as $weakness)
                                <li class="text-sm text-red-700 flex items-start">
                                    <span class="mr-2 mt-0.5">&#9679;</span>
                                    <span>{{ $weakness->domain }} <span class="text-red-500 font-medium">({{ number_format($weakness->avg_rating, 1) }})</span></span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>

                @if($prevStrengths->isEmpty() && $prevWeaknesses->isEmpty())
                    <p class="text-sm text-gray-500 italic">No detailed rating data available from previous observations.</p>
                @endif
            </div>
            @endif

        </div>

        <!-- Right Column - Sidebar -->
        <div class="space-y-6">

            <form method="POST" action="{{ route('supervisor.observations.storePreObservationPlanning', $observation) }}" class="space-y-6">
                @csrf

                <!-- Observation Preparation -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Observation Preparation</h2>

                    <!-- Observation Tool -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Observation Tool / Rubric</label>
                        <select name="observation_tool"
                                class="w-full px-3 py-2 rounded-lg bg-white border border-gray-300 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                            <option value="">Select tool...</option>
                            <option value="ppst" {{ old('observation_tool', $planning?->observation_tool) === 'ppst' ? 'selected' : '' }}>PPST</option>
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
                    <div class="bg-green-50 rounded-lg p-3 border border-green-100 mb-4">
                        <p class="text-xs font-semibold text-green-800 mb-1">Tisuyon - Peer Observation</p>
                        <p class="text-xs text-green-600">Collaborative peer observation focused on professional dialogue and shared learning.</p>
                    </div>
                    @elseif($planning?->observation_tool === 'classroom_observation_tool')
                    <div class="bg-purple-50 rounded-lg p-3 border border-purple-100 mb-4">
                        <p class="text-xs font-semibold text-purple-800 mb-1">COT - 9 Indicators</p>
                        <p class="text-xs text-purple-600">Standard classroom observation tool with 9 performance indicators.</p>
                    </div>
                    @endif

                    <!-- Supervisor's Notes -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">My Pre-Observation Notes</label>
                        <textarea name="supervisor_notes" rows="5"
                                  class="w-full px-3 py-2 rounded-lg bg-white border border-gray-300 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                  placeholder="Write your preliminary notes, things to watch for, or reminders before the class visit...">{{ old('supervisor_notes', $planning?->supervisor_notes) }}</textarea>
                        @error('supervisor_notes')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Suggested Focus Areas -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Suggested Focus Areas</label>
                        <p class="text-xs text-gray-500 mb-2">Based on previous observations and AI analysis.</p>
                        <textarea name="suggested_focus" rows="3"
                                  class="w-full px-3 py-2 rounded-lg bg-white border border-gray-300 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                                  placeholder="e.g. Classroom management, questioning techniques, learner engagement...">{{ old('suggested_focus', $planning?->suggested_focus) }}</textarea>
                        @error('suggested_focus')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Pre-Conference Details -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Pre-Conference</h2>
                    @if($preConference && $preConference->conference_date)
                        <div class="bg-green-50 rounded-lg p-4 border border-green-100">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-green-800">Scheduled</span>
                                <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Set</span>
                            </div>
                            <p class="text-sm text-green-700 font-medium">{{ $preConference->conference_date->format('M d, Y') }}</p>
                            <p class="text-xs text-green-600 mt-1">Pre-conference has been scheduled.</p>
                        </div>
                    @else
                        <div class="bg-gray-50 rounded-lg p-4 border border-dashed border-gray-300 text-center">
                            <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-sm text-gray-500 font-medium">No pre-conference scheduled</p>
                            <p class="text-xs text-gray-400 mt-1">Continue to Pre-Conference to set the date.</p>
                        </div>
                    @endif
                </div>

                <!-- Action Buttons -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="space-y-3">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span class="text-xs text-gray-500">You can save notes and continue later.</span>
                        </div>
                        <button type="submit"
                                class="block w-full px-4 py-2.5 bg-white border-2 border-indigo-600 text-indigo-700 hover:bg-indigo-50 rounded-lg font-semibold text-sm transition-colors">
                            <span class="flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                                Save Notes
                            </span>
                        </button>
                        <button type="submit" name="continue" value="pre_conference"
                                class="block w-full px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold text-sm shadow-sm transition-colors">
                            <span class="flex items-center justify-center gap-2">
                                Continue to Pre-Conference
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                            </span>
                        </button>
                    </div>
                </div>

            </form>
        </div>

    </div>

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
@push('scripts')
<script>
document.getElementById('generate-ai-insights-btn')?.addEventListener('click', function() {
    const btn = this;
    const spinner = document.getElementById('ai-spinner');
    const btnText = document.getElementById('ai-btn-text');
    const input = document.getElementById('ai_insights_input');
    const container = document.getElementById('ai-insights-container');
    const empty = document.getElementById('ai-insights-empty');

    btn.disabled = true;
    spinner.classList.remove('hidden');
    btnText.textContent = 'Generating...';

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
            input.value = data.ai_insights;
            if (empty) empty.remove();
            let existingText = document.getElementById('ai-insights-text');
            if (existingText) {
                existingText.textContent = data.ai_insights;
            } else {
                const div = document.createElement('div');
                div.className = 'bg-gray-50 rounded-lg p-4 border border-gray-200';
                div.innerHTML = '<p class="text-sm text-gray-700 whitespace-pre-wrap" id="ai-insights-text">' + data.ai_insights.replace(/\n/g, '<br>') + '</p>';
                container.appendChild(div);
            }
        } else if (data.error) {
            alert(data.error);
        }
    })
    .catch(err => {
        alert('Failed to generate AI insights. Please try again.');
        console.error(err);
    })
    .finally(() => {
        btn.disabled = false;
        spinner.classList.add('hidden');
        btnText.textContent = 'Generate AI Insights';
    });
});

document.getElementById('clear-ai-insights-btn')?.addEventListener('click', function() {
    if (!confirm('Clear AI insights? This cannot be undone.')) return;

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
            const textEl = document.getElementById('ai-insights-text');
            if (textEl) {
                const container = textEl.closest('.bg-gray-50');
                if (container) container.remove();
            }
            const container = document.getElementById('ai-insights-container');
            if (!document.getElementById('ai-insights-empty')) {
                const div = document.createElement('div');
                div.className = 'bg-gray-50 rounded-lg p-4 border border-dashed border-gray-300 text-center';
                div.id = 'ai-insights-empty';
                div.innerHTML = '<svg class="w-10 h-10 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg><p class="text-sm text-gray-500">No AI insights available yet.</p><p class="text-xs text-gray-400 mt-1">Click "Generate AI Insights" to analyze the lesson plan and generate recommendations.</p>';
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