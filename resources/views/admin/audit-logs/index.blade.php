@extends('layouts.admin')

@section('title', 'Audit Logs')

@push('styles')
<style>
    .log-entry:hover {
        background-color: #f8fafc;
    }
    .dark .log-entry:hover {
        background-color: rgba(31, 41, 55, 0.5);
    }
</style>
@endpush

@section('content')
<div class="max-w-[1100px] mx-auto px-4 sm:px-6">
    <div class="flex items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Audit Logs</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Complete audit trail of all system activities. Append-only — logs cannot be edited or deleted.</p>
        </div>
        <div class="flex items-center gap-3 text-sm text-gray-400 dark:text-gray-500">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            <span>Live</span>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-4 mb-5">
        <form method="GET" action="{{ route('admin.audit-logs.index') }}">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Date From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                           class="w-full px-2.5 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Date To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                           class="w-full px-2.5 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">User</label>
                    <select name="user_id"
                            class="w-full px-2.5 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        <option value="">All Users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Role</label>
                    <select name="role"
                            class="w-full px-2.5 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        <option value="">All Roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role }}" {{ request('role') == $role ? 'selected' : '' }}>{{ ucfirst($role) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Module</label>
                    <select name="module"
                            class="w-full px-2.5 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        <option value="">All Modules</option>
                        @foreach($modules as $module)
                            <option value="{{ $module }}" {{ request('module') == $module ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $module)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Action</label>
                    <select name="action"
                            class="w-full px-2.5 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        <option value="">All Actions</option>
                        @foreach($actions as $action)
                            <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $action)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Status</label>
                    <select name="status"
                            class="w-full px-2.5 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        <option value="">All Statuses</option>
                        <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Success</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="w-full px-2.5 py-1.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                           placeholder="Search by user, action, module...">
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit"
                        class="px-4 py-1.5 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
                    Filter
                </button>
                @if(request()->anyFilled(['search', 'user_id', 'role', 'module', 'action', 'status', 'date_from', 'date_to']))
                    <a href="{{ route('admin.audit-logs.index') }}"
                       class="px-3 py-1.5 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">
                        Clear
                    </a>
                @endif
                <span class="text-xs text-gray-400 dark:text-gray-500 ml-auto">{{ $logs->total() }} entries</span>
            </div>
        </form>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[780px]">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
                        <th class="text-left px-3 py-2.5 font-semibold text-gray-600 dark:text-gray-400 text-xs uppercase tracking-wider">Timestamp</th>
                        <th class="text-left px-3 py-2.5 font-semibold text-gray-600 dark:text-gray-400 text-xs uppercase tracking-wider">User</th>
                        <th class="text-left px-3 py-2.5 font-semibold text-gray-600 dark:text-gray-400 text-xs uppercase tracking-wider">Action</th>
                        <th class="text-left px-3 py-2.5 font-semibold text-gray-600 dark:text-gray-400 text-xs uppercase tracking-wider">Module</th>
                        <th class="text-center px-3 py-2.5 font-semibold text-gray-600 dark:text-gray-400 text-xs uppercase tracking-wider">Status</th>
                        <th class="text-left px-3 py-2.5 font-semibold text-gray-600 dark:text-gray-400 text-xs uppercase tracking-wider hidden lg:table-cell">IP</th>
                        <th class="text-right px-3 py-2.5 font-semibold text-gray-600 dark:text-gray-400 text-xs uppercase tracking-wider"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($logs as $log)
                        <tr class="log-entry">
                            <td class="px-3 py-2.5 whitespace-nowrap text-gray-500 dark:text-gray-400 text-xs">
                                {{ $log->created_at->format('M d, Y h:i A') }}
                            </td>
                            <td class="px-3 py-2.5 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 flex items-center justify-center text-[10px] font-bold shrink-0">
                                        {{ strtoupper(substr($log->user?->name ?? 'S', 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <span class="text-gray-900 dark:text-white font-medium text-sm block truncate max-w-[140px]">{{ $log->user?->name ?? 'System' }}</span>
                                        @if($log->role)
                                            <span class="inline-flex items-center px-1.5 py-0 rounded text-[10px] font-medium
                                                {{ $log->role === 'admin' ? 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300' : ($log->role === 'supervisor' ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400' : ($log->role === 'teacher' ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-gray-100 text-gray-600 dark:text-gray-400')) }}">
                                                {{ ucfirst($log->role) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-2.5 whitespace-nowrap">
                                <span class="text-sm capitalize text-gray-700 dark:text-gray-300">{{ str_replace('_', ' ', $log->action) }}</span>
                                @if($log->record_id)
                                    <span class="text-xs text-gray-400 dark:text-gray-500 ml-1">#{{ $log->record_id }}</span>
                                @endif
                            </td>
                            <td class="px-3 py-2.5 whitespace-nowrap">
                                @if($log->module)
                                <span class="text-sm text-gray-600 dark:text-gray-400 capitalize">{{ str_replace('_', ' ', $log->module) }}</span>
                                @else
                                    <span class="text-gray-400 dark:text-gray-500">—</span>
                                @endif
                            </td>
                            <td class="px-3 py-2.5 whitespace-nowrap text-center">
                                @if($log->status === 'success')
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                                @elseif($log->status === 'failed')
                                    <span class="w-2 h-2 rounded-full bg-red-500 inline-block"></span>
                                @else
                                    <span class="w-2 h-2 rounded-full bg-gray-400 inline-block"></span>
                                @endif
                            </td>
                            <td class="px-3 py-2.5 whitespace-nowrap text-xs text-gray-400 dark:text-gray-500 font-mono hidden lg:table-cell">
                                {{ $log->ip_address ?? '—' }}
                            </td>
                            <td class="px-3 py-2.5 whitespace-nowrap text-right">
                                <a href="{{ route('admin.audit-logs.show', $log) }}"
                                   class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 text-sm font-medium">
                                    Details
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-3 py-12 text-center">
                                <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mx-auto mb-3">
                                    <svg class="w-6 h-6 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <p class="text-gray-500 dark:text-gray-400 text-sm">No audit logs found matching your criteria.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($logs->hasPages())
        <div class="mt-5">
            {{ $logs->links() }}
        </div>
    @endif
</div>
@endsection
