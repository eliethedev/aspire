@extends('layouts.teacher')

@section('title', 'Feedback Details')

@include('partials.dashboard.mock-styles')

@section('content')
<div class="mock-wrap max-w-7xl mx-auto px-1 py-1">
    <div class="mock-topbar">
        <div class="mock-crumbs">School Head <span>/</span> <b>Feedback</b></div>
        <span class="mock-pill"><span class="pulse"></span>{{ $feedback->feedbackTypeLabel() }}</span>
        <div class="mock-actions">
            <a class="mock-btn" href="{{ route('school-head.feedback.list') }}">Back to Feedback</a>
        </div>
    </div>

    <div class="mock-title">
        <div>
            <h1>{{ $feedback->feedbackTypeLabel() }} Feedback</h1>
            <p>{{ $observation->observee?->user?->name ?? 'Unknown' }}{{ $observation->observation_date ? ' · ' . $observation->observation_date->format('M d, Y') : '' }}</p>
        </div>
        <time>{{ now()->format('l, F j, Y') }}</time>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        <div class="lg:col-span-2 space-y-6">
            <div class="mock-panel bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                <div class="flex flex-wrap items-center gap-2 mb-4">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-medium {{ $feedback->statusBadgeClass() }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $feedback->status === 'published' ? 'bg-green-500' : ($feedback->status === 'draft' ? 'bg-amber-500' : 'bg-gray-400') }}"></span>
                        {{ ucfirst($feedback->status) }}
                    </span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium {{ $feedback->generatedByBadgeClass() }}">
                        {{ ucfirst(str_replace('_', ' ', $feedback->generated_by)) }}
                    </span>
                    @if($feedback->confidence_score)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
                            Confidence: {{ number_format($feedback->confidence_score * 100, 0) }}%
                        </span>
                    @endif
                    @if($feedback->model_version)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
                            {{ $feedback->model_version }}
                        </span>
                    @endif
                </div>

                @if($feedback->cotRating)
                <div class="rounded-xl bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800 p-4 mb-5">
                    <p class="text-xs font-semibold text-indigo-600 dark:text-indigo-300 uppercase tracking-wider mb-1">Linked Indicator</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $feedback->cotRating->indicator_code }} — {{ $feedback->cotRating->indicator }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $feedback->cotRating->domain }}</p>
                </div>
                @endif

                <div class="mb-5">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Analysis</h3>
                    <div class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap leading-relaxed">{{ $feedback->analysis ?? 'No analysis recorded.' }}</div>
                </div>

                @if($feedback->strengths && count($feedback->strengths) > 0)
                <div class="mb-5">
                    <h3 class="text-xs font-semibold text-green-600 dark:text-green-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Strengths
                    </h3>
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

                @if($feedback->areas_for_improvement && count($feedback->areas_for_improvement) > 0)
                <div class="mb-5">
                    <h3 class="text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Areas for Improvement
                    </h3>
                    <ul class="space-y-1.5">
                        @foreach($feedback->areas_for_improvement as $area)
                            <li class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                                <svg class="w-4 h-4 text-amber-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ $area }}
                            </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @if($feedback->recommendations && count($feedback->recommendations) > 0)
                <div>
                    <h3 class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        Recommendations
                    </h3>
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
        </div>

        <aside class="space-y-6 min-w-0" aria-label="Feedback information">
            <div class="mock-panel bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Feedback Information</h2>
                </div>
                <dl class="divide-y divide-gray-100 dark:divide-gray-800">
                    <div class="px-5 py-3.5 flex items-start justify-between gap-3">
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 shrink-0 mt-0.5">Teacher</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-gray-100 text-right">{{ $observation->observee?->user?->name ?? '—' }}</dd>
                    </div>
                    <div class="px-5 py-3.5 flex items-start justify-between gap-3">
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 shrink-0 mt-0.5">Observer</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-gray-100 text-right">{{ $observation->observer?->name ?? '—' }}</dd>
                    </div>
                    @if($observation->subject)
                    <div class="px-5 py-3.5 flex items-start justify-between gap-3">
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 shrink-0 mt-0.5">Subject</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-gray-100 text-right">{{ $observation->subject }}</dd>
                    </div>
                    @endif
                    @if($observation->observation_date)
                    <div class="px-5 py-3.5 flex items-start justify-between gap-3">
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 shrink-0 mt-0.5">Date</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-gray-100 text-right">{{ $observation->observation_date->format('M d, Y') }}</dd>
                    </div>
                    @endif
                    <div class="px-5 py-3.5 flex items-start justify-between gap-3">
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 shrink-0 mt-0.5">Type</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-gray-100 text-right">{{ $feedback->feedbackTypeLabel() }}</dd>
                    </div>
                    <div class="px-5 py-3.5 flex items-start justify-between gap-3">
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 shrink-0 mt-0.5">Status</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-gray-100 text-right">{{ ucfirst($feedback->status) }}</dd>
                    </div>
                    @if($feedback->reviewer)
                    <div class="px-5 py-3.5 flex items-start justify-between gap-3">
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 shrink-0 mt-0.5">Reviewed By</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-gray-100 text-right">{{ $feedback->reviewer->name }}{{ $feedback->reviewed_at ? ' · ' . $feedback->reviewed_at->format('M d, Y') : '' }}</dd>
                    </div>
                    @endif
                    <div class="px-5 py-3.5 flex items-start justify-between gap-3">
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 shrink-0 mt-0.5">Created</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-gray-100 text-right">{{ $feedback->created_at->format('M d, Y') }}</dd>
                    </div>
                </dl>
            </div>

            <a href="{{ route('school-head.observations.show', $observation) }}"
               class="mock-panel bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 hover:shadow-md transition-shadow flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">View Observation</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Open the related observation details</p>
                </div>
            </a>
        </aside>
    </div>
</div>
@endsection
