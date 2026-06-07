@extends('layouts.supervisor')

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
<div class="max-w-7xl mx-auto px-6 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Observation Details</h1>
            <p class="text-gray-500 mt-1">{{ $observation->observee->user->name }} - {{ $observation->observation_date->format('M d, Y') }}</p>
            <p class="text-gray-400 text-sm mt-1">
                {{ $observation->isTeacherObservation() ? 'Teacher Observation' : 'School Head Observation' }}
                @if($observation->isTeacherObservation() && $observation->subject)
                    | {{ $observation->subject }} - {{ $observation->grade_level }}
                @endif
            </p>
        </div>
        <a href="{{ route('supervisor.observations.index') }}" 
           class="px-6 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors">
            Back to List
        </a>
    </div>

    @php
        $stageRoutes = [
            'pre_observation_planning' => 'supervisor.observations.preObservationPlanning',
            'pre_conference' => 'supervisor.observations.preConference',
            'observation' => 'supervisor.observations.observation',
            'post_conference' => 'supervisor.observations.postConference',
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
                        <div class="flex items-center justify-center w-10 h-10 rounded-full {{ $done ? 'bg-green-600 text-white' : ($active ? 'bg-indigo-600 text-white ring-2 ring-indigo-200' : 'bg-gray-200 text-gray-500') }} font-semibold transition-colors group-hover:shadow-md text-sm">
                            {{ $done ? '✓' : ($i + 1) }}
                        </div>
                        <span class="ml-2 {{ $done ? 'text-gray-600 font-medium' : ($active ? 'text-indigo-600 font-medium' : 'text-gray-400') }} group-hover:text-indigo-600 transition-colors text-sm">{{ $stageLabels[$key] }}</span>
                    </a>
                @else
                    <div class="flex items-center opacity-50">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-200 text-gray-400 font-semibold text-sm">
                            {{ $i + 1 }}
                        </div>
                        <span class="ml-2 text-gray-400 text-sm">{{ $stageLabels[$key] }}</span>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    <!-- Stage Navigation Cards -->
    <div class="grid grid-cols-4 gap-4 mb-8">
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
                   class="bg-white rounded-xl border {{ $active ? 'border-indigo-300 ring-2 ring-indigo-100' : 'border-gray-100' }} shadow-sm p-4 hover:shadow-md transition-all group">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-9 h-9 rounded-lg {{ $done ? 'bg-green-100 text-green-700' : ($active ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-50 text-dark-400') }} flex items-center justify-center">
                            {!! $icon !!}
                        </div>
                        <span class="text-xs font-semibold {{ $done ? 'text-green-600' : ($active ? 'text-indigo-600' : 'text-dark-400') }} uppercase tracking-wide">
                            {{ $done ? 'Completed' : ($active ? 'Current' : 'Available') }}
                        </span>
                    </div>
                    <h4 class="font-semibold text-dark-900 text-sm mb-0.5">{{ $stageLabels[$key] }}</h4>
                    <p class="text-xs text-dark-400">{{ $desc }}</p>
                </a>
            @else
                <div class="bg-gray-50 rounded-xl border border-gray-200 shadow-sm p-4 opacity-60">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-9 h-9 rounded-lg bg-gray-200 text-gray-400 flex items-center justify-center">
                            {!! $icon !!}
                        </div>
                        <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Locked</span>
                    </div>
                    <h4 class="font-semibold text-gray-500 text-sm mb-0.5">{{ $stageLabels[$key] }}</h4>
                    <p class="text-xs text-gray-400">{{ $desc }}</p>
                </div>
            @endif
        @endforeach
    </div>

    <!-- Stage Details -->
    <div class="space-y-6">
        <!-- Pre-Observation Planning -->
        @if($observation->preObservationPlanning)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Pre-Observation Planning</h2>
            <div class="space-y-3">
                @if($observation->preObservationPlanning->lesson_plan_file)
                <div>
                    <span class="text-gray-500 text-sm">Lesson Plan:</span>
                    <a href="{{ asset('storage/' . $observation->preObservationPlanning->lesson_plan_file) }}" target="_blank" class="text-indigo-600 hover:text-indigo-700 ml-2 text-sm font-medium">View File</a>
                </div>
                @endif
                @if($observation->preObservationPlanning->ai_insights)
                <div>
                    <span class="text-gray-500 text-sm">AI Insights:</span>
                    <p class="text-gray-900 mt-1">{{ $observation->preObservationPlanning->ai_insights }}</p>
                </div>
                @endif
                @if($observation->preObservationPlanning->suggested_focus)
                <div>
                    <span class="text-gray-500 text-sm">Suggested Focus:</span>
                    <p class="text-gray-900 mt-1">{{ $observation->preObservationPlanning->suggested_focus }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Pre-Conference -->
        @if($observation->preConference)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Pre-Conference</h2>
            <div class="space-y-3">
                @if($observation->preConference->conference_date)
                <div>
                    <span class="text-gray-500 text-sm">Conference Date:</span>
                    <p class="text-gray-900">{{ $observation->preConference->conference_date->format('M d, Y') }}</p>
                </div>
                @endif
                @if($observation->preConference->discussion_notes)
                <div>
                    <span class="text-gray-500 text-sm">Discussion Notes:</span>
                    <p class="text-gray-900 mt-1">{{ $observation->preConference->discussion_notes }}</p>
                </div>
                @endif
                @if($observation->preConference->finalized_focus)
                <div>
                    <span class="text-gray-500 text-sm">Finalized Focus:</span>
                    <p class="text-gray-900 mt-1">{{ $observation->preConference->finalized_focus }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Observation (COT Ratings) -->
        @if($observation->cotRatings && $observation->cotRatings->count() > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Observation Ratings</h2>
            <div class="mb-4">
                <span class="text-gray-500 text-sm">Overall Score:</span>
                <p class="text-gray-900 font-medium text-2xl">{{ number_format($observation->overall_score, 2) }} / 5.00</p>
            </div>
            <div class="space-y-3">
                @foreach($observation->cotRatings as $rating)
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <span class="text-gray-500 text-sm">Domain:</span>
                            <p class="text-gray-900">{{ $rating->domain }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500 text-sm">Indicator:</span>
                            <p class="text-gray-900">{{ $rating->indicator }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500 text-sm">Rating:</span>
                            <p class="text-gray-900 font-medium">{{ $rating->rating }} / 5</p>
                        </div>
                    </div>
                    @if($rating->comments)
                    <div class="mt-2 pt-2 border-t border-gray-200">
                        <span class="text-gray-500 text-sm">Comments:</span>
                        <p class="text-gray-900 mt-1">{{ $rating->comments }}</p>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Post-Conference -->
        @if($observation->postConference)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Post-Conference</h2>
            <div class="space-y-3">
                @if($observation->postConference->conference_date)
                <div>
                    <span class="text-gray-500 text-sm">Conference Date:</span>
                    <p class="text-gray-900">{{ $observation->postConference->conference_date->format('M d, Y') }}</p>
                </div>
                @endif
                @if($observation->postConference->ai_comparison)
                <div>
                    <span class="text-gray-500 text-sm">AI Comparison (Plan vs Actual):</span>
                    <p class="text-gray-900 mt-1">{{ $observation->postConference->ai_comparison }}</p>
                </div>
                @endif
                @if($observation->postConference->feedback)
                <div>
                    <span class="text-gray-500 text-sm">Feedback:</span>
                    <p class="text-gray-900 mt-1">{{ $observation->postConference->feedback }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    <!-- Continue Button -->
    @if($observation->stage !== 'post_conference' || $observation->status !== 'completed')
    <div class="mt-8 flex justify-center">
        @php
            $continueLabel = match($observation->stage) {
                'pre_observation_planning' => 'Continue to Pre-Observation Planning',
                'pre_conference' => 'Continue to Pre-Conference',
                'observation' => 'Continue to Observation',
                'post_conference' => 'Continue to Post-Conference',
                default => null,
            };
            $continueRoute = match($observation->stage) {
                'pre_observation_planning' => 'supervisor.observations.preObservationPlanning',
                'pre_conference' => 'supervisor.observations.preConference',
                'observation' => 'supervisor.observations.observation',
                'post_conference' => 'supervisor.observations.postConference',
                default => null,
            };
        @endphp
        @if($continueRoute && ($observation->stage !== 'post_conference' || $observation->status !== 'completed'))
            <a href="{{ route($continueRoute, $observation) }}"
               class="inline-flex items-center gap-3 px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-semibold shadow-lg shadow-indigo-600/20 transition-all hover:shadow-xl hover:shadow-indigo-600/30">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                {{ $continueLabel }}
            </a>
        @endif
    </div>
    @endif

    <!-- Observation History -->
    @if($observation->observee)
    <div class="mt-6 text-center">
        <a href="{{ route('supervisor.observations.teacher-history', $observation->observee_id) }}?type={{ $observation->observee_type }}"
           class="inline-flex items-center gap-2 text-sm text-indigo-400 hover:text-indigo-300 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            View all observations for {{ $observation->observee->user?->name ?? 'this teacher' }}
        </a>
    </div>
    @endif
</div>
@endsection
