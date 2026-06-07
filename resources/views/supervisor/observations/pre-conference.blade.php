@extends('layouts.supervisor')

@section('title', 'Pre-Conference')

@push('styles')
<style>
    select option { background-color: #1f2937; color: #ffffff; }
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

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left Column -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Pre-Observation Planning Summary -->
            @if($planning)
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Pre-Observation Planning Summary</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @if($planning->lesson_plan_file)
                    <div class="bg-blue-50 rounded-lg p-3 border border-blue-100">
                        <span class="text-xs text-gray-500 uppercase tracking-wider font-medium">Lesson Plan</span>
                        <div class="mt-1 flex items-center justify-between">
                            <span class="text-sm text-gray-700">Uploaded</span>
                            <a href="{{ asset('storage/' . $planning->lesson_plan_file) }}" target="_blank" class="text-sm text-blue-600 hover:text-blue-700 font-medium">View</a>
                        </div>
                    </div>
                    @endif
                    @if($planning->supervisor_notes)
                    <div class="bg-gray-50 rounded-lg p-3 border border-gray-200">
                        <span class="text-xs text-gray-500 uppercase tracking-wider font-medium">Supervisor Notes</span>
                        <p class="text-sm text-gray-700 mt-1">{{ Str::limit($planning->supervisor_notes, 100) }}</p>
                    </div>
                    @endif
                    @if($planning->suggested_focus)
                    <div class="bg-purple-50 rounded-lg p-3 border border-purple-100">
                        <span class="text-xs text-gray-500 uppercase tracking-wider font-medium">Suggested Focus</span>
                        <p class="text-sm text-gray-700 mt-1">{{ Str::limit($planning->suggested_focus, 100) }}</p>
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
            @endif

            <form method="POST" action="{{ route('supervisor.observations.storePreConference', $observation) }}" class="space-y-6">
                @csrf

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
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="conference_date">Pre-Conference Date</label>
                            <input type="date" name="conference_date" id="conference_date"
                                   value="{{ old('conference_date', $preConference?->conference_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                                   class="w-full px-3 py-2 rounded-lg bg-white border border-gray-300 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
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
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pre-Conference Discussion Notes</label>
                            <p class="text-xs text-gray-500 mb-2">Document key discussion points including teaching strategies, learner diversity considerations, and assessment methods.</p>
                            <textarea name="discussion_notes" rows="5"
                                      class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                                      placeholder="Document the key discussion points from the pre-conference meeting...">{{ old('discussion_notes', $preConference?->discussion_notes) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Finalized Observation Focus</label>
                            <p class="text-xs text-gray-500 mb-2">Areas agreed upon to be the focus of the classroom observation.</p>
                            <textarea name="finalized_focus" rows="4"
                                      class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                                      placeholder="e.g. Learner engagement strategies, differentiated instruction, classroom management...">{{ old('finalized_focus', $preConference?->finalized_focus) }}</textarea>
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
                        <span class="text-xs text-gray-500">Save and continue when ready.</span>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <a href="{{ route('supervisor.observations.preObservationPlanning', $observation) }}" 
                           class="flex-1 px-4 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium text-sm text-center transition-colors">
                            <span class="flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                Back to Planning
                            </span>
                        </a>
                        <button type="submit" 
                                class="flex-1 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold text-sm shadow-sm transition-colors">
                            <span class="flex items-center justify-center gap-2">
                                Save &amp; Continue
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                            </span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Right Column -->
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

            <!-- Suggested Focus Areas -->
            @if($planning?->suggested_focus)
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-3">Focus Areas</h3>
                <p class="text-sm text-gray-700">{{ $planning->suggested_focus }}</p>
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
@endsection