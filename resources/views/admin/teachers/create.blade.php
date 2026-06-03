@extends('layouts.a')

@section('title', 'Create New Teacher')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8 space-y-8">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border glass-card p-6">
        <div class="flex items-center">
            <a href="{{ route('admin.teachers.index') }}" class="mr-4 text-white hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-white">Create New Teacher</h1>
                <p class="text-white mt-1">Add a new teacher to the system.</p>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-xl shadow-sm border glass-card p-6">
        <form method="POST" action="{{ route('admin.teachers.store') }}" class="space-y-8">
            @csrf
            
            <!-- User Information -->
            <div>
                <h2 class="text-lg font-medium text-white mb-4 pb-2 border-b glass-card">User Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-white mb-1">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                               class="w-full px-3 py-2 border glass-card rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               {{ $errors->has('name') ? 'border-red-500' : '' }}"
                               placeholder="John Doe">
                        @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-white mb-1">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required
                               class="w-full px-3 py-2 border glass-card rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               {{ $errors->has('email') ? 'border-red-500' : '' }}"
                               placeholder="john@example.com">
                        @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="password" class="block text-sm font-medium text-white mb-1">
                            Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" id="password" name="password" required
                               class="w-full px-3 py-2 border glass-card rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               {{ $errors->has('password') ? 'border-red-500' : '' }}"
                               placeholder="Enter password">
                        @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-white mb-1">
                            Confirm Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required
                               class="w-full px-3 py-2 border glass-card rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               {{ $errors->has('password_confirmation') ? 'border-red-500' : '' }}"
                               placeholder="Confirm password">
                        @error('password_confirmation')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="school_id" class="block text-sm font-medium text-white mb-1">
                        School <span class="text-red-500">*</span>
                    </label>
                    <select id="school_id" name="school_id" required
                            class="w-full px-3 py-2 border bg-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                            {{ $errors->has('school_id') ? 'border-red-500' : '' }}">
                        <option value="">Select School</option>
                        @foreach($schools as $school)
                        <option value="{{ $school->id }}" 
                                {{ old('school_id') == $school->id ? 'selected' : '' }}>
                            {{ $school->name }}
                        </option>
                        @endforeach
                    </select>
                    @error('school_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Teacher Information -->
            <div>
                <h2 class="text-lg font-medium text-white mb-4 pb-2 border-b glass-card">Teacher Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="department" class="block text-sm font-medium text-white mb-1">
                            Department <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="department" name="department" value="{{ old('department') }}" required
                               class="w-full px-3 py-2 border glass-card rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               {{ $errors->has('department') ? 'border-red-500' : '' }}"
                               placeholder="e.g., Mathematics">
                        @error('department')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="position" class="block text-sm font-medium text-white mb-1">
                            Position
                        </label>
                        <input type="text" id="position" name="position" value="{{ old('position') }}"
                               class="w-full px-3 py-2 border glass-card rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               {{ $errors->has('position') ? 'border-red-500' : '' }}"
                               placeholder="e.g., Senior Teacher">
                        @error('position')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="years_of_service" class="block text-sm font-medium text-white mb-1">
                            Years of Service <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="years_of_service" name="years_of_service" 
                               value="{{ old('years_of_service') }}" min="0" required
                               class="w-full px-3 py-2 border glass-card rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               {{ $errors->has('years_of_service') ? 'border-red-500' : '' }}"
                               placeholder="5">
                        @error('years_of_service')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="mobile_number" class="block text-sm font-medium text-white mb-1">
                            Mobile Number
                        </label>
                        <input type="text" id="mobile_number" name="mobile_number" value="{{ old('mobile_number') }}"
                               class="w-full px-3 py-2 border glass-card rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               {{ $errors->has('mobile_number') ? 'border-red-500' : '' }}"
                               placeholder="+63 912 345 6789">
                        @error('mobile_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="prc_license_number" class="block text-sm font-medium text-white mb-1">
                            PRC License Number
                        </label>
                        <input type="text" id="prc_license_number" name="prc_license_number" 
                               value="{{ old('prc_license_number') }}"
                               class="w-full px-3 py-2 border glass-card rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               {{ $errors->has('prc_license_number') ? 'border-red-500' : '' }}"
                               placeholder="1234567">
                        @error('prc_license_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-between ">
                <a href="{{ route('admin.teachers.index') }}" 
                   class="px-4 py-2 text-white bg-white border glass-card rounded-lg hover:bg-slate-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Create Teacher
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
