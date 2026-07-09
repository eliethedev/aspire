@extends('layouts.admin')

@section('title', 'Audit Logs')

@push('styles')
<style>
    .log-entry:hover {
        background-color: #f8fafc;
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6">
    <div class="flex items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Audit Logs</h1>
            <p class="text-gray-500 mt-1">Complete audit trail of all system activities. Append-only — logs cannot be edited or deleted.</p>
        </div>
        <div class="flex items-center gap-3 text-sm text-gray-400">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <span>Live</span>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 mb-6">
        <form method="GET" action="{{ route('admin.audit-logs.index') }}">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Date From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                           class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Date To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                           class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">User</label>
                    <select name="user_id"
                            class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        <option value="">All Users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Role</label>
                    <select name="role"
                            class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        <option value="">All Roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role }}" {{ request('role') == $role ? 'selected' : '' }}>{{ ucfirst($role) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Module</label>
                    <select name="module"
                            class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        <option value="">All Modules</option>
                        @foreach($modules as $module)
                            <option value="{{ $module }}" {{ request('module') == $module ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $module)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Action</label>
                    <select name="action"
                            class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        <option value="">All Actions</option>
                        @foreach($actions as $action)
                            <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $action)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Status</label>
                    <select name="status"
                            class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        <option value="">All Statuses</option>
                        <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Success</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1.5">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                           placeholder="Search by user, action, module, ID...">
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button type="submit"
                        class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
                    Filter
                </button>
                @if(request()->anyFilled(['search', 'user_id', 'role', 'module', 'action', 'status', 'date_from', 'date_to']))
                    <a href="{{ route('admin.audit-logs.index') }}"
                       class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 transition-colors">
                        Clear
                    </a>
                @endif
                <span class="text-xs text-gray-400 ml-auto">{{ $logs->total() }} entries</span>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">Timestamp</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">User</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">Role</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">Action</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">Module</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">Record</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">Status</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider">IP Address</th>
                        <th class="text-right px-4 py-3 font-semibold text-gray-600 text-xs uppercase tracking-wider"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($logs as $log)
                        <tr class="log-entry">
                            <td class="px-4 py-3 whitespace-nowrap text-gray-500 text-xs">
                                {{ $log->created_at->format('M d, Y h:i A') }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold shrink-0">
                                        {{ strtoupper(substr($log->user?->name ?? 'S', 0, 1)) }}
                                    </div>
                                    <span class="text-gray-900 font-medium text-sm">{{ $log->user?->name ?? 'System' }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($log->role)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                    {{ $log->role === 'admin' ? 'bg-indigo-100 text-indigo-700' : ($log->role === 'supervisor' ? 'bg-amber-100 text-amber-700' : ($log->role === 'teacher' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600')) }}">
                                    {{ ucfirst($log->role) }}
                                </span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="text-sm capitalize">{{ str_replace('_', ' ', $log->action) }}</span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($log->module)
                                <span class="text-sm text-gray-600 capitalize">{{ str_replace('_', ' ', $log->module) }}</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                {{ $log->record_id ? "#{$log->record_id}" : '—' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                @if($log->status === 'success')
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                                @elseif($log->status === 'failed')
                                    <span class="w-2 h-2 rounded-full bg-red-500 inline-block"></span>
                                @else
                                    <span class="w-2 h-2 rounded-full bg-gray-300 inline-block"></span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-400 font-mono">
                                {{ $log->ip_address ?? '—' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-right">
                                <a href="{{ route('admin.audit-logs.show', $log) }}"
                                   class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                    Details
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center">
                                <div class="w-12 h-12 rounded-full bg-gray-50 flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <p class="text-gray-500 text-sm">No audit logs found matching your criteria.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($logs->hasPages())
        <div class="mt-6">
            {{ $logs->links() }}
        </div>
    @endif
</div>
@endsection
