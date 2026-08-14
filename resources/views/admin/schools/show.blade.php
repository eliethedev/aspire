@extends('layouts.admin')

@section('title', $school->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 space-y-6">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.schools.index') }}" class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <div class="w-12 h-12 rounded-xl bg-blue-600 flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $school->name }}</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 dark:text-gray-500">{{ $school->domain ?? $school->subdomain ?? 'No domain set' }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.schools.edit', $school) }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828L8.586 8.586z"/>
                    </svg>
                    Edit School
                </a>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-4 mt-4 pl-0 sm:pl-16">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                @if($school->is_active)
                    bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300
                @elseif($school->trial_ends_at && $school->trial_ends_at->isFuture())
                    bg-yellow-100 text-yellow-800
                @else
                    bg-gray-100 text-gray-600 dark:text-gray-400 dark:text-gray-500
                @endif">
                @if($school->is_active)
                    Active
                @elseif($school->trial_ends_at && $school->trial_ends_at->isFuture())
                    Trial
                @else
                    Inactive
                @endif
            </span>
            <span class="text-sm text-gray-500 dark:text-gray-400 dark:text-gray-500">{{ $school->users_count ?? 0 }} users</span>
            <span class="text-sm text-gray-500 dark:text-gray-400 dark:text-gray-500">Created {{ $school->created_at->format('M j, Y') }}</span>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-lg bg-blue-50 dark:bg-blue-900/20">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Total Users</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $school->users_count ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/20">
                    <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Teachers</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $school->teachers_count ?? 0 }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-lg bg-violet-50">
                    <svg class="w-6 h-6 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Supervisors</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $school->supervisors_count ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- School Information -->
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">School Information</h2>
            </div>
            <div class="p-6 space-y-5">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">School Name</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 mt-1">{{ $school->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Slug</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 mt-1">{{ $school->slug }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Domain</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 mt-1">{{ $school->domain ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Subdomain</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 mt-1">{{ $school->subdomain ?? 'N/A' }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Status</p>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mt-1
                            @if($school->is_active)
                                bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300
                            @elseif($school->trial_ends_at && $school->trial_ends_at->isFuture())
                                bg-yellow-100 text-yellow-800
                            @else
                                bg-gray-100 text-gray-600 dark:text-gray-400 dark:text-gray-500
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
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Trial Period</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 mt-1">
                            {{ $school->trial_ends_at ? $school->trial_ends_at->format('M j, Y') : 'No Trial' }}
                        </p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Created</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 mt-1">{{ $school->created_at->format('F j, Y g:i A') }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Last Updated</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100 mt-1">{{ $school->updated_at->format('F j, Y g:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- School Settings -->
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">School Settings</h2>
            </div>
            <div class="p-6">
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                    @if($school->settings)
                        <pre class="text-sm text-gray-700 dark:text-gray-300 overflow-x-auto"><code>{{ json_encode($school->settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400 dark:text-gray-500">No custom settings configured</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- User Management -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">User Management</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500 mt-0.5">Manage users assigned to this school.</p>
            </div>
            <a href="{{ route('admin.schools.users.index', $school) }}" 
               class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Manage Users
            </a>
        </div>
        <div class="p-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Total Users</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $school->users_count ?? 0 }}</p>
            </div>
            <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Teachers</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $school->teachers_count ?? 0 }}</p>
            </div>
            <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Supervisors</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $school->supervisors_count ?? 0 }}</p>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Quick Actions</h2>
        </div>
        <div class="p-4 flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.schools.edit', $school) }}" 
               class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium">
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
                        class="inline-flex items-center px-4 py-2 bg-white border border-red-300 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-50 dark:bg-red-900/20 transition-colors text-sm font-medium">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Delete School
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
