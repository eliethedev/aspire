@extends('layouts.supervisor')

@section('title', 'Post-Conference')

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
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-green-600 text-white font-semibold">✓</div>
                <span class="ml-2 text-white font-medium">Pre-Conference</span>
            </div>
            <div class="flex-1 mx-4 h-1 bg-green-600"></div>
            <div class="flex items-center">
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-green-600 text-white font-semibold">✓</div>
                <span class="ml-2 text-white font-medium">Observation</span>
            </div>
            <div class="flex-1 mx-4 h-1 bg-green-600"></div>
            <div class="flex items-center">
                <div class="flex items-center justify-center w-10 h-10 rounded-full bg-blue-600 text-white font-semibold">4</div>
                <span class="ml-2 text-white font-medium">Post-Conference</span>
            </div>
        </div>
    </div>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Post-Conference</h1>
        <p class="text-white/60 mt-1">Provide feedback and review AI-generated insights for {{ $observation->teacher->user->name }}</p>
    </div>

    <!-- Observation Summary -->
    @if($cotRatings && $cotRatings->count() > 0)
    <div class="bg-white/5 rounded-xl p-6 mb-6">
        <h2 class="text-lg font-semibold text-white mb-4">Observation Summary</h2>
        <div class="space-y-3">
            <div>
                <span class="text-white/60 text-sm">Overall Score:</span>
                <p class="text-white font-medium text-2xl">{{ number_format($observation->overall_score, 2) }} / 5.00</p>
            </div>
            <div>
                <span class="text-white/60 text-sm">Total Ratings:</span>
                <p class="text-white">{{ $cotRatings->count() }} indicators rated</p>
            </div>
        </div>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm glass-card p-6">
        <form method="POST" action="{{ route('supervisor.observations.storePostConference', $observation) }}" class="space-y-6">
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
                <input type="date" name="conference_date" value="{{ old('conference_date', $postConference?->conference_date?->format('Y-m-d')) }}"
                       class="w-full px-4 py-2 rounded-lg bg-white border border-white/20 text-dark focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('conference_date')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- AI Comparison -->
            <div>
                <label class="block text-sm font-medium text-white mb-2">AI Comparison (Plan vs Actual)</label>
                <textarea name="ai_comparison" rows="6" 
                          class="w-full px-4 py-2 rounded-lg bg-white border border-white/20 text-dark focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="AI-generated comparison between lesson plan and actual observation...">{{ old('ai_comparison', $postConference?->ai_comparison) }}</textarea>
                @error('ai_comparison')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Feedback -->
            <div>
                <label class="block text-sm font-medium text-white mb-2">Feedback</label>
                <textarea name="feedback" rows="6" 
                          class="w-full px-4 py-2 rounded-lg bg-white border border-white/20 text-dark focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Provide comprehensive feedback to the teacher...">{{ old('feedback', $postConference?->feedback) }}</textarea>
                @error('feedback')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Actions -->
            <div class="flex justify-between">
                <a href="{{ route('supervisor.observations.observation', $observation) }}" 
                   class="px-6 py-2 rounded-lg border border-white/20 text-white hover:bg-white/10">
                    Back
                </a>
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                    Complete Observation
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
