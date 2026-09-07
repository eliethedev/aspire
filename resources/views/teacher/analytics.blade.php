@extends('layouts.teacher')
@section('title','Performance Analytics')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200/70 dark:border-gray-800 p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <p class="text-blue-600 text-xs tracking-widest uppercase font-semibold">Analytics</p>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Performance Analytics</h1>
                <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Insights from your classroom observations over the last 12 months.</p>
            </div>
            <a href="{{ route('teacher.dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold shrink-0">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Stat cards -->
    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-900 rounded-2xl border p-5 hover:shadow-md transition">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-[11px] tracking-widest uppercase font-semibold text-gray-500 dark:text-gray-400">Total Observations</p>
                    <p class="text-3xl font-extrabold mt-1 text-gray-900 dark:text-white">{{ $stats['total'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Cancelled excluded</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400"><i class="fas fa-clipboard-list"></i></div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-2xl border p-5 hover:shadow-md transition">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-[11px] tracking-widest uppercase font-semibold text-gray-500 dark:text-gray-400">Completed</p>
                    <p class="text-3xl font-extrabold mt-1 text-gray-900 dark:text-white">{{ $stats['completed'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Full observation cycles</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-2xl border p-5 hover:shadow-md transition">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-[11px] tracking-widest uppercase font-semibold text-gray-500 dark:text-gray-400">Average COT</p>
                    <p class="text-3xl font-extrabold mt-1 text-gray-900 dark:text-white">{{ number_format($stats['average_score'], 2) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Out of 6.00</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400"><i class="fas fa-chart-column"></i></div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-2xl border p-5 hover:shadow-md transition">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-[11px] tracking-widest uppercase font-semibold text-gray-500 dark:text-gray-400">Best Score</p>
                    <p class="text-3xl font-extrabold mt-1 text-gray-900 dark:text-white">{{ $stats['best_score'] !== null ? number_format($stats['best_score'], 2) : '-' }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Your highest so far</p>
                </div>
                <div class="w-11 h-11 rounded-xl bg-amber-50 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 dark:text-amber-400"><i class="fas fa-star"></i></div>
            </div>
        </div>
    </div>

    <!-- Charts grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-900 rounded-2xl border p-6">
            <h2 class="text-xs font-bold tracking-widest uppercase flex items-center gap-2 mb-1"><span class="w-1.5 h-5 bg-blue-600 rounded-full"></span> Observations per Month</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4 ml-3">Last 12 months</p>
            <canvas id="monthlyChart" height="210"></canvas>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-2xl border p-6">
            <h2 class="text-xs font-bold tracking-widest uppercase flex items-center gap-2 mb-1"><span class="w-1.5 h-5 bg-blue-600 rounded-full"></span> Average Score per Month</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4 ml-3">COT overall score, 2&ndash;6 scale</p>
            <canvas id="scoreTrendChart" height="210"></canvas>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-2xl border p-6">
            <h2 class="text-xs font-bold tracking-widest uppercase flex items-center gap-2 mb-1"><span class="w-1.5 h-5 bg-blue-600 rounded-full"></span> Rating Distribution</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4 ml-3">All COT indicator ratings you received</p>
            <canvas id="distributionChart" height="210"></canvas>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-2xl border p-6">
            <h2 class="text-xs font-bold tracking-widest uppercase flex items-center gap-2 mb-1"><span class="w-1.5 h-5 bg-blue-600 rounded-full"></span> Domain Averages</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4 ml-3">Average rating per COT domain</p>
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
                                <div class="h-full rounded-full transition-all {{ $domain['average'] >= 4.5 ? 'bg-emerald-500' : ($domain['average'] >= 3.5 ? 'bg-amber-500' : 'bg-red-500') }}" style="width: {{ $width }}%"></div>
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
        <div class="bg-white dark:bg-gray-900 rounded-2xl border p-6">
            <h2 class="text-xs font-bold tracking-widest uppercase flex items-center gap-2 mb-4"><span class="w-1.5 h-5 bg-emerald-500 rounded-full"></span> <i class="fas fa-thumbs-up text-emerald-600"></i> Your Strengths</h2>
            @forelse($strengths as $row)
                <div class="flex items-start justify-between gap-3 py-2 border-b border-gray-100 dark:border-gray-800 last:border-b-0">
                    <div class="min-w-0 flex items-start gap-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 shrink-0">{{ $row['code'] }}</span>
                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $row['indicator'] ?? $row['code'] }}</p>
                    </div>
                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100 shrink-0">{{ number_format($row['average'], 2) }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-500 dark:text-gray-400">Not enough data yet &mdash; keep getting observed!</p>
            @endforelse
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-2xl border p-6">
            <h2 class="text-xs font-bold tracking-widest uppercase flex items-center gap-2 mb-4"><span class="w-1.5 h-5 bg-red-500 rounded-full"></span> <i class="fas fa-seedling text-red-500"></i> Growth Opportunities</h2>
            @forelse($weaknesses as $row)
                <div class="flex items-start justify-between gap-3 py-2 border-b border-gray-100 dark:border-gray-800 last:border-b-0">
                    <div class="min-w-0 flex items-start gap-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 shrink-0">{{ $row['code'] }}</span>
                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $row['indicator'] ?? $row['code'] }}</p>
                    </div>
                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100 shrink-0">{{ number_format($row['average'], 2) }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-500 dark:text-gray-400">No indicators flagged &mdash; great work!</p>
            @endforelse
        </div>
    </div>

    <!-- Status breakdown -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl border p-6">
        <h2 class="text-xs font-bold tracking-widest uppercase flex items-center gap-2 mb-4"><span class="w-1.5 h-5 bg-blue-600 rounded-full"></span> Observation Status Breakdown</h2>
        <div class="flex flex-wrap gap-2">
            @forelse($statusCounts as $status => $count)
                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                    {{ ucfirst(str_replace('_', ' ', $status)) }}
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
    Chart.defaults.font.family = 'Figtree, ui-sans-serif, system-ui, sans-serif';

    const monthlyCtx = document.getElementById('monthlyChart');
    if (monthlyCtx) {
        new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: @json($monthlyLabels),
                datasets: [{
                    label: 'Observations',
                    data: @json($monthlyCounts),
                    backgroundColor: 'rgba(37, 99, 235, 0.5)',
                    borderColor: 'rgba(37, 99, 235, 1)',
                    borderWidth: 1,
                    borderRadius: 6
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
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37, 99, 235, 0.08)',
                    fill: true,
                    tension: 0.38,
                    pointRadius: 4,
                    borderWidth: 2
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
                    borderRadius: 6
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
@endsection
