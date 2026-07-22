@extends('layouts.teacher')

@section('title', 'Analytics & Reports')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Analytics & Reports</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">School-wide performance data and insights.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
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
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $totalObservations }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total Observations</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
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
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-purple-50 dark:bg-purple-900/20 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $avgScore ? number_format($avgScore, 2) : '--' }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Avg Score</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider mb-4">Quarterly Observations</h2>
            <div class="space-y-3">
                @foreach($quarterlyData as $data)
                <div>
                    <div class="flex items-center justify-between text-sm mb-1">
                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ $data['quarter'] }}</span>
                        <span class="text-gray-500 dark:text-gray-400">{{ $data['count'] }} observations</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        @php
                            $maxCount = max(array_column($quarterlyData, 'count')) ?: 1;
                            $width = ($data['count'] / $maxCount) * 100;
                        @endphp
                        <div class="bg-indigo-50 dark:bg-indigo-900/200 h-2 rounded-full transition-all" style="width: {{ $width }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider mb-4">Summary</h2>
            <div class="space-y-4">
                <div class="flex items-center justify-between py-2 border-b border-gray-50">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Completion Rate</span>
                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                        {{ $totalObservations > 0 ? round(($completedObservations / $totalObservations) * 100) : 0 }}%
                    </span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-gray-50">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Active Coaching Agreements</span>
                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $activeAgreements }}</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-gray-50">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Avg Observations per Teacher</span>
                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                        {{ $totalTeachers > 0 ? round($totalObservations / $totalTeachers, 1) : 0 }}
                    </span>
                </div>
                <div class="flex items-center justify-between py-2">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Current Year</span>
                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ now()->year }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider">Recent Observations</h2>
        </div>
        @forelse($recentObservations as $observation)
        <div class="flex items-center justify-between py-3 border-b border-gray-50 last:border-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $observation->observee?->user?->name ?? 'Unknown' }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $observation->observation_date?->format('M d, Y') ?? 'No date' }}</p>
                </div>
            </div>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium
                {{ $observation->status === 'completed' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : ($observation->status === 'scheduled' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400') }}">
                {{ ucwords(str_replace('_', ' ', $observation->status)) }}
            </span>
        </div>
        @empty
        <div class="text-center py-8">
            <p class="text-sm text-gray-400 dark:text-gray-500">No observations recorded yet.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
