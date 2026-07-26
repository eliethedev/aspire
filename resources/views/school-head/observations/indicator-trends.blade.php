@extends('layouts.teacher')

@section('title', 'Indicator Trends - Observation #' . $observation->id)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Indicator Trend Analysis</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ $observation->observee?->user?->name ?? 'Teacher' }} | {{ $trends['total_observations'] }} observations
                @if($trends['date_range']['start'])
                    ({{ $trends['date_range']['start'] }} — {{ $trends['date_range']['end'] }})
                @endif
            </p>
        </div>
        <a href="{{ route('school-head.observations.show', $observation) }}" class="text-sm text-indigo-600 hover:text-indigo-700">← Back to Observation</a>
    </div>

    <!-- Domain Summary -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8">
        @foreach($trends['domains'] as $domain)
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 p-4">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ Str::replace('Domain ', 'D', $domain['domain']) }}</p>
                <p class="text-2xl font-bold
                    @if($domain['average_percentage'] >= 83) text-green-600
                    @elseif($domain['average_percentage'] >= 67) text-blue-600
                    @elseif($domain['average_percentage'] >= 50) text-yellow-600
                    @else text-red-600
                    @endif">{{ number_format($domain['average_percentage'], 0) }}%</p>
                <p class="text-[10px] text-gray-400 mt-1">{{ $domain['descriptive'] }}</p>
                @if($domain['low_indicator_count'] > 0)
                    <p class="text-[10px] text-red-500 mt-1">{{ $domain['low_indicator_count'] }} low indicator(s)</p>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Consistently Low Indicators -->
    @if($trends['low_indicators']->isNotEmpty())
    <div class="bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-200 dark:border-red-800 p-6 mb-8">
        <h2 class="text-lg font-semibold text-red-800 dark:text-red-300 mb-4">⚠️ Consistently Low-Rated Indicators</h2>
        <p class="text-sm text-red-600 dark:text-red-400 mb-4">These indicators scored below 3/6 across multiple observations and require targeted intervention.</p>
        <div class="space-y-3">
            @foreach($trends['low_indicators'] as $ind)
            <div class="bg-white dark:bg-gray-900 rounded-lg p-4 border border-red-200 dark:border-red-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-gray-100 text-sm">{{ $ind['code'] }}: {{ $ind['indicator'] }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $ind['domain'] }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-red-600 dark:text-red-400">{{ $ind['average_rating'] }}/6</p>
                        <p class="text-[10px] text-gray-400">{{ $ind['occurrences'] }} observation(s)</p>
                    </div>
                </div>
                <div class="mt-2 flex gap-1">
                    @foreach($ind['history'] as $h)
                        <span class="text-[10px] px-1.5 py-0.5 rounded
                            @if($h['rating'] >= 5) bg-green-100 text-green-700
                            @elseif($h['rating'] >= 4) bg-blue-100 text-blue-700
                            @elseif($h['rating'] >= 3) bg-yellow-100 text-yellow-700
                            @else bg-red-100 text-red-700
                            @endif">{{ $h['rating'] }}</span>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- All Indicators Table -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 shadow-sm">
        <div class="p-4 border-b border-gray-100 dark:border-gray-800">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">All Indicators</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 dark:text-gray-400">Code</th>
                        <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 dark:text-gray-400">Indicator</th>
                        <th class="text-center px-4 py-3 text-xs font-medium text-gray-500 dark:text-gray-400">Avg</th>
                        <th class="text-center px-4 py-3 text-xs font-medium text-gray-500 dark:text-gray-400">%</th>
                        <th class="text-center px-4 py-3 text-xs font-medium text-gray-500 dark:text-gray-400">Trend</th>
                        <th class="text-center px-4 py-3 text-xs font-medium text-gray-500 dark:text-gray-400">Range</th>
                        <th class="text-center px-4 py-3 text-xs font-medium text-gray-500 dark:text-gray-400">Obs</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($trends['indicators'] as $ind)
                    <tr class="{{ $ind['average_rating'] < 3 ? 'bg-red-50 dark:bg-red-900/10' : '' }}">
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">{{ $ind['code'] }}</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400 max-w-xs">{{ $ind['indicator'] }}</td>
                        <td class="px-4 py-3 text-center font-semibold
                            @if($ind['average_rating'] >= 5) text-green-600
                            @elseif($ind['average_rating'] >= 4) text-blue-600
                            @elseif($ind['average_rating'] >= 3) text-yellow-600
                            @else text-red-600
                            @endif">{{ $ind['average_rating'] }}</td>
                        <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-400">{{ $ind['average_percentage'] }}%</td>
                        <td class="px-4 py-3 text-center">
                            @if($ind['trend'] === 'improving')
                                <span class="text-green-600">📈 +{{ $ind['trend_delta'] }}</span>
                            @elseif($ind['trend'] === 'declining')
                                <span class="text-red-600">📉 {{ $ind['trend_delta'] }}</span>
                            @else
                                <span class="text-gray-400">→ Stable</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-400">{{ $ind['min_rating'] }}-{{ $ind['max_rating'] }}</td>
                        <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-400">{{ $ind['occurrences'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
