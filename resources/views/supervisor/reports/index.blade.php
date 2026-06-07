@extends('layouts.supervisor')

@section('title', 'Reports')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-dark">Reports</h1>
            <p class="text-dark/60 mt-1">Overview of your supervision activities and statistics.</p>
        </div>
        <a href="{{ route('supervisor.reports.export') }}" 
           class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm">
            Export CSV
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Teachers -->
        <div class="bg-white rounded-xl shadow-sm glass-card p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-blue-500/20">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-dark/80">Total Teachers</p>
                    <p class="text-2xl font-bold text-dark">{{ $stats['total_teachers'] }}</p>
                </div>
            </div>
        </div>

        <!-- Total Observations -->
        <div class="bg-white rounded-xl shadow-sm glass-card p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-purple-500/20">
                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-dark/80">Total Observations</p>
                    <p class="text-2xl font-bold text-dark">{{ $stats['total_observations'] }}</p>
                </div>
            </div>
        </div>

        <!-- Completed Observations -->
        <div class="bg-white rounded-xl shadow-sm glass-card p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-green-500/20">
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-dark/80">Completed</p>
                    <p class="text-2xl font-bold text-dark">{{ $stats['completed_observations'] }}</p>
                </div>
            </div>
        </div>

        <!-- Pending Observations -->
        <div class="bg-white rounded-xl shadow-sm glass-card p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-yellow-500/20">
                    <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-dark/80">Pending</p>
                    <p class="text-2xl font-bold text-dark">{{ $stats['pending_observations'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Observations Trend -->
        <div class="bg-white rounded-xl shadow-sm glass-card p-6">
            <h2 class="text-lg font-semibold text-dark mb-4">Observations Trend</h2>
            <canvas id="observationsChart" height="200"></canvas>
        </div>
        <!-- Score Distribution -->
        <div class="bg-white rounded-xl shadow-sm glass-card p-6">
            <h2 class="text-lg font-semibold text-dark mb-4">Average Score Trend</h2>
            <canvas id="scoresChart" height="200"></canvas>
        </div>
    </div>

    <!-- Recent Observations -->
    <div class="bg-white rounded-xl shadow-sm glass-card p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-dark">Recent Observations</h2>
            <span class="text-sm text-dark/60">{{ $recentObservations->count() }} total</span>
        </div>
        @if($recentObservations->count() > 0)
            <div class="space-y-4">
                @foreach($recentObservations as $observation)
                    <div class="flex items-center justify-between p-4 rounded-lg bg-white/5 hover:bg-white/10">
                        <div>
                            <p class="text-sm font-medium text-dark">{{ $observation->observee->user->name ?? 'Unknown' }}</p>
                            <p class="text-xs text-dark/60">{{ $observation->observation_date->format('M d, Y') }} - {{ ucfirst(str_replace('-', ' ', $observation->stage)) }}</p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                            {{ $observation->status === 'completed' ? 'bg-green-500/20 text-green-300' : 'bg-yellow-500/20 text-yellow-300' }}">
                            {{ ucfirst($observation->status) }}
                        </span>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <p class="text-dark/60">No recent observations to display.</p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Observations trend chart
    const obsCtx = document.getElementById('observationsChart');
    if (obsCtx) {
        new Chart(obsCtx, {
            type: 'bar',
            data: {
                labels: @json($chartData->keys()),
                datasets: [{
                    label: 'Observations',
                    data: @json($chartData->values()),
                    backgroundColor: 'rgba(59, 130, 246, 0.5)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    }

    // Scores trend chart
    const scoresCtx = document.getElementById('scoresChart');
    if (scoresCtx) {
        new Chart(scoresCtx, {
            type: 'line',
            data: {
                labels: @json($scoresData->pluck('observation_date')->map(fn($d) => $d?->format('M d'))),
                datasets: [{
                    label: 'Avg Score',
                    data: @json($scoresData->pluck('overall_score')),
                    borderColor: 'rgba(16, 185, 129, 1)',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        min: 0,
                        max: 5,
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    }
});
</script>
@endpush
@endsection
