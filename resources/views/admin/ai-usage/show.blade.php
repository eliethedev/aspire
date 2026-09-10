@extends('layouts.admin')

@section('title', 'AI Usage — '.$user->name)

@section('content')
@php
    $symbol = config('ai.pricing.currency_symbol', '₱');
    $hasData = ($analysis['stats']['calls'] ?? 0) > 0;
@endphp

<div class="max-w-7xl mx-auto px-6 space-y-6">

    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                <a href="{{ route('admin.ai-usage.index') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">&larr; AI Usage &amp; Cost</a>
            </p>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $user->name }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $user->email }} &middot; {{ ucwords($user->role) }} &middot; AI usage and estimated spend for this user.</p>
        </div>
    </div>

    <!-- Date filter -->
    <form method="GET" action="{{ route('admin.ai-usage.users.show', $user->id) }}" class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">From</label>
                <input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">To</label>
                <input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>
        <div class="flex items-center gap-3 mt-4">
            <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium">Apply</button>
            <a href="{{ route('admin.ai-usage.users.show', $user->id) }}" class="px-5 py-2 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors text-sm font-medium">Reset</a>
        </div>
    </form>

    <!-- Stats cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <p class="text-xs text-gray-500 dark:text-gray-400">Estimated Spend</p>
            <p class="text-2xl font-extrabold text-indigo-600 dark:text-indigo-400 mt-1">{{ $symbol }}{{ number_format($analysis['stats']['cost'], 2) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <p class="text-xs text-gray-500 dark:text-gray-400">Total Calls</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($analysis['stats']['calls']) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <p class="text-xs text-gray-500 dark:text-gray-400">Total Tokens</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($analysis['stats']['tokens']) }}</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <p class="text-xs text-gray-500 dark:text-gray-400">Success Rate</p>
            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ $analysis['stats']['success_rate'] }}%</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <p class="text-xs text-gray-500 dark:text-gray-400">Avg Response</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($analysis['stats']['avg_response_ms'], 0) }}ms</p>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <p class="text-xs text-gray-500 dark:text-gray-400">Avg Cost / Call</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $symbol }}{{ number_format($analysis['stats']['avg_cost_per_call'], 4) }}</p>
        </div>
    </div>

    <!-- Chart + models -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Estimated Cost per Day</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Last 30 days unless a date range is chosen</p>
            <div class="h-64">
                <canvas id="costByDayChart"></canvas>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Models Used by {{ $user->name }}</h3>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase pb-3">Model</th>
                            <th class="text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase pb-3">Calls</th>
                            <th class="text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase pb-3">Tokens</th>
                            <th class="text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase pb-3">Cost</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($analysis['top_models'] as $row)
                            <tr>
                                <td class="py-3 text-sm font-medium text-gray-900 dark:text-gray-100 break-all">{{ $row['model'] }}</td>
                                <td class="py-3 text-sm text-gray-600 dark:text-gray-300 text-right">{{ number_format($row['calls']) }}</td>
                                <td class="py-3 text-sm text-gray-600 dark:text-gray-300 text-right">{{ number_format($row['tokens']) }}</td>
                                <td class="py-3 text-sm text-indigo-600 dark:text-indigo-400 text-right font-medium">{{ $symbol }}{{ number_format($row['cost'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-sm text-gray-400 dark:text-gray-500">No AI usage for this user.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Call log -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Call History</h3>
            <form method="GET" action="{{ route('admin.ai-usage.users.show', $user->id) }}" class="flex items-center gap-2">
                @if($filters['date_from'])
                    <input type="hidden" name="date_from" value="{{ $filters['date_from'] }}">
                @endif
                @if($filters['date_to'])
                    <input type="hidden" name="date_to" value="{{ $filters['date_to'] }}">
                @endif
                <select name="per_page" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500">
                    @foreach([15, 30, 50, 100] as $per)
                        <option value="{{ $per }}" {{ $logs->perPage() === $per ? 'selected' : '' }}>{{ $per }} per page</option>
                    @endforeach
                </select>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase pb-3">When</th>
                        <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase pb-3">Feature</th>
                        <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase pb-3">Provider / Model</th>
                        <th class="text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase pb-3">In</th>
                        <th class="text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase pb-3">Out</th>
                        <th class="text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase pb-3">Est. Cost</th>
                        <th class="text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase pb-3">Time</th>
                        <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase pb-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($logs as $log)
                        <tr data-user-id="{{ $log->user_id }}">
                            <td class="py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $log->created_at->format('M j, Y g:i A') }}</td>
                            <td class="py-3 text-sm text-gray-700 dark:text-gray-200 capitalize">{{ str_replace('_', ' ', $log->stage) }}</td>
                            <td class="py-3 text-sm text-gray-600 dark:text-gray-300 break-all">
                                <span class="text-gray-400 dark:text-gray-500">{{ $log->provider }} /</span> {{ $log->model ?? 'N/A' }}
                            </td>
                            <td class="py-3 text-sm text-gray-600 dark:text-gray-300 text-right">{{ number_format($log->prompt_tokens ?? 0) }}</td>
                            <td class="py-3 text-sm text-gray-600 dark:text-gray-300 text-right">{{ number_format($log->response_tokens ?? 0) }}</td>
                            <td class="py-3 text-sm text-indigo-600 dark:text-indigo-400 text-right font-medium">{{ $symbol }}{{ number_format($log->estimatedCost(), 4) }}</td>
                            <td class="py-3 text-sm text-gray-600 dark:text-gray-300 text-right whitespace-nowrap">{{ $log->response_time_ms ? $log->response_time_ms.'ms' : '—' }}</td>
                            <td class="py-3 text-sm">
                                @if(! $log->success)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300" title="{{ $log->error_message }}">Failed</span>
                                @elseif($log->fallback_used)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300" title="Served by a fallback provider">Fallback</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300">Success</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-sm text-gray-400 dark:text-gray-500">No AI usage recorded for this user.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="mt-4">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') return;

    const tooltipStyle = {
        backgroundColor: 'rgba(17, 24, 39, 0.92)',
        padding: 12,
        cornerRadius: 8,
        titleFont: { family: 'Figtree, sans-serif' },
        bodyFont: { family: 'Figtree, sans-serif' },
    };
    const hasData = @json($hasData);
    const daily = @json($analysis['daily']);

    if (hasData && document.getElementById('costByDayChart')) {
        new Chart(document.getElementById('costByDayChart'), {
            type: 'bar',
            data: {
                labels: daily.labels,
                datasets: [{
                    label: 'Estimated cost ({{ $symbol }})',
                    data: daily.costs,
                    backgroundColor: 'rgba(99, 102, 241, 0.55)',
                    borderRadius: 6,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: tooltipStyle },
                scales: { y: { beginAtZero: true } },
            },
        });
    }
});
</script>
@endpush
@endsection