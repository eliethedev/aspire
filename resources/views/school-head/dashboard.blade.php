@extends('layouts.teacher')

@section('title', 'School Head Dashboard')

@section('content')
@php
    $user = Auth::user();
    $schoolName = $user->schoolHeadProfile?->school?->name ?? 'Your School';
    $completionRate = $stats['total'] > 0 ? round(($stats['completed'] / $stats['total']) * 100) : 0;
    $pendingActions = $stats['pending_confirmation'] ?? 0;
@endphp

<div class="max-w-7xl mx-auto space-y-6">

    <!-- Welcome Section -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center shrink-0">
                    <i class="fas fa-school"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Welcome back, {{ $user->name }}!</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $schoolName }} &middot; {{ now()->format('l, F j, Y') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                    <i class="fas fa-school"></i>
                    School Head
                </span>
                @if($pendingActions > 0)
                <a href="{{ route('school-head.observations.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700 hover:bg-amber-100 transition-colors">
                    <i class="fas fa-clock text-sm"></i>
                    {{ $pendingActions }} pending confirmation{{ $pendingActions > 1 ? 's' : '' }}
                </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Observations -->
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Observations</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ $stats['total'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center shrink-0">
                    <i class="fas fa-clipboard-list text-sm"></i>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-2 text-xs">
                <span class="text-gray-500 dark:text-gray-400">{{ $stats['completed'] }} completed</span>
                <span class="text-gray-300 dark:text-gray-600">&middot;</span>
                <span class="text-gray-500 dark:text-gray-400">{{ $stats['in_progress'] ?? 0 }} in progress</span>
            </div>
        </div>

        <!-- Average Score -->
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Avg Score</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ number_format($avgScore, 1) }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-green-50 dark:bg-green-900/30 flex items-center justify-center shrink-0">
                    <i class="fas fa-circle text-xs text-gray-400"></i>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-xs">
                @if($trend > 0)
                    <i class="fas fa-circle text-xs text-gray-400"></i>
                    <span class="text-green-600 dark:text-green-400 font-medium">+{{ number_format($trend, 1) }}</span>
                    <span class="text-gray-400 dark:text-gray-500">from previous</span>
                @elseif($trend < 0)
                    <i class="fas fa-circle text-xs text-gray-400"></i>
                    <span class="text-red-600 dark:text-red-400 font-medium">{{ number_format($trend, 1) }}</span>
                    <span class="text-gray-400 dark:text-gray-500">from previous</span>
                @else
                    <span class="text-gray-400 dark:text-gray-500">No trend data yet</span>
                @endif
            </div>
        </div>

        <!-- Completed -->
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Completed</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ $stats['completed'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center shrink-0">
                    <i class="fas fa-check-circle text-sm"></i>
                </div>
            </div>
            <div class="mt-3">
                <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5">
                    <div class="bg-emerald-500 h-1.5 rounded-full progress-bar-fill" style="width: {{ $completionRate }}%"></div>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $completionRate }}% completion rate</p>
            </div>
        </div>

        <!-- School Teachers -->
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">School Teachers</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-gray-100 mt-1">{{ $teacherCount }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-sky-50 dark:bg-sky-900/30 flex items-center justify-center shrink-0">
                    <i class="fas fa-circle text-xs text-gray-400"></i>
                </div>
            </div>
            <div class="mt-3 flex items-center gap-1.5 text-xs">
                <a href="{{ route('school-head.teachers.index') }}" class="text-indigo-600 hover:text-indigo-800 font-medium">View all &rarr;</a>
            </div>
        </div>
    </div>

    <!-- Upcoming Observation -->
    @if($nextObservation)
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-900/40 flex items-center justify-center shrink-0">
                <i class="fas fa-circle text-xs text-gray-400"></i>
            </div>
            <div class="flex-1">
                <div class="flex items-center gap-2 flex-wrap">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Upcoming Leadership Observation</h3>
                    @if($nextObservation->canConfirm())
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-amber-100 text-amber-700">Awaiting Confirmation</span>
                    @elseif($nextObservation->confirmation_status === 'confirmed')
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-green-100 text-green-700">Confirmed</span>
                    @endif
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Scheduled for <strong>{{ $nextObservation->observation_date?->format('M d, Y \a\t h:i A') ?? 'No date' }}</strong>
                    @if($nextObservation->subject) &middot; {{ $nextObservation->subject }} @endif
                </p>
                @if($nextObservation->observer)
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Observed by {{ $nextObservation->observer->name }}</p>
                @endif
                <div class="flex items-center gap-3 mt-4">
                    <a href="{{ route('school-head.observations.show', $nextObservation) }}"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
                        View Details
                        <i class="fas fa-circle text-xs text-gray-400"></i>
                    </a>
                    <a href="{{ route('school-head.observations.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">All Observations &rarr;</a>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Main Grid: COT Chart + Teachers Overview -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- COT Score Trend -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider">COT Score Trend</h2>
                @if(count($cotScores) > 0)
                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ count($cotScores) }} observation{{ count($cotScores) > 1 ? 's' : '' }}</span>
                @endif
            </div>
            @if(count($cotScores) > 0)
                <div class="max-h-56">
                    <canvas id="cotScoreChart"></canvas>
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <div class="w-14 h-14 rounded-full bg-gray-50 dark:bg-gray-800 flex items-center justify-center mb-3">
                        <i class="fas fa-circle text-xs text-gray-400"></i>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">No completed observations yet</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">COT scores will appear here once observations are completed.</p>
                </div>
            @endif
        </div>

        <!-- Teachers Overview -->
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider">Teachers</h2>
                <a href="{{ route('school-head.teachers.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">View All &rarr;</a>
            </div>
            @if($schoolTeachers->isNotEmpty())
                <div class="space-y-3">
                    @foreach($schoolTeachers->take(5) as $teacher)
                    <a href="{{ route('school-head.teachers.show', $teacher) }}" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors group">
                        <div class="w-9 h-9 rounded-full bg-indigo-100 dark:bg-indigo-900/40 flex items-center justify-center shrink-0">
                            <span class="text-indigo-600 dark:text-indigo-400 font-semibold text-xs">{{ strtoupper(substr($teacher->user->name, 0, 1)) }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ $teacher->user->name }}</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500">{{ $teacher->department ?? $teacher->subject ?? 'N/A' }}</p>
                        </div>
                        <span class="text-xs text-gray-400 dark:text-gray-500 shrink-0">{{ $teacher->observations_count ?? 0 }} obs</span>
                    </a>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center py-8 text-center">
                    <div class="w-10 h-10 rounded-full bg-gray-50 dark:bg-gray-800 flex items-center justify-center mb-2">
                        <i class="fas fa-circle text-xs text-gray-400"></i>
                    </div>
                    <p class="text-sm text-gray-400 dark:text-gray-500">No teachers assigned yet.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Bottom Row: Recent Observations + AI Feedback + Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Observations -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider">Recent Observations</h2>
                @if(isset($recentObservations) && $recentObservations->count() > 0)
                    <a href="{{ route('school-head.observations.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">View All &rarr;</a>
                @endif
            </div>
            @if(isset($recentObservations) && $recentObservations->count() > 0)
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($recentObservations as $observation)
                    <div class="flex items-center justify-between py-3 first:pt-0 last:pb-0">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-indigo-50 dark:bg-indigo-900/40 flex items-center justify-center shrink-0">
                                <i class="fas fa-circle text-xs text-gray-400"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $observation->observer?->name ?? 'Unknown' }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $observation->observation_date?->format('M d, Y') ?? 'No date' }} &middot; {{ $observation->subject ?? 'Leadership Observation' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            @if($observation->overall_score)
                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ number_format($observation->overall_score, 1) }}</span>
                            @endif
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium
                                {{ $observation->status === 'completed' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : ($observation->status === 'scheduled' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : ($observation->status === 'cancelled' ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400' : 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400')) }}">
                                {{ ucwords(str_replace('_', ' ', $observation->status)) }}
                            </span>
                            <a href="{{ route('school-head.observations.show', $observation) }}" class="text-xs text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">View</a>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center py-12 text-center">
                    <div class="w-14 h-14 rounded-full bg-gray-50 dark:bg-gray-800 flex items-center justify-center mb-3">
                        <i class="fas fa-circle text-xs text-gray-400"></i>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">No observations recorded yet.</p>
                </div>
            @endif
        </div>

        <!-- AI Feedback & Quick Actions -->
        <div class="space-y-6">
            <!-- AI Feedback -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
                <div class="flex items-center gap-2 mb-4">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider">Latest Feedback</h2>
                </div>
                @if($latestFeedbacks->isNotEmpty())
                    <div class="space-y-3">
                        @foreach($latestFeedbacks as $fb)
                        <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">{{ $fb->feedbackTypeLabel() }}</span>
                                <span class="text-[10px] text-gray-400 dark:text-gray-500">{{ $fb->created_at->format('M d, Y') }}</span>
                            </div>
                            @if($fb->content)
                            <p class="text-xs text-gray-600 dark:text-gray-400 line-clamp-2">{{ Str::limit($fb->content, 120) }}</p>
                            @endif
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex flex-col items-center py-6 text-center">
                        <div class="w-10 h-10 rounded-full bg-gray-50 dark:bg-gray-800 flex items-center justify-center mb-2">
                            <i class="fas fa-circle text-xs text-gray-400"></i>
                        </div>
                        <p class="text-xs text-gray-400 dark:text-gray-500">No feedback available yet.</p>
                    </div>
                @endif
            </div>

            <!-- Quick Actions -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
                <div class="flex items-center gap-2 mb-4">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider">Quick Actions</h2>
                </div>
                <div class="space-y-2">
                    <a href="{{ route('school-head.observations.index') }}" class="flex items-center gap-3 p-3 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 hover:bg-indigo-100 dark:hover:bg-indigo-900/30 transition-colors">
                        <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-800/40 flex items-center justify-center shrink-0">
                            <i class="fas fa-circle text-xs text-gray-400"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-indigo-700 dark:text-indigo-300">My Observations</p>
                            <p class="text-xs text-indigo-500 dark:text-indigo-400">View history & details</p>
                        </div>
                    </a>
                    <a href="{{ route('school-head.teachers.index') }}" class="flex items-center gap-3 p-3 rounded-lg bg-sky-50 dark:bg-sky-900/20 hover:bg-sky-100 dark:hover:bg-sky-900/30 transition-colors">
                        <div class="w-8 h-8 rounded-lg bg-sky-100 dark:bg-sky-800/40 flex items-center justify-center shrink-0">
                            <i class="fas fa-circle text-xs text-gray-400"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-sky-700 dark:text-sky-300">Teachers</p>
                            <p class="text-xs text-sky-500 dark:text-sky-400">Manage your faculty</p>
                        </div>
                    </a>
                    <a href="{{ route('school-head.reports.index') }}" class="flex items-center gap-3 p-3 rounded-lg bg-purple-50 dark:bg-purple-900/20 hover:bg-purple-100 dark:hover:bg-purple-900/30 transition-colors">
                        <div class="w-8 h-8 rounded-lg bg-purple-100 dark:bg-purple-800/40 flex items-center justify-center shrink-0">
                            <i class="fas fa-circle text-xs text-gray-400"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-purple-700 dark:text-purple-300">Analytics & Reports</p>
                            <p class="text-xs text-purple-500 dark:text-purple-400">School-wide insights</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>

@if(count($cotScores) > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const isDark = document.documentElement.classList.contains('dark');
    const ctx = document.getElementById('cotScoreChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($cotLabels) !!},
            datasets: [{
                label: 'COT Score',
                data: {!! json_encode($cotScores) !!},
                borderColor: '#4f46e5',
                backgroundColor: 'rgba(79, 70, 229, 0.08)',
                borderWidth: 2,
                tension: 0.35,
                fill: true,
                pointBackgroundColor: '#4f46e5',
                pointBorderColor: isDark ? '#1f2937' : '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            aspectRatio: 2.5,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: false,
                    min: 1,
                    max: 7,
                    ticks: { stepSize: 1, color: isDark ? '#9ca3af' : '#6b7280', font: { size: 10 } },
                    grid: { color: isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.05)' }
                },
                x: {
                    ticks: { color: isDark ? '#9ca3af' : '#6b7280', font: { size: 10 }, maxRotation: 45 },
                    grid: { display: false }
                }
            }
        }
    });
</script>
@endif
@endsection
