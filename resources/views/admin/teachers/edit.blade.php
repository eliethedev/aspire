@extends('layouts.admin')

@section('title', 'Edit Teacher')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8 space-y-8">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border glass-card p-6">
        <div class="flex items-center">
            <a href="{{ route('admin.teachers.index') }}" class="mr-4 text-dark dark:text-white hover:text-dark">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-dark dark:text-gray-300 ">Edit Teacher</h1>
                <p class="text-dark dark:text-gray-300  mt-1">{{ $teacher->user->name }}</p>
            </div>
        </div>
    </div>

    <!-- Teacher Info Card -->
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border glass-card p-6 mb-6">
        <div class="flex items-center space-x-4">
            <div class="w-16 h-16 bg-slate-200 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 007-7 0z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-medium text-dark dark:text-gray-300 ">{{ $teacher->user->name }}</h3>
                <p class="text-sm text-dark dark:text-gray-300 ">{{ $teacher->user->email }}</p>
                <div class="mt-2 flex items-center space-x-4">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        {{ $teacher->department ?? 'No Department' }}
                    </span>
                    <span class="text-sm text-dark dark:text-gray-300 ">
                        {{ $teacher->user->school->name ?? 'No School' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border glass-card p-6">
        <form method="POST" action="{{ route('admin.teachers.update', $teacher) }}" class="space-y-8">
            @csrf
            @method('PUT')
            
            <!-- User Information -->
            <div>
                <h2 class="text-lg font-medium text-dark mb-4 pb-2 border-b dark:text-gray-300 ">User Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-dark dark:text-gray-300 mb-1">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="name" name="name" 
                               value="{{ old('name', $teacher->user->name) }}" required
                               class="w-full px-3 py-2 border glass-card rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               {{ $errors->has('name') ? 'border-red-500' : '' }}"
                               placeholder="John Doe">
                        @error('name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-dark dark:text-gray-300 mb-1">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <input type="email" id="email" name="email" 
                               value="{{ old('email', $teacher->user->email) }}" required
                               class="w-full px-3 py-2 border glass-card rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               {{ $errors->has('email') ? 'border-red-500' : '' }}"
                               placeholder="john@example.com">
                        @error('email')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-2 mt-4 mb-2">
                    <p class="text-sm text-blue-800 dark:text-gray-300 ">
                        Leave password fields empty to keep the current password. Only enter a new password if you want to change it.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="password" class="block text-sm font-medium text-dark dark:text-gray-300 mb-1">
                            Password
                        </label>
                        <input type="password" id="password" name="password"
                               class="w-full px-3 py-2 border glass-card rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               {{ $errors->has('password') ? 'border-red-500' : '' }}"
                               placeholder="Enter new password (optional)">
                        @error('password')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-dark dark:text-gray-300 mb-1">
                            Confirm Password
                        </label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="w-full px-3 py-2 border glass-card rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               {{ $errors->has('password_confirmation') ? 'border-red-500' : '' }}"
                               placeholder="Confirm new password">
                        @error('password_confirmation')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="school_id" class="block text-sm font-medium text-dark dark:text-gray-300 mb-1">
                        School <span class="text-red-500">*</span>
                    </label>
                    <select id="school_id" name="school_id" required
                            class="w-full px-3 py-2 border bg-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                            {{ $errors->has('school_id') ? 'border-red-500' : '' }}">
                        <option value="">Select School</option>
                        @foreach($schools as $school)
                        <option value="{{ $school->id }}" 
                                {{ old('school_id', $teacher->school_id) == $school->id ? 'selected' : '' }}>
                            {{ $school->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('school_id')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Teacher Information -->
            <div>
                <h2 class="text-lg font-medium text-dark mb-4 pb-2 border-b dark:text-gray-300 ">Teacher Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="department" class="block text-sm font-medium text-dark dark:text-gray-300 mb-1">
                            Department <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="department" name="department" 
                               value="{{ old('department', $teacher->department) }}" required
                               class="w-full px-3 py-2 border glass-card rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               {{ $errors->has('department') ? 'border-red-500' : '' }}"
                               placeholder="e.g., Mathematics">
                        @error('department')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="position" class="block text-sm font-medium text-dark dark:text-gray-300 mb-1">
                            Position
                        </label>
                        <input type="text" id="position" name="position" 
                               value="{{ old('position', $teacher->position) }}"
                               class="w-full px-3 py-2 border glass-card rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               {{ $errors->has('position') ? 'border-red-500' : '' }}"
                               placeholder="e.g., Teacher II">
                        @error('position')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="career_stage" class="block text-sm font-medium text-dark dark:text-gray-300 mb-1">
                            Career Stage
                        </label>
                        <select id="career_stage" name="career_stage"
                                class="w-full px-3 py-2 border bg-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                                {{ $errors->has('career_stage') ? 'border-red-500' : '' }}">
                            <option value="">Auto-detect from position</option>
                            @foreach(App\Enums\TeacherCareerStage::options() as $value => $label)
                            <option value="{{ $value }}" {{ old('career_stage', $teacher->career_stage) === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Determines which COT instrument applies to this teacher.</p>
                        @error('career_stage')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="years_of_service" class="block text-sm font-medium text-dark dark:text-gray-300 mb-1">
                            Years of Service <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="years_of_service" name="years_of_service" 
                               value="{{ old('years_of_service', $teacher->years_of_service) }}" min="0" required
                               class="w-full px-3 py-2 border glass-card rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               {{ $errors->has('years_of_service') ? 'border-red-500' : '' }}"
                               placeholder="5">
                        @error('years_of_service')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="mobile_number" class="block text-sm font-medium text-dark dark:text-gray-300 mb-1">
                            Mobile Number
                        </label>
                        <input type="text" id="mobile_number" name="mobile_number" 
                               value="{{ old('mobile_number', $teacher->mobile_number) }}"
                               class="w-full px-3 py-2 border glass-card rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               {{ $errors->has('mobile_number') ? 'border-red-500' : '' }}"
                               placeholder="+63 912 345 6789">
                        @error('mobile_number')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="prc_license_number" class="block text-sm font-medium text-dark dark:text-gray-300 mb-1">
                            PRC License Number
                        </label>
                        <input type="text" id="prc_license_number" name="prc_license_number" 
                               value="{{ old('prc_license_number', $teacher->prc_license_number) }}"
                               class="w-full px-3 py-2 border glass-card rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               {{ $errors->has('prc_license_number') ? 'border-red-500' : '' }}"
                               placeholder="1234567">
                        @error('prc_license_number')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('admin.teachers.index') }}" 
                       class="px-4 py-2 text-dark dark:text-gray-300 bg-white border glass-card rounded-lg hover:bg-slate-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-blue-600 text-white dark:text-gray-300 rounded-lg hover:bg-blue-700 transition-colors">
                        Update Teacher
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Delete Form Outside Main Form -->
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border glass-card p-6 mt-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-medium text-dark dark:text-gray-300">Danger Zone</h3>
                <p class="text-sm text-dark dark:text-gray-300 mt-1">Once you delete a teacher, You can't restore it.</p>
            </div>
            <form method="POST" 
                  action="{{ route('admin.teachers.destroy', $teacher) }}" 
                  onsubmit="return confirm('Are you sure you want to delete this teacher? This action cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="px-4 py-2 bg-red-600 text-dark dark:text-gray-300 rounded-lg hover:bg-red-700 transition-colors">
                    Delete Teacher
                </button>
            </form>
        </div>
    </div>
@endsection
