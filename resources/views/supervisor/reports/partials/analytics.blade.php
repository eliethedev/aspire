<!-- Analytics tab -->
<div class="space-y-6">
    <!-- Charts grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-1">Observations per Month</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Last 12 months &middot; cancelled excluded</p>
            <canvas id="monthlyChart" height="210"></canvas>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-1">Average Score per Month</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">COT overall score, 2&ndash;6 scale</p>
            <canvas id="scoreTrendChart" height="210"></canvas>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-1">Rating Distribution</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">All COT indicator ratings given</p>
            <canvas id="distributionChart" height="210"></canvas>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-1">Domain Averages</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Average rating per COT domain</p>
            @if($domainAverages->isNotEmpty())
                <div class="space-y-3 mt-2">
                    @foreach($domainAverages as $domain)
                        <div>
                            <div class="flex items-center justify-between gap-3 mb-1">
                                <p class="text-sm text-gray-700 dark:text-gray-300 truncate">{{ $domain['domain'] }}</p>
                                <span class="text-sm font-semibold text-gray-900 dark:text-gray-100 shrink-0">{{ number_format($domain['average'], 2) }}/6</span>
                            </div>
                            <div class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                @php $width = max(4, min(100, round((($domain['average'] - 1) / 5) * 100))); @endphp
                                <div class="h-full rounded-full transition-all {{ $domain['average'] >= 4.5 ? 'bg-green-500' : ($domain['average'] >= 3.5 ? 'bg-yellow-500' : 'bg-red-500') }}" style="width: {{ $width }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400 py-8 text-center">No COT ratings recorded yet.</p>
            @endif
        </div>
    </div>

    <!-- Strengths / Weaknesses -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4">Strongest Indicators</h2>
            @forelse($strengths as $row)
                <div class="flex items-start justify-between gap-3 py-2 border-b border-gray-100 dark:border-gray-800 last:border-b-0">
                    <div class="min-w-0 flex items-start gap-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 shrink-0">{{ $row['code'] }}</span>
                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $row['indicator'] ?? $row['code'] }}</p>
                    </div>
                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100 shrink-0">{{ number_format($row['average'], 2) }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-500 dark:text-gray-400">Not enough data yet.</p>
            @endforelse
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
            <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4">Indicators Needing Development</h2>
            @forelse($weaknesses as $row)
                <div class="flex items-start justify-between gap-3 py-2 border-b border-gray-100 dark:border-gray-800 last:border-b-0">
                    <div class="min-w-0 flex items-start gap-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 shrink-0">{{ $row['code'] }}</span>
                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $row['indicator'] ?? $row['code'] }}</p>
                    </div>
                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100 shrink-0">{{ number_format($row['average'], 2) }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-500 dark:text-gray-400">No indicators flagged.</p>
            @endforelse
        </div>
    </div>

    <!-- Status breakdown + totals -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-4">Observation Status Breakdown</h2>
        <div class="flex flex-wrap gap-2">
            @forelse($statusCounts as $status => $count)
                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                    {{ ucfirst($status) }}
                    <span class="font-bold text-gray-900 dark:text-gray-100">{{ $count }}</span>
                </span>
            @empty
                <p class="text-sm text-gray-500 dark:text-gray-400">No observations yet.</p>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const isDark = document.documentElement.classList.contains('dark');
    Chart.defaults.color = isDark ? '#9ca3af' : '#6b7280';
    Chart.defaults.borderColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)';

    const monthlyCtx = document.getElementById('monthlyChart');
    if (monthlyCtx) {
        new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: @json($monthlyLabels),
                datasets: [{
                    label: 'Observations',
                    data: @json($monthlyCounts),
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

    const trendCtx = document.getElementById('scoreTrendChart');
    if (trendCtx) {
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: @json($monthlyLabels),
                datasets: [{
                    label: 'Avg Score',
                    data: @json($monthlyAverages),
                    spanGaps: true,
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

    const distCtx = document.getElementById('distributionChart');
    if (distCtx) {
        new Chart(distCtx, {
            type: 'bar',
            data: {
                labels: @json($distribution['labels']),
                datasets: [{
                    label: 'Ratings',
                    data: @json($distribution['counts']),
                    backgroundColor: [
                        'rgba(239, 68, 68, 0.55)',
                        'rgba(249, 115, 22, 0.55)',
                        'rgba(234, 179, 8, 0.55)',
                        'rgba(59, 130, 246, 0.55)',
                        'rgba(16, 185, 129, 0.55)',
                        'rgba(107, 114, 128, 0.45)'
                    ],
                    borderWidth: 0,
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    }
});
</script>
@endpush
