@extends('layouts.admin')

@section('title', 'AI Usage & Cost')

@section('content')
@php
    $symbol = config('ai.pricing.currency_symbol', '₱');
    $hasData = ($analysis['stats']['calls'] ?? 0) > 0;
    $hasFilters = collect($filters)->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();
@endphp

<div class="max-w-7xl mx-auto px-6 space-y-6">

    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">AI Usage &amp; Cost</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Monitor AI spend, token usage and the features, models and users consuming the most resources.</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Costs are <strong>estimates</strong> computed from the pricing catalog in <code>config/ai.php</code> (USD per 1M tokens, converted to PHP). No database changes.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.ai.index') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors text-sm font-medium">
                AI Settings
            </a>
        </div>
    </div>

    <!-- Filter bar -->
    <form method="GET" action="{{ route('admin.ai-usage.index') }}" class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Filters</h3>
            @if($hasFilters)
                <a href="{{ route('admin.ai-usage.index') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800">Clear filters</a>
            @endif
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">From</label>
                <input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">To</label>
                <input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Provider</label>
                <select name="provider" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="">All providers</option>
                    @foreach($options['providers'] as $provider)
                        <option value="{{ $provider }}" {{ $filters['provider'] === $provider ? 'selected' : '' }}>{{ $provider }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Model</label>
                <select name="model" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="">All models</option>
                    @foreach($options['models'] as $model)
                        <option value="{{ $model }}" {{ $filters['model'] === $model ? 'selected' : '' }}>{{ $model }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Feature / Stage</label>
                <select name="stage" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="">All features</option>
                    @foreach($options['stages'] as $stage)
                        <option value="{{ $stage }}" {{ $filters['stage'] === $stage ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $stage)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">User</label>
                <select name="user_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="">All users</option>
                    @foreach($options['user_ids'] as $userId)
                        @php($optUser = $options['users']->get($userId))
                        <option value="{{ $userId }}" {{ $filters['user_id'] == $userId ? 'selected' : '' }}>{{ $optUser?->name ?? 'Deleted user #'.$userId }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                <select name="success" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="">All</option>
                    <option value="1" {{ $filters['success'] === 1 ? 'selected' : '' }}>Successful</option>
                    <option value="0" {{ $filters['success'] === 0 ? 'selected' : '' }}>Failed</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Served by fallback</label>
                <select name="fallback_used" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="">All</option>
                    <option value="1" {{ $filters['fallback_used'] === 1 ? 'selected' : '' }}>Yes</option>
                    <option value="0" {{ $filters['fallback_used'] === 0 ? 'selected' : '' }}>No</option>
                </select>
            </div>
        </div>
        <div class="flex items-center gap-3 mt-4">
            <button type="submit" class="px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium">Apply Filters</button>
            <a href="{{ route('admin.ai-usage.index') }}" class="px-5 py-2 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors text-sm font-medium">Reset</a>
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

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Estimated Cost per Day</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Last 30 days unless a date range is chosen</p>
            <div class="h-64">
                <canvas id="costByDayChart"></canvas>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Cost by Provider</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Share of estimated spend per provider</p>
            <div class="h-64">
                <canvas id="costByProviderChart"></canvas>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Top Models by Cost</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Top 10 models by estimated spend</p>
            <div class="h-64">
                <canvas id="costByModelChart"></canvas>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Cost by Feature</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Estimated spend grouped by AI feature/stage</p>
            <div class="h-64">
                <canvas id="costByStageChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Breakdown tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Top Models by Cost</h3>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase pb-3">Model</th>
                            <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase pb-3">Provider</th>
                            <th class="text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase pb-3">Calls</th>
                            <th class="text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase pb-3">Tokens</th>
                            <th class="text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase pb-3">Cost</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($analysis['top_models'] as $row)
                            <tr>
                                <td class="py-3 text-sm font-medium text-gray-900 dark:text-gray-100 break-all">{{ $row['model'] }}</td>
                                <td class="py-3 text-sm text-gray-600 dark:text-gray-300">{{ $row['provider'] }}</td>
                                <td class="py-3 text-sm text-gray-600 dark:text-gray-300 text-right">{{ number_format($row['calls']) }}</td>
                                <td class="py-3 text-sm text-gray-600 dark:text-gray-300 text-right">{{ number_format($row['tokens']) }}</td>
                                <td class="py-3 text-sm text-indigo-600 dark:text-indigo-400 text-right font-medium">{{ $symbol }}{{ number_format($row['cost'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-sm text-gray-400 dark:text-gray-500">No usage data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Top Features by Cost</h3>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase pb-3">Feature / Stage</th>
                            <th class="text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase pb-3">Calls</th>
                            <th class="text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase pb-3">Tokens</th>
                            <th class="text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase pb-3">Cost</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($analysis['top_stages'] as $row)
                            <tr>
                                <td class="py-3 text-sm font-medium text-gray-900 dark:text-gray-100 capitalize">{{ str_replace('_', ' ', $row['stage']) }}</td>
                                <td class="py-3 text-sm text-gray-600 dark:text-gray-300 text-right">{{ number_format($row['calls']) }}</td>
                                <td class="py-3 text-sm text-gray-600 dark:text-gray-300 text-right">{{ number_format($row['tokens']) }}</td>
                                <td class="py-3 text-sm text-indigo-600 dark:text-indigo-400 text-right font-medium">{{ $symbol }}{{ number_format($row['cost'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-sm text-gray-400 dark:text-gray-500">No usage data.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Top users -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Top Users by Cost</h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase pb-3">User</th>
                        <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase pb-3">Role</th>
                        <th class="text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase pb-3">Calls</th>
                        <th class="text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase pb-3">Tokens</th>
                        <th class="text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase pb-3">Cost</th>
                        <th class="text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase pb-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($analysis['top_users'] as $row)
                        <tr>
                            <td class="py-3 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $row['name'] }}</td>
                            <td class="py-3 text-sm text-gray-600 dark:text-gray-300">{{ ucwords($row['role'] ?? '') }}</td>
                            <td class="py-3 text-sm text-gray-600 dark:text-gray-300 text-right">{{ number_format($row['calls']) }}</td>
                            <td class="py-3 text-sm text-gray-600 dark:text-gray-300 text-right">{{ number_format($row['tokens']) }}</td>
                            <td class="py-3 text-sm text-indigo-600 dark:text-indigo-400 text-right font-medium">{{ $symbol }}{{ number_format($row['cost'], 2) }}</td>
                            <td class="py-3 text-right">
                                <a href="{{ route('admin.ai-usage.users.show', $row['user_id']) }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">Details</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-sm text-gray-400 dark:text-gray-500">No user usage data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Detailed call log -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Detailed Call Log</h3>
            <form method="GET" action="{{ route('admin.ai-usage.index') }}" class="flex items-center gap-2">
                @foreach($filters as $key => $value)
                    @if($value !== null && $value !== '')
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach
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
                        <th class="text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase pb-3">User</th>
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
                            <td class="py-3 text-sm text-gray-700 dark:text-gray-200">
                                @if($log->user)
                                    <a href="{{ route('admin.ai-usage.users.show', $log->user_id) }}" class="hover:underline">{{ $log->user->name }}</a>
                                @else
                                    <span class="text-gray-400 dark:text-gray-500">System {{ $log->observation_id ? '· observation #'.$log->observation_id : '' }}</span>
                                @endif
                            </td>
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
                            <td colspan="9" class="py-8 text-center text-sm text-gray-400 dark:text-gray-500">No AI usage recorded yet. Trigger an AI feature to start collecting data.</td>
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

    const palette = [
        'rgba(99, 102, 241, 0.75)', 'rgba(16, 185, 129, 0.75)', 'rgba(245, 158, 11, 0.75)',
        'rgba(236, 72, 153, 0.75)', 'rgba(59, 130, 246, 0.75)', 'rgba(14, 165, 233, 0.75)',
        'rgba(168, 85, 247, 0.75)', 'rgba(239, 68, 68, 0.75)', 'rgba(5, 150, 105, 0.75)',
        'rgba(217, 119, 6, 0.75)',
    ];
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

    const providers = @json($analysis['providerMap']);
    if (hasData && document.getElementById('costByProviderChart')) {
        const pLabels = Object.keys(providers);
        new Chart(document.getElementById('costByProviderChart'), {
            type: 'doughnut',
            data: {
                labels: pLabels,
                datasets: [{
                    data: pLabels.map(k => providers[k].cost),
                    backgroundColor: palette,
                    borderWidth: 2,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'right' }, tooltip: tooltipStyle },
            },
        });
    }

    const models = @json($analysis['top_models']);
    if (hasData && document.getElementById('costByModelChart')) {
        new Chart(document.getElementById('costByModelChart'), {
            type: 'bar',
            data: {
                labels: models.map(m => m.model.length > 28 ? m.model.slice(0, 27) + '…' : m.model),
                datasets: [{
                    label: 'Cost ({{ $symbol }})',
                    data: models.map(m => m.cost),
                    backgroundColor: palette,
                    borderRadius: 6,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: { legend: { display: false }, tooltip: tooltipStyle },
                scales: { x: { beginAtZero: true } },
            },
        });
    }

    const stages = @json($analysis['top_stages']);
    if (hasData && document.getElementById('costByStageChart')) {
        const sLabels = stages.map(s => s.stage.replace(/_/g, ' '));
        new Chart(document.getElementById('costByStageChart'), {
            type: 'doughnut',
            data: {
                labels: sLabels,
                datasets: [{
                    data: stages.map(s => s.cost),
                    backgroundColor: palette,
                    borderWidth: 2,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'right' }, tooltip: tooltipStyle },
            },
        });
    }
});
</script>
@endpush
@endsection