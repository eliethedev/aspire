@extends('layouts.teacher')

@section('title', 'Coaching Agreement')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6">
    <!-- Breadcrumb -->
    <nav class="mb-6 text-sm">
        <ol class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
            <li><a href="{{ route('teacher.coaching.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Improvement Plan</a></li>
            <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"/></svg></li>
            <li class="text-gray-900 dark:text-gray-100 font-medium">Coaching Agreement</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Coaching Agreement</h1>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $agreement->statusBadgeClass() }}">
                    {{ ucfirst($agreement->status) }}
                </span>
            </div>
            <p class="text-gray-500 dark:text-gray-400 mt-1">
                {{ $agreement->observation->observation_date->format('M d, Y') }}
                &middot; {{ $agreement->supervisor?->name ?? 'Supervisor' }}
            </p>
        </div>
        <a href="{{ route('teacher.coaching.index') }}"
           class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 text-sm font-medium transition-colors">
            Back to List
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Focus Areas -->
            @if($agreement->focus_areas)
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Focus Areas</h3>
                <ul class="space-y-2">
                    @foreach($agreement->focus_areas as $area)
                        <li class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <svg class="w-4 h-4 text-indigo-500 dark:text-indigo-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                            {{ $area }}
                        </li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Action Steps -->
            @if($agreement->action_steps)
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Action Steps</h3>
                <ol class="space-y-3">
                    @foreach($agreement->action_steps as $i => $step)
                        <li class="flex items-start gap-3">
                            <span class="flex items-center justify-center w-6 h-6 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 text-xs font-bold shrink-0">{{ $i + 1 }}</span>
                            <span class="text-sm text-gray-700 dark:text-gray-300 pt-0.5">{{ $step }}</span>
                        </li>
                    @endforeach
                </ol>
            </div>
            @endif

            <!-- Resources & Success Indicators -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                @if($agreement->resources_needed)
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Resources Needed</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 whitespace-pre-wrap">{{ $agreement->resources_needed }}</p>
                </div>
                @endif

                @if($agreement->success_indicators)
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Success Indicators</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 whitespace-pre-wrap">{{ $agreement->success_indicators }}</p>
                </div>
                @endif
            </div>

            @if($agreement->timeline)
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Timeline</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $agreement->timeline }}</p>
            </div>
            @endif

            @if($agreement->supervisor_notes)
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Supervisor Notes</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 whitespace-pre-wrap">{{ $agreement->supervisor_notes }}</p>
            </div>
            @endif

            @if($agreement->teacher_notes)
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Your Notes</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 whitespace-pre-wrap">{{ $agreement->teacher_notes }}</p>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Signatures -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">Signatures</h3>
                <div class="space-y-4">
                    <!-- Teacher Signature -->
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">You (Teacher)</p>
                            @if($agreement->teacher_signed_at)
                                <p class="text-xs text-green-600 dark:text-green-400">Signed on {{ $agreement->teacher_signed_at->format('M d, Y') }}</p>
                                @if($agreement->teacher_signature)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 font-serif italic">{{ $agreement->teacher_signature }}</p>
                                @endif
                            @else
                                <p class="text-xs text-amber-600 dark:text-amber-400">Not yet signed</p>
                            @endif
                        </div>
                        @if($agreement->teacher_signed_at)
                            <svg class="w-6 h-6 text-green-500 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        @endif
                    </div>
                    <hr class="border-gray-100 dark:border-gray-700">
                    <!-- Supervisor Signature -->
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $agreement->supervisor?->name ?? 'Supervisor' }}</p>
                            @if($agreement->supervisor_signed_at)
                                <p class="text-xs text-green-600 dark:text-green-400">Signed on {{ $agreement->supervisor_signed_at->format('M d, Y') }}</p>
                                @if($agreement->supervisor_signature)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 font-serif italic">{{ $agreement->supervisor_signature }}</p>
                                @endif
                            @else
                                <p class="text-xs text-amber-600 dark:text-amber-400">Not yet signed</p>
                            @endif
                        </div>
                        @if($agreement->supervisor_signed_at)
                            <svg class="w-6 h-6 text-green-500 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sign Form -->
            @if(!$agreement->teacher_signed_at)
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6" x-data="{ showForm: false }">
                <button @click="showForm = !showForm"
                        class="w-full px-4 py-2.5 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors">
                    Sign Agreement
                </button>

                <form method="POST" action="{{ route('teacher.coaching.sign', $agreement) }}" x-show="showForm" x-cloak class="mt-4 space-y-4"
                      x-data="{ signing: false }" x-on:submit="signing = true">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Type your full name to sign</label>
                        <input type="text" name="signature"
                               class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                               placeholder="Type your name as signature" required>
                        @error('signature')
                            <p class="text-xs text-red-500 dark:text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Notes (optional)</label>
                        <textarea name="teacher_notes" rows="2"
                                  class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                                  placeholder="Any notes about this agreement..."></textarea>
                    </div>
                    <button type="submit" :disabled="signing"
                            :class="signing ? 'opacity-60 cursor-not-allowed' : ''"
                            class="w-full px-4 py-2 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition-colors">
                        <span x-show="!signing">Confirm Signature</span>
                        <span x-show="signing" class="flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Signing...
                        </span>
                    </button>
                </form>
            </div>
            @endif

            <!-- Observation Link -->
            <a href="{{ route('teacher.observations.show', $agreement->observation) }}"
               class="block bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5 hover:border-indigo-200 dark:hover:border-indigo-800 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">View Observation</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $agreement->observation->observation_date->format('M d, Y') }}</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection
