@extends('layouts.teacher')

@section('title', 'School Head Dashboard')

@section('content')
@php
    $schoolName = $user->school?->name ?? ($schoolHead?->school?->name ?? 'Your School');
    $syLabel = $schoolYear ?? (now()->format('Y') . '-' . (now()->year + 1));
    $firstName = explode(' ', trim($user->name ?? ''))[0] ?? 'School Head';
    $cycleLabel = 'Quarter ' . ($quarter ?? 1) . ' · SY ' . $syLabel;
@endphp

<div class="max-w-7xl mx-auto space-y-4">

    <!-- ======================= Welcome ======================= -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-indigo-600 flex items-center justify-center shrink-0 shadow-md">
                    <span class="text-white text-lg font-bold">{{ strtoupper(substr($user->name ?? 'S', 0, 1)) }}</span>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Welcome back, {{ $firstName }}!</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $schoolName }} &middot; {{ now()->format('l, F j, Y') }} &middot; {{ $cycleLabel }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> {{ $quickStats['teacher_count'] }} Teachers
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200">
                    <span class="w-2 h-2 rounded-full bg-indigo-500"></span> {{ $quickStats['total'] }} Observations
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span> Avg {{ number_format($quickStats['avg_score'] ?? 0, 1) }}
                </span>
            </div>
        </div>

        <div class="mt-5 flex flex-wrap items-center gap-2">
            <a href="{{ route('school-head.observations.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition-colors">
                <i class="fas fa-plus text-xs"></i> Schedule observation
            </a>
            <a href="{{ route('school-head.lesson-plans.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 text-sm font-semibold hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                <i class="fas fa-book-open text-gray-500 dark:text-gray-400 text-xs"></i> Review lesson plans
            </a>
            <a href="{{ route('school-head.reports.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 text-sm font-semibold hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                <i class="fas fa-chart-line text-gray-500 dark:text-gray-400 text-xs"></i> View reports
            </a>
        </div>

        @if(($attention['total'] ?? 0) > 0)
        <div class="mt-4 rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50/60 dark:bg-amber-900/10 p-4">
            <div class="flex items-center gap-2 mb-3">
                <i class="fas fa-bell text-amber-500 text-sm"></i>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Needs your attention</h2>
                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $attention['total'] }} waiting</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                @foreach($attention['items'] as $item)
                @if(!empty($item['route']))
                <a href="{{ $item['route'] }}" class="flex items-center justify-between gap-2 rounded-lg bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 px-3 py-2 hover:border-amber-300 dark:hover:border-amber-700 transition-colors">
                    <span class="min-w-0">
                        <span class="block text-xs font-semibold text-gray-800 dark:text-gray-200">{{ $item['label'] }}</span>
                        <span class="block text-[11px] text-gray-500 dark:text-gray-400 truncate">{{ $item['hint'] }}</span>
                    </span>
                    <span class="inline-flex items-center justify-center min-w-7 px-2 py-1 rounded-lg text-sm font-bold bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 shrink-0">{{ $item['count'] }}</span>
                </a>
                @endif
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- ======================= Teachers and their observations ======================= -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
            <div class="flex items-center gap-2">
                <i class="fas fa-users text-sm text-gray-400"></i>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider">Teachers and their observations <span class="text-gray-400 dark:text-gray-500 normal-case">&middot; {{ $cycleLabel }}</span></h2>
            </div>
            <a href="{{ route('school-head.observations.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">All observations &rarr;</a>
        </div>

        @if(count($teacherRows) > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[10px] uppercase tracking-wider text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                        <th class="py-2 pr-3 font-semibold">Teacher</th>
                        <th class="py-2 pr-3 font-semibold">Observations done</th>
                        <th class="py-2 pr-3 font-semibold">Latest COT result</th>
                        <th class="py-2 font-semibold">Remarks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach($teacherRows as $row)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/60 transition-colors">
                        <td class="py-2.5 pr-3">
                            <p class="font-medium text-gray-900 dark:text-gray-100">{{ $row['teacher'] }}</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500">{{ $row['position'] }}</p>
                        </td>
                        <td class="py-2.5 pr-3">
                            @if($row['observations_done'] > 0)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300">{{ $row['observations_done'] }}</span>
                            @else
                                <span class="text-xs text-gray-400">&mdash;</span>
                            @endif
                        </td>
                        <td class="py-2.5 pr-3">
                            @if($row['latest_score'] !== null)
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ number_format($row['latest_score'], 1) }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $row['latest_result'] }}</p>
                            @else
                                <span class="text-xs text-gray-400">&mdash;</span>
                            @endif
                        </td>
                        <td class="py-2.5">
                            @if($row['remark'] === 'On track')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">On track</span>
                            @elseif($row['remark'] === 'For coaching')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400">For coaching</span>
                            @elseif($row['remark'] === 'Needs support')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">Needs support</span>
                            @elseif($row['remark'] === 'Observation scheduled')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400">Observation scheduled</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">Not started</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
            <div class="flex flex-col items-center py-10 text-center">
                <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-3">
                    <i class="fas fa-users text-gray-300 dark:text-gray-600"></i>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">No teachers assigned to your school yet.</p>
            </div>
        @endif

        @if(count($cotTrend) > 0)
        <div class="mt-5 pt-4 border-t border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">COT scores over time</h3>
                <span class="text-xs text-gray-500 dark:text-gray-400">{{ count($cotTrend) }} result{{ count($cotTrend) > 1 ? 's' : '' }}</span>
            </div>
            <div class="max-h-48">
                <canvas id="cotScoreChart"></canvas>
            </div>
        </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <!-- ======================= Recent COT results ======================= -->
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <i class="fas fa-star-half-stroke text-sm text-gray-400"></i>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider">Recent COT results</h2>
                </div>
                <a href="{{ route('school-head.observations.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">Recent &rarr;</a>
            </div>

            @if(count($rubricScoring) > 0)
            <div class="space-y-4">
                @foreach($rubricScoring as $rs)
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                    <div class="flex items-start justify-between gap-2 flex-wrap">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $rs['teacher'] }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $rs['date'] }}@if($rs['subject']) &middot; {{ $rs['subject'] }} @endif</p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-lg font-bold {{ $rs['score'] >= 4.5 ? 'text-emerald-600' : ($rs['score'] >= 3.5 ? 'text-amber-600' : 'text-red-600') }}">{{ number_format($rs['score'], 1) }}<span class="text-xs text-gray-400 font-medium">/{{ $rs['scale_max'] }}</span></p>
                            <p class="text-[10px] font-medium {{ $rs['score'] >= 4.5 ? 'text-emerald-600' : ($rs['score'] >= 3.5 ? 'text-amber-600' : 'text-red-600') }}">{{ $rs['descriptive'] }}</p>
                        </div>
                    </div>

                    <div class="mt-3 space-y-2">
                        @foreach($rs['domains'] as $domain => $avg)
                        <div>
                            <div class="flex items-center justify-between text-[11px] mb-0.5">
                                <span class="text-gray-600 dark:text-gray-300 truncate">{{ $domain }}</span>
                                <span class="font-semibold text-gray-800 dark:text-gray-200">{{ number_format($avg, 1) }}</span>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5">
                                <div class="h-1.5 rounded-full bg-sky-500" style="width: {{ ($avg / $rs['scale_max']) * 100 }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="flex flex-wrap items-center gap-2 mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                        @if($rs['strength'])
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-medium bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400">
                            <i class="fas fa-thumbs-up"></i> Strongest in: {{ $rs['strength'] }}
                        </span>
                        @endif
                        @if($rs['attention'])
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-medium bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400">
                            <i class="fas fa-lightbulb"></i> Needs support in: {{ $rs['attention'] }}
                        </span>
                        @endif
                        <a href="{{ route('school-head.observations.show', $rs['id']) }}" class="ml-auto text-xs text-indigo-600 dark:text-indigo-400 font-medium hover:underline">View &rarr;</a>
                    </div>
                </div>
                @endforeach
            </div>
            @else
                <div class="flex flex-col items-center py-10 text-center">
                    <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-3">
                        <i class="fas fa-star-half-stroke text-gray-300 dark:text-gray-600"></i>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">No finished observations yet.</p>
                </div>
            @endif
        </div>

        <!-- ======================= Coaching and follow-up ======================= -->
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <i class="fas fa-handshake text-sm text-gray-400"></i>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider">Coaching and follow-up</h2>
                </div>
                <a href="{{ route('school-head.coaching.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">All &rarr;</a>
            </div>

            @if(count($coaching['items'] ?? []) > 0)
            <div class="space-y-3">
                @foreach($coaching['items'] as $ag)
                <a href="{{ $ag['route'] }}" class="block rounded-xl border border-gray-200 dark:border-gray-700 p-4 hover:border-violet-300 dark:hover:border-violet-700 transition-colors group">
                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $ag['teacher'] }}</p>
                        @if($ag['status'] === 'active')
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-violet-100 dark:bg-violet-900/30 text-violet-700 dark:text-violet-400">Ongoing</span>
                        @elseif($ag['status'] === 'completed')
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400">Done</span>
                        @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">Draft</span>
                        @endif
                    </div>
                    @if($ag['focus'])
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">Focus: {{ $ag['focus'] }}</p>
                    @endif
                    <div class="flex items-center justify-between mt-2">
                        <p class="text-[11px] text-gray-400 dark:text-gray-500">Mentor: {{ $ag['supervisor'] }}</p>
                        <p class="text-[11px] font-medium {{ $ag['signature_state'] === 'signed' ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                            {{ $ag['signature_state'] === 'signed' ? 'Signed' : 'For signing' }}
                        </p>
                    </div>
                </a>
                @endforeach
            </div>
            @else
                <div class="flex flex-col items-center py-10 text-center">
                    <div class="w-12 h-12 rounded-full bg-violet-50 dark:bg-violet-900/20 flex items-center justify-center mb-3">
                        <i class="fas fa-handshake text-violet-300 dark:text-violet-600"></i>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">No coaching agreements yet.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- ======================= Lesson plans ======================= -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <a href="{{ route('school-head.lesson-plans.index') }}" class="inline-flex items-center gap-2 hover:opacity-80">
                <i class="fas fa-book-open text-sm text-gray-400"></i>
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider">Lesson plans</h2>
            </a>
            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $cycleLabel }}</span>
        </div>

        <div class="grid grid-cols-3 gap-3 mb-4">
            <div class="rounded-xl bg-emerald-50 dark:bg-emerald-900/20 p-3 text-center">
                <p class="text-xl font-bold text-emerald-700 dark:text-emerald-300">{{ $dll['submitted'] }}</p>
                <p class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wide">Submitted</p>
            </div>
            <div class="rounded-xl bg-red-50 dark:bg-red-900/20 p-3 text-center">
                <p class="text-xl font-bold text-red-700 dark:text-red-300">{{ $dll['not_submitted'] }}</p>
                <p class="text-[10px] font-semibold text-red-600 dark:text-red-400 uppercase tracking-wide">Not yet submitted</p>
            </div>
            <div class="rounded-xl bg-amber-50 dark:bg-amber-900/20 p-3 text-center">
                <p class="text-xl font-bold text-amber-700 dark:text-amber-300">{{ $dll['for_checking'] }}</p>
                <p class="text-[10px] font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wide">For checking</p>
            </div>
        </div>

        @if(count($dll['rows'] ?? []) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-2">
                @foreach($dll['rows'] as $row)
                <div class="flex items-center justify-between gap-2 rounded-lg border border-gray-100 dark:border-gray-700 px-3 py-2">
                    <p class="text-xs font-medium text-gray-800 dark:text-gray-200 truncate">{{ $row['teacher'] }}</p>
                    <span class="text-[10px] font-medium px-2 py-0.5 rounded shrink-0
                        {{ $row['status'] === 'submitted' ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400' : ($row['status'] === 'not_submitted' ? 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400' : ($row['status'] === 'for_checking' ? 'bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400')) }}">
                        {{ $row['label'] }}
                    </span>
                </div>
                @endforeach
            </div>
            <a href="{{ route('school-head.lesson-plans.index') }}" class="block text-center text-xs text-indigo-600 dark:text-indigo-400 font-medium mt-4 hover:underline">Review submitted lesson plans &rarr;</a>
        @else
            <p class="text-sm text-gray-400 dark:text-gray-500 py-6 text-center">No lesson plans yet.</p>
        @endif
    </div>

</div>

@if(count($cotTrend) > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const isDark = document.documentElement.classList.contains('dark');
    const ctx = document.getElementById('cotScoreChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($cotLabels),
            datasets: [{
                label: 'COT Score',
                data: @json($cotTrend),
                borderColor: '#0ea5e9',
                backgroundColor: 'rgba(14, 165, 233, 0.08)',
                borderWidth: 2,
                tension: 0.35,
                fill: true,
                pointBackgroundColor: '#0ea5e9',
                pointBorderColor: isDark ? '#1f2937' : '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            aspectRatio: 3,
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