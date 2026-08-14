@extends('layouts.teacher')

@section('title', 'Lesson Plan')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6">
    <div class="mb-8">
        <a href="{{ route('school-head.lesson-plans.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:text-gray-300 transition-colors mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Lesson Plans
        </a>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 mb-6">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-12 h-12 rounded-full bg-amber-100 dark:bg-amber-900/30 dark:bg-amber-900/30 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $observation->subject ?? 'Lesson Plan' }}</h1>
                <p class="text-gray-500 dark:text-gray-400">Submitted by <span class="font-medium text-gray-700 dark:text-gray-300">{{ $observation->observee?->user?->name ?? 'Unknown' }}</span></p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
            <div>
                <p class="text-xs text-gray-400 dark:text-gray-500 uppercase tracking-wider font-medium">Teacher</p>
                <p class="text-sm text-gray-900 dark:text-gray-100 mt-1">{{ $observation->observee?->user?->name ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 dark:text-gray-500 uppercase tracking-wider font-medium">Supervisor</p>
                <p class="text-sm text-gray-900 dark:text-gray-100 mt-1">{{ $observation->observer?->name ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 dark:text-gray-500 uppercase tracking-wider font-medium">Date</p>
                <p class="text-sm text-gray-900 dark:text-gray-100 mt-1">{{ $observation->observation_date?->format('M d, Y') ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 dark:text-gray-500 uppercase tracking-wider font-medium">Status</p>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium mt-1
                    {{ $observation->status === 'completed' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : ($observation->status === 'scheduled' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : 'bg-amber-100 dark:bg-amber-900/30 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400') }}">
                    {{ ucwords(str_replace('_', ' ', $observation->status)) }}
                </span>
            </div>
        </div>

        @if($plan->lesson_plan_file)
        <div class="border-t border-gray-100 dark:border-gray-700 pt-6">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Uploaded File</h3>
            <a href="{{ asset('storage/' . $plan->lesson_plan_file) }}" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Download Lesson Plan
            </a>
        </div>
        @endif

        @if($plan->ai_insights)
        <div class="border-t border-gray-100 dark:border-gray-700 pt-6 mt-6">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">AI Insights</h3>
            <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-xl p-4">
                <p class="text-sm text-indigo-800 dark:text-indigo-300">{{ is_string($plan->ai_insights) ? $plan->ai_insights : json_encode($plan->ai_insights, JSON_PRETTY_PRINT) }}</p>
            </div>
        </div>
        @endif

        @if($plan->supervisor_notes)
        <div class="border-t border-gray-100 dark:border-gray-700 pt-6 mt-6">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Supervisor Notes</h3>
            <p class="text-sm text-gray-700 dark:text-gray-300">{{ $plan->supervisor_notes }}</p>
        </div>
        @endif
    </div>
</div>
@endsection
