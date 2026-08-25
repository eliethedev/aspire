<!-- Overview tab -->
<div class="space-y-6">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-blue-100 dark:bg-blue-900/30 shrink-0">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <div class="ml-4 min-w-0">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Teachers</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['total_teachers'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-purple-100 dark:bg-purple-900/30 shrink-0">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div class="ml-4 min-w-0">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Observations</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['total_observations'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-green-100 dark:bg-green-900/30 shrink-0">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="ml-4 min-w-0">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Completed</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['completed_observations'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-4">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-amber-100 dark:bg-amber-900/30 shrink-0">
                    <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="ml-4 min-w-0">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">In Progress</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['in_progress_observations'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4">Observations Trend</h2>
            <canvas id="observationsChart" height="200"></canvas>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4">Average Score Trend</h2>
            <canvas id="scoresChart" height="200"></canvas>
        </div>
    </div>

    <!-- Recent Observations -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">Recent Observations</h2>
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $recentObservations->count() }} shown</span>
        </div>
        @if($recentObservations->count() > 0)
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($recentObservations as $observation)
                    <a href="{{ route('supervisor.observations.show', $observation) }}"
                       class="flex items-center justify-between gap-3 py-3 px-3 -mx-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ $observation->observee?->user?->name ?? 'Unknown' }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $observation->observation_date?->format('M d, Y') ?? 'No date' }} &middot; {{ ucfirst(str_replace('_', ' ', $observation->stage)) }}
                                @if($observation->overall_score !== null) &middot; <span class="font-semibold">{{ number_format($observation->overall_score, 2) }}/6</span>@endif
                            </p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium shrink-0
                            {{ $observation->status === 'completed' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : ($observation->status === 'cancelled' ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400' : 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400') }}">
                            {{ ucfirst($observation->status) }}
                        </span>
                    </a>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <p class="text-sm text-gray-500 dark:text-gray-400">No recent observations to display.</p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const isDark = document.documentElement.classList.contains('dark');
    Chart.defaults.color = isDark ? '#9ca3af' : '#6b7280';
    Chart.defaults.borderColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)';

    const obsCtx = document.getElementById('observationsChart');
    if (obsCtx) {
        new Chart(obsCtx, {
            type: 'bar',
            data: {
                labels: @json($chartData->keys()),
                datasets: [{
                    label: 'Observations',
                    data: @json($chartData->values()),
                    backgroundColor: 'rgba(99, 102, 241, 0.5)',
                    borderColor: 'rgba(99, 102, 241, 1)',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    }

    const scoresCtx = document.getElementById('scoresChart');
    if (scoresCtx) {
        new Chart(scoresCtx, {
            type: 'line',
            data: {
                labels: @json($scoresData->pluck('observation_date')->map(fn ($d) => $d?->format('M d'))),
                datasets: [{
                    label: 'Avg Score',
                    data: @json($scoresData->pluck('overall_score')),
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { min: 0, max: 6, ticks: { stepSize: 1 } } }
            }
        });
    }
});
</script>
@endpush
