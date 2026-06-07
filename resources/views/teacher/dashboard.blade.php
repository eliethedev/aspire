@extends('layouts.teacher')

@section('title', 'Teacher Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8 space-y-8">
    <!-- Welcome Section -->
    <div class="relative overflow-hidden bg-gradient-to-br from-gray-900 via-gray-800 to-gray-700 rounded-2xl shadow-lg p-8">
        <div class="relative z-10">
            <h1 class="text-2xl font-bold text-white">Welcome back, {{ Auth::user()->name }}!</h1>
            <p class="text-gray-300 mt-1">Here is your supervision progress overview.</p>
        </div>
        <div class="absolute right-6 top-6 text-right">
            <p class="text-sm text-gray-400">{{ now()->format('l, F j, Y') }}</p>
        </div>
    </div>

    <!-- Performance Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-indigo-100">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Total Observations</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_observations'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-green-100">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Average Score</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['average_cot_score'], 2) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-amber-100">
                    <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Upcoming</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['upcoming'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-purple-100">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Completed</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['completed'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Trend Chart -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">COT Score Trend</h2>
        @if(count($cotScores) > 0)
            <canvas id="growthChart" width="400" height="200"></canvas>
        @else
            <p class="text-gray-400 text-sm">No completed observations yet to show trend data.</p>
        @endif
    </div>

    <!-- Recent Observation & AI Feedback -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Observation Results -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Recent Observation</h2>
                @if($recentObservation)
                    <a href="{{ route('teacher.observations.show', $recentObservation) }}" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">View Details &rarr;</a>
                @endif
            </div>
            @if($recentObservation)
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Date:</span>
                        <span class="text-sm font-medium text-gray-900">{{ $recentObservation->observation_date->format('M d, Y') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Supervisor:</span>
                        <span class="text-sm font-medium text-gray-900">{{ $recentObservation->observer?->name ?? 'Unknown' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Stage:</span>
                        <span class="text-sm font-medium text-gray-900 capitalize">{{ str_replace('_', ' ', $recentObservation->stage) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Overall Score:</span>
                        <span class="text-lg font-bold text-indigo-600">{{ number_format($recentObservation->overall_score, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Status:</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $recentObservation->status === 'completed' ? 'bg-green-100 text-green-700' : ($recentObservation->status === 'scheduled' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') }}">
                            {{ ucwords(str_replace('_', ' ', $recentObservation->status)) }}
                        </span>
                    </div>
                </div>
            @else
                <p class="text-gray-400 text-sm">No observation data available yet.</p>
            @endif
        </div>

        <!-- AI Coaching Feedback -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Post-Conference Feedback</h2>
            @if($recentFeedback)
                <div class="space-y-4">
                    @if($recentFeedback->feedback)
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 mb-1">Feedback:</h3>
                            <p class="text-gray-900 text-sm">{{ Str::limit($recentFeedback->feedback, 200) }}</p>
                        </div>
                    @endif
                    @if($recentFeedback->action_plan)
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 mb-1">Action Plan:</h3>
                            <p class="text-gray-900 text-sm">{{ Str::limit($recentFeedback->action_plan, 200) }}</p>
                        </div>
                    @endif
                    @if(!$recentFeedback->feedback && !$recentFeedback->action_plan)
                        <p class="text-gray-400 text-sm">No feedback recorded yet.</p>
                    @endif
                </div>
            @else
                <p class="text-gray-400 text-sm">No post-conference feedback available yet.</p>
            @endif
        </div>
    </div>

    <!-- Next Observation & Agreements -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Next Observation -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Next Observation</h2>
                @if($nextObservation)
                    <a href="{{ route('teacher.observations.show', $nextObservation) }}" class="text-sm text-indigo-600 hover:text-indigo-700 font-medium">View Details &rarr;</a>
                @endif
            </div>
            @if($nextObservation)
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Supervisor:</span>
                        <span class="text-sm font-medium text-gray-900">{{ $nextObservation->observer?->name ?? 'Unknown' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Date:</span>
                        <span class="text-sm font-medium text-gray-900">{{ $nextObservation->observation_date->format('M d, Y') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Subject:</span>
                        <span class="text-sm font-medium text-gray-900">{{ $nextObservation->subject ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-500">Stage:</span>
                        <span class="text-sm font-medium text-gray-900 capitalize">{{ str_replace('_', ' ', $nextObservation->stage) }}</span>
                    </div>
                </div>
            @else
                <p class="text-gray-400 text-sm">No upcoming observations scheduled.</p>
            @endif
        </div>

        <!-- Improvement Plan / Agreements -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Links</h2>
            <div class="space-y-3">
                <a href="{{ route('teacher.observations.index') }}"
                   class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 hover:bg-indigo-50 transition-colors group">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900 group-hover:text-indigo-600 transition-colors">All Observations</p>
                        <p class="text-xs text-gray-500">View your complete observation history</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

@if(count($cotScores) > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('growthChart').getContext('2d');
    const growthChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($cotLabels) !!},
            datasets: [{
                label: 'COT Score',
                data: {!! json_encode($cotScores) !!},
                borderColor: 'rgb(79, 70, 229)',
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    min: 1,
                    max: 5,
                    ticks: {
                        stepSize: 0.5
                    }
                }
            }
        }
    });
</script>
@endif
@endsection