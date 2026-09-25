@extends('layouts.admin')

@section('title', 'User Invitations')
@include('partials.dashboard.mock-styles')

@section('content')
<div class="mock-wrap max-w-7xl mx-auto px-1 py-1">
    <div class="mock-topbar">
        <div class="mock-crumbs">Admin <span>/</span> <b>User Invitations</b></div>
        <span class="mock-pill"><span class="pulse"></span>{{ $statistics['total'] }} invitations</span>
        <div class="mock-actions">
            <a href="{{ route('admin.invitations.create') }}" class="mock-btn primary"><i class="fas fa-plus mr-2"></i>New Invitation</a>
        </div>
    </div>
    <div class="mock-title"><div><h1>User Invitations</h1><p>Manage user invitations and track their status.</p></div><time>{{ now()->format('l, F j, Y') }}</time></div>

    <!-- Statistics — mockup KPIs -->
    <div class="mock-kpis">
        <div class="mock-kpi hot">
            <label>Total Invitations</label>
            <div class="val">{{ $statistics['total'] }}</div>
            <div class="delta mock-flat">All sent invites</div>
        </div>
        <div class="mock-kpi">
            <label>Pending</label>
            <div class="val">{{ $statistics['pending'] }}</div>
            <div class="delta mock-flat">Awaiting acceptance</div>
        </div>
        <div class="mock-kpi">
            <label>Accepted</label>
            <div class="val">{{ $statistics['used'] }}</div>
            <div class="delta mock-flat">Joined the system</div>
        </div>
        <div class="mock-kpi">
            <label>Expired</label>
            <div class="val">{{ $statistics['expired'] }}</div>
            <div class="delta mock-flat">Lapsed invites</div>
        </div>
    </div>

    <!-- Filters -->
    <section class="mock-panel">
        <div class="mock-panel-head"><h2>Filters</h2></div>
        <div style="padding:12px 16px">
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
    </section>

    <!-- Invitations Table -->
    <section class="mock-panel"><div class="mock-panel-head"><h2>User Invitations</h2></div>
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
                                    <span class="text-blue-600 dark:text-blue-400 font-medium">{{ substr($invitation->user?->name ?? $invitation->email, 0, 1) }}</span>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $invitation->user?->name ?? $invitation->email }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500">Invited by {{ $invitation->invitedBy?->name ?? 'System' }}</div>
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
                                <section class="mock-panel"><form method="POST" action="{{ route('admin.invitations.resend', $invitation) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-blue-600 dark:text-blue-400 hover:text-blue-900" title="Resend">
                                        <i class="fas fa-redo"></i>
                                    </button>
                                </form></section>
                                <section class="mock-panel"><form method="POST" action="{{ route('admin.invitations.cancel', $invitation) }}" class="inline" onsubmit="return confirm('Are you sure you want to cancel this invitation?');">
                                    @csrf
                                    <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-900" title="Cancel">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form></section>
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
    </section>

    <!-- Pagination -->
    @if($invitations->hasPages())
        <div class="flex justify-center">
            {{ $invitations->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@endsection
