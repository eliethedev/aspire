@extends('layouts.supervisor')

@section('title', 'PD Recommendations - Observation #' . $observation->id)
@include('partials.dashboard.mock-styles')

@section('content')
<div class="mock-wrap max-w-7xl mx-auto px-1 py-1">
    <div class="mock-topbar">
        <div class="mock-crumbs">Supervisor <span>/</span> <b>PD Recommendations</b></div>
        <div class="mock-actions">
            <a class="mock-btn" href="{{ route('supervisor.observations.show', $observation) }}">← Back to Observation</a>
        </div>
    </div>

    <div class="mock-title">
        <div>
            <h1>Professional Development Recommendations</h1>
            <p>{{ $observation->observee?->user?->name ?? 'Teacher' }}</p>
        </div>
        <time>Observation #{{ $observation->id }}</time>
    </div>

    @if($recommendations)
    <section class="mock-panel" aria-label="PD recommendations">
        <div class="mock-panel-head"><h2>Recommendations</h2><span class="hint">{{ count($recommendations) }} focus areas</span></div>
    <div class="space-y-6" style="padding:14px 16px">
        @foreach($recommendations as $rec)
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-100 shadow-sm p-6
            @if($rec['severity'] === 'critical') border-l-4 border-l-red-500
            @elseif($rec['severity'] === 'high') border-l-4 border-l-orange-500
            @elseif($rec['severity'] === 'medium') border-l-4 border-l-yellow-500
            @endif">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ $rec['indicator_code'] }}: {{ $rec['indicator'] }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $rec['domain'] }}</p>
                </div>
                <div class="text-right">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        @if($rec['severity'] === 'critical') bg-red-100 text-red-800
                        @elseif($rec['severity'] === 'high') bg-orange-100 text-orange-800
                        @elseif($rec['severity'] === 'medium') bg-yellow-100 text-yellow-800
                        @else bg-green-100 text-green-800
                        @endif">
                        {{ ucfirst($rec['severity']) }}
                    </span>
                    <p class="text-lg font-bold text-gray-900 dark:text-gray-100 mt-1">{{ $rec['current_average'] }}/6</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Recommended Activities</p>
                    <ul class="space-y-1.5">
                        @foreach($rec['activities'] as $activity)
                        <li class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-400">
                            <svg class="w-4 h-4 text-indigo-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            {{ $activity }}
                        </li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Classroom Strategies</p>
                    <ul class="space-y-1.5">
                        @foreach($rec['strategies'] as $strategy)
                        <li class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-400">
                            <svg class="w-4 h-4 text-green-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            {{ $strategy }}
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    </section>

    @if(!empty($pdPlan['short_term_goals']) || !empty($pdPlan['long_term_goals']))
    <section class="mock-panel" style="margin-top:16px" aria-label="Development plan summary">
        <div class="mock-panel-head"><h2>Development Plan Summary</h2></div>
        <div style="padding:14px 16px">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @if(!empty($pdPlan['short_term_goals']))
            <div>
                <h3 class="text-sm font-semibold text-red-700 mb-2">Short-Term (1-2 Months)</h3>
                @foreach($pdPlan['short_term_goals'] as $goal)
                <div class="bg-red-50 rounded-lg p-3 mb-2">
                    <p class="text-xs font-medium text-gray-900">{{ $goal['indicator'] }}</p>
                    <p class="text-xs text-gray-600 mt-1">{{ $goal['target'] }}</p>
                </div>
                @endforeach
            </div>
            @endif
            @if(!empty($pdPlan['long_term_goals']))
            <div>
                <h3 class="text-sm font-semibold text-blue-700 mb-2">Long-Term (3-6 Months)</h3>
                @foreach($pdPlan['long_term_goals'] as $goal)
                <div class="bg-blue-50 rounded-lg p-3 mb-2">
                    <p class="text-xs font-medium text-gray-900">{{ $goal['indicator'] }}</p>
                    <p class="text-xs text-gray-600 mt-1">{{ $goal['target'] }}</p>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
    </section>
    @endif

    @else
    <div class="mock-panel"><div class="mock-empty">
        <svg class="w-16 h-16 text-green-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="text-gray-500 dark:text-gray-400 font-medium">No low-rated indicators found</p>
        <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">This teacher has no consistently low indicators across observations. Great performance!</p>
    </div></div>
    @endif
</div>
@endsection
