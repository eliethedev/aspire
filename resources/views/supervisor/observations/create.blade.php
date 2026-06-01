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
    <div class="max-w-7xl mx-auto px-6 py-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-dark">Create New Observation</h1>
            <p class="text-dark/60 mt-1">Fill in the details below to create a new classroom observation.</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm glass-card p-6">
            <form method="POST" action="{{ route('supervisor.observations.store') }}" class="space-y-6">
                @csrf
                
                <!-- Teacher Selection -->
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

                <!-- Observation Date -->
                <div>
                    <label class="block text-sm font-medium text-dark mb-2">Observation Date</label>
                    <input type="date" name="observation_date" required value="{{ old('observation_date') }}"
                        class="w-full px-4 py-2 rounded-lg bg-white border border-white/20 text-dark focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('observation_date')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
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
    @endsection
