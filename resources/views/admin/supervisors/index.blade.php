@extends('layouts.admin')

@section('title', 'Supervisors Management')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8 space-y-8">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm glass-card p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-dark">Supervisors</h1>
                <p class="text-dark mt-1">Manage school supervisors and their information.</p>
            </div>
            <a href="{{ route('admin.supervisors.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add New Supervisor
            </a>
        </div>
    </div>

    <!-- Search and Filter Form -->
    <div class="bg-white rounded-xl shadow-sm glass-card p-6">
        <form method="GET" action="{{ route('admin.supervisors.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
                <label for="search" class="block text-sm font-medium text-dark mb-1">Search</label>
                <input type="text" id="search" name="search" value="{{ request('search') }}"
                       class="w-full px-3 py-2 glass-card text-dark rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Search by name or email...">
            </div>
            <div>
                <label for="school_id" class="block text-sm font-medium text-dark mb-1">School</label>
                <select id="school_id" name="school_id"
                        class="w-full px-3 py-2 glass-card text-dark rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Schools</option>
                    @foreach($schools as $school)
                    <option value="{{ $school->id }}" {{ request('school_id') == $school->id ? 'selected' : '' }}>
                        {{ $school->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-dark mb-1">Status</label>
                <select id="status" name="status"
                        class="w-full px-3 py-2 glass-card text-dark rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="md:col-span-4">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Filter
                </button>
                <a href="{{ route('admin.supervisors.index') }}" class="px-4 py-2 glass-card text-dark rounded-lg hover:bg-white/10 transition-colors ml-2">
                    Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Supervisors Table -->
    <div class="bg-white rounded-xl shadow-sm glass-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-dark border-glass-card">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dark uppercase tracking-wider">Full Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dark uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dark uppercase tracking-wider">School</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dark uppercase tracking-wider">Position</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dark uppercase tracking-wider">Employee ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dark uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dark uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @if($supervisors->count() > 0)
                        @foreach($supervisors as $supervisor)
                        <tr class="hover:bg-dark text-dark">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium">{{ $supervisor->user->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm">{{ $supervisor->user->email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm">{{ $supervisor->school->name ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm">{{ $supervisor->position }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm">{{ $supervisor->employee_id ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $supervisor->status === 'active' ? 'bg-green-500 text-white' : 'bg-slate-500 text-white' }}">
                                    {{ $supervisor->status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('admin.supervisors.show', $supervisor) }}" 
                                       class="text-blue-400 hover:text-blue-300" title="View">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.supervisors.edit', $supervisor) }}" 
                                       class="text-blue-400 hover:text-blue-300" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828L8.586 8.586z"/>
                                        </svg>
                                    </a>
                                    <form action="{{ route('admin.supervisors.destroy', $supervisor) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this supervisor? This action cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-300" title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-dark">
                                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <p class="mt-2">No supervisors found</p>
                                <a href="{{ route('admin.supervisors.create') }}" class="mt-2 inline-block text-blue-500 hover:text-blue-400">
                                    Add your first supervisor
                                </a>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($supervisors->hasPages())
        <div class="px-6 py-4 border-t border-white/10">
            {{ $supervisors->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>
@endsection