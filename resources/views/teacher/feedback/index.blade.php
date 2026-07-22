@extends('layouts.teacher')

@section('title', 'Feedback & Coaching')

@push('styles')
<style>
    .fb-card { transition: all 0.2s ease; }
    .fb-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.06); }
    .dark .fb-card:hover { box-shadow: 0 8px 25px rgba(0,0,0,0.3); }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Feedback & Coaching</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">View published feedback from your observations.</p>
        </div>
    </div>

    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
             class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 rounded-xl p-4 flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
    @endif

    @if($feedbacks->isEmpty())
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-12 text-center">
            <div class="w-16 h-16 rounded-full bg-gray-50 dark:bg-gray-800 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">No Feedback Available</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Published feedback from your supervisors will appear here.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @foreach($feedbacks as $feedback)
                @php $obs = $feedback->observation; @endphp
                <div class="fb-card bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                    <div class="p-5">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium
                                {{ $feedback->feedback_type === 'pre_observation' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : '' }}
                                {{ $feedback->feedback_type === 'post_observation' ? 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400' : '' }}
                                {{ $feedback->feedback_type === 'post_conference' ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400' : '' }}
                                {{ $feedback->feedback_type === 'final_summary' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : '' }}">
                                {{ $feedback->feedbackTypeLabel() }}
                            </span>
                            <span class="text-xs text-gray-400 dark:text-gray-500">
                                {{ $obs->observation_date->format('M d, Y') }}
                            </span>
                        </div>

                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3 line-clamp-3">
                            {{ Str::limit(strip_tags($feedback->analysis), 120) }}
                        </p>

                        @if($feedback->strengths && count($feedback->strengths) > 0)
                            <div class="flex flex-wrap gap-1.5 mb-3">
                                @foreach(array_slice($feedback->strengths, 0, 2) as $strength)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                        {{ Str::limit($strength, 30) }}
                                    </span>
                                @endforeach
                                @if(count($feedback->strengths) > 2)
                                    <span class="text-xs text-gray-400 dark:text-gray-500">+{{ count($feedback->strengths) - 2 }} more</span>
                                @endif
                            </div>
                        @endif

                        <div class="flex items-center justify-between">
                            <div class="text-xs text-gray-400 dark:text-gray-500">
                                {{ $obs->observer?->name ?? 'Supervisor' }}
                            </div>
                            <a href="{{ route('teacher.feedback.show', $feedback) }}"
                               class="inline-flex items-center gap-1 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">
                                View Details
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $feedbacks->links() }}
        </div>
    @endif
</div>
@endsection
