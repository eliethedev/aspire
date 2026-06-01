@extends('layouts.app')

@section('title', 'Teacher Details')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8 space-y-8">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border glass-card p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-gray-900 font-bold text-xl">Teacher Details</h1>
                <p class="text-gray-600 mt-1">View teacher profile and information.</p>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('teachers.edit', $teacher) }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white nded-lg hover:bg-indigo-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828L8.586 8.586z"/>
                    </svg>
                    Edit Teacher
                </a>
                <a href="{{ route('teachers.index') }}" 
                   class="inline-flex items-center px-4 py-2 text-dark bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to List
                </a>
            </div>
        </div>
    </div>

    <!-- Teacher Profile Card -->
    <div class="bg-white rounded-xl shadow-sm border glass-card p-6 mb-6">
        <div class="flex items-start space-x-6">
            <div class="w-20 h-20 bg-slate-200 rounded-full flex items-center justify-center">
                <svg class="w-10 h-10 text-dark " fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 007-7 0z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-gray-900 font-semibold text-xl">{{ $teacher->user->name }}</h2>
                <p class="text-gray-600">{{ $teacher->user->email }}</p>
                <div class="mt-3 flex items-center space-x-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-dark medium bg-blue-100 text-dark ">
                        {{ $teacher->department ?? 'No Department' }}
                    </span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-dark medium bg-green-100 text-dark ">
                        {{ $teacher->user->school->name ?? 'No School' }}
                    </span>
                    @if($teacher->user->email_verified_at)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-dark medium bg-green-100 text-dark ">
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
        <div class="bg-white rounded-xl shadow-sm border glass-card p-6">
            <div class="flex items-center mb-4">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-gray-700 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 007-7 0z"/>
                    </svg>
                </div>
                <h3 class="text-gray-900 font-semibold text-lg">User Information</h3>
            </div>
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-gray-700 font-medium text-sm">Full Name</h4>
                        <p class="text-gray-900">{{ $teacher->user->name }}</p>
                    </div>
                    <div>
                        <h4 class="text-gray-700 font-medium text-sm">Email Address</h4>
                        <p class="text-gray-900">{{ $teacher->user->email }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-gray-700 font-medium text-sm">Role</h4>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-dark medium bg-purple-100 text-dark 0">
                            {{ ucfirst($teacher->user->role) }}
                        </span>
                    </div>
                    <div>
                        <h4 class="text-gray-700 font-medium text-sm">School</h4>
                        <p class="text-gray-900">{{ $teacher->user->school->name ?? 'N/A' }}</p>
                    </div>
                </div>
                <div>
                    <h4 class="text-gray-700 font-medium text-sm">Email Status</h4>
                    @if($teacher->user->email_verified_at)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-dark medium bg-green-100 text-dark ">
                        <i class="fas fa-check-circle mr-1"></i>
                        Verified
                    </span>
                    @else
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-dark medium bg-yellow-100 text-dark 0">
                        <i class="fas fa-exclamation-circle mr-1"></i>
                        Not Verified
                    </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Teacher Information -->
        <div class="bg-white rounded-xl shadow-sm border glass-card p-6">
            <div class="flex items-center mb-4">
                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-dark " fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <h3 class="text-gray-900 font-semibold text-lg">Teacher Information</h3>
            </div>
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-gray-700 font-medium text-sm">Department</h4>
                        <p class="text-gray-900">{{ $teacher->department ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <h4 class="text-gray-700 font-medium text-sm">Position</h4>
                        <p class="text-gray-900">{{ $teacher->position ?? 'N/A' }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-gray-700 font-medium text-sm">Years of Service</h4>
                        <p class="text-gray-900">{{ $teacher->years_of_service ?? 'N/A' }} years</p>
                    </div>
                    <div>
                        <h4 class="text-gray-700 font-medium text-sm">Mobile Number</h4>
                        <p class="text-gray-900">{{ $teacher->mobile_number ?? 'N/A' }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-gray-700 font-medium text-sm">PRC License Number</h4>
                        <p class="text-gray-900">{{ $teacher->prc_license_number ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <h4 class="text-gray-700 font-medium text-sm">Employee ID</h4>
                        <p class="text-gray-900">{{ $teacher->employee_id ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Information -->
    <div class="bg-white rounded-xl shadow-sm border glass-card p-6">
        <div class="flex items-center mb-4">
            <div class="w-8 h-8 bg-slate-100 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-dark " fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002 2v6m-6 0V5a2 2 0 00-2-2H5a2 2 0 00-2 2v3m0 2h4a2 2 0 012 2v10a2 2 0 012 2H7a2 2 0 002-2v-3m0 2h4a2 2 0 012 2v10a2 2 0 012 2H7a2 2 0 002-2v-3z"/>
                </svg>
            </div>
            <h3 class="text-gray-900 font-semibold text-lg">System Information</h3>
        </div>
        <div class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <h4 class="text-gray-700 font-medium text-sm">User ID</h4>
                    <p class="text-gray-900">#{{ $teacher->user_id }}</p>
                </div>
                <div>
                    <h4 class="text-gray-700 font-medium text-sm">Teacher ID</h4>
                    <p class="text-gray-900">#{{ $teacher->id }}</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <h4 class="text-gray-700 font-medium text-sm">Account Created</h4>
                    <p class="text-gray-900">{{ $teacher->user->created_at->format('F j, Y g:i A') }}</p>
                </div>
                <div>
                    <h4 class="text-gray-700 font-medium text-sm">Last Updated</h4>
                    <p class="text-gray-900">{{ $teacher->updated_at->format('F j, Y g:i A') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="bg-white rounded-xl shadow-sm border glass-card p-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-gray-900 font-semibold text-lg">Quick Actions</h3>
                <p class="text-gray-600 mt-1">Common actions for this teacher.</p>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('teachers.edit', $teacher) }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white nded-lg hover:bg-indigo-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828L8.586 8.586z"/>
                    </svg>
                    Edit Teacher
                </a>
                
                <form method="POST" 
                      action="{{ route('teachers.destroy', $teacher) }}" 
                      onsubmit="return confirm('Are you sure you want to delete this teacher? This action cannot be undone.')"
                      class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-red-600 text-white nded-lg hover:bg-red-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Delete Teacher
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
