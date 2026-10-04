@extends('layouts.teacher')

@section('title', 'AI Feedback')

@include('partials.dashboard.mock-styles')

@section('content')
<div class="mock-wrap max-w-7xl mx-auto px-1 py-1">
    <div class="mock-topbar">
        <div class="mock-crumbs">School Head <span>/</span> <b>Feedback</b></div>
        @isset($teachers)
            <span class="mock-pill"><span class="pulse"></span>{{ $teachers->total() }} teachers</span>
        @endisset
        @isset($teacher)
            <span class="mock-pill"><span class="pulse"></span>{{ $teacher->user->name ?? 'Teacher' }}</span>
            <div class="mock-actions">
                <a class="mock-btn" href="{{ route('school-head.feedback.list') }}">All Teachers</a>
            </div>
        @endisset
    </div>

    <div class="mock-title">
        <div>
            <h1>AI Feedback &amp; Coaching</h1>
            <p>
                @isset($teacher)
                    Feedback for {{ $teacher->user->name ?? 'this teacher' }}
                @else
                    One row per teacher — open a teacher to see every feedback
                @endisset
            </p>
        </div>
        <time>{{ now()->format('l, F j, Y') }}</time>
    </div>

    @isset($teachers)
    <div class="mock-kpis grid grid-cols-2 gap-4 mb-8">
        <div class="mock-kpi bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-teal-50 dark:bg-teal-500/10 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-teal-600 dark:text-teal-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['total_feedback'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total AI Feedback</p>
                </div>
            </div>
        </div>
        <div class="mock-kpi bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
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
    @endisset

    <div class="mock-panel bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5 mb-6">
        <form method="GET" action="{{ route('school-head.feedback.list') }}">
            @isset($teacher)
                <input type="hidden" name="teacher" value="{{ $teacher->id }}">
            @endisset
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Search</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" name="search" value="{{ request('search') }}"
                               class="w-full pl-9 pr-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                               placeholder="{{ isset($teacher) ? 'Search by subject...' : 'Search by teacher or subject...' }}">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Feedback Type</label>
                    <select name="type"
                            class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        <option value="">All Types</option>
                        @foreach($feedbackTypes as $key => $label)
                            <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                @empty($teacher)
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Status</label>
                    <select name="status"
                            class="px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        <option value="">All</option>
                        <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Has Published</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Has Drafts</option>
                    </select>
                </div>
                @endempty
                <button type="submit"
                        class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
                    {{ isset($teacher) ? 'Search' : 'Filter' }}
                </button>
                @if(request('search') || request('type') || request('status'))
                    <a href="{{ route('school-head.feedback.list', isset($teacher) ? ['teacher' => $teacher->id] : []) }}" class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:text-gray-300 transition-colors">Clear</a>
                @endif
            </div>
        </form>
    </div>

    @isset($teachers)
    @if($teachers->total() > 0)
    <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Showing <span class="font-medium text-gray-700 dark:text-gray-300">{{ $teachers->firstItem() }}</span>
            to <span class="font-medium text-gray-700 dark:text-gray-300">{{ $teachers->lastItem() }}</span>
            of <span class="font-medium text-gray-700 dark:text-gray-300">{{ $teachers->total() }}</span> teachers
        </p>
    </div>
    @endif

    @forelse($teachers as $row)
    @php $t = $row['teacher']; @endphp
    <div class="mock-panel bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5 mb-4 hover:shadow-md transition-shadow">
        <div class="flex items-center gap-4">
            <div class="w-11 h-11 rounded-full bg-teal-50 dark:bg-teal-500/10 flex items-center justify-center shrink-0">
                <span class="text-teal-600 dark:text-teal-300 font-semibold text-sm">{{ strtoupper(substr($t->user->name ?? '?', 0, 1)) }}</span>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ $t->user->name ?? 'Unknown' }}</h3>
                    @if($row['draft'] > 0)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        {{ $row['draft'] }} draft{{ $row['draft'] > 1 ? 's' : '' }}
                    </span>
                    @endif
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                    {{ $row['observations_count'] }} observation{{ $row['observations_count'] !== 1 ? 's' : '' }}
                    &middot; {{ $row['total'] }} feedback ({{ $row['published'] }} published)
                    @if($row['latest_at'])
                        &middot; latest {{ $row['latest_at']->format('M d, Y') }}
                    @endif
                </p>
                @if(!empty($row['by_type']))
                <div class="flex flex-wrap items-center gap-1.5 mt-2">
                    @foreach($row['by_type'] as $type => $count)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800">
                            {{ $feedbackTypes[$type] ?? $type }} · {{ $count }}
                        </span>
                    @endforeach
                </div>
                @endif
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('school-head.feedback.list', array_merge(request()->except(['teacher', 'page']), ['teacher' => $t->id])) }}"
                   class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
                    View Feedback
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="mock-panel bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-12 text-center">
        <div class="w-16 h-16 rounded-full bg-gray-50 dark:bg-gray-800 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">No teachers found</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">No teachers have AI feedback yet, or none match your filters.</p>
    </div>
    @endforelse

    @if($teachers->hasPages())
        <div class="mt-8">{{ $teachers->links() }}</div>
    @endif
    @endisset

    @isset($aiFeedbacks)
    @forelse($aiFeedbacks as $feedback)
    @php
        $fbObservation = $feedback->observation;
        $isParty = $fbObservation && ((int) $fbObservation->observer_id === (int) auth()->id() || (int) ($fbObservation->school_head_id ?? 0) === (int) auth()->id());
        $planAgreement = $fbObservation?->latestCoachingAgreement;
    @endphp
    <div class="mock-panel bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5 mb-4 hover:shadow-md transition-shadow">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-full bg-teal-50 dark:bg-teal-500/10 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-teal-600 dark:text-teal-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ $feedback->observation?->subject ?? 'Observation' }}</h3>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300">{{ $feedback->feedbackTypeLabel() }}</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium {{ $feedback->statusBadgeClass() }}">{{ ucfirst($feedback->status) }}</span>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Teacher: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $feedback->observation?->observee?->user?->name ?? 'Unknown' }}</span>
                    &middot; {{ $feedback->created_at->format('M d, Y') }}
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2 line-clamp-3">{{ $feedback->feedback ?? $feedback->content ?? $feedback->analysis ?? 'No content' }}</p>
                @if($fbObservation && $isParty)
                <div class="flex flex-wrap items-center gap-2 mt-3">
                    <a href="{{ route('school-head.feedback.index', $fbObservation) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-800 text-xs font-semibold hover:bg-purple-100 dark:hover:bg-purple-900/30 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        Feedback Management
                    </a>
                    @if($planAgreement)
                        <a href="{{ route('school-head.coaching.show', $planAgreement) }}"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-xs font-semibold hover:bg-emerald-100 dark:hover:bg-emerald-900/30 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            View Improvement Plan
                        </a>
                    @else
                        <a href="{{ route('school-head.coaching.create', $fbObservation) }}"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 text-xs font-semibold hover:bg-emerald-100 dark:hover:bg-emerald-900/30 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            Create Improvement Plan
                        </a>
                    @endif
                </div>
                @endif
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('school-head.feedback.show', $feedback) }}"
                   class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
                    View Details
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="mock-panel bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-12 text-center">
        <div class="w-16 h-16 rounded-full bg-gray-50 dark:bg-gray-800 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">No feedback found</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">No feedback matches your filters.</p>
    </div>
    @endforelse

    @if($aiFeedbacks->hasPages())
        <div class="mt-8">{{ $aiFeedbacks->links() }}</div>
    @endif
    @endisset
</div>
@endsection
