@extends('layouts.admin')

@section('title', 'Audit Log Details')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Audit Log Details</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Complete record of the action performed.</p>
        </div>
        <a href="{{ route('admin.audit-logs.index') }}"
           class="px-6 py-2 rounded-lg border border-gray-300 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:bg-gray-800 transition-colors text-sm">
            Back to Logs
        </a>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div>
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Action</p>
                <p class="text-gray-900 dark:text-gray-100 font-semibold capitalize">{{ str_replace('_', ' ', $auditLog->action) }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Status</p>
                @if($auditLog->status === 'success')
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Success
                    </span>
                @elseif($auditLog->status === 'failed')
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                        Failed
                    </span>
                @else
                    <span class="text-gray-400 dark:text-gray-500">â€”</span>
                @endif
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Timestamp</p>
                <p class="text-gray-900 dark:text-gray-100">{{ $auditLog->created_at->format('F d, Y h:i A') }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Module</p>
                <p class="text-gray-900 dark:text-gray-100 capitalize">{{ $auditLog->module ? str_replace('_', ' ', $auditLog->module) : 'â€”' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Affected Record</p>
                <p class="text-gray-900 dark:text-gray-100">{{ $auditLog->record_id ? "#{$auditLog->record_id}" : 'â€”' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Description</p>
                <p class="text-gray-900 dark:text-gray-100">{{ $auditLog->description ?? 'â€”' }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 mb-6">
        <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">Performed By</h2>
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 flex items-center justify-center text-lg font-bold shrink-0">
                {{ strtoupper(substr($auditLog->user?->name ?? 'S', 0, 1)) }}
            </div>
            <div>
                <p class="text-gray-900 dark:text-gray-100 font-semibold">{{ $auditLog->user?->name ?? 'System' }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $auditLog->user?->email ?? 'â€”' }}</p>
            </div>
            @if($auditLog->role)
                <span class="ml-auto inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                    {{ $auditLog->role === 'admin' ? 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300' : ($auditLog->role === 'supervisor' ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400' : ($auditLog->role === 'teacher' ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-gray-100 text-gray-600 dark:text-gray-400')) }}">
                    {{ ucfirst($auditLog->role) }}
                </span>
            @endif
        </div>
    </div>

    @if(!empty($auditLog->old_values) || !empty($auditLog->new_values))
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 mb-6">
        <div class="flex items-center gap-2 mb-4">
            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Changes</h2>
        </div>
        @php
            $allKeys = array_unique(array_merge(array_keys($auditLog->old_values ?? []), array_keys($auditLog->new_values ?? [])));
            $ignoreKeys = ['updated_at', 'created_at', 'password', 'remember_token'];
        @endphp
        <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-xl">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left px-4 py-2.5 font-semibold text-gray-600 dark:text-gray-400 text-xs uppercase tracking-wider">Field</th>
                        <th class="text-left px-4 py-2.5 font-semibold text-gray-600 dark:text-gray-400 text-xs uppercase tracking-wider">Before</th>
                        <th class="text-left px-4 py-2.5 font-semibold text-gray-600 dark:text-gray-400 text-xs uppercase tracking-wider">After</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($allKeys as $key)
                        @if(in_array($key, $ignoreKeys)) @continue @endif
                        @php
                            $oldVal = $auditLog->old_values[$key] ?? null;
                            $newVal = $auditLog->new_values[$key] ?? null;
                            $changed = $oldVal !== $newVal;
                            $displayOld = is_array($oldVal) ? json_encode($oldVal) : (is_bool($oldVal) ? ($oldVal ? 'true' : 'false') : ($oldVal ?? 'â€”'));
                            $displayNew = is_array($newVal) ? json_encode($newVal) : (is_bool($newVal) ? ($newVal ? 'true' : 'false') : ($newVal ?? 'â€”'));
                        @endphp
                        <tr class="{{ $changed ? 'bg-amber-50 dark:bg-amber-950/30' : 'hover:bg-gray-50 dark:hover:bg-gray-800/50' }}">
                            <td class="px-4 py-2.5 font-medium text-gray-700 dark:text-gray-300 capitalize whitespace-nowrap">{{ str_replace('_', ' ', $key) }}</td>
                            <td class="px-4 py-2.5 {{ $changed ? 'text-red-600 dark:text-red-400' : 'text-gray-500 dark:text-gray-400' }} {{ $changed ? 'font-medium' : '' }}">
                                @if($oldVal !== null && $changed)
                                    <span class="line-through decoration-red-400">{{ $displayOld }}</span>
                                @else
                                    {{ $displayOld }}
                                @endif
                            </td>
                            <td class="px-4 py-2.5 {{ $changed ? 'text-emerald-600 dark:text-emerald-400 font-medium' : 'text-gray-500 dark:text-gray-400' }}">
                                {{ $displayNew }}
                                @if($changed)
                                    <span class="ml-1.5 text-[10px] font-semibold text-emerald-500">CHANGED</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-6 text-center text-gray-400 dark:text-gray-500 text-sm">
                                No field-level changes recorded.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Request Details</h2>
            <div class="space-y-3">
                <div>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-0.5">IP Address</p>
                    <p class="text-gray-900 dark:text-gray-100 text-sm font-mono">{{ $auditLog->ip_address ?? 'â€”' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-0.5">User Agent</p>
                    <p class="text-gray-900 dark:text-gray-100 text-sm break-all">{{ $auditLog->user_agent ?? 'â€”' }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Additional Metadata</h2>
            <div class="space-y-3">
                @if($auditLog->metadata && count($auditLog->metadata) > 0)
                    @foreach($auditLog->metadata as $metaKey => $metaValue)
                        <div>
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-0.5">{{ ucwords(str_replace('_', ' ', $metaKey)) }}</p>
                            <p class="text-gray-900 dark:text-gray-100 text-sm">{{ is_array($metaValue) ? json_encode($metaValue) : (is_bool($metaValue) ? ($metaValue ? 'true' : 'false') : $metaValue) }}</p>
                        </div>
                    @endforeach
                @else
                    <p class="text-gray-400 dark:text-gray-500 text-sm">No additional metadata.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                <p class="text-sm font-medium text-amber-800 dark:text-amber-300">Append-Only Record</p>
                <p class="text-xs text-amber-700 dark:text-amber-400 mt-1">This audit log entry is immutable. It was recorded at the time of the action and cannot be edited or deleted. This ensures a trustworthy audit trail for accountability and security investigations.</p>
            </div>
        </div>
    </div>
</div>
@endsection
