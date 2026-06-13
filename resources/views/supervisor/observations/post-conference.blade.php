@extends('layouts.supervisor')

@section('title', 'Post-Conference')

@push('styles')
<style>
    select option { background-color: #1f2937; color: #ffffff; }
    .sidebar-sticky { position: sticky; top: 1.5rem; align-self: start; max-height: calc(100vh - 3rem); overflow-y: auto; }
    .sidebar-sticky::-webkit-scrollbar { width: 4px; }
    .sidebar-sticky::-webkit-scrollbar-track { background: transparent; }
    .sidebar-sticky::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
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
    $currentStage = $observation->stage;
    $currentIdx = array_search($currentStage, $stageKeys);
@endphp

@section('content')
<div class="max-w-7xl mx-auto px-6">
    <!-- Breadcrumb -->
    <nav class="mb-6 text-sm">
        <ol class="flex items-center gap-2 text-gray-500">
            <li><a href="{{ route('supervisor.observations.index') }}" class="hover:text-indigo-600 transition-colors">Evaluations</a></li>
            <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"/></svg></li>
            <li><a href="{{ route('supervisor.observations.show', $observation) }}" class="hover:text-indigo-600 transition-colors">Observation Details</a></li>
            <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"/></svg></li>
            <li class="text-gray-900 font-medium">Post-Conference</li>
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
        <h1 class="text-2xl font-bold text-gray-900">Post-Conference</h1>
        <p class="text-gray-500 mt-1">{{ $observation->observee->user->name ?? 'Unknown' }} &middot; {{ $observation->observation_date->format('M d, Y') }}</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left Column -->
        <div class="lg:col-span-2 space-y-6">

            <!-- COT Ratings Summary -->
            @if($cotRatings && $cotRatings->count() > 0)
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">COT Ratings Summary</h2>
                    <span class="text-2xl font-bold text-blue-600">{{ number_format($observation->overall_score, 2) }} <span class="text-sm font-normal text-gray-500">/ 6.00</span></span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50">
                                <th class="text-left px-3 py-2 text-gray-600 font-medium">Domain</th>
                                <th class="text-left px-3 py-2 text-gray-600 font-medium">Indicator</th>
                                <th class="text-center px-3 py-2 text-gray-600 font-medium w-20">Rating</th>
                                <th class="text-left px-3 py-2 text-gray-600 font-medium">Comments</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($cotRatings as $rating)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 text-gray-700 text-xs">{{ $rating->domain }}</td>
                                <td class="px-3 py-2 text-gray-700 text-xs">{{ $rating->indicator }}</td>
                                <td class="px-3 py-2 text-center">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-xs font-bold
                                        {{ $rating->rating >= 4 ? 'bg-green-100 text-green-700' : ($rating->rating >= 3 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                        {{ number_format($rating->rating, 1) }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-gray-500 text-xs">{{ $rating->comments ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="text-xs text-gray-500 mt-3">{{ $cotRatings->count() }} indicator(s) rated</p>
            </div>

            <!-- Score Breakdown -->
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Score Breakdown by Domain</h2>
                <div class="space-y-3">
                    @php
                        $grouped = $cotRatings->groupBy('domain');
                    @endphp
                    @foreach($grouped as $domain => $ratings)
                        @php $avg = $ratings->avg('rating'); @endphp
                        <div>
                            <div class="flex items-center justify-between text-sm mb-1">
                                <span class="text-gray-700">{{ $domain }}</span>
                                <span class="font-medium {{ $avg >= 4 ? 'text-green-600' : ($avg >= 3 ? 'text-yellow-600' : 'text-red-600') }}">{{ number_format($avg, 2) }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="h-2 rounded-full {{ $avg >= 4 ? 'bg-green-500' : ($avg >= 3 ? 'bg-yellow-500' : 'bg-red-500') }}" style="width: {{ ($avg / 6) * 100 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Evidence Files -->
            @if($observation->evidence_files)
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Evidence Files</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($observation->evidence_files as $file)
                    <a href="{{ asset('storage/' . $file['path']) }}" target="_blank"
                       class="flex items-center space-x-3 bg-gray-50 rounded-lg p-3 border border-gray-200 hover:bg-gray-100">
                        <svg class="w-8 h-8 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $file['original_name'] ?? basename($file['path']) }}</p>
                            <p class="text-xs text-gray-500">{{ isset($file['size']) ? number_format($file['size'] / 1024, 1) . ' KB' : '' }}</p>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            <form method="POST" action="{{ route('supervisor.observations.storePostConference', $observation) }}" class="space-y-6"
                  x-data="{ submitting: false }" x-on:submit="submitting = true">
                @csrf

                <!-- Section 1: Conference Schedule -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Conference Details</h2>
                        <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full font-medium">Post-Conference</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Teacher</label>
                            <p class="text-gray-900 font-semibold">{{ $observation->observee->user->name ?? 'Unknown' }}</p>
                            <p class="text-sm text-gray-500">{{ $observation->observee->position ?? 'Teacher' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="conference_date">Post-Conference Date</label>
                            <input type="date" name="conference_date" id="conference_date"
                                   value="{{ old('conference_date', $postConference?->conference_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                                   class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                            @error('conference_date')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Enhanced Post Observation Conference Guide -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Enhanced Post-Observation Conference Guide</h2>
                        <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-1 rounded-full font-medium">DepEd CID Format</span>
                    </div>
                    <p class="text-sm text-gray-500 mb-4">Follow this structured guide for a productive post-observation conference.</p>

                    <!-- Step 1: Warm and Clear Opening -->
                    <div class="border-l-4 border-blue-400 bg-blue-50 rounded-r-lg p-4 mb-4">
                        <h3 class="text-sm font-semibold text-blue-800">Step 1: Warm and Clear Opening</h3>
                        <p class="text-xs text-blue-600 mt-1">Establish rapport and set the purpose of the conference.</p>
                    </div>

                    <!-- Step 2: STAR Notes - What's Going Well -->
                    <div class="border border-green-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-semibold text-green-800">Step 2: What's Going Well <span class="text-xs font-normal text-green-600">(STAR Notes)</span></h3>
                            <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Strengths</span>
                        </div>
                        <p class="text-xs text-gray-500 mb-2">Document specific observations of effective teaching practices observed.</p>
                        <textarea name="star_notes" rows="4"
                                  class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500 text-sm"
                                  placeholder="Describe what went well during the observation. Be specific and cite examples...">{{ old('star_notes', $postConference?->star_notes) }}</textarea>
                    </div>

                    <!-- Step 3: Challenges Facing the Teacher -->
                    <div class="border border-yellow-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-semibold text-yellow-800">Step 3: Identify Challenges</h3>
                            <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full">Challenges</span>
                        </div>
                        <p class="text-xs text-gray-500 mb-2">Identify challenges or difficulties observed during the lesson.</p>
                        <textarea name="challenges_facing_teacher" rows="3"
                                  class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 focus:outline-none focus:ring-2 focus:ring-yellow-500 text-sm"
                                  placeholder="What challenges did the teacher face during the lesson? e.g. time management, learner engagement, materials...">{{ old('challenges_facing_teacher', $postConference?->challenges_facing_teacher) }}</textarea>
                    </div>

                    <!-- Step 4: Areas for Improvement -->
                    <div class="border border-orange-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-semibold text-orange-800">Step 4: Areas for Improvement</h3>
                            <span class="text-xs bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full">Growth Areas</span>
                        </div>
                        <p class="text-xs text-gray-500 mb-2">Non-threatening areas where the teacher can grow and develop further.</p>
                        <textarea name="areas_for_improvement" rows="3"
                                  class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 focus:outline-none focus:ring-2 focus:ring-orange-500 text-sm"
                                  placeholder="Identify specific areas where the teacher can improve. Frame these constructively...">{{ old('areas_for_improvement', $postConference?->areas_for_improvement) }}</textarea>
                    </div>

                    <!-- Step 5: Generating Ideas -->
                    <div class="border border-purple-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-semibold text-purple-800">Step 5: Ideas for Addressing Challenges</h3>
                            <span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full">Solutions</span>
                        </div>
                        <p class="text-xs text-gray-500 mb-2">Collaboratively generate strategies and solutions to address the identified challenges.</p>
                        <textarea name="ideas_for_addressing_challenges" rows="4"
                                  class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 focus:outline-none focus:ring-2 focus:ring-purple-500 text-sm"
                                  placeholder="Brainstorm strategies with the teacher. What resources, training, or support can help address these challenges?">{{ old('ideas_for_addressing_challenges', $postConference?->ideas_for_addressing_challenges) }}</textarea>
                    </div>

                    <!-- Step 6: Prioritizing Next Steps -->
                    <div class="border border-indigo-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-semibold text-indigo-800">Step 6: Prioritized Next Steps</h3>
                            <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full">Action Plan</span>
                        </div>
                        <p class="text-xs text-gray-500 mb-2">Agree on actionable next steps with clear timelines and responsibilities.</p>
                        <textarea name="prioritized_next_steps" rows="4"
                                  class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"
                                  placeholder="1. ... (by when)
2. ... (by when)
3. ... (by when)">{{ old('prioritized_next_steps', $postConference?->prioritized_next_steps) }}</textarea>
                    </div>

                    <!-- Step 7: Ending the Conference -->
                    <div class="border-l-4 border-green-400 bg-green-50 rounded-r-lg p-4 mb-4">
                        <h3 class="text-sm font-semibold text-green-800">Step 7: Ending the Post-Observation Conference</h3>
                        <p class="text-xs text-green-600 mt-1">Summarize key points, acknowledge the teacher's efforts, and express confidence in their growth.</p>
                    </div>
                </div>

                <!-- Teacher Reflection -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Teacher Reflection</h2>
                    <p class="text-xs text-gray-500 mb-2">The teacher's reflection on their observed lesson and the post-conference discussion.</p>
                    <textarea name="teacher_reflection" rows="4"
                              class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                              placeholder="Teacher's personal reflection on the observation feedback and insights gained...">{{ old('teacher_reflection', $postConference?->teacher_reflection) }}</textarea>
                </div>

                <!-- AI Comparison -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100" x-data="{ generating: false }">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">AI Comparison & Feedback</h2>
                        <span class="text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded-full font-medium">AI-Powered</span>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">AI Comparison (Plan vs Actual)</label>
                            <p class="text-xs text-gray-500 mb-2">AI-generated comparison between the lesson plan and actual classroom observation.</p>
                            <textarea name="ai_comparison" id="ai_comparison_textarea" rows="4"
                                      class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                                      placeholder="AI-generated comparison analysis will appear here...">{{ old('ai_comparison', $postConference?->ai_comparison) }}</textarea>
                            <button type="button" id="generate-ai-comparison-btn"
                                    class="mt-2 px-4 py-2 bg-purple-600 hover:bg-purple-700 disabled:bg-purple-300 text-white text-sm rounded-lg font-medium transition-colors inline-flex items-center gap-2">
                                <svg id="ai-comparison-spinner" class="hidden w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                <span id="ai-comparison-btn-text">Generate AI Comparison</span>
                            </button>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Supervisor Feedback</label>
                            <p class="text-xs text-gray-500 mb-2">Overall feedback summary for the teacher.</p>
                            <textarea name="feedback" rows="4"
                                      class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                                      placeholder="Provide overall feedback summarizing the observation and conference...">{{ old('feedback', $postConference?->feedback) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Supervisor Notes (Private) -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Supervisor's Private Notes</h2>
                        <span class="text-xs bg-gray-100 text-gray-500 px-2 py-1 rounded-full font-medium">Private</span>
                    </div>
                    <p class="text-xs text-gray-500 mb-2">These notes are for your reference only and will not be visible to the teacher.</p>
                    <textarea name="supervisor_notes" rows="3"
                              class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                              placeholder="Your private notes and reminders for future reference...">{{ old('supervisor_notes', $postConference?->supervisor_notes) }}</textarea>
                </div>

                <!-- Actions -->
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <div class="flex items-center gap-2 mb-3">
                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                        <span class="text-xs text-gray-600">Completing the observation will finalize all ratings and notify the teacher.</span>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <a href="{{ route('supervisor.observations.observation', $observation) }}" 
                           class="flex-1 px-4 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 font-medium text-sm text-center transition-colors">
                            <span class="flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                Back to Observation
                            </span>
                        </a>
                        <button type="submit" :disabled="submitting"
                                :class="submitting ? 'opacity-60 cursor-not-allowed' : ''"
                                class="flex-1 px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold text-sm shadow-sm transition-colors"
                                onclick="return confirm('Mark this observation as complete? This will finalize all ratings and notify the teacher.')">
                            <span x-show="!submitting" class="flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Complete Observation
                            </span>
                            <span x-show="submitting" class="flex items-center justify-center gap-2">
                                <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                Saving...
                            </span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Right Column -->
        <div class="space-y-6 sidebar-sticky">

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
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Completed</span>
                    </div>
                </div>
            </div>

            <!-- Overall Score Card -->
            @if($observation->overall_score)
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 text-center">
                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-3">Overall Score</h3>
                <div class="text-4xl font-bold {{ $observation->overall_score >= 4 ? 'text-green-600' : ($observation->overall_score >= 3 ? 'text-yellow-600' : 'text-red-600') }}">
                    {{ number_format($observation->overall_score, 2) }}
                </div>
                <p class="text-sm text-gray-500 mt-1">out of 5.00</p>
                <div class="mt-3 w-full bg-gray-200 rounded-full h-3">
                    <div class="h-3 rounded-full {{ $observation->overall_score >= 4 ? 'bg-green-500' : ($observation->overall_score >= 3 ? 'bg-yellow-500' : 'bg-red-500') }}"
                         style="width: {{ $observation->overall_score ? ($observation->overall_score / 6) * 100 : 0 }}%"></div>
                </div>
            </div>
            @endif

            <!-- Pre-Observation Planning Summary -->
            @if($planning)
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-3">Planning Summary</h3>
                <div class="space-y-2">
                    @if($planning->observation_tool)
                    <div>
                        <span class="text-xs text-gray-500">Tool</span>
                        <p class="text-sm text-gray-900">{{ str_replace('_', ' ', ucfirst($planning->observation_tool)) }}</p>
                    </div>
                    @endif
                    @if($planning->suggested_focus)
                    <div>
                        <span class="text-xs text-gray-500">Focus</span>
                        <p class="text-sm text-gray-700">{{ Str::limit($planning->suggested_focus, 80) }}</p>
                    </div>
                    @endif
                    @if($preConference?->finalized_focus)
                    <div>
                        <span class="text-xs text-gray-500">Finalized Focus</span>
                        <p class="text-sm text-gray-700">{{ Str::limit($preConference->finalized_focus, 80) }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

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
document.getElementById('generate-ai-comparison-btn')?.addEventListener('click', function() {
    const btn = this;
    const spinner = document.getElementById('ai-comparison-spinner');
    const btnText = document.getElementById('ai-comparison-btn-text');
    const textarea = document.getElementById('ai_comparison_textarea');

    btn.disabled = true;
    spinner.classList.remove('hidden');
    btnText.textContent = 'Generating...';

    fetch('{{ route("supervisor.observations.generate-ai-comparison", $observation) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
        },
    })
    .then(res => res.json())
    .then(data => {
        if (data.ai_comparison) {
            textarea.value = data.ai_comparison;
        } else if (data.error) {
            alert(data.error);
        }
    })
    .catch(err => {
        alert('Failed to generate AI comparison. Please try again.');
        console.error(err);
    })
    .finally(() => {
        btn.disabled = false;
        spinner.classList.add('hidden');
        btnText.textContent = 'Generate AI Comparison';
    });
});
</script>
@endpush
@endsection