@extends('layouts.teacher')

@section('title', 'Classroom Observation')

@push('styles')
<style>
    .rating-btn { transition: all 0.15s ease; min-width: 2.75rem; cursor: pointer; }
    .rating-btn:hover { transform: scale(1.08); box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .rating-btn.active { transform: scale(1.12); box-shadow: 0 2px 12px rgba(34,197,94,0.3); }
    .rating-btn-no { transition: all 0.15s ease; cursor: pointer; }
    .rating-btn-no:hover { transform: scale(1.08); box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .rating-btn-no.active { background-color: #6b7280; color: white; border-color: #6b7280; transform: scale(1.12); }
    .rating-btn-na { transition: all 0.15s ease; cursor: pointer; }
    .rating-btn-na:hover { transform: scale(1.08); box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
    .rating-btn-na.active { background-color: #f59e0b; color: white; border-color: #f59e0b; transform: scale(1.12); }
    .indicator-row { transition: background-color 0.15s ease; }
    .indicator-row:hover { background-color: #f9fafb; }
    .indicator-row.selected { background-color: #eef2ff; }
    .indicator-row.no-selected { background-color: #f9fafb; }
    .indicator-row.na-selected { background-color: #fffbeb; }
    .cot-table th { font-size: 0.7rem; letter-spacing: 0.05em; }
    .cot-table td, .cot-table th { vertical-align: middle; }
    .comment-toggle { transition: all 0.15s ease; cursor: pointer; }
    .comment-toggle:hover { background-color: #e0e7ff; color: #4f46e5; border-color: #818cf8; }
    .comment-toggle.has-comment { color: #4f46e5; background-color: #e0e7ff; border-color: #a5b4fc; }
    .comment-row { display: none; }
    .comment-row.open { display: table-row; }
    .comment-row td { padding: 0 1rem 0.75rem 3rem; background-color: #fafbff; }
    .comment-row textarea { width: 100%; font-size: 0.8rem; padding: 0.5rem; border: 1px solid #c7d2fe; border-radius: 0.5rem; resize: vertical; min-height: 3rem; outline: none; }
    .comment-row textarea:focus { border-color: #818cf8; box-shadow: 0 0 0 2px rgba(129,140,248,0.15); }
</style>
@endpush

@php
    $currentStage = $observation->stage;
    $indicatorIndex = 0;

    $groupedIndicators = [];
    foreach ($cotIndicators as $indicator) {
        $groupedIndicators[$indicator['domain']][] = $indicator;
    }

    $ratingValues = array_reverse(array_keys($ratingScale ?? config('cot.rating_scale', [])));
    $ratingColspan = 2 + count($ratingValues) + 2;
@endphp

@section('content')
<div class="max-w-7xl mx-auto px-6">
    <nav class="mb-6 text-sm">
        <ol class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
            <li><a href="{{ route('school-head.observations.index') }}" class="hover:text-indigo-600 dark:text-indigo-400 transition-colors">Evaluations</a></li>
            <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"/></svg></li>
            <li><a href="{{ route('school-head.observations.show', $observation) }}" class="hover:text-indigo-600 dark:text-indigo-400 transition-colors">Observation Details</a></li>
            <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"/></svg></li>
            <li class="text-gray-900 dark:text-gray-100 font-medium">Classroom Observation</li>
        </ol>
    </nav>

    @include('partials.observation-stepper', ['routeBase' => 'school-head'])

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
            {{ $observation->isTeacherObservation() ? 'Classroom Observation' : 'School Head Observation' }}
        </h1>
        <p class="text-gray-500 dark:text-gray-400 mt-1">
            Rate each indicator for {{ $observation->observee->user->name ?? 'Unknown' }}
            &middot; SY {{ $schoolYear }}
        </p>
    </div>

    @if($preConference && !$observation->isSchoolHeadObservation())
    <div x-data="{ open: true }" class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 mb-6">
        <button type="button" @click="open = !open"
                class="w-full flex items-center justify-between p-4 text-left">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Pre-Observation Conversation Summary</h2>
            <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div x-show="open" x-collapse>
            <div class="p-4 pt-0 border-t border-gray-100">
                <div class="space-y-2">
                    @if($preConference->finalized_focus)
                    <div>
                        <span class="text-gray-500 dark:text-gray-400 text-sm">Agreed Focus:</span>
                        <p class="text-gray-900 dark:text-gray-100 mt-1">{{ $preConference->finalized_focus }}</p>
                    </div>
                    @endif
                    @if($preConference->discussion_notes)
                    <div>
                        <span class="text-gray-500 dark:text-gray-400 text-sm">Discussion Notes:</span>
                        <p class="text-gray-900 dark:text-gray-100 mt-1 text-sm">{{ Str::limit($preConference->discussion_notes, 200) }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <form method="POST" action="{{ route('school-head.observations.storeObservationData', $observation) }}" class="space-y-6" enctype="multipart/form-data"
          x-data="{ submitting: false }" x-on:submit="submitting = true">
        @csrf

        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            @if($observation->isSchoolHeadObservation())
                <div class="p-4 border-b border-gray-100 bg-gradient-to-r from-indigo-50 to-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Observation Rating Sheet</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Rate the School Head's post-observation conference practices &middot; SY {{ $schoolYear }}</p>
                        </div>
                    </div>
                </div>

                @include('supervisor.observations.partials.epoc-form', compact('observation', 'epocEvaluation', 'schoolHead'))
            @else
            <div class="p-4 border-b border-gray-100 bg-gradient-to-r from-indigo-50 to-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Observation Rating Sheet</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Rate what you observed during the lesson &middot; SY {{ $schoolYear }}</p>
                    </div>
                    <button type="button" data-action="mark-all-no"
                            class="px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 rounded-lg transition-colors inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                        Mark All as NO
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm cot-table">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                            <th class="text-left px-4 py-3 text-gray-600 dark:text-gray-400 font-semibold w-8">#</th>
                            <th class="text-left px-4 py-3 text-gray-600 dark:text-gray-400 font-semibold">Indicator</th>
                            @foreach($ratingValues as $val)
                                @php $label = $ratingScale[$val] ?? ''; @endphp
                                <th class="text-center px-1 py-3 text-gray-600 dark:text-gray-400 font-semibold w-16">
                                    <div class="text-xs font-bold">{{ $val }}</div>
                                    @if($label)
                                        <div class="text-[10px] font-normal text-gray-400 dark:text-gray-500 leading-tight mt-0.5">{{ Str::limit($label, 12) }}</div>
                                    @endif
                                </th>
                            @endforeach
                            <th class="text-center px-1 py-3 text-gray-600 dark:text-gray-400 font-semibold w-14">
                                <div class="text-xs font-bold">NO</div>
                                <div class="text-[10px] font-normal text-gray-400 dark:text-gray-500 leading-tight mt-0.5">Not Obs.</div>
                            </th>
                            <th class="text-center px-1 py-3 text-gray-600 dark:text-gray-400 font-semibold w-14">
                                <div class="text-xs font-bold">N/A</div>
                                <div class="text-[10px] font-normal text-gray-400 dark:text-gray-500 leading-tight mt-0.5">Not Appl.</div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $indicatorIndex = 0; @endphp
                        @foreach($groupedIndicators as $domain => $indicators)
                            <tr class="bg-indigo-50/50 border-b border-indigo-100">
                                <td colspan="{{ $ratingColspan }}" class="px-4 py-2.5 text-sm font-semibold text-indigo-800 dark:text-indigo-200">{{ $domain }}</td>
                            </tr>
                            @foreach($indicators as $indicator)
                                @php
                                    $existingRating = $cotRatings->where('indicator_code', $indicator['code'])->first();
                                    $savedRating = $existingRating?->rating;
                                    $savedNo = $existingRating?->not_observed;
                                    $savedNa = $existingRating?->not_applicable;
                                    $savedComment = $existingRating?->comments ?? '';
                                @endphp
                                <tr class="indicator-row border-b border-gray-100" data-index="{{ $indicatorIndex }}" data-code="{{ $indicator['code'] }}">
                                    <td class="px-4 py-2.5 text-gray-400 dark:text-gray-500 text-xs align-top pt-3">{{ $indicatorIndex + 1 }}</td>
                                    <td class="px-4 py-2.5">
                                        <div class="flex items-start gap-2">
                                            <span class="text-xs font-mono font-semibold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 px-1.5 py-0.5 rounded whitespace-nowrap mt-0.5">{{ $indicator['code'] }}</span>
                                            <span class="text-gray-800 text-sm leading-relaxed">{{ $indicator['description'] }}</span>
                                            <button type="button" data-action="toggle-comment" data-index="{{ $indicatorIndex }}"
                                                    class="comment-toggle shrink-0 mt-0.5 inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium text-gray-400 border border-gray-200 hover:border-indigo-300 {{ $savedComment ? 'has-comment' : '' }}"
                                                    title="Add comment for this indicator">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                                                {{ $savedComment ? 'View Comment' : 'Add Comment' }}
                                            </button>
                                        </div>
                                        <input type="hidden" name="ratings[{{ $indicatorIndex }}][indicator_code]" value="{{ $indicator['code'] }}">
                                        <input type="hidden" name="ratings[{{ $indicatorIndex }}][domain]" value="{{ $indicator['domain'] }}">
                                        <input type="hidden" name="ratings[{{ $indicatorIndex }}][indicator]" value="{{ $indicator['description'] }}">
                                    </td>
                                    @foreach($ratingValues as $val)
                                        <td class="text-center px-0.5 py-2.5">
                                            <button type="button"
                                                    data-action="select-rating" data-index="{{ $indicatorIndex }}" data-value="{{ $val }}"
                                                    class="rating-btn w-10 h-10 rounded-full text-xs font-bold border-2 {{ $savedRating === $val && !$savedNo && !$savedNa ? 'active bg-green-600 text-white border-green-600' : 'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-400 border-gray-300 dark:border-gray-600 hover:border-green-400 hover:bg-green-50 dark:hover:bg-green-900/20' }}">
                                                {{ $val }}
                                            </button>
                                        </td>
                                    @endforeach
                                    <td class="text-center px-0.5 py-2.5">
                                        <button type="button"
                                                data-action="select-no" data-index="{{ $indicatorIndex }}"
                                                class="rating-btn-no w-10 h-10 rounded-lg text-[10px] font-bold border-2 {{ $savedNo ? 'active bg-gray-500 text-white border-gray-500' : 'bg-white dark:bg-gray-900 text-gray-500 dark:text-gray-500 border-gray-300 dark:border-gray-600 hover:border-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800' }}">
                                            NO
                                        </button>
                                    </td>
                                    <td class="text-center px-0.5 py-2.5">
                                        <button type="button"
                                                data-action="select-na" data-index="{{ $indicatorIndex }}"
                                                title="Not Applicable: indicator will not be recorded"
                                                class="rating-btn-na w-10 h-10 rounded-lg text-[10px] font-bold border-2 {{ $savedNa ? 'active bg-amber-400 text-white border-amber-400' : 'bg-white dark:bg-gray-900 text-amber-500 dark:text-amber-400 border-gray-300 dark:border-gray-600 hover:border-amber-400 hover:bg-amber-50 dark:hover:bg-amber-900/20' }}">
                                            N/A
                                        </button>
                                    </td>
                                </tr>
                                <tr class="comment-row" id="comment-row-{{ $indicatorIndex }}" data-index="{{ $indicatorIndex }}">
                                    <td colspan="{{ $ratingColspan }}">
                                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Comment / Observation Note</label>
                                        <textarea name="ratings[{{ $indicatorIndex }}][comments]" rows="2" data-comment-input="{{ $indicatorIndex }}"
                                                  placeholder="Add specific observations for this indicator...">{{ $savedComment }}</textarea>
                                    </td>
                                </tr>
                                @php $indicatorIndex++; @endphp
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-100 bg-gray-50 dark:bg-gray-800">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Other Comments</label>
                <textarea name="other_comments" rows="3"
                          class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"
                          placeholder="Additional comments or observations...">{{ $observation->cotRatings->first()?->comments ?? '' }}</textarea>
            </div>
            @endif
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm p-6 border border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Your Notes</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">These notes will be used during the Post-Observation Conference and will inform the AI analysis.</p>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Strengths Observed <span class="text-gray-400 dark:text-gray-500 font-normal">(What went well)</span></label>
                    <textarea name="star_notes" rows="4"
                              class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-green-500 text-sm"
                              placeholder="Document specific observations of effective teaching practices observed...">{{ $observation->postConference?->star_notes }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Private Notes</label>
                    <textarea name="supervisor_notes" rows="3"
                              class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-400 text-sm"
                              placeholder="Your private notes for future reference...">{{ $observation->postConference?->supervisor_notes }}</textarea>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm p-6 border border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Evidence Files <span class="text-gray-400 dark:text-gray-500 font-normal">(photos, videos, documents)</span></h2>
            <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center hover:border-indigo-400 transition-colors">
                <svg class="w-8 h-8 text-gray-400 dark:text-gray-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Drop files here or click to upload</p>
                <p class="text-xs text-gray-400 dark:text-gray-500">Upload photos, videos, or documents as evidence</p>
                <input type="file" name="evidence_files[]" multiple accept="image/*,video/*,.pdf,.doc,.docx"
                       class="mt-3 block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 dark:bg-indigo-900/20 file:text-indigo-700 hover:file:bg-indigo-100 dark:bg-indigo-900/30">
            </div>
            @if($observation->evidence_files)
                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach($observation->evidence_files as $file)
                        <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-800 rounded-lg p-3 border border-gray-200 dark:border-gray-700">
                            <a href="{{ asset('storage/' . $file['path']) }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 text-sm font-medium truncate">
                                {{ $file['original_name'] ?? basename($file['path']) }}
                            </a>
                            <span class="text-gray-400 dark:text-gray-500 text-xs shrink-0 ml-2">{{ isset($file['size']) ? number_format($file['size'] / 1024, 1) . ' KB' : '' }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-2 mb-3">
                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                <span class="text-xs text-gray-600 dark:text-gray-400">Saving will auto-generate AI analysis and redirect to the Post-Observation Conference page.</span>
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                @if(!$observation->isSchoolHeadObservation())
                <a href="{{ route('school-head.observations.preConference', $observation) }}"
                   class="flex-1 px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:bg-gray-800 font-medium text-sm text-center transition-colors">
                    <span class="flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        Back to Pre-Observation Conversation
                    </span>
                </a>
                @endif
                <button type="submit" :disabled="submitting"
                        :class="submitting ? 'opacity-60 cursor-not-allowed' : ''"
                        class="flex-[2] px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold text-sm shadow-sm transition-colors">
                    <span x-show="!submitting" class="flex items-center justify-center gap-2">
                        Save Ratings &amp; Continue to Post-Observation Conference
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </span>
                    <span x-show="submitting" class="flex items-center justify-center gap-2">
                        <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Saving...
                    </span>
                </button>
            </div>
        </div>
    </form>

    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <a href="{{ route('school-head.observations.show', $observation) }}"
               class="inline-flex items-center gap-2 px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:text-gray-100 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Details
            </a>
            <a href="{{ route('school-head.observations.index') }}"
               class="text-sm text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:text-gray-400 transition-colors">
                All Evaluations
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const totalIndicators = {{ $indicatorIndex }};
    const selections = {};

    function selectRating(index, value) {
        const row = document.querySelector('.indicator-row[data-index="' + index + '"]');
        if (!row) return;

        row.querySelectorAll('.rating-btn').forEach(function(btn) {
            btn.classList.remove('active', 'bg-green-600', 'text-white', 'border-green-600');
            btn.classList.add('bg-white', 'dark:bg-gray-900', 'text-gray-600', 'dark:text-gray-400', 'border-gray-300', 'dark:border-gray-600');
        });

        var btn = row.querySelector('.rating-btn[data-index="' + index + '"][data-value="' + value + '"]');
        if (btn) {
            btn.classList.remove('bg-white', 'dark:bg-gray-900', 'text-gray-600', 'dark:text-gray-400', 'border-gray-300', 'dark:border-gray-600');
            btn.classList.add('active', 'bg-green-600', 'text-white', 'border-green-600');
        }

        var noBtn = row.querySelector('.rating-btn-no');
        if (noBtn) {
            noBtn.classList.remove('active', 'bg-gray-500', 'text-white', 'border-gray-500');
            noBtn.classList.add('bg-white', 'dark:bg-gray-900', 'text-gray-500', 'dark:text-gray-500', 'border-gray-300', 'dark:border-gray-600');
        }

        var naBtn = row.querySelector('.rating-btn-na');
        if (naBtn) {
            naBtn.classList.remove('active', 'bg-amber-400', 'text-white', 'border-amber-400');
            naBtn.classList.add('bg-white', 'dark:bg-gray-900', 'text-amber-500', 'dark:text-amber-400', 'border-gray-300', 'dark:border-gray-600');
        }

        row.classList.remove('no-selected', 'na-selected');
        row.classList.add('selected');

        var existing = document.getElementById('rating-input-' + index);
        if (existing) existing.remove();

        var noHidden = document.getElementById('no-input-' + index);
        if (noHidden) noHidden.remove();

        var naHidden = document.getElementById('na-input-' + index);
        if (naHidden) naHidden.remove();

        var hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = 'ratings[' + index + '][rating]';
        hidden.value = value;
        hidden.id = 'rating-input-' + index;
        row.appendChild(hidden);

        selections[index] = 'rating';
        updateRowHidden(row, index);

        if (window.asAutoSaveDebounced) asAutoSaveDebounced('observation');
    }

    function selectNo(index) {
        var row = document.querySelector('.indicator-row[data-index="' + index + '"]');
        if (!row) return;

        row.querySelectorAll('.rating-btn').forEach(function(btn) {
            btn.classList.remove('active', 'bg-green-600', 'text-white', 'border-green-600');
            btn.classList.add('bg-white', 'dark:bg-gray-900', 'text-gray-600', 'dark:text-gray-400', 'border-gray-300', 'dark:border-gray-600');
        });

        var noBtn = row.querySelector('.rating-btn-no');
        if (noBtn) {
            noBtn.classList.remove('bg-white', 'dark:bg-gray-900', 'text-gray-500', 'dark:text-gray-500', 'border-gray-300', 'dark:border-gray-600');
            noBtn.classList.add('active', 'bg-gray-500', 'text-white', 'border-gray-500');
        }

        var naBtn = row.querySelector('.rating-btn-na');
        if (naBtn) {
            naBtn.classList.remove('active', 'bg-amber-400', 'text-white', 'border-amber-400');
            naBtn.classList.add('bg-white', 'dark:bg-gray-900', 'text-amber-500', 'dark:text-amber-400', 'border-gray-300', 'dark:border-gray-600');
        }

        row.classList.remove('selected', 'na-selected');
        row.classList.add('no-selected');

        var existing = document.getElementById('rating-input-' + index);
        if (existing) existing.remove();

        var naHidden = document.getElementById('na-input-' + index);
        if (naHidden) naHidden.remove();

        var noHidden = document.createElement('input');
        noHidden.type = 'hidden';
        noHidden.name = 'ratings[' + index + '][not_observed]';
        noHidden.value = '1';
        noHidden.id = 'no-input-' + index;
        row.appendChild(noHidden);

        selections[index] = 'no';
        updateRowHidden(row, index);

        if (window.asAutoSaveDebounced) asAutoSaveDebounced('observation');
    }

    function selectNA(index) {
        var row = document.querySelector('.indicator-row[data-index="' + index + '"]');
        if (!row) return;

        row.querySelectorAll('.rating-btn').forEach(function(btn) {
            btn.classList.remove('active', 'bg-green-600', 'text-white', 'border-green-600');
            btn.classList.add('bg-white', 'dark:bg-gray-900', 'text-gray-600', 'dark:text-gray-400', 'border-gray-300', 'dark:border-gray-600');
        });

        var noBtn = row.querySelector('.rating-btn-no');
        if (noBtn) {
            noBtn.classList.remove('active', 'bg-gray-500', 'text-white', 'border-gray-500');
            noBtn.classList.add('bg-white', 'dark:bg-gray-900', 'text-gray-500', 'dark:text-gray-500', 'border-gray-300', 'dark:border-gray-600');
        }

        var naBtn = row.querySelector('.rating-btn-na');
        if (naBtn) {
            naBtn.classList.remove('bg-white', 'dark:bg-gray-900', 'text-amber-500', 'dark:text-amber-400', 'border-gray-300', 'dark:border-gray-600');
            naBtn.classList.add('active', 'bg-amber-400', 'text-white', 'border-amber-400');
        }

        row.classList.remove('selected', 'no-selected');
        row.classList.add('na-selected');

        var existing = document.getElementById('rating-input-' + index);
        if (existing) existing.remove();

        var noHidden = document.getElementById('no-input-' + index);
        if (noHidden) noHidden.remove();

        var naHidden = document.createElement('input');
        naHidden.type = 'hidden';
        naHidden.name = 'ratings[' + index + '][not_applicable]';
        naHidden.value = '1';
        naHidden.id = 'na-input-' + index;
        row.appendChild(naHidden);

        selections[index] = 'na';
        updateRowHidden(row, index);

        if (window.asAutoSaveDebounced) asAutoSaveDebounced('observation');
    }

    function markAllNo() {
        if (!confirm('Mark all indicators as Not Observed (NO)?')) return;
        for (var i = 0; i < totalIndicators; i++) {
            selectNo(i);
        }
    }

    function updateRowHidden(row, index) {
        var hidden = document.getElementById('rating-selection-' + index);
        if (!hidden) {
            hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'ratings[' + index + '][has_rating]';
            hidden.id = 'rating-selection-' + index;
        }
        hidden.value = selections[index] || '';
        var existingHidden = row.querySelector('#rating-selection-' + index);
        if (existingHidden) existingHidden.remove();
        row.appendChild(hidden);
    }

    function toggleComment(index) {
        var commentRow = document.getElementById('comment-row-' + index);
        if (!commentRow) return;
        var isOpen = commentRow.classList.contains('open');
        if (isOpen) {
            commentRow.classList.remove('open');
        } else {
            commentRow.classList.add('open');
            var textarea = commentRow.querySelector('textarea');
            if (textarea) textarea.focus();
        }
    }

    function updateCommentButtonState(index) {
        var row = document.getElementById('comment-row-' + index);
        if (!row) return;
        var textarea = row.querySelector('textarea');
        var btn = document.querySelector('.comment-toggle[data-index="' + index + '"]');
        if (!textarea || !btn) return;
        var hasText = textarea.value.trim().length > 0;
        if (hasText) {
            btn.classList.add('has-comment');
            btn.innerHTML = '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg> View Comment';
        } else {
            btn.classList.remove('has-comment');
            btn.innerHTML = '<svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg> Add Comment';
        }
    }

    document.addEventListener('input', function(e) {
        if (e.target.matches('[data-comment-input]')) {
            var index = parseInt(e.target.getAttribute('data-comment-input'));
            updateCommentButtonState(index);
            if (window.asAutoSaveDebounced) asAutoSaveDebounced('observation');
        }
    });

    document.addEventListener('click', function(e) {
        var btn = e.target.closest('[data-action]');
        if (!btn) return;
        var action = btn.getAttribute('data-action');
        if (action === 'select-rating') {
            selectRating(parseInt(btn.getAttribute('data-index')), parseInt(btn.getAttribute('data-value')));
        } else if (action === 'select-no') {
            selectNo(parseInt(btn.getAttribute('data-index')));
        } else if (action === 'select-na') {
            selectNA(parseInt(btn.getAttribute('data-index')));
        } else if (action === 'mark-all-no') {
            markAllNo();
        } else if (action === 'toggle-comment') {
            toggleComment(parseInt(btn.getAttribute('data-index')));
        }
    });
</script>
@endpush
@endsection