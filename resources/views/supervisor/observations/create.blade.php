@extends('layouts.supervisor')

@section('title', 'Create Observation')

@push('styles')
<style>
    select option {
        background-color: #1f2937;
        color: #ffffff;
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-dark">Create New Observation</h1>
        <p class="text-dark/60 mt-1">Fill in the details below to create a new classroom observation.</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm glass-card p-6">
        <form method="POST" action="{{ route('supervisor.observations.store') }}" class="space-y-6">
            @csrf
            
            <!-- Step 1: Teacher Selection -->
            <div class="border-b border-gray-200 pb-6">
                <h2 class="text-lg font-semibold text-dark mb-4">Step 1: Select Teacher</h2>
                
                <div>
                    <label class="block text-sm font-medium text-dark mb-2">Teacher</label>
                    <select name="teacher_id" required 
                            class="w-full px-4 py-2 rounded-lg bg-white border border-white/20 text-dark focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select a teacher</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                {{ $teacher->user->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('teacher_id')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Step 3: Observation Details -->
            <div class="border-b border-gray-200 pb-6" id="observation-details-section">
                <h2 class="text-lg font-semibold text-dark mb-4">Step 3: Observation Details</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- School Year -->
                    <div>
                        <label class="block text-sm font-medium text-dark mb-2">School Year</label>
                        <input type="text" name="school_year" value="{{ old('school_year', now()->year . '-' . (now()->year + 1)) }}"
                            class="w-full px-4 py-2 rounded-lg bg-white border border-white/20 text-dark focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="e.g., 2024-2025">
                        @error('school_year')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Quarter -->
                    <div>
                        <label class="block text-sm font-medium text-dark mb-2">Quarter</label>
                        <select name="quarter" 
                                class="w-full px-4 py-2 rounded-lg bg-white border border-white/20 text-dark focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select quarter</option>
                            <option value="1" {{ old('quarter') == 1 ? 'selected' : '' }}>1st Quarter</option>
                            <option value="2" {{ old('quarter') == 2 ? 'selected' : '' }}>2nd Quarter</option>
                            <option value="3" {{ old('quarter') == 3 ? 'selected' : '' }}>3rd Quarter</option>
                            <option value="4" {{ old('quarter') == 4 ? 'selected' : '' }}>4th Quarter</option>
                        </select>
                        @error('quarter')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Observation Number -->
                    <div>
                        <label class="block text-sm font-medium text-dark mb-2">Observation Number</label>
                        <select name="observation_number" 
                                class="w-full px-4 py-2 rounded-lg bg-white border border-white/20 text-dark focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="1" {{ old('observation_number') == 1 ? 'selected' : '' }}>1st Observation</option>
                            <option value="2" {{ old('observation_number') == 2 ? 'selected' : '' }}>2nd Observation</option>
                        </select>
                        @error('observation_number')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Observation Mode -->
                    <div>
                        <label class="block text-sm font-medium text-dark mb-2">Observation Mode</label>
                        <select name="observation_mode" 
                                class="w-full px-4 py-2 rounded-lg bg-white border border-white/20 text-dark focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="in_person" {{ old('observation_mode') == 'in_person' ? 'selected' : '' }}>In-Person</option>
                            <option value="virtual" {{ old('observation_mode') == 'virtual' ? 'selected' : '' }}>Virtual</option>
                            <option value="hybrid" {{ old('observation_mode') == 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                        </select>
                        @error('observation_mode')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Subject -->
                    <div>
                        <label class="block text-sm font-medium text-dark mb-2">Subject</label>
                        <input type="text" name="subject" value="{{ old('subject') }}"
                            class="w-full px-4 py-2 rounded-lg bg-white border border-white/20 text-dark focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="e.g., Mathematics">
                        @error('subject')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Grade Level -->
                    <div>
                        <label class="block text-sm font-medium text-dark mb-2">Grade Level</label>
                        <input type="text" name="grade_level" value="{{ old('grade_level') }}"
                            class="w-full px-4 py-2 rounded-lg bg-white border border-white/20 text-dark focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="e.g., Grade 10">
                        @error('grade_level')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Observation Date -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-dark mb-2">Observation Date</label>
                        <input type="date" name="observation_date" required value="{{ old('observation_date', now()->format('Y-m-d')) }}"
                            class="w-full px-4 py-2 rounded-lg bg-white border border-white/20 text-dark focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('observation_date')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Step 4: Schedule Type -->
            <div class="border-b border-gray-200 pb-6" id="schedule-type-section">
                <h2 class="text-lg font-semibold text-dark mb-4">Step 4: Schedule Type</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-dark mb-2">Schedule Type</label>
                        <select name="schedule_type" required 
                                class="w-full px-4 py-2 rounded-lg bg-white border border-white/20 text-dark focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="scheduled" {{ old('schedule_type') == 'scheduled' ? 'selected' : '' }}>Scheduled Observation</option>
                            <option value="immediate" {{ old('schedule_type') == 'immediate' ? 'selected' : '' }}>Immediate Observation</option>
                        </select>
                        @error('schedule_type')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-sm text-gray-500">
                            <strong>Scheduled:</strong> Teacher will be notified and you can prepare in advance.<br>
                            <strong>Immediate:</strong> Start the COT evaluation right away.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-sm font-medium text-dark mb-2">Notes</label>
                <textarea name="notes" rows="4" 
                        class="w-full px-4 py-2 rounded-lg bg-white border border-white/20 text-dark focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Add any additional notes...">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-4">
                <a href="{{ route('supervisor.observations.index') }}" 
                class="px-6 py-2 rounded-lg border border-white/20 text-dark hover:bg-white/10">
                    Cancel
                </a>
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                    Create Observation
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function toggleObserveeSelection() {
        const observationType = document.getElementById('observation_type').value;
        const teacherSelection = document.getElementById('teacher-selection');
        const schoolHeadSelection = document.getElementById('school-head-selection');
        const subjectField = document.getElementById('subject-field');
        const gradeLevelField = document.getElementById('grade-level-field');
        
        // Hide all selections first
        teacherSelection.classList.add('hidden');
        schoolHeadSelection.classList.add('hidden');
        
        // Show relevant selection based on observation type
        if (observationType === 'teacher_observation') {
            teacherSelection.classList.remove('hidden');
            subjectField.classList.remove('hidden');
            gradeLevelField.classList.remove('hidden');
        } else if (observationType === 'school_head_observation') {
            schoolHeadSelection.classList.remove('hidden');
            subjectField.classList.add('hidden');
            gradeLevelField.classList.add('hidden');
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleObserveeSelection();
    });
</script>
@endpush
@endsection
