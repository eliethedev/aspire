@extends('layouts.supervisor')

@section('title', 'Feedback Management')

@push('styles')
<style>
    .feedback-card { transition: all 0.2s ease; }
    .feedback-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.06); }
    .tab-btn { transition: all 0.2s ease; }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6">
    <!-- Breadcrumb -->
    <nav class="mb-6 text-sm">
        <ol class="flex items-center gap-2 text-gray-500 dark:text-gray-400 dark:text-gray-500">
            <li><a href="{{ route('supervisor.observations.index') }}" class="hover:text-indigo-600 dark:text-indigo-400 transition-colors">Evaluations</a></li>
            <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"/></svg></li>
            <li><a href="{{ route('supervisor.observations.show', $observation) }}" class="hover:text-indigo-600 dark:text-indigo-400 transition-colors">Observation Details</a></li>
            <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"/></svg></li>
            <li class="text-gray-900 dark:text-gray-100 font-medium">Feedback Management</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-dark-900">Feedback Management</h1>
            <p class="text-dark-500 mt-1">
                {{ $observation->observee->user->name ?? 'Unknown' }}
                &middot; {{ $observation->observation_date->format('M d, Y') }}
                @if($observation->subject) &middot; {{ $observation->subject }} @endif
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('supervisor.observations.show', $observation) }}"
               class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:bg-gray-800 text-sm font-medium transition-colors">
                Back to Observation
            </a>
        </div>
    </div>

    @php
        $feedbackTypes = [
            'pre_observation' => 'Pre-Observation',
            'post_observation' => 'Post-Observation',
            'post_conference' => 'Post-Conference',
            'final_summary' => 'Final Summary',
        ];
        $icons = [
            'pre_observation' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
            'post_observation' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>',
            'post_conference' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>',
            'final_summary' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        ];
        $selectedType = request('type', 'post_observation');
    @endphp

    <!-- Feedback Type Tabs -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-1.5 mb-6 inline-flex flex-wrap gap-1">
        @foreach($feedbackTypes as $key => $label)
            @php
                $isActive = $selectedType === $key;
                $hasExisting = $feedbacks->where('feedback_type', $key)->isNotEmpty();
            @endphp
            <a href="{{ route('supervisor.feedback.index', [$observation, 'type' => $key]) }}"
               class="tab-btn inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-colors
                   {{ $isActive ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 dark:text-gray-400 dark:text-gray-500 hover:text-gray-900 dark:text-gray-100 hover:bg-gray-50 dark:bg-gray-800' }}">
                {!! $icons[$key] !!}
                {{ $label }}
                @if($hasExisting)
                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-[10px] font-bold {{ $isActive ? 'bg-white dark:bg-gray-900 text-indigo-600 dark:text-indigo-400' : 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400' }}">
                        {{ $feedbacks->where('feedback_type', $key)->count() }}
                    </span>
                @endif
            </a>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            @php
                $typeFeedbacks = $feedbacks->where('feedback_type', $selectedType);
            @endphp

            @if($typeFeedbacks->isEmpty())
                <!-- No feedback yet -->
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-12 text-center">
                    <div class="w-16 h-16 rounded-full bg-gray-50 dark:bg-gray-800 flex items-center justify-center mx-auto mb-4">
                        {!! $icons[$selectedType] !!}
                    </div>
                    <h3 class="text-lg font-semibold text-dark-900 mb-1">No {{ $feedbackTypes[$selectedType] }} Feedback Yet</h3>
                    <p class="text-sm text-dark-500 mb-6">
                        Generate AI feedback or create a manual entry to get started.
                    </p>
                    <div class="flex flex-wrap items-center justify-center gap-3">
                        <button type="button" onclick="openGenerateModal('{{ $selectedType }}')"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-purple-600 text-white rounded-lg font-medium hover:bg-purple-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                            Generate AI Feedback
                        </button>
                        <a href="{{ route('supervisor.feedback.create', [$observation, 'type' => $selectedType]) }}"
                           class="inline-flex items-center gap-2 px-5 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg font-medium hover:bg-gray-50 dark:bg-gray-800 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            Create Manual Entry
                        </a>
                    </div>
                </div>
            @else
                @foreach($typeFeedbacks as $feedback)
                    <div class="feedback-card bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                        <!-- Header -->
                        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg flex items-center justify-center text-sm font-bold
                                    {{ $feedback->generated_by === 'ai' ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400' : 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' }}">
                                    {{ $feedback->generated_by === 'ai' ? 'AI' : 'ME' }}
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-dark-900 text-sm">
                                            {{ $feedback->feedbackTypeLabel() }}
                                        </span>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium {{ $feedback->statusBadgeClass() }}">
                                            <span class="w-1.5 h-1.5 rounded-full
                                                {{ $feedback->status === 'published' ? 'bg-green-500' : ($feedback->status === 'draft' ? 'bg-amber-500' : 'bg-gray-400') }}"></span>
                                            {{ ucfirst($feedback->status) }}
                                        </span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium {{ $feedback->generatedByBadgeClass() }}">
                                            {{ ucfirst($feedback->generated_by) }}
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-dark-400">
                                        Confidence: {{ number_format($feedback->confidence_score * 100, 0) }}%
                                        &middot; {{ $feedback->created_at->format('M d, Y h:i A') }}
                                        @if($feedback->model_version)
                                            &middot; {{ $feedback->model_version }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                @if($feedback->status === 'draft')
                                    <form method="POST" action="{{ route('supervisor.feedback.publish', [$observation, $feedback]) }}" class="inline">
                                        @csrf
                                        <button type="submit"
                                                class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded-lg text-xs font-medium transition-colors">
                                            Publish
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('supervisor.feedback.edit', [$observation, $feedback]) }}"
                                   class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:bg-gray-800 rounded-lg text-xs font-medium transition-colors">
                                    Edit
                                </a>
                                <a href="{{ route('supervisor.feedback.export', [$observation, $feedback]) }}"
                                   class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:bg-gray-800 rounded-lg text-xs font-medium transition-colors">
                                    Export
                                </a>
                                <form method="POST" action="{{ route('supervisor.feedback.destroy', [$observation, $feedback]) }}" class="inline" onsubmit="return confirm('Delete this feedback?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="px-3 py-1.5 border border-red-200 text-red-600 dark:text-red-400 hover:bg-red-50 dark:bg-red-900/20 rounded-lg text-xs font-medium transition-colors">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Body -->
                        <div class="p-6 space-y-5">
                            <!-- Analysis -->
                            <div>
                                <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2">Analysis</h4>
                                <div class="prose prose-sm max-w-none text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $feedback->analysis }}</div>
                            </div>

                            <!-- Strengths -->
                            @if($feedback->strengths && count($feedback->strengths) > 0)
                            <div>
                                <h4 class="text-xs font-semibold text-green-600 dark:text-green-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Strengths
                                </h4>
                                <ul class="space-y-1.5">
                                    @foreach($feedback->strengths as $strength)
                                        <li class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                                            <svg class="w-4 h-4 text-green-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                            {{ $strength }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

                            <!-- Areas for Improvement -->
                            @if($feedback->areas_for_improvement && count($feedback->areas_for_improvement) > 0)
                            <div>
                                <h4 class="text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Areas for Improvement
                                </h4>
                                <ul class="space-y-1.5">
                                    @foreach($feedback->areas_for_improvement as $afi)
                                        <li class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                                            <svg class="w-4 h-4 text-amber-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            {{ $afi }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

                            <!-- Recommendations -->
                            @if($feedback->recommendations && count($feedback->recommendations) > 0)
                            <div>
                                <h4 class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                    Recommendations
                                </h4>
                                <ul class="space-y-1.5">
                                    @foreach($feedback->recommendations as $rec)
                                        <li class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                                            <svg class="w-4 h-4 text-indigo-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                            {{ $rec }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif
                        </div>

                        <!-- Footer: Review info -->
                        @if($feedback->reviewed_at)
                        <div class="px-6 py-3 bg-gray-50 dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700">
                            <p class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500">
                                Reviewed by {{ $feedback->reviewer?->name ?? 'Unknown' }}
                                on {{ $feedback->reviewed_at->format('M d, Y h:i A') }}
                            </p>
                        </div>
                        @endif
                    </div>
                @endforeach
            @endif

            <!-- Per-Indicator AI Feedback (Post-Observation only) -->
            @if($selectedType === 'post_observation' && $perIndicatorFeedbacks->isNotEmpty())
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">Per-Indicator AI Feedback</h3>
                <div class="space-y-3">
                    @foreach($perIndicatorFeedbacks as $aiFb)
                        @php $rating = $aiFb->cotRating; @endphp
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $rating->indicator }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500">{{ $rating->domain }}</p>
                                </div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $rating->isNotObserved() ? 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 dark:text-gray-500' : ($rating->rating >= 4 ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : ($rating->rating >= 3 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 dark:bg-red-900/30 text-red-700')) }}">
                                    {{ $rating->isNotObserved() ? 'NO' : $rating->rating }}/6
                                </span>
                            </div>
                            @if($aiFb->analysis)
                                <p class="text-sm text-gray-700 dark:text-gray-300">{{ Str::limit($aiFb->analysis, 200) }}</p>
                            @endif
                            @if($aiFb->confidence_score)
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Confidence: {{ number_format($aiFb->confidence_score * 100, 0) }}%</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider mb-3">Quick Actions</h3>
                <div class="space-y-2.5">
                    <button type="button" onclick="openGenerateModal('{{ $selectedType }}')"
                            class="w-full flex items-center gap-3 px-4 py-2.5 rounded-lg bg-purple-50 dark:bg-purple-900/20 hover:bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400 text-sm font-medium transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                        Generate AI Feedback
                    </button>
                    <a href="{{ route('supervisor.feedback.create', [$observation, 'type' => $selectedType]) }}"
                       class="w-full flex items-center gap-3 px-4 py-2.5 rounded-lg bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 text-sm font-medium transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Create Manual Entry
                    </a>
                </div>
            </div>

            <!-- Observation Info -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider mb-3">Observation Info</h3>
                <div class="space-y-3">
                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500">Teacher</span>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $observation->observee->user->name ?? 'Unknown' }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500">Subject</span>
                        <p class="text-sm text-gray-900 dark:text-gray-100">{{ $observation->subject ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500">Grade Level</span>
                        <p class="text-sm text-gray-900 dark:text-gray-100">{{ $observation->grade_level ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500">Date</span>
                        <p class="text-sm text-gray-900 dark:text-gray-100">{{ $observation->observation_date->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500">Status</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $observation->status === 'completed' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : ($observation->status === 'in_progress' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400') }}">
                            {{ ucwords(str_replace('_', ' ', $observation->status)) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Stage Progress -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider mb-3">Stage Progress</h3>
                <div class="space-y-2.5">
                    @php $stages = ['pre_observation_planning', 'pre_conference', 'observation', 'post_conference']; @endphp
                    @foreach($stages as $i => $stage)
                        @php
                            $done = match($stage) {
                                'pre_observation_planning' => (bool) $observation->preObservationPlanning,
                                'pre_conference' => (bool) $observation->preConference,
                                'observation' => $observation->cotRatings->count() > 0,
                                'post_conference' => (bool) $observation->postConference,
                                default => false,
                            };
                            $stageLabel = str_replace('_', ' ', $stage);
                            $stageLabel = ucwords($stageLabel);
                            $stageLabel = str_replace('Pre Observation Planning', 'Pre-Observation Planning', $stageLabel);
                        @endphp
                        <div class="flex items-center gap-2.5">
                            <div class="w-6 h-6 rounded-full flex items-center justify-center {{ $done ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : 'bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500' }}">
                                @if($done)
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                @else
                                    <span class="text-xs font-medium">{{ $i + 1 }}</span>
                                @endif
                            </div>
                            <span class="text-xs {{ $done ? 'text-gray-700 dark:text-gray-300 font-medium' : 'text-gray-400 dark:text-gray-500' }}">{{ $stageLabel }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Feedback Summary -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 uppercase tracking-wider mb-3">Feedback Summary</h3>
                <div class="space-y-2">
                    @foreach($feedbackTypes as $key => $label)
                        @php
                            $count = $feedbacks->where('feedback_type', $key)->count();
                            $publishedCount = $feedbacks->where('feedback_type', $key)->where('status', 'published')->count();
                        @endphp
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400 dark:text-gray-500">{{ $label }}</span>
                            <span class="text-xs {{ $publishedCount > 0 ? 'text-green-600 dark:text-green-400 font-medium' : ($count > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-400 dark:text-gray-500') }}">
                                {{ $publishedCount }}/{{ $count }} published
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Generate AI Feedback Modal -->
<div id="generate-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/30 backdrop-blur-sm">
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-2xl max-w-md w-full mx-4 p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Generate AI Feedback</h3>
                <p id="generate-modal-type" class="text-sm text-gray-500 dark:text-gray-400 dark:text-gray-500"></p>
            </div>
        </div>
        <p class="text-sm text-gray-600 dark:text-gray-400 dark:text-gray-500 mb-6">
            AI will analyze the observation data and generate comprehensive feedback including analysis, strengths, areas for improvement, and recommendations. Review all AI output before publishing.
        </p>
        <div class="flex gap-3">
            <button type="button" onclick="closeGenerateModal()"
                    class="flex-1 px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:bg-gray-800 font-medium text-sm transition-colors">
                Cancel
            </button>
            <form id="generate-form" method="POST" class="flex-1">
                @csrf
                <input type="hidden" name="type" id="generate-type-input" value="">
                <button type="submit"
                        class="w-full px-4 py-2.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-medium text-sm transition-colors">
                    Generate
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openGenerateModal(type) {
        const labels = {
            'pre_observation': 'Pre-Observation',
            'post_observation': 'Post-Observation',
            'post_conference': 'Post-Conference',
            'final_summary': 'Final Summary',
        };
        document.getElementById('generate-modal-type').textContent = labels[type] || type;
        document.getElementById('generate-type-input').value = type;
        document.getElementById('generate-form').action = '{{ route("supervisor.feedback.generate-ai", $observation) }}';
        document.getElementById('generate-modal').classList.remove('hidden');
        document.getElementById('generate-modal').classList.add('flex');
    }

    function closeGenerateModal() {
        document.getElementById('generate-modal').classList.add('hidden');
        document.getElementById('generate-modal').classList.remove('flex');
    }

    document.getElementById('generate-modal')?.addEventListener('click', function(e) {
        if (e.target === this) closeGenerateModal();
    });
</script>
@endpush
@endsection
