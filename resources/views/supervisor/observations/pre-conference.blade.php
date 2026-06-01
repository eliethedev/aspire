@extends('layouts.supervisor')

@section('title', 'Pre-Conference')

@push('styles')
<style>
    select option {
        background-color: #1f2937;
        color: #ffffff;
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8">
    <!-- Progress Steps -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-green-600 text-white font-semibold">✓</div>
                <span class="ml-2 text-white font-medium">Pre-Observation Planning</span>
            </div>
            <div class="flex-1 mx-4 h-1 bg-green-600"></div>
            <div class="flex items-center">
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-blue-600 text-white font-semibold">2</div>
                <span class="ml-2 text-white font-medium">Pre-Conference</span>
            </div>
            <div class="flex-1 mx-4 h-1 bg-white/20"></div>
            <div class="flex items-center">
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-white/20 text-white/60 font-semibold">3</div>
                <span class="ml-2 text-white/60">Observation</span>
            </div>
            <div class="flex-1 mx-4 h-1 bg-white/20"></div>
            <div class="flex items-center">
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-white/20 text-white/60 font-semibold">4</div>
                <span class="ml-2 text-white/60">Post-Conference</span>
            </div>
        </div>
    </div>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Pre-Conference</h1>
        <p class="text-white/60 mt-1">Meet with the teacher to discuss the lesson plan and finalize observation focus</p>
    </div>

    <!-- Pre-Observation Planning Summary -->
    @if($planning)
    <div class="bg-white/5 rounded-xl p-6 mb-6">
        <h2 class="text-lg font-semibold text-white mb-4">Pre-Observation Planning Summary</h2>
        <div class="space-y-3">
            @if($planning->lesson_plan_file)
            <div>
                <span class="text-white/60 text-sm">Lesson Plan:</span>
                <a href="{{ asset('storage/' . $planning->lesson_plan_file) }}" target="_blank" class="text-blue-400 hover:text-blue-300 ml-2">View File</a>
            </div>
            @endif
            @if($planning->ai_insights)
            <div>
                <span class="text-white/60 text-sm">AI Insights:</span>
                <p class="text-white mt-1">{{ $planning->ai_insights }}</p>
            </div>
            @endif
            @if($planning->suggested_focus)
            <div>
                <span class="text-white/60 text-sm">Suggested Focus:</span>
                <p class="text-white mt-1">{{ $planning->suggested_focus }}</p>
            </div>
            @endif
        </div>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm glass-card p-6">
        <form method="POST" action="{{ route('supervisor.observations.storePreConference', $observation) }}" class="space-y-6">
            @csrf
            
            <!-- Teacher Info -->
            <div class="bg-white/5 rounded-lg p-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-white/60 text-sm">Teacher</span>
                        <p class="text-white font-medium">{{ $observation->teacher->user->name }}</p>
                    </div>
                    <div>
                        <span class="text-white/60 text-sm">Observation Date</span>
                        <p class="text-white font-medium">{{ $observation->observation_date->format('M d, Y') }}</p>
                    </div>
                </div>
            </div>

            <!-- Conference Date -->
            <div>
                <label class="block text-sm font-medium text-white mb-2">Conference Date</label>
                <input type="date" name="conference_date" value="{{ old('conference_date', $preConference?->conference_date?->format('Y-m-d')) }}"
                       class="w-full px-4 py-2 rounded-lg bg-white border border-white/20 text-dark focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('conference_date')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Discussion Notes -->
            <div>
                <label class="block text-sm font-medium text-white mb-2">Discussion Notes</label>
                <textarea name="discussion_notes" rows="6" 
                          class="w-full px-4 py-2 rounded-lg bg-white border border-white/20 text-dark focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Notes from the pre-conference meeting with the teacher...">{{ old('discussion_notes', $preConference?->discussion_notes) }}</textarea>
                @error('discussion_notes')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Finalized Focus -->
            <div>
                <label class="block text-sm font-medium text-white mb-2">Finalized Observation Focus</label>
                <textarea name="finalized_focus" rows="4" 
                          class="w-full px-4 py-2 rounded-lg bg-white border border-white/20 text-dark focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Finalized focus areas for the classroom observation...">{{ old('finalized_focus', $preConference?->finalized_focus) }}</textarea>
                @error('finalized_focus')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Actions -->
            <div class="flex justify-between">
                <a href="{{ route('supervisor.observations.preObservationPlanning', $observation) }}" 
                   class="px-6 py-2 rounded-lg border border-white/20 text-white hover:bg-white/10">
                    Back
                </a>
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                    Save & Continue to Observation
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
