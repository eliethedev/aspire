@extends('layouts.admin')

@section('title', 'System Reports')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">System Reports</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Platform-wide analytics and performance insights across all schools.</p>
        </div>
    </div>

    <!-- Top Stat Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $totalSchools }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Schools</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $totalTeachers }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Teachers</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $totalObservations }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Observations</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-50 dark:bg-green-900/20 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $completedObservations }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Completed</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $avgCotScore ? number_format($avgCotScore, 1) : '--' }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Avg COT Score</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-purple-50 dark:bg-purple-900/20 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $activeAgreements }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Active Coaching</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Monthly Trend -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider mb-4">Monthly Observation Trend</h2>
            <div class="h-64">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>

        <!-- Status Breakdown -->
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider mb-4">Status Breakdown</h2>
            <div class="h-64 flex items-center justify-center">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>

    <!-- School Performance + Domain Averages -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- School Performance -->
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider mb-4">School Performance</h2>
            @if($schoolStats->count() > 0)
                <div class="space-y-3">
                    @foreach($schoolStats->take(8) as $school)
                        <div>
                            <div class="flex items-center justify-between text-sm mb-1">
                                <span class="font-medium text-gray-700 dark:text-gray-300 truncate max-w-[200px]">{{ $school['name'] }}</span>
                                <span class="text-gray-500 dark:text-gray-400 text-xs">{{ $school['observations_count'] }} obs</span>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-2">
                                @php
                                    $maxObs = $schoolStats->max('observations_count') ?: 1;
                                    $width = ($school['observations_count'] / $maxObs) * 100;
                                @endphp
                                <div class="bg-indigo-500 h-2 rounded-full transition-all" style="width: {{ $width }}%"></div>
                            </div>
                            <div class="flex items-center gap-3 mt-1 text-xs text-gray-400 dark:text-gray-500">
                                <span>{{ $school['teachers_count'] }} teachers</span>
                                <span>{{ $school['completed_count'] }} completed</span>
                                @if($school['avg_score'])
                                    <span class="ml-auto font-medium text-amber-600 dark:text-amber-400">Avg: {{ $school['avg_score'] }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-400 dark:text-gray-500 text-center py-8">No school data available.</p>
            @endif
        </div>

        <!-- COT Domain Averages -->
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider mb-4">COT Domain Averages</h2>
            @if($domainAverages->count() > 0)
                <div class="space-y-4">
                    @foreach($domainAverages as $domain)
                        <div>
                            <div class="flex items-center justify-between text-sm mb-1">
                                <span class="font-medium text-gray-700 dark:text-gray-300">{{ $domain->domain }}</span>
                                <span class="text-gray-500 dark:text-gray-400">{{ number_format($domain->avg_rating, 1) }} <span class="text-xs text-gray-400">({{ $domain->count }} ratings)</span></span>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-2">
                                @php
                                    $pct = ($domain->avg_rating / 6) * 100;
                                @endphp
                                <div class="h-2 rounded-full transition-all {{ $domain->avg_rating >= 4 ? 'bg-green-500' : ($domain->avg_rating >= 3 ? 'bg-amber-500' : 'bg-red-500') }}" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-400 dark:text-gray-500 text-center py-8">No COT rating data available.</p>
            @endif
        </div>
    </div>

    <!-- Top Teachers + Summary -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Top Teachers -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider mb-4">Top Performing Teachers</h2>
            @if($topTeachers->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <th class="text-left px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">#</th>
                                <th class="text-left px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Teacher</th>
                                <th class="text-left px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">School</th>
                                <th class="text-center px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Observations</th>
                                <th class="text-center px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Avg Score</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                            @foreach($topTeachers as $i => $teacher)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                    <td class="px-3 py-2.5 text-gray-400 dark:text-gray-500">{{ $i + 1 }}</td>
                                    <td class="px-3 py-2.5 font-medium text-gray-900 dark:text-gray-100">{{ $teacher['name'] }}</td>
                                    <td class="px-3 py-2.5 text-gray-500 dark:text-gray-400">{{ $teacher['school'] }}</td>
                                    <td class="px-3 py-2.5 text-center text-gray-600 dark:text-gray-300">{{ $teacher['observations'] }}</td>
                                    <td class="px-3 py-2.5 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                                            {{ $teacher['avg_score'] >= 4 ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : ($teacher['avg_score'] >= 3 ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400' : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400') }}">
                                            {{ $teacher['avg_score'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-gray-400 dark:text-gray-500 text-center py-8">No teacher data with 2+ observations yet.</p>
            @endif
        </div>

        <!-- Summary -->
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider mb-4">Platform Summary</h2>
            <div class="space-y-3">
                <div class="flex items-center justify-between py-2 border-b border-gray-50 dark:border-gray-800">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Total Users</span>
                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $totalUsers }}</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-gray-50 dark:border-gray-800">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Supervisors</span>
                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $totalSupervisors }}</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-gray-50 dark:border-gray-800">
                    <span class="text-sm text-gray-600 dark:text-gray-400">School Heads</span>
                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $totalSchoolHeads }}</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-gray-50 dark:border-gray-800">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Completion Rate</span>
                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                        {{ $totalObservations > 0 ? round(($completedObservations / $totalObservations) * 100) : 0 }}%
                    </span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-gray-50 dark:border-gray-800">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Avg Obs per Teacher</span>
                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                        {{ $totalTeachers > 0 ? round($totalObservations / $totalTeachers, 1) : 0 }}
                    </span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-gray-50 dark:border-gray-800">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Coaching Completion</span>
                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                        {{ $totalAgreements > 0 ? round(($activeAgreements / $totalAgreements) * 100) : 0 }}% active
                    </span>
                </div>
                <div class="flex items-center justify-between py-2">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Avg Overall Score</span>
                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                        {{ $avgOverallScore ? number_format($avgOverallScore, 2) : '--' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider">Recent Observations</h2>
            <a href="{{ route('admin.observations.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 font-medium">View All &rarr;</a>
        </div>
        @if($recentObservations->count() > 0)
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($recentObservations as $obs)
                    <div class="flex items-center justify-between py-3 first:pt-0 last:pb-0">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $obs->observee?->user?->name ?? 'Unknown' }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $obs->observation_date?->format('M d, Y') ?? 'No date' }} &middot; {{ $obs->subject ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            @if($obs->overall_score)
                                <span class="text-xs font-semibold text-amber-600 dark:text-amber-400">{{ $obs->overall_score }}</span>
                            @endif
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium
                                {{ $obs->status === 'completed' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : ($obs->status === 'scheduled' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : ($obs->status === 'cancelled' ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400' : 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400')) }}">
                                {{ ucwords(str_replace('_', ' ', $obs->status)) }}
                            </span>
                            <a href="{{ route('admin.observations.show', $obs) }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 font-medium">View</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-gray-400 dark:text-gray-500 text-center py-8">No observations yet.</p>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const isDark = document.documentElement.classList.contains('dark');
    const gridColor = isDark ? 'rgba(75, 85, 99, 0.3)' : 'rgba(0, 0, 0, 0.05)';
    const textColor = isDark ? '#9ca3af' : '#6b7280';

    // Monthly Trend Chart
    const monthlyCtx = document.getElementById('monthlyChart');
    if (monthlyCtx) {
        new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode(array_column($monthlyTrend, 'month')) !!},
                datasets: [
                    {
                        label: 'Total',
                        data: {!! json_encode(array_column($monthlyTrend, 'total')) !!},
                        backgroundColor: isDark ? 'rgba(99, 102, 241, 0.4)' : 'rgba(99, 102, 241, 0.6)',
                        borderColor: '#6366f1',
                        borderWidth: 1,
                        borderRadius: 4
                    },
                    {
                        label: 'Completed',
                        data: {!! json_encode(array_column($monthlyTrend, 'completed')) !!},
                        backgroundColor: isDark ? 'rgba(34, 197, 94, 0.4)' : 'rgba(34, 197, 94, 0.6)',
                        borderColor: '#22c55e',
                        borderWidth: 1,
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { color: textColor, usePointStyle: true, pointStyle: 'circle', padding: 16, font: { size: 11 } }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, color: textColor, font: { size: 10 } },
                        grid: { color: gridColor }
                    },
                    x: {
                        ticks: { color: textColor, font: { size: 10 } },
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // Status Breakdown Chart
    const statusCtx = document.getElementById('statusChart');
    if (statusCtx) {
        const statusData = {!! json_encode($statusBreakdown) !!};
        const statusLabels = Object.keys(statusData).map(s => s.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()));
        const statusValues = Object.values(statusData);
        const statusColors = Object.keys(statusData).map(s => {
            if (s === 'completed') return '#22c55e';
            if (s === 'scheduled') return '#3b82f6';
            if (s === 'in_progress') return '#f59e0b';
            if (s === 'cancelled') return '#ef4444';
            return '#6b7280';
        });

        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusValues,
                    backgroundColor: statusColors,
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: textColor, usePointStyle: true, pointStyle: 'circle', padding: 12, font: { size: 11 } }
                    }
                }
            }
        });
    }
});
</script>
@endpush
@endsection
