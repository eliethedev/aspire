@extends('layouts.teacher')

@section('title', 'Observation Details')

@push('styles')
<style>
    .progress-step {
        transition: all 0.2s ease;
    }
    .progress-step:hover .step-circle {
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
    }
    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
@php
    $stageLabels = [
        'pre_observation_planning' => 'Pre-Observation Planning',
        'pre_conference' => 'Pre-Conference',
        'observation' => 'Observation',
        'post_conference' => 'Post-Conference',
    ];
    $stageCompleted = [
        'pre_observation_planning' => (bool) $observation->preObservationPlanning,
        'pre_conference' => (bool) $observation->preConference,
        'observation' => $observation->cotRatings && $observation->cotRatings->count() > 0,
        'post_conference' => (bool) $observation->postConference,
    ];
    $stageKeys = ['pre_observation_planning', 'pre_conference', 'observation', 'post_conference'];
    $currentIdx = array_search($observation->stage, $stageKeys);
    $defaultRoom = $observation->teacher?->user?->teacherProfile?->default_room;
    $detailFilter = request('detail_filter') ?? 'all';
@endphp
<div class="obs-show max-w-7xl mx-auto px-6 py-8" x-data="{ detailFilter: '{{ $detailFilter }}' }">
    <div class="flex justify-between items-center mb-6">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Observation Details</h1>
                @if($observation->status === 'cancelled')
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                        Cancelled
                    </span>
                @endif
            </div>
            <p class="text-gray-500 dark:text-gray-400 mt-1">{{ $observation->observer?->name ?? 'Unknown Supervisor' }} - {{ $observation->observation_date?->format('M d, Y') ?? 'No date' }}
                @if($observation->start_time_label)
                    @ {{ $observation->start_time_label }}@if($observation->end_time_label) - {{ $observation->end_time_label }}@endif
                @endif
                @if($observation->location)
                    &middot; {{ $observation->location }}
                @endif
            </p>
            @if($observation->subject)
                <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">{{ $observation->subject }} @if($observation->grade_level)- Grade {{ $observation->grade_level }} @endif</p>
            @endif
        </div>
        <a href="{{ route('teacher.observations.index') }}" 
           class="px-6 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-sm">
            Back to List
        </a>
    </div>

    <!-- Details Filter -->
    <div class="flex flex-wrap gap-2 mb-4">
        <button @click="detailFilter = 'all'"
                class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors {{ $detailFilter === 'all' ? 'bg-indigo-600 text-white border-indigo-500' : 'text-gray-700 dark:text-gray-300' }}"
                >
            All Details
        </button>
        <button @click="detailFilter = 'ratings'"
                class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors {{ $detailFilter === 'ratings' ? 'bg-indigo-600 text-white border-indigo-500' : 'text-gray-700 dark:text-gray-300' }}"
                >
            Ratings Only
        </button>
        <button @click="detailFilter = 'result'"
                class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors {{ $detailFilter === 'result' ? 'bg-indigo-600 text-white border-indigo-500' : 'text-gray-700 dark:text-gray-300' }}"
                >
            Result Only
        </button>
        <button @click="detailFilter = 'pre_observation'"
                class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors {{ $detailFilter === 'pre_observation' ? 'bg-indigo-600 text-white border-indigo-500' : 'text-gray-700 dark:text-gray-300' }}"
                >
            Pre-Observation
        </button>
        <button @click="detailFilter = 'pre_conference'"
                class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors {{ $detailFilter === 'pre_conference' ? 'bg-indigo-600 text-white border-indigo-500' : 'text-gray-700 dark:text-gray-300' }}"
                >
            Pre-Conference
        </button>
        <button @click="detailFilter = 'post_conference'"
                class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors {{ $detailFilter === 'post_conference' ? 'bg-indigo-600 text-white border-indigo-500' : 'text-gray-700 dark:text-gray-300' }}"
                >
            Post-Conference
        </button>
    </div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        <div class="lg:col-span-2 min-w-0">
            <!-- Progress Steps (read-only) -->
            <div class="mb-8">
        <div class="flex items-center justify-between">
            @foreach($stageKeys as $i => $key)
                @php
                    $done = $stageCompleted[$key];
                    $active = $i === $currentIdx && !$done;
                @endphp

                @if($i > 0)
                    <div class="flex-1 mx-4 h-1 {{ $stageCompleted[$stageKeys[$i - 1]] ? 'bg-green-400' : 'bg-gray-200 dark:bg-gray-700' }}"></div>
                @endif

                <div class="flex items-center">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full {{ $done ? 'bg-green-600 text-white' : ($active ? 'bg-indigo-600 text-white ring-2 ring-indigo-200 dark:ring-indigo-800' : 'bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400') }} font-semibold text-sm">
                        @if($done)
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        @else
                            {{ $i + 1 }}
                        @endif
                    </div>
                    <span class="ml-2 {{ $done ? 'text-gray-700 dark:text-gray-300 font-medium' : ($active ? 'text-indigo-600 dark:text-indigo-400 font-medium' : 'text-gray-400 dark:text-gray-500') }} text-sm">{{ $stageLabels[$key] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Confirmation Needed Banner -->
    @if($observation->canConfirm())
    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-6 mb-6" x-data="{ showRejectModal: false }">
        <div class="flex gap-3">
            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div class="flex-1">
                <h3 class="font-semibold text-amber-800 dark:text-amber-300">Confirm Your Observation Schedule</h3>
                <p class="text-sm text-amber-700 dark:text-amber-400 mt-1">
                    Your supervisor has scheduled an observation on <strong>{{ $observation->observation_date?->format('M d, Y') ?? 'No date' }}</strong>.
                    Please confirm your availability or provide a reason if you need to reschedule.
                </p>
                <div class="flex items-center gap-3 mt-4">
                    <form action="{{ route('teacher.observations.confirm', $observation) }}" method="POST"
                          x-data="{ submitting: false }"
                          x-on:submit="submitting = true">
                        @csrf
                        <button type="submit"
                                :disabled="submitting"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg x-show="submitting" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span x-show="!submitting">Confirm Availability</span>
                            <span x-show="submitting">Confirming...</span>
                        </button>
                    </form>
                    <button @click="showRejectModal = true"
                            class="px-5 py-2.5 border border-red-300 dark:border-red-700 text-red-700 dark:text-red-400 rounded-lg text-sm font-medium hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                        Request Reschedule
                    </button>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div x-show="showRejectModal"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center"
             x-on:keydown.escape.window="showRejectModal = false">
            <div class="fixed inset-0 bg-black/50" @click="showRejectModal = false"></div>
            <div class="relative bg-white dark:bg-gray-900 rounded-xl shadow-xl max-w-lg w-full mx-4 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Request Reschedule</h3>
                    <button @click="showRejectModal = false" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form action="{{ route('teacher.observations.reject', $observation) }}" method="POST"
                      x-data="{ submitting: false }"
                      x-on:submit="submitting = true">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Reason <span class="text-red-500">*</span></label>
                            <select name="rejection_reason" required
                                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                                <option value="">Select a reason...</option>
                                <option value="scheduling_conflict">Scheduling Conflict</option>
                                <option value="health_concern">Health Concern</option>
                                <option value="insufficient_preparation">Insufficient Preparation</option>
                                <option value="other">Other</option>
                            </select>
                            @error('rejection_reason')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Additional Notes</label>
                            <textarea name="rejection_notes" rows="3"
                                      class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                                      placeholder="Provide any additional context..."></textarea>
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-3 mt-6">
                        <button type="button" @click="showRejectModal = false"
                                class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                :disabled="submitting"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg x-show="submitting" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span x-show="!submitting">Submit Reschedule Request</span>
                            <span x-show="submitting">Submitting...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Confirmation Info (already confirmed/rejected) -->
    @if($observation->confirmation_status === 'confirmed')
    <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl p-6 mb-6">
        <div class="flex gap-3">
            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                <h3 class="font-semibold text-emerald-800 dark:text-emerald-300">Observation Confirmed</h3>
                <p class="text-sm text-emerald-700 dark:text-emerald-400 mt-1">
                    You confirmed this observation on {{ $observation->confirmed_at?->format('M d, Y \a\t h:i A') }}.
                </p>
            </div>
        </div>
    </div>
    @endif

    @if($observation->confirmation_status === 'rejected')
    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-6 mb-6">
        <div class="flex gap-3">
            <svg class="w-5 h-5 text-red-600 dark:text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div>
                <h3 class="font-semibold text-red-800 dark:text-red-300">Observation Rejected</h3>
                <p class="text-sm text-red-700 dark:text-red-400 mt-1">
                    You rejected this observation on {{ $observation->rejected_at?->format('M d, Y \a\t h:i A') }}.
                </p>
                @if($observation->rejection_reason)
                <p class="text-sm text-red-700 dark:text-red-400 mt-1">
                    <strong>Reason:</strong> {{ str_replace('_', ' ', ucwords($observation->rejection_reason)) }}
                </p>
                @endif
                @if($observation->rejection_notes)
                <p class="text-sm text-red-700 dark:text-red-400 mt-1">
                    <strong>Notes:</strong> {{ $observation->rejection_notes }}
                </p>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Cancellation Info -->
    @if($observation->status === 'cancelled')
    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-6 mb-6">
        <div class="flex gap-3">
            <svg class="w-5 h-5 text-red-600 dark:text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M4.293 4.293a1 1 0 011.414 0L12 10.586l6.293-6.293a1 1 0 111.414 1.414L13.414 12l6.293 6.293a1 1 0 01-1.414 1.414L12 13.414l-6.293 6.293a1 1 0 01-1.414-1.414L10.586 12 4.293 5.707a1 1 0 010-1.414z"/></svg>
            <div>
                <h3 class="font-semibold text-red-800 dark:text-red-300">Observation Cancelled</h3>
                <p class="text-sm text-red-700 dark:text-red-400 mt-1">
                    This observation was cancelled on {{ $observation->cancelled_at?->format('M d, Y \a\t h:i A') }}.
                </p>
                @if($observation->cancellation_reason)
                <p class="text-sm text-red-700 dark:text-red-400 mt-1">
                    <strong>Reason:</strong> {{ ucwords(str_replace('_', ' ', $observation->cancellation_reason)) }}
                </p>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Stage Details -->
    <div class="space-y-6">
        <!-- Pre-Observation Planning -->
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6" x-show="detailFilter === 'all' || detailFilter === 'pre_observation'">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Pre-Observation Planning</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Lesson plan, AI insights, and supervisor recommendations</p>
                </div>
                @if($observation->preObservationPlanning)
                    <span class="ml-auto inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        Completed
                    </span>
                @elseif($observation->stage === 'pre_observation_planning')
                    <span class="ml-auto inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        In Progress
                    </span>
                @endif
            </div>

            @if($observation->stage === 'pre_observation_planning')
                <div class="mb-6 p-5 border-2 border-dashed border-indigo-200 dark:border-indigo-800 rounded-xl bg-gradient-to-br from-indigo-50 to-blue-50 dark:from-indigo-900/20 dark:to-blue-900/20">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-xl bg-white dark:bg-gray-800 shadow-sm flex items-center justify-center">
                            <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-indigo-900 dark:text-indigo-300">Upload Your Lesson Plan</h3>
                            <p class="text-xs text-indigo-600 dark:text-indigo-400">Submit your lesson plan for supervisor review</p>
                        </div>
                    </div>

                    <!-- AI Lesson Plan Guide -->
                    <div x-data="{ guideOpen: false }" class="mb-4 rounded-xl border border-indigo-100 dark:border-indigo-800/50 bg-white/60 dark:bg-gray-800/40 overflow-hidden">
                        <button type="button" @click="guideOpen = !guideOpen" class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-indigo-50/50 dark:hover:bg-indigo-900/20 transition-colors">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-indigo-500 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span class="text-xs font-semibold text-indigo-700 dark:text-indigo-300">What to include in your Lesson Plan</span>
                            </div>
                            <svg class="w-4 h-4 text-indigo-400 transition-transform duration-200" :class="{ 'rotate-180': guideOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="guideOpen" x-collapse x-cloak class="px-4 pb-4">
                            <p class="text-xs text-gray-600 dark:text-gray-400 mb-3">For the AI to generate useful insights, your lesson plan should clearly include these elements:</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <div class="flex items-start gap-2 p-2 rounded-lg bg-indigo-50/80 dark:bg-indigo-900/20">
                                    <svg class="w-3.5 h-3.5 text-indigo-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>
                                    <div>
                                        <p class="text-xs font-semibold text-indigo-800 dark:text-indigo-300">Learning Objective</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">What students should know or be able to do</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2 p-2 rounded-lg bg-indigo-50/80 dark:bg-indigo-900/20">
                                    <svg class="w-3.5 h-3.5 text-indigo-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>
                                    <div>
                                        <p class="text-xs font-semibold text-indigo-800 dark:text-indigo-300">Teaching Strategies</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Methods you'll use (e.g., collaborative, inquiry-based)</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2 p-2 rounded-lg bg-indigo-50/80 dark:bg-indigo-900/20">
                                    <svg class="w-3.5 h-3.5 text-indigo-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>
                                    <div>
                                        <p class="text-xs font-semibold text-indigo-800 dark:text-indigo-300">Materials & Resources</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Visual aids, worksheets, tech tools, etc.</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2 p-2 rounded-lg bg-indigo-50/80 dark:bg-indigo-900/20">
                                    <svg class="w-3.5 h-3.5 text-indigo-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>
                                    <div>
                                        <p class="text-xs font-semibold text-indigo-800 dark:text-indigo-300">Assessment Methods</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">How you'll check for understanding</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2 p-2 rounded-lg bg-indigo-50/80 dark:bg-indigo-900/20">
                                    <svg class="w-3.5 h-3.5 text-indigo-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>
                                    <div>
                                        <p class="text-xs font-semibold text-indigo-800 dark:text-indigo-300">Lesson Procedure / Activities</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Step-by-step flow with time estimates</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2 p-2 rounded-lg bg-indigo-50/80 dark:bg-indigo-900/20">
                                    <svg class="w-3.5 h-3.5 text-indigo-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>
                                    <div>
                                        <p class="text-xs font-semibold text-indigo-800 dark:text-indigo-300">PPST / COT Alignment</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Which indicators the lesson addresses</p>
                                    </div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-3 italic">Tip: The more specific your lesson plan, the better the AI insights will be.</p>
                        </div>
                    </div>

                    <form action="{{ route('teacher.observations.upload-lesson-plan', $observation) }}" method="POST" enctype="multipart/form-data"
                          x-data="{ submitting: false }"
                          x-on:submit="submitting = true">
                        @csrf
                        <div class="flex items-center gap-3">
                            <input type="file" name="lesson_plan_file" id="lesson_plan" accept=".pdf,.doc,.docx"
                                   class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-indigo-100 file:text-indigo-700 dark:file:bg-indigo-900/30 dark:file:text-indigo-300 hover:file:bg-indigo-200 dark:hover:file:bg-indigo-800/40 transition-colors cursor-pointer">
                            <button type="submit"
                                    :disabled="submitting"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-700 transition-colors shrink-0 disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg x-show="submitting" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span x-show="!submitting">Upload</span>
                                <span x-show="submitting">Uploading...</span>
                            </button>
                        </div>
                        @error('lesson_plan_file')
                            <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">Accepted formats: PDF, DOC, DOCX (max 20MB)</p>
                    </form>
                </div>
            @endif

            @if($observation->preObservationPlanning)
                <div class="space-y-3">
                    @if($observation->preObservationPlanning->lesson_plan_file)
                    <div class="flex items-center justify-between p-4 rounded-xl bg-gradient-to-r from-emerald-50 to-green-50 dark:from-emerald-900/20 dark:to-green-900/20 border border-emerald-100 dark:border-emerald-800">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white dark:bg-gray-800 shadow-sm flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Lesson Plan</p>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="inline-flex items-center gap-1 text-xs text-emerald-600 dark:text-emerald-400 font-medium">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Submitted
                                    </span>
                                    <span class="text-gray-300 dark:text-gray-600">&middot;</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ preg_replace('/^\d+_/', '', basename($observation->preObservationPlanning->lesson_plan_file)) }}</span>
                                </div>
                            </div>
                        </div>
                        <a href="{{ asset('storage/' . $observation->preObservationPlanning->lesson_plan_file) }}" target="_blank"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl hover:bg-indigo-100 dark:hover:bg-indigo-800/30 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            View File
                        </a>
                    </div>
                    @endif
                    @if($observation->preObservationPlanning->ai_insights)
                    <div class="p-4 rounded-xl bg-gradient-to-br from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20 border border-purple-100 dark:border-purple-800">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-2 h-2 rounded-full bg-purple-500"></div>
                            <span class="text-xs font-semibold text-purple-700 dark:text-purple-400 uppercase tracking-wider">AI Insights/Supervisor's Insights</span>
                        </div>
                        @php $insightSections = $observation->preObservationPlanning->insightsSections(); @endphp
                        @if(isset($insightSections['raw']))
                            <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-wrap">{{ $insightSections['raw'] }}</p>
                        @else
                            {!! view('partials.ai-insights-display', ['sections' => $insightSections])->render() !!}
                        @endif
                    </div>
                    @endif
                    @if($observation->preObservationPlanning->suggested_focus)
                    <div class="p-4 rounded-xl bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20 border border-blue-100 dark:border-blue-800">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-8 h-8 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                            </div>
                            <span class="text-xs font-semibold text-blue-700 dark:text-blue-400 uppercase tracking-wider">Suggested Focus</span>
                        </div>
                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $observation->preObservationPlanning->suggested_focus }}</p>
                    </div>
                    @endif
                    @if($observation->preObservationPlanning->supervisor_notes)
                    <div class="p-4 rounded-xl bg-gradient-to-br from-amber-50 to-yellow-50 dark:from-amber-900/20 dark:to-yellow-900/20 border border-amber-100 dark:border-amber-800">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                                <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </div>
                            <span class="text-xs font-semibold text-amber-700 dark:text-amber-400 uppercase tracking-wider">Supervisor Notes</span>
                        </div>
                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $observation->preObservationPlanning->supervisor_notes }}</p>
                    </div>
                    @endif
                </div>
            @else
                @if($observation->stage !== 'pre_observation_planning')
                    <div class="flex flex-col items-center py-8 text-center">
                        <svg class="w-10 h-10 text-gray-300 dark:text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <p class="text-sm text-gray-500 dark:text-gray-400">No pre-observation planning data recorded yet.</p>
                    </div>
                @endif
            @endif
        </div>

        <!-- Pre-Conference -->
        @if($observation->preConference)
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6" x-show="detailFilter === 'all' || detailFilter === 'pre_conference'">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/></svg>
                </div>
                <div class="flex-1">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Pre-Conference</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Pre-observation discussion between teacher and supervisor</p>
                </div>
                @if($observation->preObservationPlanning?->ai_insights_reviewed)
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                        AI Reviewed
                    </span>
                @endif
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @if($observation->preObservationPlanning?->ai_insights)
                <div class="md:col-span-2 p-4 rounded-xl bg-gradient-to-br from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20 border border-purple-100 dark:border-purple-800">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-2 h-2 rounded-full bg-purple-500"></div>
                        <span class="text-xs font-semibold text-purple-700 dark:text-purple-400 uppercase tracking-wider">AI Pre-Observation Insights</span>
                    </div>
                    @php $insightSections = $observation->preObservationPlanning->insightsSections(); @endphp
                    @if(isset($insightSections['raw']))
                        <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap leading-relaxed">{{ $insightSections['raw'] }}</p>
                    @else
                        {!! view('partials.ai-insights-display', ['sections' => $insightSections])->render() !!}
                    @endif
                </div>
                @endif
                @if($observation->preConference->conference_date)
                <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Conference Date</span>
                    <p class="text-gray-900 dark:text-gray-100 font-medium mt-1">{{ $observation->preConference->conference_date->format('M d, Y') }}</p>
                </div>
                @endif
                @if($observation->preConference->topic)
                <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Topic</span>
                    <p class="text-gray-900 dark:text-gray-100 font-medium mt-1">{{ $observation->preConference->topic }}</p>
                </div>
                @endif
                @if($observation->preConference->learning_objectives)
                <div class="md:col-span-2 p-4 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Learning Objectives</span>
                    <p class="text-sm text-gray-700 dark:text-gray-300 mt-1 whitespace-pre-wrap">{{ $observation->preConference->learning_objectives }}</p>
                </div>
                @endif
                @if($observation->preConference->teaching_strategies)
                <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Teaching Strategies</span>
                    <p class="text-sm text-gray-700 dark:text-gray-300 mt-1 whitespace-pre-wrap">{{ $observation->preConference->teaching_strategies }}</p>
                </div>
                @endif
                @if($observation->preConference->assessment_activity)
                <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Assessment/Activity</span>
                    <p class="text-sm text-gray-700 dark:text-gray-300 mt-1 whitespace-pre-wrap">{{ $observation->preConference->assessment_activity }}</p>
                </div>
                @endif
                @if($observation->preConference->discussion_notes)
                <div class="md:col-span-2 p-4 rounded-xl bg-gradient-to-br from-amber-50 to-yellow-50 dark:from-amber-900/20 dark:to-yellow-900/20 border border-amber-100 dark:border-amber-800">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        <span class="text-sm font-semibold text-amber-800 dark:text-amber-300">Discussion Notes</span>
                    </div>
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $observation->preConference->discussion_notes }}</p>
                </div>
                @endif
                @if($observation->preConference->finalized_focus)
                <div class="md:col-span-2 p-4 rounded-xl bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20 border border-blue-100 dark:border-blue-800">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        <span class="text-sm font-semibold text-blue-800 dark:text-blue-300">Finalized Focus</span>
                    </div>
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $observation->preConference->finalized_focus }}</p>
                </div>
                @endif
                @if($observation->preConference->expected_challenges)
                <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Expected Challenges</span>
                    <p class="text-sm text-gray-700 dark:text-gray-300 mt-1 whitespace-pre-wrap">{{ $observation->preConference->expected_challenges }}</p>
                </div>
                @endif
                @if($observation->preConference->feedback_areas)
                <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Feedback Areas</span>
                    <p class="text-sm text-gray-700 dark:text-gray-300 mt-1 whitespace-pre-wrap">{{ $observation->preConference->feedback_areas }}</p>
                </div>
                @endif
                @if($observation->preConference->teacher_reflection)
                <div class="md:col-span-2 p-4 rounded-xl bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 border border-emerald-100 dark:border-emerald-800">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span class="text-sm font-semibold text-emerald-800 dark:text-emerald-300">Teacher Reflection</span>
                    </div>
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $observation->preConference->teacher_reflection }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Observation (COT Ratings) -->
        @if($observation->cotRatings && $observation->cotRatings->count() > 0)
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6" x-show="detailFilter === 'all' || detailFilter === 'ratings'">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Observation Ratings</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">COT-based performance assessment</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider font-medium">Overall Score</p>
                    <div class="flex items-end gap-1">
                        <p class="text-gray-900 dark:text-gray-100 font-bold text-3xl tracking-tight">{{ number_format($observation->overall_score, 1) }}</p>
                        <p class="text-gray-400 dark:text-gray-500 font-medium text-lg mb-0.5">/ 6</p>
                    </div>
                    @php
                        $descTotal = \App\Models\CotRating::descriptiveTotal((float) $observation->overall_score);
                        $descClass = $observation->overall_score >= 5.5 ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : ($observation->overall_score >= 4.5 ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : ($observation->overall_score >= 3.5 ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400' : ($observation->overall_score >= 2.5 ? 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400' : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400')));
                        $scorePct = ($observation->overall_score / 6) * 100;
                        $scoreColor = $scorePct >= 80 ? 'text-emerald-600' : ($scorePct >= 60 ? 'text-amber-600' : 'text-red-600');
                        $scoreBg = $scorePct >= 80 ? 'bg-emerald-500' : ($scorePct >= 60 ? 'bg-amber-500' : 'bg-red-500');
                    @endphp
                    <div class="w-24 h-1.5 bg-gray-100 dark:bg-gray-700 rounded-full mt-1 ml-auto">
                        <div class="{{ $scoreBg }} h-1.5 rounded-full" style="width: {{ $scorePct }}%"></div>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider mt-1.5 ml-auto {{ $descClass }}">{{ $descTotal }}</span>
                </div>
            </div>
            <div class="space-y-2.5">
                @foreach($observation->cotRatings as $rating)
                    @php
                        $na = $rating->not_applicable;
                        $no = $rating->not_observed;
                        $r = ($na || $no) ? null : $rating->rating;
                        $rPct = $r ? ($r / 6) * 100 : 0;
                        $rColor = !$r ? ($na ? 'border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/10' : 'bg-gray-100 dark:bg-gray-800 border-gray-200 dark:border-gray-700') : ($r >= 5 ? 'border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/20' : ($r >= 4 ? 'border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20' : ($r >= 3 ? 'border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20' : 'border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20')));
                        $rBadge = !$r ? ($na ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400') : ($r >= 5 ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : ($r >= 4 ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : ($r >= 3 ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400' : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400')));
                    @endphp
                    <div class="rounded-xl p-4 border {{ $rColor }}">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $rating->indicator }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $rating->domain }}</p>
                                @if($rating->comments)
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2 pt-2 border-t border-gray-200/60 dark:border-gray-700/60">{{ $rating->comments }}</p>
                                @endif
                            </div>
                            <div class="text-center shrink-0">
                                <div class="w-14 h-14 rounded-xl {{ $rBadge }} flex items-center justify-center">
                                    <span class="text-lg font-bold">{{ $r ? number_format($r, 1) : ($na ? 'N/A' : 'NO') }}</span>
                                </div>
                                <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-0.5">{{ $na ? 'Not Applicable' : ($no ? 'Not Observed' : '/ 6') }}</p>
                            </div>
                        </div>
                        @if($r)
                        <div class="mt-2 w-full h-1 bg-gray-100 dark:bg-gray-700 rounded-full">
                            <div class="h-1 rounded-full {{ $r >= 5 ? 'bg-emerald-500' : ($r >= 4 ? 'bg-blue-500' : ($r >= 3 ? 'bg-amber-500' : 'bg-red-500')) }}" style="width: {{ $rPct }}%"></div>
                        </div>
                        @endif
                    </div>
                @endforeach
                @if($observation->cotRatings->contains(fn($x) => $x->not_observed || $x->not_applicable))
                <div class="pt-1 flex flex-wrap items-center gap-x-5 gap-y-1.5 text-[11px] text-gray-400 dark:text-gray-500">
                    <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 inline-block shrink-0"></span> NO — Not observed</span>
                    <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-amber-100 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 inline-block shrink-0"></span> N/A — Not applicable (excluded from score)</span>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Post-Conference -->
        @if($observation->postConference)
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6" x-show="detailFilter === 'all' || detailFilter === 'post_conference'">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Post-Conference</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Feedback and action plan from supervisor</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @if($observation->postConference->conference_date)
                <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Conference Date</span>
                    <p class="text-gray-900 dark:text-gray-100 font-medium mt-1">{{ $observation->postConference->conference_date->format('M d, Y') }}</p>
                </div>
                @endif
                @if($observation->postConference->start_time_label || $observation->postConference->location)
                <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Conference Schedule</span>
                    <p class="text-gray-900 dark:text-gray-100 font-medium mt-1">
                        @if($observation->postConference->start_time_label)
                            {{ $observation->postConference->start_time_label }}
                            @if($observation->postConference->end_time_label) - {{ $observation->postConference->end_time_label }} @endif
                        @endif
                        @if($observation->postConference->location)
                            @if($observation->postConference->start_time_label) &middot; @endif
                            {{ $observation->postConference->location }}
                        @endif
                    </p>
                    @if($observation->postConference->mode)
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 capitalize">{{ str_replace('_', ' ', $observation->postConference->mode) }}</p>
                    @endif
                </div>
                @endif
                @if($observation->postConference->feedback)
                <div class="md:col-span-2 p-4 rounded-xl bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 border border-emerald-100 dark:border-emerald-800">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/></svg>
                        <span class="text-sm font-semibold text-emerald-800 dark:text-emerald-300">Supervisor Feedback</span>
                    </div>
                    <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">{{ $observation->postConference->feedback }}</p>
                </div>
                @endif
                @if($observation->postConference->ai_comparison)
                <div class="md:col-span-2 p-4 rounded-xl bg-gradient-to-br from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20 border border-purple-100 dark:border-purple-800">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                        <span class="text-sm font-semibold text-purple-800 dark:text-purple-300">AI Comparison (Plan vs Actual)</span>
                    </div>
                    <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">{{ $observation->postConference->ai_comparison }}</p>
                </div>
                @endif
                @if($observation->postConference->action_plan)
                <div class="md:col-span-2 p-4 rounded-xl bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20 border border-blue-100 dark:border-blue-800">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        <span class="text-sm font-semibold text-blue-800 dark:text-blue-300">Action Plan</span>
                    </div>
                    <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">{{ $observation->postConference->action_plan }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
        </div>

        {{-- Right rail: schedule details --}}
        <aside class="lg:col-span-1 lg:order-none lg:sticky lg:top-24 space-y-6 min-w-0" x-data="{ editing: false, saving: false }">
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Schedule Details</h2>
                </div>
                <dl class="divide-y divide-gray-100 dark:divide-gray-800">
                    <div class="px-5 py-3.5 flex items-start justify-between gap-3">
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 shrink-0 mt-0.5">Date</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-gray-100 text-right">
                            {{ $observation->observation_date?->format('M d, Y') ?? 'No date' }}
                        </dd>
                    </div>
                    @if($observation->has_time_schedule)
                    <div class="px-5 py-3.5 flex items-start justify-between gap-3">
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 shrink-0 mt-0.5">Time</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-gray-100 text-right">
                            {{ $observation->start_time_label }}@if($observation->end_time_label) - {{ $observation->end_time_label }}@endif
                        </dd>
                    </div>
                    @endif
                    <div class="px-5 py-3.5 flex items-start justify-between gap-3">
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 shrink-0 mt-0.5">Type</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-gray-100 text-right capitalize">
                            {{ str_replace('_', ' ', $observation->observation_type) }}
                        </dd>
                    </div>
                    @if($observation->observation_mode)
                    <div class="px-5 py-3.5 flex items-start justify-between gap-3">
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 shrink-0 mt-0.5">Mode</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-gray-100 text-right capitalize">
                            {{ str_replace('_', ' ', $observation->observation_mode) }}
                        </dd>
                    </div>
                    @endif
                    @if($observation->subject)
                    <div class="px-5 py-3.5 flex items-start justify-between gap-3">
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 shrink-0 mt-0.5">Subject</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-gray-100 text-right">
                            {{ $observation->subject }}@if($observation->grade_level) &middot; Gr. {{ $observation->grade_level }}@endif
                        </dd>
                    </div>
                    @endif
                    <div class="px-5 py-3.5 flex items-start justify-between gap-3">
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 shrink-0 mt-0.5">Supervisor</dt>
                        <dd class="text-sm font-medium text-gray-900 dark:text-gray-100 text-right">
                            {{ $observation->observer?->name ?? 'Unknown' }}
                        </dd>
                    </div>

                    {{-- Room / Location --}}
                    <div class="px-5 py-3.5">
                        <div class="flex items-center justify-between mb-1.5">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Room / Location</dt>
                            @if(!in_array($observation->status, ['completed', 'cancelled']))
                                <button type="button" x-show="!editing" @click="editing = true" class="inline-flex items-center gap-1 text-[11px] font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 transition-colors">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    Edit
                                </button>
                            @endif
                        </div>

                        {{-- View mode --}}
                        <div x-show="!editing">
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                {{ $observation->location ?? 'Not set yet' }}
                            </p>
                            @if(!$observation->location && $defaultRoom)
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1.5 flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span>
                                    Default room: <span class="font-medium">{{ $defaultRoom }}</span>
                                </p>
                            @endif
                        </div>

                        {{-- Edit mode --}}
                        <template x-if="editing">
                            <form method="POST" action="{{ route('teacher.observations.update-location', $observation) }}" x-on:submit="saving = true" class="mt-1">
                                @csrf
                                @method('PATCH')
                                <div class="flex gap-2">
                                    <input type="text" name="location" x-ref="locationInput" x-init="$nextTick(() => $refs.locationInput.focus())"
                                           value="{{ $observation->location ?? $defaultRoom ?? '' }}"
                                           placeholder="e.g. Room 201, Building A"
                                           class="flex-1 min-w-0 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 rounded-lg focus:border-indigo-500 focus:ring-indigo-500 outline-none" required>
                                </div>
                                <div class="flex items-center gap-2 mt-2">
                                    <button type="submit" :disabled="saving"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold transition-colors disabled:opacity-50">
                                        <span x-show="saving" class="animate-spin inline-block h-3 w-3 border-2 border-white/40 border-t-white rounded-full"></span>
                                        <span x-show="!saving">Save</span>
                                        <span x-show="saving">Saving...</span>
                                    </button>
                                    <button type="button" @click="editing = false"
                                            class="px-3 py-1.5 text-xs font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </template>
                    </div>
                </dl>
            </div>
        </aside>
    </div>
</div>
@endsection