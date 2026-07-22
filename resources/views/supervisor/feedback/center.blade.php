@extends('layouts.supervisor')

@section('title', 'Feedback Center')

@push('styles')
<style>
    .fb-card { transition: all 0.2s ease; }
    .fb-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.06); }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-dark-900">Feedback Center</h1>
            <p class="text-dark-500 mt-1">Review, edit, and publish feedback for all observations.</p>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5 mb-6">
        <form method="GET" action="{{ route('supervisor.feedback.center') }}">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-medium text-dark-500 mb-1.5">Search</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" name="search" value="{{ request('search') }}"
                               class="w-full pl-9 pr-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                               placeholder="Search by teacher name...">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-dark-500 mb-1.5">Filter</label>
                    <select name="status"
                            class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        <option value="">All Observations</option>
                        <option value="needs_review" {{ request('status') == 'needs_review' ? 'selected' : '' }}>Needs Review</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
                <button type="submit"
                        class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
                    Filter
                </button>
                @if(request()->anyFilled(['search', 'status']))
                    <a href="{{ route('supervisor.feedback.center') }}"
                       class="px-4 py-2 text-sm text-dark-500 hover:text-dark-700 transition-colors">Clear</a>
                @endif
            </div>
        </form>
    </div>

    <!-- List -->
    @forelse($observations as $observation)
        @php
            $teacherName = $observation->observee->user->name ?? 'Unknown';
            $initial = strtoupper(substr($teacherName, 0, 1));
            $feedbacks = $observation->aiFeedbacks;
            $totalFb = $feedbacks->count();
            $draftFb = $feedbacks->where('status', 'draft')->count();
            $publishedFb = $feedbacks->where('status', 'published')->count();
            $hasUnpublished = $draftFb > 0;
        @endphp
        <div class="fb-card bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5 mb-4">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                <div class="flex items-center gap-3 min-w-0 flex-1">
                    <div class="w-11 h-11 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 flex items-center justify-center text-base font-bold shrink-0">
                        {{ $initial }}
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="font-semibold text-dark-900 truncate">{{ $teacherName }}</h3>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium
                                {{ $observation->status === 'completed' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400' }}">
                                {{ ucwords(str_replace('_', ' ', $observation->status)) }}
                            </span>
                        </div>
                        <p class="text-sm text-dark-500">
                            {{ $observation->observation_date->format('M d, Y') }}
                            @if($observation->subject) &middot; {{ $observation->subject }} @endif
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-4 text-sm shrink-0">
                    <!-- Feedback summary -->
                    <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gray-50 dark:bg-gray-800">
                        <svg class="w-4 h-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/></svg>
                        <span class="font-medium text-dark-700">{{ $publishedFb }}</span>
                        <span class="text-dark-400">/ {{ $totalFb }}</span>
                    </div>

                    @if($hasUnpublished)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            {{ $draftFb }} draft{{ $draftFb > 1 ? 's' : '' }}
                        </span>
                    @endif

                    <a href="{{ route('supervisor.feedback.index', $observation) }}"
                       class="px-4 py-1.5 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
                        Manage Feedback
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-12 text-center">
            <div class="w-16 h-16 rounded-full bg-gray-50 dark:bg-gray-800 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-dark-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/></svg>
            </div>
            <h3 class="text-lg font-semibold text-dark-900 mb-1">No observations found</h3>
            <p class="text-sm text-dark-500">
                @if(request('search') || request('status'))
                    No observations match your filters.
                @else
                    Create an observation first to start managing feedback.
                @endif
            </p>
        </div>
    @endforelse

    @if($observations->hasPages())
        <div class="mt-8">
            {{ $observations->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@endsection
