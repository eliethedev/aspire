@extends('layouts.teacher')

@section('title', 'Lesson Plan')

@include('partials.dashboard.mock-styles')

@section('content')
<div class="mock-wrap max-w-7xl mx-auto px-1 py-1">
    <div class="mock-topbar">
        <div class="mock-crumbs">School Head <span>/</span> <b>Lesson Plans</b></div>
        <span class="mock-pill"><span class="pulse"></span>Submitted plan</span>
        <div class="mock-actions">
            <a class="mock-btn" href="{{ route('school-head.lesson-plans.index') }}">Back to Lesson Plans</a>
        </div>
    </div>

    <div class="mock-title">
        <div>
            <h1>{{ $observation->subject ?? 'Lesson Plan' }}</h1>
            <p>Submitted by {{ $observation->observee?->user?->name ?? 'Unknown' }}{{ $observation->observation_date ? ' · ' . $observation->observation_date->format('M d, Y') : '' }}</p>
        </div>
        <time>{{ now()->format('l, F j, Y') }}</time>
    </div>

    <div class="mock-panel bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 mb-6">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-12 h-12 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center shrink-0">
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
                    {{ $observation->status === 'completed' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : ($observation->status === 'scheduled' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400') }}">
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
            <div class="flex flex-wrap items-center gap-2 mb-3">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">AI Insights</h3>
                @if($plan->ai_insights_reviewed)
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    AI Reviewed
                </span>
                @endif
            </div>
            <div class="rounded-xl bg-gradient-to-br from-purple-50 dark:from-purple-900/20 to-indigo-50 dark:to-indigo-900/20 border border-purple-100 dark:border-purple-800 p-4">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-2 h-2 rounded-full bg-purple-500"></div>
                    <span class="text-xs font-semibold text-purple-700 dark:text-purple-300 uppercase tracking-wider">AI Lesson Plan Analysis</span>
                </div>
                @php $insightSections = $plan->insightsSections(); @endphp
                @if(isset($insightSections['raw']))
                    <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap leading-relaxed">{{ $insightSections['raw'] }}</p>
                @else
                    {!! view('partials.ai-insights-display', ['sections' => $insightSections])->render() !!}
                @endif
            </div>
        </div>
        @endif

        @if($plan->suggested_focus)
        <div class="border-t border-gray-100 dark:border-gray-700 pt-6 mt-6">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Suggested Focus</h3>
            <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">{{ is_array($plan->suggested_focus) ? implode(', ', $plan->suggested_focus) : $plan->suggested_focus }}</p>
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
