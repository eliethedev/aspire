@extends('layouts.admin')

@section('title', 'User Management')

@section('content')
<div class="max-w-7xl mx-auto px-6 space-y-8">
    <!-- Header -->
    <div class="bg-dark rounded-xl shadow-sm border glass-card p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-dark">User Management</h1>
                <p class="text-dark mt-1">Manage system users and their roles.</p>
            </div>
            <a href="{{ route('admin.users.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Invite Registration 
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-dark rounded-xl shadow-sm border glass-card p-6">
        <form method="GET" action="{{ route('admin.users.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-dark mb-1">Search</label>
                    <input type="text" id="search" name="search" value="{{ request('search') }}" 
                           class="w-full px-3 py-2 border glass-card rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Name or email...">
                </div>
                
                <div>
                    <label for="role" class="block text-sm font-medium text-dark mb-1">Role</label>
                    <select id="role" name="role" class="w-full px-3 py-2 border bg-dark rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Roles</option>
                        @foreach($roles as $role)
                        <option value="{{ $role }}" {{ request('role') == $role ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $role)) }}
                        </option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label for="school_id" class="block text-sm font-medium text-dark mb-1">School</label>
                    <select id="school_id" name="school_id" class="w-full px-3 py-2 border bg-dark rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Schools</option>
                        @foreach($schools as $school)
                        <option value="{{ $school->id }}" {{ request('school_id') == $school->id ? 'selected' : '' }}>
                            {{ $school->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="flex items-end">
                    <button type="submit" class="w-full px-4 py-2 bg-slate-600 text-white rounded-lg hover:bg-dark-700 transition-colors">
                        Filter
                    </button>
                </div>
            </div>
            
            @if(request()->hasAny(['search', 'role', 'school_id']))
            <div class="flex items-center">
                <a href="{{ route('admin.users.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
                    Clear filters
                </a>
            </div>
            @endif
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-dark rounded-xl shadow-sm border glass-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b glass-card">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dark uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dark uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dark uppercase tracking-wider">School</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dark uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dark uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-dark uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($users as $user)
                    <tr class="hover:bg-dark">
                        <td class="px-6 py-4 darkspace-nowrap">
                            <div>
                                <div class="text-sm font-medium text-dark">{{ $user->name }}</div>
                                <div class="text-sm text-dark">{{ $user->email }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 darkspace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($user->role === 'admin') bg-purple-100 text-purple-800
                                @elseif($user->role === 'school_head') bg-blue-100 text-blue-800
                                @elseif($user->role === 'supervisor') bg-green-100 text-green-800
                                @elseif($user->role === 'teacher') bg-yellow-100 text-yellow-800
                                @endif">
                                {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 darkspace-nowrap">
                            <span class="text-sm text-dark">
                                {{ $user->school?->name ?? 'No School' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 darkspace-nowrap">
                            @if($user->status === 'invited')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-clock mr-1"></i>
                                    Waiting for Password
                                </span>
                            @elseif($user->status === 'active')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Active
                                </span>
                            @elseif($user->status === 'suspended')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-ban mr-1"></i>
                                    Suspended
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ ucfirst($user->status) }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 darkspace-nowrap">
                            <span class="text-sm text-dark">{{ $user->created_at->format('M j, Y') }}</span>
                        </td>
                        <td class="px-6 py-4 darkspace-nowrap text-sm">
                            <div class="flex items-center space-x-2">
                                @if($user->status === 'invited')
                                    @php
                                        $invitation = \App\Models\Invitation::where('user_id', $user->id)->valid()->first();
                                    @endphp
                                    @if($invitation)
                                        <form method="POST" action="{{ route('admin.invitations.resend', $invitation) }}" 
                                              onsubmit="return confirm('Resend invitation to {{ $user->email }}?')">
                                            @csrf
                                            <button type="submit" class="text-orange-600 hover:text-orange-800" title="Resend Invitation">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                @endif
                                <a href="{{ route('admin.users.show', $user) }}" 
                                   class="text-blue-600 hover:text-blue-800" title="View">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <a href="{{ route('admin.users.edit', $user) }}" 
                                   class="text-indigo-600 hover:text-indigo-800" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" 
                                      onsubmit="return confirm('Are you sure you want to delete this user?')">
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
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="text-dark">
                                <svg class="mx-auto h-12 w-12 text-dark" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                <p class="mt-2">No users found</p>
                                <a href="{{ route('admin.users.create') }}" class="mt-2 inline-flex text-blue-600 hover:text-blue-800">
                                    Create your first user
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($users->hasPages())
        <div class="px-6 py-4 border-t glass-card">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
