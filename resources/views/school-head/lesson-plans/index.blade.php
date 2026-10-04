@extends('layouts.teacher')

@section('title', 'Lesson Plans')

@include('partials.dashboard.mock-styles')

@section('content')
<div class="mock-wrap max-w-7xl mx-auto px-1 py-1">
    <div class="mock-topbar">
        <div class="mock-crumbs">School Head <span>/</span> <b>Lesson Plans</b></div>
        <span class="mock-pill"><span class="pulse"></span>{{ $lessonPlans->total() }} submitted plans</span>
    </div>

    <div class="mock-title">
        <div>
            <h1>Lesson Plans</h1>
            <p>Browse lesson plans submitted by teachers</p>
        </div>
        <time>{{ now()->format('l, F j, Y') }}</time>
    </div>

    <div class="mock-panel bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5 mb-6">
        <form method="GET" action="{{ route('school-head.lesson-plans.index') }}">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Search</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" name="search" value="{{ request('search') }}"
                               class="w-full pl-9 pr-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                               placeholder="Search by subject or teacher...">
                    </div>
                </div>
                <button type="submit"
                        class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
                    Search
                </button>
                @if(request('search'))
                    <a href="{{ route('school-head.lesson-plans.index') }}" class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">Clear</a>
                @endif
            </div>
        </form>
    </div>

    @if($lessonPlans->total() > 0)
    <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Showing <span class="font-medium text-gray-700 dark:text-gray-300">{{ $lessonPlans->firstItem() }}</span>
            to <span class="font-medium text-gray-700 dark:text-gray-300">{{ $lessonPlans->lastItem() }}</span>
            of <span class="font-medium text-gray-700 dark:text-gray-300">{{ $lessonPlans->total() }}</span> lesson plans
        </p>
    </div>
    @endif

    @forelse($lessonPlans as $plan)
    @php
        $observation = $plan->observation;
        $teacher = $observation?->observee;
        $statusColor = match ($observation?->status) {
            'completed' => 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
            'in_progress' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
            'scheduled' => 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
            default => 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400',
        };
    @endphp
    <div class="mock-panel bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5 mb-4 hover:shadow-md transition-shadow">
        <div class="flex items-center gap-4">
            <div class="w-11 h-11 rounded-full bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ $observation?->subject ?? 'Observation' }}</h3>
                    @if($observation?->status)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $statusColor }}">
                        {{ ucwords(str_replace('_', ' ', $observation->status)) }}
                    </span>
                    @endif
                    @if($plan->ai_insights_reviewed)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300">
                        AI Reviewed
                    </span>
                    @elseif($plan->ai_insights)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300">
                        AI Insights
                    </span>
                    @endif
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">by {{ $teacher?->user?->name ?? 'Unknown' }} · {{ $observation?->observation_date?->format('M d, Y') ?? 'No date' }}</p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5 truncate" title="{{ preg_replace('/^\d+_/', '', basename($plan->lesson_plan_file ?? '')) }}">
                    {{ preg_replace('/^\d+_/', '', basename($plan->lesson_plan_file ?? '')) }}
                </p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('school-head.lesson-plans.show', $plan->id) }}"
                   class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
                    View Plan
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="mock-panel bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
        <x-empty-state title="No lesson plans" hint="No lesson plans have been submitted by teachers yet.">
            <x-slot:icon>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </x-slot:icon>
        </x-empty-state>
    </div>
    @endforelse

    @if($lessonPlans->hasPages())
        <div class="mt-8">{{ $lessonPlans->links() }}</div>
    @endif
</div>
@endsection
