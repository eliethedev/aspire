@extends('layouts.admin')

@section('title', 'Add New User')

@section('content')
<div class="max-w-7xl mx-auto px-6 space-y-8">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border glass-card p-6">
        <div class="flex items-center">
            <a href="{{ route('admin.users.index') }}" class="mr-4 text-dark hover:text-dark">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-dark">Add New User</h1>
                <p class="text-dark mt-1">Create a new user account in the system.</p>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border glass-card p-6">
        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-6">
            @csrf
            
            <!-- Personal Information -->
            <div>
                <h3 class="text-lg font-medium text-dark mb-4">Personal Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-dark mb-1">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required
                               class="w-full px-3 py-2 border text-dark glass-card rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="John Doe">
                        @error('name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-dark mb-1">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required
                               class="w-full px-3 py-2 border text-dark glass-card rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="john@example.com">
                        @error('email')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Role Assignment -->
            <div>
                <h3 class="text-lg font-medium text-dark mb-4">Role Assignment</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="role" class="block text-sm font-medium text-dark mb-1">
                            User Role <span class="text-red-500">*</span>
                        </label>
                        <select id="role" name="role" required
                                class="w-full px-3 py-2 border bg-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select a role</option>
                            @foreach($roles as $role)
                            <option value="{{ $role }}" {{ old('role') == $role ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $role)) }}
                            </option>
                            @endforeach
                        </select>
                        @error('role')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-dark">
                            Select the appropriate role for this user based on their responsibilities.
                        </p>
                    </div>
                    
                    <div>
                        <label for="school_id" class="block text-sm font-medium text-dark mb-1">
                            School Assignment
                        </label>
                        <select id="school_id" name="school_id"
                                class="w-full px-3 py-2 border bg-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">No School (System Admin)</option>
                            @foreach($schools as $school)
                            <option value="{{ $school->id }}" {{ old('school_id') == $school->id ? 'selected' : '' }}>
                                    {{ $school->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('school_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-dark">
                            Assign to a school if this user is not a system administrator.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Password -->
            <div>
                <h3 class="text-lg font-medium text-dark mb-4">Password</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="password" class="block text-sm font-medium text-dark mb-1">
                            Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" id="password" name="password" required
                               class="w-full px-3 py-2 border text-dark glass-card rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
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
                               class="w-full px-3 py-2 border text-dark glass-card rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Confirm password">
                        @error('password_confirmation')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <p class="mt-2 text-sm text-dark">
                    Password must be at least 8 characters and include uppercase, lowercase, numbers, and special characters.
                </p>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-4 pt-6 ">
                <a href="{{ route('admin.users.index') }}" 
                   class="px-4 py-2 text-dark bg-white border glass-card rounded-lg hover:bg-slate-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Create User
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
