@extends('layouts.admin')

@section('title', 'User Details')

@section('content')
<div class="max-w-7xl mx-auto px-6 space-y-8">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center">
                <a href="{{ route('admin.users.index') }}" class="mr-4 text-slate-600 hover:text-slate-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">User Details</h1>
                    <p class="text-slate-600 mt-1">View user information and activity.</p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('admin.users.edit', $user) }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit User
                </a>
            </div>
        </div>
    </div>

    <!-- User Profile -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile Card -->
        <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-slate-200 p-6">
                <div class="text-center">
                    <div class="w-24 h-24 bg-slate-200 rounded-full mx-auto flex items-center justify-center">
                        <svg class="w-12 h-12 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <h3 class="mt-4 text-lg font-medium text-slate-900">{{ $user->name }}</h3>
                    <p class="text-sm text-slate-500">{{ $user->email }}</p>
                    
                    <div class="mt-4 space-y-2">
                        <div class="flex items-center justify-center">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                @if($user->role === 'admin') bg-purple-100 dark:bg-purple-900/30 text-purple-800
                                @elseif($user->role === 'school_head') bg-blue-100 dark:bg-blue-900/30 text-blue-800
                                @elseif($user->role === 'supervisor') bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300
                                @elseif($user->role === 'teacher') bg-yellow-100 text-yellow-800
                                @endif">
                                {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                            </span>
                        </div>
                        
                        @if($user->school)
                        <div class="text-sm text-slate-600">
                            <strong>School:</strong> {{ $user->school->name }}
                        </div>
                        @endif
                    </div>
                </div>
                
                <div class="mt-6 pt-6 border-t border-slate-200 space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-600">User ID</span>
                        <span class="text-slate-900">#{{ $user->id }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-600">Joined</span>
                        <span class="text-slate-900">{{ $user->created_at->format('M j, Y') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-600">Last Updated</span>
                        <span class="text-slate-900">{{ $user->updated_at->format('M j, Y') }}</span>
                    </div>
                    @if($user->email_verified_at)
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-600">Email Verified</span>
                        <span class="text-green-600 dark:text-green-400">Yes</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Details and Activity -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Contact Information -->
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-slate-200 p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Contact Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Full Name</label>
                        <p class="text-slate-900">{{ $user->name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Email Address</label>
                        <p class="text-slate-900">{{ $user->email }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Role</label>
                        <p class="text-slate-900">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">School</label>
                        <p class="text-slate-900">{{ $user->school?->name ?? 'No School Assigned' }}</p>
                    </div>
                </div>
            </div>

            <!-- Teacher Specific Information (if applicable) -->
            @if($user->teacher)
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-slate-200 p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Teacher Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Employee ID</label>
                        <p class="text-slate-900">{{ $user->teacher->employee_id ?? 'Not Set' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Department</label>
                        <p class="text-slate-900">{{ $user->teacher->department ?? 'Not Set' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Total Observations</label>
                        <p class="text-slate-900">{{ $user->teacher->observations()->count() }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Completed Observations</label>
                        <p class="text-slate-900">{{ $user->teacher->observations()->completed()->count() }}</p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Recent Activity -->
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-slate-200 p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Recent Activity</h2>
                <div class="space-y-4">
                    <div class="flex items-start space-x-3">
                        <div class="w-2 h-2 bg-green-50 dark:bg-green-900/200 rounded-full mt-2"></div>
                        <div class="flex-1">
                            <p class="text-sm text-slate-900">Account created</p>
                            <p class="text-xs text-slate-500">{{ $user->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    
                    @if($user->email_verified_at)
                    <div class="flex items-start space-x-3">
                        <div class="w-2 h-2 bg-blue-50 dark:bg-blue-900/200 rounded-full mt-2"></div>
                        <div class="flex-1">
                            <p class="text-sm text-slate-900">Email verified</p>
                            <p class="text-xs text-slate-500">{{ $user->email_verified_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @endif
                    
                    <div class="flex items-start space-x-3">
                        <div class="w-2 h-2 bg-purple-50 dark:bg-purple-900/200 rounded-full mt-2"></div>
                        <div class="flex-1">
                            <p class="text-sm text-slate-900">Profile last updated</p>
                            <p class="text-xs text-slate-500">{{ $user->updated_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-slate-200 p-6">
                <h2 class="text-lg font-semibold text-slate-900 mb-4">Quick Actions</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <a href="{{ route('admin.users.edit', $user) }}" 
                       class="flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit User
                    </a>
                    
                    <form method="POST" action="{{ route('admin.users.sendPasswordReset', $user) }}">
                        @csrf
                        <button type="submit" 
                                class="w-full flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        Send Password Reset
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
