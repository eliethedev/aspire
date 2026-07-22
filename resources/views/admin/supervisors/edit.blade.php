@extends('layouts.admin')

@section('title', 'Edit Supervisor')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8 space-y-8">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border glass-card p-6">
        <div class="flex items-center">
            <a href="{{ route('admin.supervisors.index') }}" class="mr-4 text-white hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-white">Edit Supervisor</h1>
                <p class="text-white mt-1">{{ $supervisor->user->name }}</p>
            </div>
        </div>
    </div>

    <!-- Supervisor Info Card -->
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border glass-card p-6 mb-6">
        <div class="flex items-center space-x-4">
            <div class="w-16 h-16 bg-slate-200 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 007-7 0z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-medium text-white">{{ $supervisor->user->name }}</h3>
                <p class="text-sm text-white">{{ $supervisor->user->email }}</p>
                <div class="mt-2 flex items-center space-x-4">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 dark:bg-blue-900/200/20 text-blue-300">
                        {{ $supervisor->position ?? 'No Position' }}
                    </span>
                    <span class="text-sm text-white">
                        {{ $supervisor->school->name ?? 'No School' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border glass-card p-6">
        <form method="POST" action="{{ route('admin.supervisors.update', $supervisor) }}" class="space-y-8">
            @csrf
            @method('PUT')
            
            <!-- User Information -->
            <div>
                <h2 class="text-lg font-medium text-white mb-4 pb-2 border-b glass-card p-4 rounded-lg">User Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="fullName" class="block text-sm font-medium text-white mb-1">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="fullName" name="fullName" 
                               value="{{ old('fullName', $supervisor->user->name) }}" required
                               class="w-full px-3 py-2 text-white border glass-card rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               {{ $errors->has('fullName') ? 'border-red-500' : '' }}"
                               placeholder="Juan Dela Cruz">
                        @error('fullName')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-white mb-1">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <input type="email" id="email" name="email" 
                               value="{{ old('email', $supervisor->user->email) }}" required
                               class="w-full px-3 py-2 text-white border glass-card rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               {{ $errors->has('email') ? 'border-red-500' : '' }}"
                               placeholder="supervisor@school.edu.ph">
                        @error('email')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6">
                    <label for="phoneNumber" class="block text-sm font-medium text-white mb-1">
                        Phone Number
                    </label>
                    <input type="tel" id="phoneNumber" name="phoneNumber" 
                           value="{{ old('phoneNumber', $supervisor->phone_number) }}"
                           class="w-full px-3 py-2 text-white border glass-card rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                           {{ $errors->has('phoneNumber') ? 'border-red-500' : '' }}"
                           placeholder="+63 912 345 6789">
                    @error('phoneNumber')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Supervisor Information -->
            <div>
                <h2 class="text-lg font-medium text-white mb-4 pb-2 border-b glass-card p-4 rounded-lg">Supervisor Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="schoolId" class="block text-sm font-medium text-white mb-1">
                            School <span class="text-red-500">*</span>
                        </label>
                        <select id="schoolId" name="schoolId" required
                                class="w-full px-3 py-2 border bg-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                {{ $errors->has('schoolId') ? 'border-red-500' : '' }}">
                            <option value="">Select School</option>
                            @foreach($schools as $school)
                            <option value="{{ $school->id }}" 
                                    {{ old('schoolId', $supervisor->school_id) == $school->id ? 'selected' : '' }}>
                                {{ $school->name }}
                            </option>
                            @endforeach
                        </select>
                        @error('schoolId')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="employeeId" class="block text-sm font-medium text-white mb-1">
                            DepEd / Employee ID
                        </label>
                        <input type="text" id="employeeId" name="employeeId" 
                               value="{{ old('employeeId', $supervisor->employee_id) }}"
                               class="w-full px-3 py-2 text-white border glass-card rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               {{ $errors->has('employeeId') ? 'border-red-500' : '' }}"
                               placeholder="1234567">
                        @error('employeeId')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <label for="position" class="block text-sm font-medium text-white mb-1">
                            Position / Designation <span class="text-red-500">*</span>
                        </label>
                        <select id="position" name="position" required
                                class="w-full px-3 py-2 border bg-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                {{ $errors->has('position') ? 'border-red-500' : '' }}">
                            <option value="">Select Position</option>
                            <option value="Principal" {{ old('position', $supervisor->position) == 'Principal' ? 'selected' : '' }}>Principal</option>
                            <option value="Assistant Principal" {{ old('position', $supervisor->position) == 'Assistant Principal' ? 'selected' : '' }}>Assistant Principal</option>
                            <option value="Master Teacher" {{ old('position', $supervisor->position) == 'Master Teacher' ? 'selected' : '' }}>Master Teacher</option>
                            <option value="Head Teacher" {{ old('position', $supervisor->position) == 'Head Teacher' ? 'selected' : '' }}>Head Teacher</option>
                            <option value="Supervisor" {{ old('position', $supervisor->position) == 'Supervisor' ? 'selected' : '' }}>Supervisor</option>
                            <option value="Other" {{ old('position', $supervisor->position) == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('position')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="status" class="block text-sm font-medium text-white mb-1">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select id="status" name="status" required
                                class="w-full px-3 py-2 border bg-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                {{ $errors->has('status') ? 'border-red-500' : '' }}">
                            <option value="active" {{ old('status', $supervisor->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $supervisor->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('admin.supervisors.index') }}" 
                       class="px-4 py-2 text-white bg-white border glass-card rounded-lg hover:bg-slate-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Update Supervisor
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Delete Form Outside Main Form -->
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border glass-card p-6 mt-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-medium text-white">Danger Zone</h3>
                <p class="text-sm text-white mt-1">Once you delete a supervisor, You can't restore it.</p>
            </div>
            <form method="POST" 
                  action="{{ route('admin.supervisors.destroy', $supervisor) }}" 
                  onsubmit="return confirm('Are you sure you want to delete this supervisor? This action cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    Delete Supervisor
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
