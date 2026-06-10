@extends('layouts.teacher')

@section('title', 'Observation Details')

@push('styles')
<style>
    .progress-step {
        transition: all 0.2s ease;
    }
    .progress-step:hover .step-circle {
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
    }
</style>
@endpush

@section('content')
@if(session('success'))
    <div class="max-w-7xl mx-auto px-6 mb-4">
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-5 py-3 text-sm flex items-center gap-2">
            <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
    </div>
@endif
@if(session('error'))
    <div class="max-w-7xl mx-auto px-6 mb-4">
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-5 py-3 text-sm flex items-center gap-2">
            <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('error') }}
        </div>
    </div>
@endif
<div class="max-w-7xl mx-auto px-6 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-gray-900">Observation Details</h1>
                @if($observation->status === 'cancelled')
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                        Cancelled
                    </span>
                @endif
            </div>
            <p class="text-gray-500 mt-1">{{ $observation->observer?->name ?? 'Unknown Supervisor' }} - {{ $observation->observation_date->format('M d, Y') }}</p>
            @if($observation->subject)
                <p class="text-gray-400 text-sm mt-1">{{ $observation->subject }} @if($observation->grade_level)- Grade {{ $observation->grade_level }} @endif</p>
            @endif
        </div>
        <a href="{{ route('teacher.observations.index') }}" 
           class="px-6 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors text-sm">
            Back to List
        </a>
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

    <!-- Progress Steps (read-only) -->
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

                <div class="flex items-center">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full {{ $done ? 'bg-green-600 text-white' : ($active ? 'bg-indigo-600 text-white ring-2 ring-indigo-200' : 'bg-gray-200 text-gray-500') }} font-semibold text-sm">
                        @if($done)
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        @else
                            {{ $i + 1 }}
                        @endif
                    </div>
                    <span class="ml-2 {{ $done ? 'text-gray-700 font-medium' : ($active ? 'text-indigo-600 font-medium' : 'text-gray-400') }} text-sm">{{ $stageLabels[$key] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Cancellation Info -->
    @if($observation->status === 'cancelled')
    <div class="bg-red-50 border border-red-200 rounded-xl p-6 mb-6">
        <div class="flex gap-3">
            <svg class="w-5 h-5 text-red-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M4.293 4.293a1 1 0 011.414 0L12 10.586l6.293-6.293a1 1 0 111.414 1.414L13.414 12l6.293 6.293a1 1 0 01-1.414 1.414L12 13.414l-6.293 6.293a1 1 0 01-1.414-1.414L10.586 12 4.293 5.707a1 1 0 010-1.414z"/></svg>
            <div>
                <h3 class="font-semibold text-red-800">Observation Cancelled</h3>
                <p class="text-sm text-red-700 mt-1">
                    This observation was cancelled on {{ $observation->cancelled_at?->format('M d, Y \a\t h:i A') }}.
                </p>
                @if($observation->cancellation_reason)
                <p class="text-sm text-red-700 mt-1">
                    <strong>Reason:</strong> {{ ucwords(str_replace('_', ' ', $observation->cancellation_reason)) }}
                </p>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Stage Details -->
    <div class="space-y-6">
        <!-- Pre-Observation Planning -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Pre-Observation Planning</h2>

            @if($observation->stage === 'pre_observation_planning')
                <div class="mb-6 p-4 border-2 border-dashed border-gray-300 rounded-xl bg-gray-50">
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Upload Your Lesson Plan</h3>
                    <form action="{{ route('teacher.observations.upload-lesson-plan', $observation) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="flex items-center gap-3">
                            <input type="file" name="lesson_plan_file" id="lesson_plan" accept=".pdf,.doc,.docx"
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 transition-colors cursor-pointer">
                            <button type="submit"
                                    class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors shrink-0">
                                Upload
                            </button>
                        </div>
                        @error('lesson_plan_file')
                            <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-400 mt-2">Accepted formats: PDF, DOC, DOCX (max 20MB)</p>
                    </form>
                </div>
            @endif

            @if($observation->preObservationPlanning)
                <div class="space-y-3">
                    @if($observation->preObservationPlanning->lesson_plan_file)
                    <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 border border-gray-200">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Lesson Plan</p>
                                <p class="text-xs text-gray-500">{{ preg_replace('/^\d+_/', '', basename($observation->preObservationPlanning->lesson_plan_file)) }}</p>
                            </div>
                        </div>
                        <a href="{{ asset('storage/' . $observation->preObservationPlanning->lesson_plan_file) }}" target="_blank"
                           class="px-3 py-1.5 text-xs font-medium text-indigo-600 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-colors">
                            View File
                        </a>
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
                    @if($observation->preObservationPlanning->supervisor_notes)
                    <div>
                        <span class="text-gray-500 text-sm">Supervisor Notes:</span>
                        <p class="text-gray-900 mt-1">{{ $observation->preObservationPlanning->supervisor_notes }}</p>
                    </div>
                    @endif
                </div>
            @else
                @if($observation->stage !== 'pre_observation_planning')
                    <p class="text-gray-400 text-sm">No pre-observation planning data recorded yet.</p>
                @endif
            @endif
        </div>

        <!-- Pre-Conference -->
        @if($observation->preConference)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Pre-Conference</h2>
                @if($observation->preObservationPlanning?->ai_insights_reviewed)
                    <span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full font-medium">AI Insights Reviewed</span>
                @endif
            </div>
            <div class="space-y-3">
                @if($observation->preObservationPlanning?->ai_insights)
                <div class="bg-purple-50 rounded-lg p-3 border border-purple-100">
                    <div class="flex items-center gap-1.5 mb-1">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                        <span class="text-xs text-purple-700 font-medium">AI Pre-Observation Insights</span>
                    </div>
                    <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ is_array($observation->preObservationPlanning->ai_insights) ? (json_encode($observation->preObservationPlanning->ai_insights) ?: '') : $observation->preObservationPlanning->ai_insights }}</p>
                </div>
                @endif
                @if($observation->preConference->conference_date)
                <div>
                    <span class="text-gray-500 text-sm">Conference Date:</span>
                    <p class="text-gray-900">{{ $observation->preConference->conference_date->format('M d, Y') }}</p>
                </div>
                @endif
                @if($observation->preConference->lesson_plan_review)
                <div>
                    <span class="text-gray-500 text-sm">Lesson Plan Review:</span>
                    <p class="text-gray-900 mt-1">{{ $observation->preConference->lesson_plan_review }}</p>
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
                @if($observation->preConference->teacher_reflection)
                <div>
                    <span class="text-gray-500 text-sm">Teacher Reflection:</span>
                    <p class="text-gray-900 mt-1">{{ $observation->preConference->teacher_reflection }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Observation (COT Ratings) -->
        @if($observation->cotRatings && $observation->cotRatings->count() > 0)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Observation Ratings</h2>
                <div class="text-right">
                    <p class="text-sm text-gray-500">Overall Score</p>
                    <p class="text-gray-900 font-bold text-2xl">{{ number_format($observation->overall_score, 2) }} <span class="text-base text-gray-400 font-normal">/ 6.00</span></p>
                </div>
            </div>
            <div class="space-y-3">
                @foreach($observation->cotRatings as $rating)
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <span class="text-gray-500 text-sm">Domain:</span>
                            <p class="text-gray-900 font-medium">{{ $rating->domain }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500 text-sm">Indicator:</span>
                            <p class="text-gray-900">{{ $rating->indicator }}</p>
                        </div>
                        <div>
                            <span class="text-gray-500 text-sm">Rating:</span>
                            <p class="text-gray-900 font-medium">{{ $rating->not_observed ? 'NO' : number_format($rating->rating, 1) }} / 6</p>
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
                @if($observation->postConference->action_plan)
                <div>
                    <span class="text-gray-500 text-sm">Action Plan:</span>
                    <p class="text-gray-900 mt-1">{{ $observation->postConference->action_plan }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection