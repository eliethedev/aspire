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

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8">
    <!-- Progress Steps -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-blue-600 text-white font-semibold">1</div>
                <span class="ml-2 text-white font-medium">Pre-Observation Planning</span>
            </div>
            <div class="flex-1 mx-4 h-1 bg-white/20"></div>
            <div class="flex items-center">
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-white/20 text-white/60 font-semibold">2</div>
                <span class="ml-2 text-white/60">Pre-Conference</span>
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
        <h1 class="text-2xl font-bold text-white">Pre-Observation Planning</h1>
        <p class="text-white/60 mt-1">Upload lesson plan and review AI-generated insights for {{ $observation->teacher->user->name }}</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm glass-card p-6">
        <form method="POST" action="{{ route('supervisor.observations.storePreObservationPlanning', $observation) }}" enctype="multipart/form-data" class="space-y-6">
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

            <!-- Lesson Plan Upload -->
            <div>
                <label class="block text-sm font-medium text-white mb-2">Lesson Plan Upload</label>
                <input type="file" name="lesson_plan_file" accept=".pdf,.doc,.docx"
                       class="w-full px-4 py-2 rounded-lg bg-white border border-white/20 text-dark focus:outline-none focus:ring-2 focus:ring-blue-500">
                @if($planning && $planning->lesson_plan_file)
                    <p class="mt-2 text-sm text-white/60">
                        Current file: <a href="{{ asset('storage/' . $planning->lesson_plan_file) }}" target="_blank" class="text-blue-400 hover:text-blue-300">View</a>
                    </p>
                @endif
                @error('lesson_plan_file')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- AI Insights -->
            <div>
                <label class="block text-sm font-medium text-white mb-2">AI Insights</label>
                <textarea name="ai_insights" rows="6" 
                          class="w-full px-4 py-2 rounded-lg bg-white border border-white/20 text-dark focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="AI-generated insights from the lesson plan analysis...">{{ old('ai_insights', $planning?->ai_insights) }}</textarea>
                @error('ai_insights')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Suggested Focus -->
            <div>
                <label class="block text-sm font-medium text-white mb-2">Suggested Focus Areas</label>
                <textarea name="suggested_focus" rows="4" 
                          class="w-full px-4 py-2 rounded-lg bg-white border border-white/20 text-dark focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Suggested focus areas for the observation based on the lesson plan...">{{ old('suggested_focus', $planning?->suggested_focus) }}</textarea>
                @error('suggested_focus')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Actions -->
            <div class="flex justify-between">
                <a href="{{ route('supervisor.observations.index') }}" 
                   class="px-6 py-2 rounded-lg border border-white/20 text-white hover:bg-white/10">
                    Cancel
                </a>
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                    Save & Continue to Pre-Conference
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
