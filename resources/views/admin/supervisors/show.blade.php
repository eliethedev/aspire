@extends('layouts.admin')

@section('title', 'Supervisor Details')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8 space-y-8">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border glass-card p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-dark dark:text-gray-300">Supervisor Details</h1>
                <p class="text-dark dark:text-gray-300 mt-1">View supervisor profile and information.</p>
            </div>
            <div class="flex flex-wrap items-center gap-4">
                <a href="{{ route('admin.supervisors.edit', $supervisor) }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-50 dark:bg-blue-900/200 text-white dark:text-dark rounded-lg hover:bg-indigo-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828L8.586 8.586z"/>
                    </svg>
                    Edit Supervisor
                </a>
                <a href="{{ route('admin.supervisors.index') }}" 
                   class="inline-flex items-center px-4 py-2 text-dark dark:text-gray-300 glass-card rounded-lg hover:bg-white/10 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to List
                </a>
            </div>
        </div>
    </div>

    <!-- Supervisor Profile Card -->
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border glass-card p-6 mb-6">
        <div class="flex items-start space-x-6">
            <div class="w-20 h-20 bg-slate-200 rounded-full flex items-center justify-center">
                <svg class="w-10 h-10 text-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 007-7 0z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-dark dark:text-gray-300 ">{{ $supervisor->user->name }}</h2>
                <p class="text-dark">{{ $supervisor->user->email }}</p>
                <div class="mt-3 flex flex-wrap items-center gap-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-50 dark:bg-blue-900/200 text-white dark:text-dark ">
                        {{ $supervisor->position ?? 'No Position' }}
                    </span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-50 dark:bg-green-900/200 text-white dark:text-dark ">
                        {{ $supervisor->school->name ?? 'No School' }}
                    </span>
                    @if($supervisor->user->email_verified_at)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-50 dark:bg-green-900/200 text-white dark:text-dark ">
                        Email Verified
                    </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Information Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- User Information -->
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border glass-card p-6">
            <div class="flex items-center mb-4">
                <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 007-7 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-dark">User Information</h3>
            </div>
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-sm font-medium text-dark/80">Full Name</h4>
                        <p class="text-dark">{{ $supervisor->user->name }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-dark/80">Email Address</h4>
                        <p class="text-dark">{{ $supervisor->user->email }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-sm font-medium text-dark/80">Role</h4>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-50 dark:bg-purple-900/200 text-white">
                            {{ ucfirst($supervisor->user->role) }}
                        </span>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-dark/80">School</h4>
                        <p class="text-dark">{{ $supervisor->school->name ?? 'N/A' }}</p>
                    </div>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-dark/80">Email Status</h4>
                    @if($supervisor->user->email_verified_at)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 dark:bg-green-900/200 text-white">
                        Verified
                    </span>
                    @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-500 text-white">
                        Not Verified
                    </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Supervisor Information -->
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border glass-card p-6">
            <div class="flex items-center mb-4">
                <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-dark">Supervisor Information</h3>
            </div>
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-sm font-medium text-dark/80">Position</h4>
                        <p class="text-dark">{{ $supervisor->position ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-dark/80">Status</h4>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $supervisor->status === 'active' ? 'bg-green-50 dark:bg-green-900/200 text-white' : 'bg-slate-500 text-slate-300' }}">
                            {{ ucfirst($supervisor->status) }}
                        </span>
                    </div>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-dark/80">Employee ID</h4>
                    <p class="text-dark">{{ $supervisor->employee_id ?? 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- System Information -->
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border glass-card p-6">
        <div class="flex items-center mb-4">
            <div class="w-8 h-8 bg-slate-100 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002 2v6m-6 0V5a2 2 0 00-2-2H5a2 2 0 00-2 2v3m0 2h4a2 2 0 012 2v10a2 2 0 012 2H7a2 2 0 002-2v-3m0 2h4a2 2 0 012 2v10a2 2 0 012 2H7a2 2 0 002-2v-3z"/>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-dark">System Information</h3>
        </div>
        <div class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <h4 class="text-sm font-medium text-dark">User ID</h4>
                    <p class="text-dark">#{{ $supervisor->user_id }}</p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-dark">Supervisor ID</h4>
                    <p class="text-dark">#{{ $supervisor->id }}</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <h4 class="text-sm font-medium text-dark">Account Created</h4>
                    <p class="text-dark">{{ $supervisor->user->created_at->format('F j, Y g:i A') }}</p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-dark">Last Updated</h4>
                    <p class="text-dark">{{ $supervisor->updated_at->format('F j, Y g:i A') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border glass-card p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="text-lg font-medium text-dark">Quick Actions</h3>
                <p class="text-dark mt-1">Common actions for this supervisor.</p>
            </div>
            <div class="flex flex-wrap items-center gap-4">
                <a href="{{ route('admin.supervisors.edit', $supervisor) }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828L8.586 8.586z"/>
                    </svg>
                    Edit Supervisor
                </a>
                
                <form method="POST" 
                      action="{{ route('admin.supervisors.destroy', $supervisor) }}" 
                      onsubmit="return confirm('Are you sure you want to delete this supervisor? This action cannot be undone.')"
                      class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Delete Supervisor
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
