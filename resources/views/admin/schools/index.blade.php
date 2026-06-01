@extends('layouts.admin')

@section('title', 'School Management')

@section('content')
<div class="max-w-7xl mx-auto px-4 space-y-4">
    <!-- Header -->
    <div class="bg-dark rounded-xl shadow-sm glass-card  p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-dark">Schools</h1>
                <p class="text-dark mt-1">Manage educational institutions and their settings.</p>
            </div>
            <a href="{{ route('admin.schools.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-dark rounded-lg hover:bg-blue-700 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add New School
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 glass-card  glass-card -green-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Search and Filter Form -->
    <div class="bg-dark rounded-xl shadow-sm glass-card  p-6">
        <form method="GET" action="{{ route('admin.schools.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label for="search" class="block text-sm font-medium text-dark mb-1">Search</label>
                    <input type="text" id="search" name="search" 
                           class="w-full px-3 py-2 glass-card text-dark rounded-lg focus:ring-2 focus:ring-blue-500 focus:glass-card -blue-500"
                           placeholder="Search by name, domain, or subdomain..." 
                           value="{{ request('search') }}">
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-dark mb-1">Status</label>
                    <select id="status" name="status" 
                            class="w-full px-3 text-dark py-2 glass-card-white  rounded-lg focus:ring-2 focus:ring-blue-500 focus:glass-card -blue-500">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="trial" {{ request('status') == 'trial' ? 'selected' : '' }}>Trial</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" 
                            class="w-full px-4 py-2 bg-slate-600 text-dark rounded-lg hover:bg-slate-700 transition-colors">
                        Filter
                    </button>
                </div>
            </div>
            @if(request()->hasAny(['search', 'status']))
            <div class="flex items-center mt-2">
                <a href="{{ route('admin.schools.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
                    Clear filters
                </a>
            </div>
            @endif
        </form>
    </div>

    <!-- Schools Table -->
    <div class="bg-dark rounded-xl shadow-sm glass-card  overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-dark glass-card glass-card-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dark uppercase tracking-wider">
                            <input type="checkbox" class="rounded" id="selectAll">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dark uppercase tracking-wider">School Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dark uppercase tracking-wider">Domain</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dark uppercase tracking-wider">Subdomain</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dark uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dark uppercase tracking-wider">Trial Ends</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dark uppercase tracking-wider">Users</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dark uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dark uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($schools as $school)
                    <tr class="hover:bg-dark-50 text-dark">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" class="rounded" value="{{ $school->id }}">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-dark">{{ $school->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-dark">{{ $school->domain ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-dark">{{ $school->subdomain ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($school->is_active)
                                    bg-green-100 text-green-800
                                @elseif($school->trial_ends_at && $school->trial_ends_at->isFuture())
                                    bg-yellow-100 text-yellow-800
                                @else
                                    bg-slate-100 text-dark
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
                            <div class="text-sm text-dark">
                                {{ $school->trial_ends_at ? $school->trial_ends_at->format('M j, Y') : 'N/A' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-dark">{{ $school->users_count ?? 0 }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-dark">{{ $school->created_at->format('M j, Y') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('admin.schools.show', $school) }}" 
                                   class="text-blue-600 hover:text-blue-800" title="View">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                </a>
                                <a href="{{ route('admin.schools.edit', $school) }}" 
                                   class="text-indigo-600 hover:text-indigo-800" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828L8.586 8.586z"/>
                                    </svg>
                                </a>
                                <form method="POST" 
                                      action="{{ route('admin.schools.destroy', $school) }}" 
                                      onsubmit="return confirm('Are you sure you want to delete this school? This action cannot be undone.')"
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800" title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-6 py-12 text-center">
                            <div class="text-dark">
                                <svg class="mx-auto h-12 w-12 text-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2h-3a2 2 0 00-2-2v14a2 2 0 002 2h3a2 2 0 002 2v3m0 2h4a2 2 0 012 2v10a2 2 0 012 2H7a2 2 0 002-2v-3z"/>
                                </svg>
                                <p class="mt-2">No schools found</p>
                                <a href="{{ route('admin.schools.create') }}" class="mt-2 inline-flex text-blue-600 hover:text-blue-800">
                                    Add your first school
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($schools->hasPages())
        <div class="px-6 py-4 glass-card -t">
            {{ $schools->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
