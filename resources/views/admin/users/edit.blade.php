@extends('layouts.admin')

@section('title', 'Edit User')

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
                <h1 class="text-2xl font-bold text-dark">Edit User</h1>
                <p class="text-dark mt-1">Update user information and settings.</p>
            </div>
        </div>
    </div>

    <!-- User Info Card -->
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border glass-card p-6">
        <div class="flex items-center space-x-4">
            <div class="w-16 h-16 bg-slate-200 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-medium text-dark">{{ $user->name }}</h3>
                <p class="text-sm text-dark">{{ $user->email }}</p>
                <div class="flex items-center mt-1 space-x-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        @if($user->role === 'admin') bg-purple-100 dark:bg-purple-900/30 text-purple-800
                        @elseif($user->role === 'school_head') bg-blue-100 dark:bg-blue-900/30 text-blue-800
                        @elseif($user->role === 'supervisor') bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300
                        @elseif($user->role === 'teacher') bg-yellow-100 text-yellow-800
                        @endif">
                        {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                    </span>
                    <span class="text-xs text-dark">
                        Joined {{ $user->created_at->format('M j, Y') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border glass-card p-6">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6">
            @csrf
            @method('PUT')
            
            <!-- Personal Information -->
            <div>
                <h3 class="text-lg font-medium text-dark mb-4">Personal Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-dark mb-1">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                               class="w-full px-3 py-2 border glass-card text-dark rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="John Doe">
                        @error('name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-dark mb-1">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                               class="w-full px-3 py-2 border glass-card text-dark rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="john@example.com">
                        @error('email')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-amber-600 dark:text-amber-400">
                            Emergency edit only: changing the email also invalidates password reset links sent to the old address and un-verifies the new one.
                        </p>

                        <div class="mt-3 p-3 rounded-lg border border-amber-300 bg-amber-50 dark:bg-amber-900/20">
                            <label class="flex items-start gap-2 text-sm text-amber-800 dark:text-amber-300 cursor-pointer">
                                <input type="checkbox" id="confirm_email_change" name="confirm_email_change" value="1"
                                       class="mt-0.5 rounded border-amber-400 text-amber-600 focus:ring-amber-500">
                                <span>
                                    I confirm this is a deliberate email change for
                                    <strong>{{ $user->name }}</strong>.
                                </span>
                            </label>
                            @error('confirm_email_change')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
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
                                class="w-full px-3 py-2 border glass-card text-dark rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @foreach($roles as $role)
                            <option value="{{ $role }}" {{ old('role', $user->role) == $role ? 'selected' : '' }}>
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
                                class="w-full px-3 py-2 border glass-card text-dark rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">No School (System Admin)</option>
                            @foreach($schools as $school)
                            <option value="{{ $school->id }}" {{ old('school_id', $user->school_id) == $school->id ? 'selected' : '' }}>
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

            <!-- Form Actions -->
            <div class="flex flex-wrap items-center justify-between gap-3 pt-6">
                
                <div class="flex items-center space-x-4">
                    <a href="{{ route('admin.users.show', $user) }}" 
                       class="px-4 py-2 text-indigo-600 dark:text-indigo-400 border border-indigo-300 rounded-lg hover:bg-indigo-50 dark:bg-indigo-900/20 transition-colors">
                        View User
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('admin.users.index') }}" 
                       class="px-4 py-2 text-dark bg-white border glass-card rounded-lg hover:bg-slate-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        Update User
                    </button>
                </div>
            </div>
        </form>
    </div>
    </div>
</div>
@endsection
