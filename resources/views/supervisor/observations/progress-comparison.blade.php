@extends('layouts.supervisor')

@section('title', 'Progress Comparison - Observation #' . $observation->id)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Progress Comparison</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $observation->observee?->user?->name ?? 'Teacher' }}</p>
        </div>
        <a href="{{ route('supervisor.observations.show', $observation) }}" class="text-sm text-indigo-600 hover:text-indigo-700">← Back to Observation</a>
    </div>

    @if($comparison)
    <!-- Overall Score Comparison -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 p-6 mb-8">
        <div class="grid grid-cols-3 gap-6 text-center">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Previous ({{ $comparison['previous_date'] }})</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ number_format($comparison['previous_overall'], 2) }}%</p>
            </div>
            <div class="flex items-center justify-center">
                <div class="text-4xl font-bold
                    @if($comparison['overall_direction'] === 'improved') text-green-600
                    @elseif($comparison['overall_direction'] === 'declined') text-red-600
                    @else text-gray-400
                    @endif">
                    @if($comparison['overall_delta'] !== null)
                        {{ $comparison['overall_delta'] > 0 ? '+' : '' }}{{ $comparison['overall_delta'] }}%
                    @else
                        N/A
                    @endif
                </div>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Current ({{ $comparison['current_date'] }})</p>
                <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400 mt-1">{{ number_format($comparison['current_overall'], 2) }}%</p>
            </div>
        </div>
    </div>

    <!-- Detailed Comparison -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 shadow-sm mb-8">
        <div class="p-4 border-b border-gray-100 dark:border-gray-800">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Indicator-by-Indicator Comparison</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-medium text-gray-500">Indicator</th>
                        <th class="text-center px-4 py-3 text-xs font-medium text-gray-500">Previous</th>
                        <th class="text-center px-4 py-3 text-xs font-medium text-gray-500">Current</th>
                        <th class="text-center px-4 py-3 text-xs font-medium text-gray-500">Change</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($comparison['comparisons'] as $c)
                    <tr>
                        <td class="px-4 py-3">
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $c['code'] }}</span>
                            <span class="text-gray-500 dark:text-gray-400 ml-2">{{ $c['indicator'] }}</span>
                        </td>
                        <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-400">{{ $c['previous_rating'] }}/6</td>
                        <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-400">{{ $c['current_rating'] }}/6</td>
                        <td class="px-4 py-3 text-center font-semibold
                            @if($c['direction'] === 'improved') text-green-600
                            @elseif($c['direction'] === 'declined') text-red-600
                            @else text-gray-400
                            @endif">
                            @if($c['delta'] !== null)
                                {{ $c['delta'] > 0 ? '+' : '' }}{{ $c['delta'] }}
                            @else
                                N/A
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- PD Plan -->
    @if($pdPlan && (!empty($pdPlan['short_term_goals']) || !empty($pdPlan['long_term_goals'])))
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Professional Development Plan</h2>

        @if(!empty($pdPlan['short_term_goals']))
        <h3 class="text-sm font-semibold text-red-700 dark:text-red-400 mb-3">Short-Term Goals (1-2 Months)</h3>
        <div class="space-y-3 mb-6">
            @foreach($pdPlan['short_term_goals'] as $goal)
            <div class="bg-red-50 dark:bg-red-900/20 border-l-4 border-red-400 rounded-r-lg p-4">
                <p class="font-medium text-gray-900 dark:text-gray-100 text-sm">{{ $goal['indicator'] }}</p>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1"><strong>Target:</strong> {{ $goal['target'] }}</p>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1"><strong>Activities:</strong></p>
                <ul class="list-disc list-inside text-xs text-gray-600 dark:text-gray-400 mt-1">
                    @foreach($goal['activities'] as $a)
                    <li>{{ $a }}</li>
                    @endforeach
                </ul>
                <p class="text-xs text-gray-500 dark:text-gray-500 mt-1"><strong>Support:</strong> {{ $goal['support_needed'] }}</p>
            </div>
            @endforeach
        </div>
        @endif

        @if(!empty($pdPlan['long_term_goals']))
        <h3 class="text-sm font-semibold text-blue-700 dark:text-blue-400 mb-3">Long-Term Goals (3-6 Months)</h3>
        <div class="space-y-3">
            @foreach($pdPlan['long_term_goals'] as $goal)
            <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-400 rounded-r-lg p-4">
                <p class="font-medium text-gray-900 dark:text-gray-100 text-sm">{{ $goal['indicator'] }}</p>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1"><strong>Target:</strong> {{ $goal['target'] }}</p>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1"><strong>Activities:</strong></p>
                <ul class="list-disc list-inside text-xs text-gray-600 dark:text-gray-400 mt-1">
                    @foreach($goal['activities'] as $a)
                    <li>{{ $a }}</li>
                    @endforeach
                </ul>
                <p class="text-xs text-gray-500 dark:text-gray-500 mt-1"><strong>Support:</strong> {{ $goal['support_needed'] }}</p>
            </div>
            @endforeach
        </div>
        @endif
    </div>
    @endif

    @else
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 p-8 text-center">
        <p class="text-gray-500 dark:text-gray-400">No previous observation found for comparison. At least two completed observations are needed.</p>
    </div>
    @endif
</div>
@endsection
