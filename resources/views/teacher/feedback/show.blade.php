@extends('layouts.teacher')

@section('title', 'Feedback Details')

@push('styles')
<style>
    .feedback-card { transition: all 0.2s ease; }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6">
    <!-- Breadcrumb -->
    <nav class="mb-6 text-sm">
        <ol class="flex items-center gap-2 text-gray-500">
            <li><a href="{{ route('teacher.feedback.index') }}" class="hover:text-indigo-600 transition-colors">Feedback & Coaching</a></li>
            <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"/></svg></li>
            <li class="text-gray-900 font-medium">Feedback Details</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $feedback->feedbackTypeLabel() }}</h1>
            <p class="text-gray-500 mt-1">
                {{ $feedback->observation->observation_date->format('M d, Y') }}
                &middot; {{ $feedback->observation->observer?->name ?? 'Supervisor' }}
            </p>
        </div>
        <a href="{{ route('teacher.feedback.index') }}"
           class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium transition-colors">
            Back to List
        </a>
    </div>

    <!-- Feedback Content -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <!-- Analysis -->
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Analysis</h3>
            <div class="prose prose-sm max-w-none text-gray-700 whitespace-pre-wrap">{{ $feedback->analysis }}</div>
        </div>

        <!-- Strengths -->
        @if($feedback->strengths && count($feedback->strengths) > 0)
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-xs font-semibold text-green-600 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Strengths
            </h3>
            <ul class="space-y-2">
                @foreach($feedback->strengths as $strength)
                    <li class="flex items-start gap-2 text-sm text-gray-700">
                        <svg class="w-4 h-4 text-green-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        {{ $strength }}
                    </li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Areas for Improvement -->
        @if($feedback->areas_for_improvement && count($feedback->areas_for_improvement) > 0)
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-xs font-semibold text-amber-600 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Areas for Improvement
            </h3>
            <ul class="space-y-2">
                @foreach($feedback->areas_for_improvement as $afi)
                    <li class="flex items-start gap-2 text-sm text-gray-700">
                        <svg class="w-4 h-4 text-amber-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ $afi }}
                    </li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Recommendations -->
        @if($feedback->recommendations && count($feedback->recommendations) > 0)
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-xs font-semibold text-indigo-600 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                Recommendations
            </h3>
            <ul class="space-y-2">
                @foreach($feedback->recommendations as $rec)
                    <li class="flex items-start gap-2 text-sm text-gray-700">
                        <svg class="w-4 h-4 text-indigo-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        {{ $rec }}
                    </li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Per-Indicator Ratings -->
        @if($feedback->observation->cotRatings->isNotEmpty())
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">COT Ratings</h3>
            <div class="space-y-2">
                @foreach($feedback->observation->cotRatings as $rating)
                    <div class="flex items-center justify-between py-2 px-3 bg-gray-50 rounded-lg">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $rating->indicator }}</p>
                            <p class="text-xs text-gray-500">{{ $rating->domain }}</p>
                        </div>
                        <span class="shrink-0 ml-3 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                            {{ $rating->isNotObserved() ? 'bg-gray-100 text-gray-600' : ($rating->rating >= 4 ? 'bg-green-100 text-green-700' : ($rating->rating >= 3 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700')) }}">
                            {{ $rating->isNotObserved() ? 'NO' : $rating->rating }}/6
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Published Info -->
        <div class="p-4 bg-gray-50 flex items-center gap-2 text-xs text-gray-400">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Published on {{ $feedback->reviewed_at?->format('M d, Y h:i A') ?? $feedback->updated_at->format('M d, Y h:i A') }}
            @if($feedback->confidence_score)
                &middot; Confidence: {{ number_format($feedback->confidence_score * 100, 0) }}%
            @endif
        </div>
    </div>

    <!-- Observation Context -->
    <a href="{{ route('teacher.observations.show', $feedback->observation) }}"
       class="mt-4 inline-flex items-center gap-2 text-sm text-indigo-600 hover:text-indigo-700 font-medium">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
        View Full Observation Details
    </a>
</div>
@endpush
