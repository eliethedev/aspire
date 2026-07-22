@extends('layouts.teacher')

@section('title', 'AI Feedback')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">AI Feedback & Coaching</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">View AI-generated feedback and coaching insights for your teachers.</p>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4 mb-8">
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-teal-50 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['total_feedback'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total AI Feedback</p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-50 dark:bg-green-900/20 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['recent_feedback'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">This Week</p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5 mb-6">
        <form method="GET" action="{{ route('school-head.feedback.index') }}">
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
                    <a href="{{ route('school-head.feedback.index') }}" class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:text-gray-300 transition-colors">Clear</a>
                @endif
            </div>
        </form>
    </div>

    @forelse($aiFeedbacks as $feedback)
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5 mb-4 hover:shadow-md transition-shadow">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-full bg-teal-50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ $feedback->observation?->subject ?? 'Observation' }}</h3>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300">AI Generated</span>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Teacher: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $feedback->observation?->observee?->user?->name ?? 'Unknown' }}</span>
                    &middot; {{ $feedback->created_at->format('M d, Y') }}
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2 line-clamp-3">{{ $feedback->feedback ?? $feedback->content ?? 'No content' }}</p>
            </div>
        </div>
    </div>
    @empty
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-12 text-center">
        <div class="w-16 h-16 rounded-full bg-gray-50 dark:bg-gray-800 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">No AI feedback yet</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">AI feedback will appear here once observations are completed with AI analysis.</p>
    </div>
    @endforelse

    @if($aiFeedbacks->hasPages())
        <div class="mt-8">{{ $aiFeedbacks->links() }}</div>
    @endif
</div>
@endsection
