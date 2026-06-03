@extends('layouts.supervisor')

@section('title', 'Observation Details')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white">Observation Details</h1>
            <p class="text-white/60 mt-1">{{ $observation->observee->user->name }} - {{ $observation->observation_date->format('M d, Y') }}</p>
            <p class="text-white/40 text-sm mt-1">
                {{ $observation->isTeacherObservation() ? 'Teacher Observation' : 'School Head Observation' }}
                @if($observation->isTeacherObservation() && $observation->subject)
                    | {{ $observation->subject }} - {{ $observation->grade_level }}
                @endif
            </p>
        </div>
        <a href="{{ route('supervisor.observations.index') }}" 
           class="px-6 py-2 rounded-lg border border-white/20 text-white hover:bg-white/10">
            Back to List
        </a>
    </div>

    <!-- Progress Steps -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="flex items-center justify-center w-10 h-10 rounded-full {{ $observation->preObservationPlanning ? 'bg-green-600' : 'bg-white/20' }} text-white font-semibold">
                    {{ $observation->preObservationPlanning ? '✓' : '1' }}
                </div>
                <span class="ml-2 {{ $observation->preObservationPlanning ? 'text-white' : 'text-white/60' }} font-medium">Pre-Observation Planning</span>
            </div>
            <div class="flex-1 mx-4 h-1 {{ $observation->preConference ? 'bg-green-600' : 'bg-white/20' }}"></div>
            <div class="flex items-center">
                <div class="flex items-center justify-center w-10 h-10 rounded-full {{ $observation->preConference ? 'bg-green-600' : 'bg-white/20' }} text-white font-semibold">
                    {{ $observation->preConference ? '✓' : '2' }}
                </div>
                <span class="ml-2 {{ $observation->preConference ? 'text-white' : 'text-white/60' }} font-medium">Pre-Conference</span>
            </div>
            <div class="flex-1 mx-4 h-1 {{ $observation->cotRatings && $observation->cotRatings->count() > 0 ? 'bg-green-600' : 'bg-white/20' }}"></div>
            <div class="flex items-center">
                <div class="flex items-center justify-center w-10 h-10 rounded-full {{ $observation->cotRatings && $observation->cotRatings->count() > 0 ? 'bg-green-600' : 'bg-white/20' }} text-white font-semibold">
                    {{ $observation->cotRatings && $observation->cotRatings->count() > 0 ? '✓' : '3' }}
                </div>
                <span class="ml-2 {{ $observation->cotRatings && $observation->cotRatings->count() > 0 ? 'text-white' : 'text-white/60' }} font-medium">Observation</span>
            </div>
            <div class="flex-1 mx-4 h-1 {{ $observation->postConference ? 'bg-green-600' : 'bg-white/20' }}"></div>
            <div class="flex items-center">
                <div class="flex items-center justify-center w-10 h-10 rounded-full {{ $observation->postConference ? 'bg-green-600' : 'bg-white/20' }} text-white font-semibold">
                    {{ $observation->postConference ? '✓' : '4' }}
                </div>
                <span class="ml-2 {{ $observation->postConference ? 'text-white' : 'text-white/60' }} font-medium">Post-Conference</span>
            </div>
        </div>
    </div>

    <!-- Stage Details -->
    <div class="space-y-6">
        <!-- Pre-Observation Planning -->
        @if($observation->preObservationPlanning)
        <div class="bg-white rounded-xl shadow-sm glass-card p-6">
            <h2 class="text-lg font-semibold text-white mb-4">Pre-Observation Planning</h2>
            <div class="space-y-3">
                @if($observation->preObservationPlanning->lesson_plan_file)
                <div>
                    <span class="text-white/60 text-sm">Lesson Plan:</span>
                    <a href="{{ asset('storage/' . $observation->preObservationPlanning->lesson_plan_file) }}" target="_blank" class="text-blue-400 hover:text-blue-300 ml-2">View File</a>
                </div>
                @endif
                @if($observation->preObservationPlanning->ai_insights)
                <div>
                    <span class="text-white/60 text-sm">AI Insights:</span>
                    <p class="text-white mt-1">{{ $observation->preObservationPlanning->ai_insights }}</p>
                </div>
                @endif
                @if($observation->preObservationPlanning->suggested_focus)
                <div>
                    <span class="text-white/60 text-sm">Suggested Focus:</span>
                    <p class="text-white mt-1">{{ $observation->preObservationPlanning->suggested_focus }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Pre-Conference -->
        @if($observation->preConference)
        <div class="bg-white rounded-xl shadow-sm glass-card p-6">
            <h2 class="text-lg font-semibold text-white mb-4">Pre-Conference</h2>
            <div class="space-y-3">
                @if($observation->preConference->conference_date)
                <div>
                    <span class="text-white/60 text-sm">Conference Date:</span>
                    <p class="text-white">{{ $observation->preConference->conference_date->format('M d, Y') }}</p>
                </div>
                @endif
                @if($observation->preConference->discussion_notes)
                <div>
                    <span class="text-white/60 text-sm">Discussion Notes:</span>
                    <p class="text-white mt-1">{{ $observation->preConference->discussion_notes }}</p>
                </div>
                @endif
                @if($observation->preConference->finalized_focus)
                <div>
                    <span class="text-white/60 text-sm">Finalized Focus:</span>
                    <p class="text-white mt-1">{{ $observation->preConference->finalized_focus }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Observation (COT Ratings) -->
        @if($observation->cotRatings && $observation->cotRatings->count() > 0)
        <div class="bg-white rounded-xl shadow-sm glass-card p-6">
            <h2 class="text-lg font-semibold text-white mb-4">Observation Ratings</h2>
            <div class="mb-4">
                <span class="text-white/60 text-sm">Overall Score:</span>
                <p class="text-white font-medium text-2xl">{{ number_format($observation->overall_score, 2) }} / 5.00</p>
            </div>
            <div class="space-y-3">
                @foreach($observation->cotRatings as $rating)
                <div class="bg-white/5 rounded-lg p-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <span class="text-white/60 text-sm">Domain:</span>
                            <p class="text-white">{{ $rating->domain }}</p>
                        </div>
                        <div>
                            <span class="text-white/60 text-sm">Indicator:</span>
                            <p class="text-white">{{ $rating->indicator }}</p>
                        </div>
                        <div>
                            <span class="text-white/60 text-sm">Rating:</span>
                            <p class="text-white font-medium">{{ $rating->rating }} / 5</p>
                        </div>
                    </div>
                    @if($rating->comments)
                    <div class="mt-2">
                        <span class="text-white/60 text-sm">Comments:</span>
                        <p class="text-white mt-1">{{ $rating->comments }}</p>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Post-Conference -->
        @if($observation->postConference)
        <div class="bg-white rounded-xl shadow-sm glass-card p-6">
            <h2 class="text-lg font-semibold text-white mb-4">Post-Conference</h2>
            <div class="space-y-3">
                @if($observation->postConference->conference_date)
                <div>
                    <span class="text-white/60 text-sm">Conference Date:</span>
                    <p class="text-white">{{ $observation->postConference->conference_date->format('M d, Y') }}</p>
                </div>
                @endif
                @if($observation->postConference->ai_comparison)
                <div>
                    <span class="text-white/60 text-sm">AI Comparison (Plan vs Actual):</span>
                    <p class="text-white mt-1">{{ $observation->postConference->ai_comparison }}</p>
                </div>
                @endif
                @if($observation->postConference->feedback)
                <div>
                    <span class="text-white/60 text-sm">Feedback:</span>
                    <p class="text-white mt-1">{{ $observation->postConference->feedback }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    <!-- Action Buttons -->
    @if($observation->stage !== 'post_conference' || $observation->status !== 'completed')
    <div class="mt-6 flex justify-end gap-4">
        @if($observation->stage === 'pre_observation_planning')
            <a href="{{ route('supervisor.observations.preObservationPlanning', $observation) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                Continue to Pre-Observation Planning
            </a>
        @elseif($observation->stage === 'pre_conference')
            <a href="{{ route('supervisor.observations.preConference', $observation) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                Continue to Pre-Conference
            </a>
        @elseif($observation->stage === 'observation')
            <a href="{{ route('supervisor.observations.observation', $observation) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                Continue to Observation
            </a>
        @elseif($observation->stage === 'post_conference' && $observation->status !== 'completed')
            <a href="{{ route('supervisor.observations.postConference', $observation) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                Continue to Post-Conference
            </a>
        @endif
    </div>
    @endif
</div>
@endsection
