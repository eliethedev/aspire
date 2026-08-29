@extends('layouts.admin')

@section('title', 'School Management')

@section('content')
<div class="max-w-7xl mx-auto px-4 space-y-4">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm glass-card p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Schools</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1">Manage educational institutions and their settings.</p>
            </div>
            <a href="{{ route('admin.schools.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add New School
            </a>
        </div>
    </div>

    <!-- Search and Filter Form -->
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm glass-card p-6">
        <form method="GET" action="{{ route('admin.schools.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                    <input type="text" id="search" name="search" 
                           class="w-full px-3 py-2 border text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="Search by name, domain, or subdomain..." 
                           value="{{ request('search') }}">
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                    <select id="status" name="status" 
                            class="w-full px-3 py-2 border text-gray-900 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="trial" {{ request('status') == 'trial' ? 'selected' : '' }}>Trial</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" 
                            class="w-full px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-slate-700 transition-colors">
                        Filter
                    </button>
                </div>
            </div>
            @if(request()->hasAny(['search', 'status']))
            <div class="flex items-center mt-2">
                <a href="{{ route('admin.schools.index') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800">
                    Clear filters
                </a>
            </div>
            @endif
        </form>
    </div>

    <!-- Schools Table -->
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm glass-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">School Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Domain</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Subdomain</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Trial Ends</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Users</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($schools as $school)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 text-gray-900 dark:text-white">
                        <td class="px-6 py-4">
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $school->name }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $school->slug }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $school->domain ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $school->subdomain ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($school->is_active)
                                    bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300
                                @elseif($school->trial_ends_at && $school->trial_ends_at->isFuture())
                                    bg-yellow-100 text-yellow-800
                                @else
                                    bg-gray-100 text-gray-600 dark:text-gray-400
                                @endif">
                                @if($school->is_active)
                                    Active
                                @elseif($school->trial_ends_at && $school->trial_ends_at->isFuture())
                                    Trial
                                @else
                                    Inactive
                                @endif
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $school->trial_ends_at ? $school->trial_ends_at->format('M j, Y') : 'N/A' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $school->users_count ?? 0 }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $school->created_at->format('M j, Y') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center space-x-3">
                                <a href="{{ route('admin.schools.show', $school) }}" 
                                   class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 text-sm font-medium" title="View">
                                    View
                                </a>
                                <a href="{{ route('admin.schools.edit', $school) }}" 
                                   class="text-blue-600 dark:text-blue-400 hover:text-blue-900 text-sm font-medium" title="Edit">
                                    Edit
                                </a>
                                <form method="POST" 
                                      action="{{ route('admin.schools.destroy', $school) }}" 
                                      onsubmit="return confirm('Are you sure you want to delete this school? This action cannot be undone.')"
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-900 text-sm font-medium" title="Delete">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No schools found.</p>
                            <a href="{{ route('admin.schools.create') }}" class="mt-3 inline-flex items-center text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-500">
                                Add your first school
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($schools->hasPages())
    <div class="mt-4">
        {{ $schools->links() }}
    </div>
    @endif
</div>
@endsection
