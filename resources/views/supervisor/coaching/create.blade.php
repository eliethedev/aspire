@extends('layouts.supervisor')

@section('title', 'Create Coaching Agreement')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6">
    <!-- Breadcrumb -->
    <nav class="mb-6 text-sm">
        <ol class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
            <li><a href="{{ route('supervisor.observations.index') }}" class="hover:text-indigo-600 dark:text-indigo-400 transition-colors">Observations</a></li>
            <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"/></svg></li>
            <li><a href="{{ route('supervisor.observations.show', $observation) }}" class="hover:text-indigo-600 dark:text-indigo-400 transition-colors">Observation Details</a></li>
            <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"/></svg></li>
            <li class="text-gray-900 dark:text-gray-100 font-medium">Create Coaching Agreement</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Create Coaching Agreement</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-1">
            {{ $observation->observee->user->name ?? 'Unknown' }}
            &middot; {{ $observation->observation_date->format('M d, Y') }}
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

        <!-- LEFT — Coaching Agreement Form -->
        <form method="POST" action="{{ route('supervisor.coaching.store') }}" class="lg:col-span-2 space-y-6"
              x-data="{ submitting: false }" x-on:submit="submitting = true">
            @csrf
            <input type="hidden" name="observation_id" value="{{ $observation->id }}">

            <!-- Focus Areas -->
            @php
                $focusTexts = collect($suggestedFocusAreas)->pluck('text')->all();
                $focusSources = collect($suggestedFocusAreas)
                    ->map(fn ($s) => ['label' => $s['label'], 'badge' => $s['badge']])
                    ->all();
                if (empty($focusTexts)) {
                    $focusTexts = [''];
                    $focusSources = [null];
                }
            @endphp
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6"
                 x-data="{
                     areas: {{ Js::from($focusTexts) }},
                     sources: {{ Js::from($focusSources) }}
                 }">
                <div class="flex items-center justify-between mb-3">
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100">Focus Areas</label>
                    @if(count($suggestedFocusAreas))
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-purple-100 text-purple-700">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M10 1l1.93 5.814a2 2 0 001.256 1.256L19 10l-5.814 1.93a2 2 0 00-1.256 1.256L10 19l-1.93-5.814a2 2 0 00-1.256-1.256L1 10l5.814-1.93a2 2 0 001.256-1.256L10 1z"/></svg>
                        Auto-generated
                    </span>
                    @endif
                </div>
                @if(count($suggestedFocusAreas))
                <div class="flex items-start gap-2 bg-purple-50 dark:bg-purple-900/20 border border-purple-100 dark:border-purple-800 rounded-lg p-3 mb-4">
                    <svg class="w-4 h-4 text-purple-500 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M10 1l1.93 5.814a2 2 0 001.256 1.256L19 10l-5.814 1.93a2 2 0 00-1.256 1.256L10 19l-1.93-5.814a2 2 0 00-1.256-1.256L1 10l5.814-1.93a2 2 0 001.256-1.256L10 1z"/></svg>
                    <p class="text-xs text-purple-800 dark:text-purple-300">
                        These were generated from this observation's data &mdash; AI feedback insights, low COT ratings, and post-conference next steps.
                        Each item shows where it came from. Review and adjust before creating the agreement.
                    </p>
                </div>
                @else
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Key areas the teacher should focus on improving.</p>
                @endif
                <template x-for="(area, index) in areas" :key="index">
                    <div class="flex items-center gap-2 mb-2">
                        <input type="text" :name="'focus_areas[' + index + ']'" x-model="areas[index]"
                               class="flex-1 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                               placeholder="e.g., Classroom Management">
                        <span x-show="sources[index]"
                              class="hidden sm:inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold shrink-0 max-w-[140px] truncate"
                              :class="sources[index] ? sources[index].badge : ''"
                              x-text="sources[index] ? sources[index].label : ''"
                              :title="sources[index] ? sources[index].label : ''"></span>
                        <button type="button" @click="areas.splice(index, 1); sources.splice(index, 1)" x-show="areas.length > 1"
                                class="p-2 text-red-400 dark:text-red-400 hover:text-red-600 dark:text-red-400 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </template>
                <button type="button" @click="areas.push(''); sources.push(null)"
                        class="mt-1 inline-flex items-center gap-1 text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Add Focus Area
                </button>
            </div>

            <!-- Action Steps -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6" x-data="{ steps: [''] }">
                <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Action Steps</label>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Specific actions the teacher will take to improve.</p>
                <template x-for="(step, index) in steps" :key="index">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 text-xs font-bold shrink-0" x-text="index + 1"></span>
                        <input type="text" :name="'action_steps[' + index + ']'" x-model="steps[index]"
                               class="flex-1 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                               placeholder="e.g., Implement a positive behavior support system">
                        <button type="button" @click="steps.splice(index, 1)" x-show="steps.length > 1"
                                class="p-2 text-red-400 dark:text-red-400 hover:text-red-600 dark:text-red-400 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </template>
                <button type="button" @click="steps.push('')"
                        class="mt-1 inline-flex items-center gap-1 text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                    Add Action Step
                </button>
            </div>

            <!-- Resources & Timeline -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Resources Needed</label>
                    <textarea name="resources_needed" rows="3"
                              class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                              placeholder="Materials, training, or support required..."></textarea>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Timeline</label>
                    <input type="text" name="timeline"
                           class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                           placeholder="e.g., 4 weeks, End of Quarter 2">
                </div>
            </div>

            <!-- Success Indicators & Notes -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Success Indicators</label>
                    <textarea name="success_indicators" rows="3"
                              class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                              placeholder="How will success be measured?"></textarea>
                </div>
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                    <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Supervisor Notes</label>
                    <textarea name="supervisor_notes" rows="3"
                              class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                              placeholder="Internal notes about this agreement..."></textarea>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('supervisor.observations.show', $observation) }}"
                   class="px-5 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg font-medium hover:bg-gray-50 dark:bg-gray-800 transition-colors">
                    Cancel
                </a>
                <button type="submit" :disabled="submitting"
                        :class="submitting ? 'opacity-60 cursor-not-allowed' : ''"
                        class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors">
                    <span x-show="!submitting">Create Agreement</span>
                    <span x-show="submitting" class="flex items-center gap-2">
                        <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Creating...
                    </span>
                </button>
            </div>
        </form>

        <!-- RIGHT — Observation Data & History -->
        <aside class="space-y-6">
            <!-- Post-Conference Context -->
            @if($observation->postConference)
            <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800 rounded-xl p-5">
                <h3 class="text-sm font-semibold text-indigo-900 dark:text-indigo-200 dark:text-indigo-300 mb-2">Post-Conference Reference</h3>
                <div class="space-y-2 text-sm text-indigo-800 dark:text-indigo-200 dark:text-indigo-300">
                    @if($observation->postConference->challenges_facing_teacher)
                        <p><strong>Challenges:</strong> {{ Str::limit($observation->postConference->challenges_facing_teacher, 150) }}</p>
                    @endif
                    @if($observation->postConference->prioritized_next_steps)
                        <p><strong>Next Steps:</strong> {{ Str::limit($observation->postConference->prioritized_next_steps, 150) }}</p>
                    @endif
                </div>
            </div>
            @endif

            <!-- Current COT Results & AI Insights -->
            @if($observation->cotRatings->isNotEmpty())
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">This Observation's COT Results</h2>
                    <div class="flex items-baseline gap-1">
                        <span class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ number_format($observation->overall_score, 1) }}</span>
                        <span class="text-xs text-gray-400 dark:text-gray-500">/ 6 overall</span>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-2">
                    @foreach($observation->cotRatings as $rating)
                        @php
                            $r = $rating->not_observed ? null : $rating->rating;
                            $scoreBadge = !$r ? 'bg-gray-100 text-gray-500' : ($r >= 5 ? 'bg-emerald-100 text-emerald-700' : ($r >= 4 ? 'bg-blue-100 text-blue-700' : ($r >= 3 ? 'bg-orange-100 text-orange-700' : 'bg-red-100 text-red-700')));
                        @endphp
                        <div class="flex items-center gap-2 bg-gray-50 dark:bg-gray-800 rounded-lg px-3 py-2 border border-gray-100 dark:border-gray-700">
                            @if($rating->indicator_code)
                            <span class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400 shrink-0">{{ $rating->indicator_code }}</span>
                            @endif
                            <span class="text-xs text-gray-600 dark:text-gray-300 truncate flex-1" title="{{ $rating->indicator }}">{{ $rating->indicator }}</span>
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-md text-[11px] font-bold shrink-0 {{ $scoreBadge }}">{{ $r ?? 'NO' }}</span>
                        </div>
                    @endforeach
                </div>

                @if($observation->aiFeedbacks->isNotEmpty())
                <div class="mt-4 space-y-3">
                    @foreach($observation->aiFeedbacks->take(3) as $feedback)
                    <div class="bg-purple-50 dark:bg-purple-900/20 border border-purple-100 dark:border-purple-800 rounded-lg p-3">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-purple-600 dark:text-purple-300 mb-1">
                            AI Insight
                            @if($feedback->cotRating)
                                &middot; {{ $feedback->cotRating->indicator_code }}
                            @endif
                            @if($feedback->confidence_score)
                                &middot; {{ number_format($feedback->confidence_score * 100, 0) }}% confidence
                            @endif
                        </p>
                        @if($feedback->analysis)
                        <p class="text-xs text-gray-700 dark:text-gray-300 mb-2">{{ Str::limit($feedback->analysis, 280) }}</p>
                        @endif
                        @if(!empty($feedback->recommendations))
                        <ul class="space-y-1">
                            @foreach(array_slice($feedback->recommendations, 0, 3) as $rec)
                            <li class="flex items-start gap-1.5 text-xs text-gray-600 dark:text-gray-300">
                                <svg class="w-3 h-3 text-purple-400 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                {{ Str::limit($rec, 160) }}
                            </li>
                            @endforeach
                        </ul>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
            @endif

            <!-- Teacher's Past Data -->
            @php $hasPastData = $pastObservations->isNotEmpty() || $weakIndicators->isNotEmpty() || $pastAgreements->isNotEmpty(); @endphp
            @if($hasPastData)
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4">Past Data</h2>

                @if($pastObservations->isNotEmpty())
                <div class="mb-5">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-2">Recent Observation Scores</p>
                    @php $trend = $pastObservations->sortBy('observation_date')->values(); @endphp
                    <div class="flex items-end gap-1.5 h-14 mb-3">
                        @foreach($trend as $past)
                        <div class="flex-1 max-w-[48px] flex flex-col justify-end h-full" title="{{ $past->observation_date?->format('M d, Y') }} — {{ number_format($past->overall_score, 1) }}/6">
                            <div class="{{ $past->overall_score >= 5 ? 'bg-emerald-400' : ($past->overall_score >= 4 ? 'bg-blue-400' : ($past->overall_score >= 3 ? 'bg-orange-400' : 'bg-red-400')) }} rounded-t"
                                 style="height: {{ max(8, ($past->overall_score / 6) * 100) }}%"></div>
                        </div>
                        @endforeach
                        @if($observation->overall_score !== null)
                        <div class="flex-1 max-w-[48px] flex flex-col justify-end h-full" title="This observation — {{ number_format($observation->overall_score, 1) }}/6">
                            <div class="bg-indigo-500 rounded-t ring-2 ring-indigo-200" style="height: {{ max(8, ($observation->overall_score / 6) * 100) }}%"></div>
                        </div>
                        @endif
                    </div>
                    <div class="space-y-1.5">
                        @foreach($pastObservations as $past)
                        <div class="flex items-center justify-between gap-3 text-xs py-1 border-b border-gray-50 dark:border-gray-800 last:border-0">
                            <span class="text-gray-600 dark:text-gray-300">{{ $past->observation_date?->format('M d, Y') }}</span>
                            <span class="text-gray-400 dark:text-gray-500 truncate hidden sm:block">{{ $past->observer?->name ?? '' }}</span>
                            @php
                                $ps = $past->overall_score;
                                $psBadge = $ps >= 5 ? 'bg-emerald-100 text-emerald-700' : ($ps >= 4 ? 'bg-blue-100 text-blue-700' : ($ps >= 3 ? 'bg-orange-100 text-orange-700' : 'bg-red-100 text-red-700'));
                            @endphp
                            <span class="inline-flex items-center justify-center px-1.5 min-w-[34px] h-5 rounded text-[11px] font-bold {{ $psBadge }}">{{ number_format($ps, 1) }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($weakIndicators->isNotEmpty())
                <div class="mb-5">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-2">Recurring Areas Needing Support</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($weakIndicators as $indicator)
                        <span class="inline-flex items-center gap-1.5 bg-orange-50 border border-orange-100 rounded-full pl-2.5 pr-1.5 py-1 max-w-full">
                            <span class="text-xs text-orange-900 truncate" title="{{ $indicator['indicator'] }} ({{ $indicator['domain'] }})">{{ $indicator['indicator'] }}</span>
                            <span class="inline-flex items-center justify-center px-1.5 h-[18px] rounded-full bg-orange-500 text-white text-[10px] font-bold shrink-0">
                                {{ $indicator['count'] }}&times;
                            </span>
                        </span>
                        @endforeach
                    </div>
                    <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1.5">Rated 3 or below / Not Observed across past observations.</p>
                </div>
                @endif

                @if($pastAgreements->isNotEmpty())
                <div class="@unless($weakIndicators->isEmpty() && $pastObservations->isEmpty()) pt-4 border-t border-gray-100 dark:border-gray-800 @endunless">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-2">Previous Coaching Agreements</p>
                    <div class="space-y-2">
                        @foreach($pastAgreements as $agreement)
                        <div class="flex items-start justify-between gap-3 bg-gray-50 dark:bg-gray-800 rounded-lg px-3 py-2 border border-gray-100 dark:border-gray-700">
                            <div class="min-w-0">
                                <p class="text-xs text-gray-600 dark:text-gray-300 truncate">
                                    @if(!empty($agreement->focus_areas))
                                        {{ implode(' · ', array_slice($agreement->focus_areas, 0, 2)) }}{{ count($agreement->focus_areas) > 2 ? ' +' . (count($agreement->focus_areas) - 2) : '' }}
                                    @else
                                        No focus areas recorded
                                    @endif
                                </p>
                                <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-0.5">Created {{ $agreement->created_at->format('M d, Y') }}</p>
                            </div>
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full shrink-0 {{ $agreement->statusBadgeClass() }}">{{ ucfirst($agreement->status) }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            @endif

            <p class="text-[10px] text-gray-400 dark:text-gray-500 text-center px-4">
                Reference data used to auto-generate the suggested focus areas.
            </p>
        </aside>
    </div>
</div>
@endsection
