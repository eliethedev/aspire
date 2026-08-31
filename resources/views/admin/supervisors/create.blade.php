@extends('layouts.admin')

@section('title', 'Create New Supervisor')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8 space-y-8">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border glass-card p-6">
        <div class="flex items-center">
            <a href="{{ route('admin.supervisors.index') }}" class="mr-4 text-dark hover:text-blue-600 dark:text-blue-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-dark">Create New Supervisor</h1>
                <p class="text-dark mt-1">Add a new supervisor to the system.</p>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border glass-card p-6">
        <form method="POST" action="{{ route('admin.supervisors.store') }}" class="space-y-8">
            @csrf
            
            <!-- User Information -->
            <div>
                <h2 class="text-lg font-medium text-dark mb-4 p-2 rounded-lg pb-2 border-b glass-card">User Information</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="fullName" class="block text-sm font-medium text-dark mb-1">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="fullName" name="fullName" value="{{ old('fullName') }}" required
                               class="w-full px-3 text-dark py-2 border glass-card rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               {{ $errors->has('fullName') ? 'border-red-500' : '' }}"
                               placeholder="Juan Dela Cruz">
                        @error('fullName')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-dark mb-1">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required
                               class="w-full px-3 py-2 text-dark border glass-card rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               {{ $errors->has('email') ? 'border-red-500' : '' }}"
                               placeholder="supervisor@school.edu.ph">
                        @error('email')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <label for="password" class="block text-sm font-medium text-dark mb-1">
                            Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" id="password" name="password" required
                               class="w-full px-3 py-2 text-dark border glass-card rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               {{ $errors->has('password') ? 'border-red-500' : '' }}"
                               placeholder="Enter password">
                        @error('password')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-dark mb-1">
                            Confirm Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required
                               class="w-full px-3 py-2 text-dark border glass-card rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               {{ $errors->has('password_confirmation') ? 'border-red-500' : '' }}"
                               placeholder="Confirm password">
                        @error('password_confirmation')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Supervisor Information -->
            <div>
                <h2 class="text-lg font-medium text-dark mb-4 p-2 rounded-lg pb-2 border-b glass-card">Supervisor Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="schoolId" class="block text-sm font-medium text-dark mb-1">
                            School <span class="text-red-500">*</span>
                        </label>
                        <select id="schoolId" name="schoolId" required
                                class="w-full px-3 py-2 border bg-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                {{ $errors->has('schoolId') ? 'border-red-500' : '' }}">
                            <option value="">Select School</option>
                            @foreach($schools as $school)
                            <option value="{{ $school->id }}" 
                                    {{ old('schoolId') == $school->id ? 'selected' : '' }}>
                                {{ $school->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('schoolId')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="employeeId" class="block text-sm font-medium text-dark mb-1">
                            DepEd / Employee ID
                        </label>
                        <input type="text" id="employeeId" name="employeeId" value="{{ old('employeeId') }}"
                               class="w-full px-3 py-2 text-dark border glass-card rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               {{ $errors->has('employeeId') ? 'border-red-500' : '' }}"
                               placeholder="1234567">
                        @error('employeeId')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <label for="position" class="block text-sm font-medium text-dark mb-1">
                            Position / Designation <span class="text-red-500">*</span>
                        </label>
                        <select id="position" name="position" required
                                class="w-full px-3 py-2 border bg-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                {{ $errors->has('position') ? 'border-red-500' : '' }}">
                            <option value="">Select Position</option>
                            <option value="Principal" {{ old('position') == 'Principal' ? 'selected' : '' }}>Principal</option>
                            <option value="Assistant Principal" {{ old('position') == 'Assistant Principal' ? 'selected' : '' }}>Assistant Principal</option>
                            <option value="Master Teacher" {{ old('position') == 'Master Teacher' ? 'selected' : '' }}>Master Teacher</option>
                            <option value="Head Teacher" {{ old('position') == 'Head Teacher' ? 'selected' : '' }}>Head Teacher</option>
                            <option value="Supervisor" {{ old('position') == 'Supervisor' ? 'selected' : '' }}>Supervisor</option>
                            <option value="Other" {{ old('position') == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('position')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="status" class="block text-sm font-medium text-dark mb-1">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select id="status" name="status" required
                                class="w-full px-3 py-2 border bg-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                {{ $errors->has('status') ? 'border-red-500' : '' }}">
                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-between ">
                <a href="{{ route('admin.supervisors.index') }}" 
                   class="px-4 py-2 text-dark bg-white border glass-card rounded-lg hover:bg-slate-100 transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Create Supervisor
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
