@extends('layouts.teacher')

@section('title', 'Lesson Plans')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6">
    <x-page-header title="Lesson Plans" subtitle="Browse lesson plans submitted by teachers." />

    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5 mb-6">
        <form method="GET" action="{{ route('school-head.lesson-plans.index') }}">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Search</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" name="search" value="{{ request('search') }}"
                               class="w-full pl-9 pr-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                               placeholder="Search by subject...">
                    </div>
                </div>
                <button type="submit"
                        class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
                    Search
                </button>
                @if(request('search'))
                    <a href="{{ route('school-head.lesson-plans.index') }}" class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:text-gray-300 transition-colors">Clear</a>
                @endif
            </div>
        </form>
    </div>

    @forelse($lessonPlans as $plan)
    @php
        $observation = $plan->observation;
        $teacher = $observation?->observee;
    @endphp
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5 mb-4 hover:shadow-md transition-shadow">
        <div class="flex items-center gap-4">
            <div class="w-11 h-11 rounded-full bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ $observation?->subject ?? 'Observation' }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">by {{ $teacher?->user?->name ?? 'Unknown' }}</p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ $observation?->observation_date?->format('M d, Y') ?? 'No date' }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('school-head.lesson-plans.show', $plan->id) }}"
                   class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
                    View Plan
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-12 text-center">
        <div class="w-16 h-16 rounded-full bg-gray-50 dark:bg-gray-800 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">No lesson plans</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">No lesson plans have been submitted by teachers yet.</p>
    </div>
    @endforelse

    @if($lessonPlans->hasPages())
        <div class="mt-8">{{ $lessonPlans->links() }}</div>
    @endif
</div>
@endsection
