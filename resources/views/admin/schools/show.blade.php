@extends('layouts.app')

@section('title', 'School Details')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8 space-y-8">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm border glass-card p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white">School Details</h1>
                <p class="text-white mt-1">View school information and settings.</p>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.schools.edit', $school) }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828L8.586 8.586z"/>
                    </svg>
                    Edit School
                </a>
                <a href="{{ route('admin.schools.index') }}" 
                   class="inline-flex items-center px-4 py-2 text-white bg-white border glass-card rounded-lg hover:bg-slate-50 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to List
                </a>
            </div>
        </div>
    </div>

    <!-- School Profile Card -->
    <div class="bg-white rounded-xl shadow-sm border glass-card p-6 mb-6">
        <div class="flex items-start space-x-6">
            <div class="w-20 h-20 bg-slate-200 rounded-full flex items-center justify-center">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2h-3a2 2 0 00-2-2v14a2 2 0 002 2h3a2 2 0 002 2v3m0 2h4a2 2 0 012 2v10a2 2 0 012 2H7a2 2 0 002-2v-3z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-xl font-bold text-white">{{ $school->name }}</h2>
                <p class="text-white mt-1">{{ $school->domain ?? $school->subdomain }}</p>
                <div class="mt-3 flex items-center space-x-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        @if($school->is_active)
                            bg-green-100 text-green-800
                        @elseif($school->trial_ends_at && $school->trial_ends_at->isFuture())
                            bg-yellow-100 text-yellow-800
                        @else
                            bg-slate-100 text-white
                        @endif">
                        @if($school->is_active)
                            Active
                        @elseif($school->trial_ends_at && $school->trial_ends_at->isFuture())
                            Trial
                        @else
                            Inactive
                        @endif
                    </span>
                    <span class="text-sm text-white">
                        {{ $school->users_count ?? 0 }} users
                    </span>
                    <span class="text-sm text-white">
                        Created {{ $school->created_at->format('M j, Y') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Information Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- School Information -->
        <div class="bg-white rounded-xl shadow-sm border glass-card p-6">
            <div class="flex items-center mb-4">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2h-3a2 2 0 00-2-2v14a2 2 0 002 2h3a2 2 0 002 2v3m0 2h4a2 2 0 012 2v10a2 2 0 012 2H7a2 2 0 002-2v-3z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-white">School Information</h3>
            </div>
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-sm font-medium text-white">School Name</h4>
                        <p class="text-white">{{ $school->name }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-white">Slug</h4>
                        <p class="text-white">{{ $school->slug }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-sm font-medium text-white">Domain</h4>
                        <p class="text-white">{{ $school->domain ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-white">Subdomain</h4>
                        <p class="text-white">{{ $school->subdomain ?? 'N/A' }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-sm font-medium text-white">Status</h4>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium
                            @if($school->is_active)
                                bg-green-100 text-green-800
                            @elseif($school->trial_ends_at && $school->trial_ends_at->isFuture())
                                bg-yellow-100 text-yellow-800
                            @else
                                bg-slate-100 text-white
                            @endif">
                            @if($school->is_active)
                                Active
                            @elseif($school->trial_ends_at && $school->trial_ends_at->isFuture())
                                Trial
                            @else
                                Inactive
                            @endif
                        </span>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-white">Trial Period</h4>
                        <p class="text-white">
                            {{ $school->trial_ends_at ? $school->trial_ends_at->format('M j, Y') : 'No Trial' }}
                        </p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-sm font-medium text-white">Created</h4>
                        <p class="text-white">{{ $school->created_at->format('F j, Y g:i A') }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-white">Last Updated</h4>
                        <p class="text-white">{{ $school->updated_at->format('F j, Y g:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- School Settings -->
        <div class="bg-white rounded-xl shadow-sm border glass-card p-6">
            <div class="flex items-center mb-4">
                <div class="w-8 h-8 bg-slate-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 12.3c-.426 0-1.943-.925 1.943 0 0-1.943-.925 1.943 0 0-1.943-.925-1.943H1.006l.006.006h14.038l-.006.006.01.038v3.514l-.01.01-1.047-.01.01-1.047C14.03 13.925 13.099 13.5 0 14.038 14.5c0 .55.453.025.825.025.825 0 1.414 1.414 1.414z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-white">School Settings</h3>
            </div>
            <div class="bg-slate-50 border glass-card rounded-lg p-4">
                @if($school->settings)
                    <pre class="text-sm text-white overflow-x-auto"><code>{{ json_encode($school->settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
                @else
                    <p class="text-sm text-white">No custom settings configured</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Users Management -->
    <div class="bg-white rounded-xl shadow-sm border glass-card p-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-medium text-white">User Management</h3>
                <p class="text-sm text-white mt-1">Manage users assigned to this school.</p>
            </div>
            <a href="{{ route('admin.schools.users.index', $school) }}" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Manage Users
            </a>
        </div>
        <div class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <h4 class="text-sm font-medium text-white">Total Users</h4>
                    <p class="text-2xl font-bold text-white">{{ $school->users_count ?? 0 }}</p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-white">Teachers</h4>
                    <p class="text-2xl font-bold text-white">{{ $school->teachers_count ?? 0 }}</p>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-white">Supervisors</h4>
                    <p class="text-2xl font-bold text-white">{{ $school->supervisors_count ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="bg-white rounded-xl shadow-sm border glass-card p-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-medium text-white">Quick Actions</h3>
                <p class="text-sm text-white mt-1">Common actions for this school.</p>
            </div>
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.schools.edit', $school) }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828L8.586 8.586z"/>
                    </svg>
                    Edit School
                </a>
                
                <form method="POST" 
                      action="{{ route('admin.schools.destroy', $school) }}" 
                      onsubmit="return confirm('Are you sure you want to delete this school? This action cannot be undone.')"
                      class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Delete School
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
