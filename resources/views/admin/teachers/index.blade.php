@extends('layouts.admin')

@section('title', 'Teachers Management')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8 space-y-8">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm glass-card p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-dark">Teachers</h1>
                <p class="text-dark mt-1">Manage teacher profiles and assignments.</p>
            </div>
            <a href="{{ route('admin.invitations.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add New Teacher
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
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
    <div class="bg-white rounded-xl shadow-sm glass-card p-6">
        <form method="GET" action="{{ route('admin.teachers.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <label for="search" class="block text-sm font-medium text-dark mb-1">Search</label>
                    <input type="text" id="search" name="search" 
                           class="w-full px-3 py-2 glass-card text-white rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Search by name or email..." 
                           value="{{ request('search') }}">
                </div>
                <div>
                    <label for="school_id" class="block text-sm font-medium text-dark mb-1">School</label>
                    <select id="school_id" name="school_id" 
                            class="w-full px-3 py-2 bg-white  rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Schools</option>
                        @foreach($schools as $school)
                        <option value="{{ $school->id }}" 
                                {{ request('school_id') == $school->id ? 'selected' : '' }}>
                            {{ $school->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" 
                            class="w-full px-4 py-2 bg-slate-600 text-dark rounded-lg hover:bg-slate-700 transition-colors">
                        Filter
                    </button>
                </div>
            </div>
            @if(request()->hasAny(['search', 'school_id']))
            <div class="flex items-center mt-2">
                <a href="{{ route('admin.teachers.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
                    Clear filters
                </a>
            </div>
            @endif
        </form>
    </div>

    <!-- Teachers Table -->
    <div class="glass-card rounded-xl shadow-sm  overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-dark border-glass-card">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dark uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dark uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dark uppercase tracking-wider">School</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dark uppercase tracking-wider">Department</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dark uppercase tracking-wider">Position</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dark uppercase tracking-wider">Years of Service</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dark uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($teachers as $teacher)
                    <tr class="hover:bg-dark">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-dark">{{ $teacher->user->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-dark">{{ $teacher->user->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-dark">{{ $teacher->user->school->name ?? 'N/A' }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-dark">{{ $teacher->department ?? 'N/A' }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-dark">{{ $teacher->position ?? 'N/A' }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-dark">{{ $teacher->years_of_service ?? 'N/A' }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('admin.teachers.show', $teacher) }}" 
                                   class="text-blue-600 hover:text-blue-800" title="View">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <a href="{{ route('admin.teachers.edit', $teacher) }}" 
                                   class="text-indigo-600 hover:text-indigo-800" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <form method="POST" 
                                      action="{{ route('admin.teachers.destroy', $teacher) }}" 
                                      onsubmit="return confirm('Are you sure you want to delete this teacher?')"
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
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="text-dark">
                                <svg class="mx-auto h-12 w-12 text-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                <p class="mt-2">No teachers found</p>
                                <a href="{{ route('admin.teachers.create') }}" class="mt-2 inline-flex text-blue-600 hover:text-blue-800">
                                    Add your first teacher
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($teachers->hasPages())
        <div class="px-6 py-4 border-glass-card">
            {{ $teachers->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
