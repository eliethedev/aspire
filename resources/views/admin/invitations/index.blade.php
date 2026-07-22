@extends('layouts.admin')

@section('title', 'User Invitations')

@section('content')
<div class="max-w-7xl mx-auto px-6 space-y-8">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border glass-card p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-dark">User Invitations</h1>
                <p class="text-dark mt-1">Manage user invitations and track their status.</p>
            </div>
            <a href="{{ route('admin.invitations.create') }}" 
               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-plus mr-2"></i>New Invitation
            </a>
        </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border glass-card p-6">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                    <i class="fas fa-envelope text-blue-600 dark:text-blue-400"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-dark">Total Invitations</p>
                    <p class="text-2xl font-bold text-dark">{{ $statistics['total'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border glass-card p-6">
            <div class="flex items-center">
                <div class="p-3 bg-yellow-100 rounded-lg">
                    <i class="fas fa-clock text-yellow-600"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-dark">Pending</p>
                    <p class="text-2xl font-bold text-dark">{{ $statistics['pending'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border glass-card p-6">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-lg">
                    <i class="fas fa-check text-green-600 dark:text-green-400"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-dark">Accepted</p>
                    <p class="text-2xl font-bold text-dark">{{ $statistics['used'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border glass-card p-6">
            <div class="flex items-center">
                <div class="p-3 bg-red-100 dark:bg-red-900/30 rounded-lg">
                    <i class="fas fa-times text-red-600 dark:text-red-400"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-dark">Expired</p>
                    <p class="text-2xl font-bold text-dark">{{ $statistics['expired'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border glass-card p-6">
        <form method="GET" action="{{ route('admin.invitations.index') }}" class="flex flex-wrap gap-4">
            <div>
                <select name="status" class="px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="used" {{ request('status') == 'used' ? 'selected' : '' }}>Accepted</option>
                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                </select>
            </div>
            <div>
                <select name="role" class="px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">All Roles</option>
                    <option value="teacher" {{ request('role') == 'teacher' ? 'selected' : '' }}>Teacher</option>
                    <option value="supervisor" {{ request('role') == 'supervisor' ? 'selected' : '' }}>Supervisor</option>
                    <option value="school_head" {{ request('role') == 'school_head' ? 'selected' : '' }}>School Head</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
            </div>
            <div>
                <select name="school_id" class="px-3 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">All Schools</option>
                    @foreach($schools as $school)
                        <option value="{{ $school->id }}" {{ request('school_id') == $school->id ? 'selected' : '' }}>
                            {{ $school->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Filter
            </button>
            <a href="{{ route('admin.invitations.index') }}" class="px-4 py-2 text-dark bg-white border rounded-lg hover:bg-slate-50">
                Clear
            </a>
        </form>
    </div>

    <!-- Invitations Table -->
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border glass-card overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">School</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Expires</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($invitations as $invitation)
                    <tr class="hover:bg-gray-50 dark:bg-gray-800">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                    <span class="text-blue-600 dark:text-blue-400 font-medium">{{ substr($invitation->user->name, 0, 1) }}</span>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $invitation->user->name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500">Invited by {{ $invitation->invitedBy->name }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $invitation->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-800">
                                {{ ucfirst($invitation->role) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                            {{ $invitation->school?->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($invitation->is_used)
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">
                                    Accepted
                                </span>
                            @elseif($invitation->isExpired())
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 dark:bg-red-900/30 text-red-800">
                                    Expired
                                </span>
                            @else
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Pending
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                            {{ $invitation->expires_at->format('M j, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                            @if(!$invitation->is_used && !$invitation->isExpired())
                                <form method="POST" action="{{ route('admin.invitations.resend', $invitation) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-blue-600 dark:text-blue-400 hover:text-blue-900" title="Resend">
                                        <i class="fas fa-redo"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.invitations.cancel', $invitation) }}" class="inline" onsubmit="return confirm('Are you sure you want to cancel this invitation?');">
                                    @csrf
                                    <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-900" title="Cancel">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            @endif
                            @if($invitation->is_used)
                                <a href="{{ route('admin.users.show', $invitation->user) }}" class="text-green-600 dark:text-green-400 hover:text-green-900" title="View User">
                                    <i class="fas fa-user"></i>
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="text-gray-500 dark:text-gray-400 dark:text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-4"></i>
                                <p class="text-lg">No invitations found</p>
                                <p class="text-sm">Create your first invitation to get started</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($invitations->hasPages())
        <div class="flex justify-center">
            {{ $invitations->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@endsection
