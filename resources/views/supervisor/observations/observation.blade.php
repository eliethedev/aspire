@extends('layouts.supervisor')

@section('title', 'Classroom Observation')

@push('styles')
<style>
    .rating-btn { transition: all 0.15s ease; min-width: 2.5rem; }
    .rating-btn:hover { transform: scale(1.05); }
    .rating-btn.active { transform: scale(1.1); }
    .rating-btn-no { transition: all 0.15s ease; }
    .rating-btn-no.active { background-color: #6b7280; color: white; border-color: #6b7280; }
    .indicator-row { transition: background-color 0.15s ease; }
    .indicator-row:hover { background-color: #f9fafb; }
    .indicator-row.selected { background-color: #eef2ff; }
    .indicator-row.no-selected { background-color: #f9fafb; }
    .cot-table th { font-size: 0.7rem; letter-spacing: 0.05em; }
</style>
@endpush

@php
    $stageKeys = ['pre_observation_planning', 'pre_conference', 'observation', 'post_conference'];
    $stageLabels = [
        'pre_observation_planning' => 'Pre-Observation Planning',
        'pre_conference' => 'Pre-Conference',
        'observation' => 'Observation',
        'post_conference' => 'Post-Conference',
    ];
    $stageRoutes = [
        'pre_observation_planning' => 'supervisor.observations.preObservationPlanning',
        'pre_conference' => 'supervisor.observations.preConference',
        'observation' => 'supervisor.observations.observation',
        'post_conference' => 'supervisor.observations.postConference',
    ];
    $currentStage = 'observation';
    $currentIdx = array_search($currentStage, $stageKeys);

    $groupedIndicators = [];
    foreach ($cotIndicators as $indicator) {
        $groupedIndicators[$indicator['domain']][] = $indicator;
    }

    $ratingValues = [6, 5, 4, 3, 2];
@endphp

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8">
    <nav class="mb-6 text-sm">
        <ol class="flex items-center gap-2 text-gray-500">
            <li><a href="{{ route('supervisor.observations.index') }}" class="hover:text-indigo-600 transition-colors">Evaluations</a></li>
            <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"/></svg></li>
            <li><a href="{{ route('supervisor.observations.show', $observation) }}" class="hover:text-indigo-600 transition-colors">Observation Details</a></li>
            <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"/></svg></li>
            <li class="text-gray-900 font-medium">Observation</li>
        </ol>
    </nav>

    <div class="mb-8">
        <div class="flex items-center justify-between">
            @foreach($stageKeys as $i => $key)
                @php
                    $isCurrent = $key === $currentStage;
                    $isCompleted = $i < $currentIdx;
                    $canAccess = $isCurrent || $isCompleted;
                @endphp
                @if($i > 0)
                    <div class="flex-1 mx-4 h-1 {{ $isCompleted ? 'bg-green-400' : 'bg-gray-200' }}"></div>
                @endif
                @if($canAccess)
                    <a href="{{ $isCurrent ? '#' : route($stageRoutes[$key], $observation) }}"
                       class="flex items-center group {{ $isCurrent ? 'cursor-default' : 'cursor-pointer' }}">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full {{ $isCompleted ? 'bg-green-600 text-white' : 'bg-indigo-600 text-white ring-2 ring-indigo-200' }} font-semibold transition-colors group-hover:shadow-md text-sm">
                            @if($isCompleted)
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            @else
                                {{ $i + 1 }}
                            @endif
                        </div>
                        <span class="ml-2 {{ $isCompleted ? 'text-gray-600' : 'text-gray-900 font-medium' }} text-sm group-hover:text-indigo-600 transition-colors">{{ $stageLabels[$key] }}</span>
                    </a>
                @else
                    <div class="flex items-center opacity-50">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full bg-gray-200 text-gray-400 font-semibold text-sm">{{ $i + 1 }}</div>
                        <span class="ml-2 text-gray-400 text-sm">{{ $stageLabels[$key] }}</span>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">
            {{ $observation->isTeacherObservation() ? 'Classroom Observation' : 'School Head Observation' }}
        </h1>
        <p class="text-gray-500 mt-1">
            Complete the {{ $observation->isTeacherObservation() ? 'PPST' : 'Leadership' }} COT Form for {{ $observation->observee->user->name }}
            &middot; SY {{ $schoolYear }}
        </p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-indigo-100 mb-6">
        <div class="flex items-center justify-between p-4 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-t-xl border-b border-indigo-100">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                <h2 class="text-lg font-semibold text-gray-900">AI Observation Suggestions</h2>
                <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full font-medium">AI-Powered</span>
            </div>
            <button type="button" id="generate-obs-suggestions-btn"
                    class="px-3 py-1.5 text-xs font-medium text-indigo-700 bg-indigo-100 hover:bg-indigo-200 rounded-lg transition-colors inline-flex items-center gap-1.5 disabled:opacity-50">
                <svg id="obs-suggestions-spinner" class="hidden w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span id="obs-suggestions-btn-text">Generate Suggestions</span>
            </button>
        </div>
        <div class="p-4" id="obs-suggestions-container">
            <div class="flex items-start gap-2 text-xs text-gray-500 bg-gray-50 rounded-lg p-3 border border-gray-200 mb-3">
                <svg class="w-4 h-4 text-gray-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p>AI-powered suggestions based on pre-conference data. Use these as a guide during observation.</p>
            </div>
            <div id="obs-suggestions-content" class="prose prose-sm max-w-none text-gray-700 whitespace-pre-wrap">
                @if(isset($existingSuggestions) && $existingSuggestions)
                    <div class="bg-indigo-50 rounded-lg p-4 border border-indigo-100">
                        <p class="text-sm text-gray-600 italic">Pre-observation AI insights are available. Click "Generate Suggestions" for observation-specific guidance based on the pre-conference focus.</p>
                    </div>
                @else
                    <p class="text-sm text-gray-400 italic">Generate AI suggestions to guide your observation focus areas and look-fors.</p>
                @endif
            </div>
        </div>
    </div>

    @if($preConference)
    <div x-data="{ open: true }" class="bg-white rounded-xl shadow-sm border border-gray-100 mb-6">
        <button type="button" @click="open = !open"
                class="w-full flex items-center justify-between p-4 text-left">
            <h2 class="text-lg font-semibold text-gray-900">Pre-Conference Summary</h2>
            <svg class="w-5 h-5 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div x-show="open" x-collapse>
            <div class="p-4 pt-0 border-t border-gray-100">
                <div class="space-y-2">
                    @if($preConference->finalized_focus)
                    <div>
                        <span class="text-gray-500 text-sm">Finalized Focus:</span>
                        <p class="text-gray-900 mt-1">{{ $preConference->finalized_focus }}</p>
                    </div>
                    @endif
                    @if($preConference->discussion_notes)
                    <div>
                        <span class="text-gray-500 text-sm">Discussion Notes:</span>
                        <p class="text-gray-900 mt-1 text-sm">{{ Str::limit($preConference->discussion_notes, 200) }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <form method="POST" action="{{ route('supervisor.observations.storeObservationData', $observation) }}" class="space-y-6" enctype="multipart/form-data">
        @csrf

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-4 border-b border-gray-100 bg-gradient-to-r from-indigo-50 to-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">COT Rating Sheet</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Annex E-2 &middot; SY {{ $schoolYear }} &middot; {{ $observation->isTeacherObservation() ? 'PPST' : 'Leadership' }} Indicators</p>
                    </div>
                    <button type="button" onclick="markAllNo()"
                            class="px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                        Mark All as NO
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm cot-table">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="text-left px-4 py-3 text-gray-600 font-semibold w-8">#</th>
                            <th class="text-left px-4 py-3 text-gray-600 font-semibold">Indicator</th>
                            <th class="text-center px-2 py-3 text-gray-600 font-semibold w-16">6</th>
                            <th class="text-center px-2 py-3 text-gray-600 font-semibold w-16">5</th>
                            <th class="text-center px-2 py-3 text-gray-600 font-semibold w-16">4</th>
                            <th class="text-center px-2 py-3 text-gray-600 font-semibold w-16">3</th>
                            <th class="text-center px-2 py-3 text-gray-600 font-semibold w-16">2</th>
                            <th class="text-center px-2 py-3 text-gray-600 font-semibold w-16">NO</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $indicatorIndex = 0; @endphp
                        @foreach($groupedIndicators as $domain => $indicators)
                            <tr class="bg-indigo-50/50 border-b border-indigo-100">
                                <td colspan="8" class="px-4 py-2.5 text-sm font-semibold text-indigo-800">{{ $domain }}</td>
                            </tr>
                            @foreach($indicators as $indicator)
                                @php
                                    $existingRating = $cotRatings->where('indicator_code', $indicator['code'])->first();
                                    $savedRating = $existingRating?->rating;
                                    $savedNo = $existingRating?->not_observed;
                                @endphp
                                <tr class="indicator-row border-b border-gray-100" data-index="{{ $indicatorIndex }}" data-code="{{ $indicator['code'] }}">
                                    <td class="px-4 py-2.5 text-gray-400 text-xs align-top pt-3">{{ $indicatorIndex + 1 }}</td>
                                    <td class="px-4 py-2.5">
                                        <div class="flex items-start gap-2">
                                            <span class="text-xs font-mono font-semibold text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded whitespace-nowrap mt-0.5">{{ $indicator['code'] }}</span>
                                            <span class="text-gray-800 text-xs leading-relaxed">{{ $indicator['description'] }}</span>
                                        </div>
                                        <input type="hidden" name="ratings[{{ $indicatorIndex }}][indicator_code]" value="{{ $indicator['code'] }}">
                                        <input type="hidden" name="ratings[{{ $indicatorIndex }}][domain]" value="{{ $indicator['domain'] }}">
                                        <input type="hidden" name="ratings[{{ $indicatorIndex }}][indicator]" value="{{ $indicator['description'] }}">
                                    </td>
                                    @foreach($ratingValues as $val)
                                        <td class="text-center px-2 py-2.5">
                                            <button type="button"
                                                    onclick="selectRating({{ $indicatorIndex }}, {{ $val }})"
                                                    class="rating-btn w-8 h-8 rounded-full text-xs font-bold border-2 {{ $savedRating === $val && !$savedNo ? 'active bg-green-600 text-white border-green-600' : 'bg-white text-gray-500 border-gray-200 hover:border-green-300 hover:bg-green-50' }}"
                                                    data-index="{{ $indicatorIndex }}" data-value="{{ $val }}">
                                                ✓
                                            </button>
                                        </td>
                                    @endforeach
                                    <td class="text-center px-2 py-2.5">
                                        <button type="button"
                                                onclick="selectNo({{ $indicatorIndex }})"
                                                class="rating-btn-no w-8 h-8 rounded-lg text-xs font-bold border {{ $savedNo ? 'active bg-gray-500 text-white border-gray-500' : 'bg-white text-gray-400 border-gray-200 hover:border-gray-400 hover:bg-gray-50' }}"
                                                data-index="{{ $indicatorIndex }}">
                                            NO
                                        </button>
                                    </td>
                                </tr>
                                @php $indicatorIndex++; @endphp
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-100 bg-gray-50">
                <label class="block text-sm font-medium text-gray-700 mb-1">Other Comments</label>
                <textarea name="other_comments" rows="3"
                          class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"
                          placeholder="Additional comments or observations...">{{ $observation->cotRatings->first()?->comments ?? '' }}</textarea>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Supervisor Notes</h2>
            <p class="text-xs text-gray-500 mb-3">These notes will be passed to the Post-Conference as STAR notes and will inform the AI analysis.</p>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">STAR Notes <span class="text-gray-400 font-normal">(What went well)</span></label>
                    <textarea name="star_notes" rows="4"
                              class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500 text-sm"
                              placeholder="Document specific observations of effective teaching practices observed...">{{ $observation->postConference?->star_notes }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Supervisor's Private Notes</label>
                    <textarea name="supervisor_notes" rows="3"
                              class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-400 text-sm"
                              placeholder="Your private notes for future reference...">{{ $observation->postConference?->supervisor_notes }}</textarea>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Evidence Files <span class="text-gray-400 font-normal">(photos, videos, documents)</span></h2>
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-indigo-400 transition-colors">
                <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                <p class="text-sm text-gray-500 mb-1">Drop files here or click to upload</p>
                <p class="text-xs text-gray-400">Upload photos, videos, or documents as evidence</p>
                <input type="file" name="evidence_files[]" multiple accept="image/*,video/*,.pdf,.doc,.docx"
                       class="mt-3 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
            </div>
            @if($observation->evidence_files)
                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach($observation->evidence_files as $file)
                        <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3 border border-gray-200">
                            <a href="{{ asset('storage/' . $file['path']) }}" target="_blank" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium truncate">
                                {{ $file['original_name'] ?? basename($file['path']) }}
                            </a>
                            <span class="text-gray-400 text-xs shrink-0 ml-2">{{ isset($file['size']) ? number_format($file['size'] / 1024, 1) . ' KB' : '' }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
            <div class="flex items-center gap-2 mb-3">
                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                <span class="text-xs text-gray-600">Saving will auto-generate AI analysis and redirect to the Post-Conference page.</span>
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                <a href="{{ route('supervisor.observations.preConference', $observation) }}"
                   class="flex-1 px-4 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 font-medium text-sm text-center transition-colors">
                    <span class="flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        Back to Pre-Conference
                    </span>
                </a>
                <button type="submit"
                        class="flex-[2] px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold text-sm shadow-sm transition-colors">
                    <span class="flex items-center justify-center gap-2">
                        Save Ratings &amp; Continue to Post-Conference
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </span>
                </button>
            </div>
        </div>
    </form>

    <div class="mt-8 pt-6 border-t border-gray-200">
        <div class="flex items-center justify-between">
            <a href="{{ route('supervisor.observations.show', $observation) }}"
               class="inline-flex items-center gap-2 px-4 py-2 text-sm text-gray-600 hover:text-gray-900 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Details
            </a>
            <a href="{{ route('supervisor.observations.index') }}"
               class="text-sm text-gray-400 hover:text-gray-600 transition-colors">
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
        const row = document.querySelector(`.indicator-row[data-index="${index}"]`);
        if (!row) return;

        row.querySelectorAll('.rating-btn').forEach(btn => {
            btn.classList.remove('active', 'bg-green-600', 'text-white', 'border-green-600');
            btn.classList.add('bg-white', 'text-gray-500', 'border-gray-200');
        });

        const btn = row.querySelector(`.rating-btn[data-index="${index}"][data-value="${value}"]`);
        if (btn) {
            btn.classList.remove('bg-white', 'text-gray-500', 'border-gray-200');
            btn.classList.add('active', 'bg-green-600', 'text-white', 'border-green-600');
        }

        const noBtn = row.querySelector('.rating-btn-no');
        if (noBtn) {
            noBtn.classList.remove('active', 'bg-gray-500', 'text-white', 'border-gray-500');
            noBtn.classList.add('bg-white', 'text-gray-400', 'border-gray-200');
        }

        row.classList.remove('no-selected');
        row.classList.add('selected');

        const hidden = document.createElement('input');
        hidden.type = 'hidden';
        hidden.name = `ratings[${index}][rating]`;
        hidden.value = value;
        hidden.id = `rating-input-${index}`;

        const existing = document.getElementById(`rating-input-${index}`);
        if (existing) existing.remove();

        const noHidden = document.getElementById(`no-input-${index}`);
        if (noHidden) noHidden.remove();

        row.appendChild(hidden);
        selections[index] = 'rating';

        updateRowHidden(row, index);
    }

    function selectNo(index) {
        const row = document.querySelector(`.indicator-row[data-index="${index}"]`);
        if (!row) return;

        row.querySelectorAll('.rating-btn').forEach(btn => {
            btn.classList.remove('active', 'bg-green-600', 'text-white', 'border-green-600');
            btn.classList.add('bg-white', 'text-gray-500', 'border-gray-200');
        });

        const noBtn = row.querySelector('.rating-btn-no');
        if (noBtn) {
            noBtn.classList.remove('bg-white', 'text-gray-400', 'border-gray-200');
            noBtn.classList.add('active', 'bg-gray-500', 'text-white', 'border-gray-500');
        }

        row.classList.remove('selected');
        row.classList.add('no-selected');

        const existing = document.getElementById(`rating-input-${index}`);
        if (existing) existing.remove();

        const noHidden = document.createElement('input');
        noHidden.type = 'hidden';
        noHidden.name = `ratings[${index}][not_observed]`;
        noHidden.value = '1';
        noHidden.id = `no-input-${index}`;
        row.appendChild(noHidden);
        selections[index] = 'no';

        updateRowHidden(row, index);
    }

    function markAllNo() {
        if (!confirm('Mark all indicators as Not Observed (NO)?')) return;
        for (let i = 0; i < totalIndicators; i++) {
            selectNo(i);
        }
    }

    function updateRowHidden(row, index) {
        let hidden = document.getElementById(`rating-selection-${index}`);
        if (!hidden) {
            hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = `ratings[${index}][has_rating]`;
            hidden.id = `rating-selection-${index}`;
        }
        hidden.value = selections[index] || '';

        const existingHidden = row.querySelector(`#rating-selection-${index}`);
        if (existingHidden) existingHidden.remove();

        row.appendChild(hidden);
    }

    document.getElementById('generate-obs-suggestions-btn')?.addEventListener('click', function() {
        const btn = this;
        const spinner = document.getElementById('obs-suggestions-spinner');
        const btnText = document.getElementById('obs-suggestions-btn-text');
        const content = document.getElementById('obs-suggestions-content');

        btn.disabled = true;
        spinner.classList.remove('hidden');
        btnText.textContent = 'Generating...';

        fetch('{{ route("supervisor.observations.generate-observation-suggestions", $observation) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Content-Type': 'application/json',
            },
        })
        .then(res => res.json())
        .then(data => {
            if (data.suggestions) {
                content.innerHTML = `<div class="bg-indigo-50 rounded-lg p-4 border border-indigo-100">${data.suggestions.replace(/\n/g, '<br>')}</div>`;
            } else if (data.error) {
                content.innerHTML = `<div class="bg-red-50 rounded-lg p-4 border border-red-100 text-sm text-red-700">${data.error}</div>`;
            }
        })
        .catch(err => {
            content.innerHTML = `<div class="bg-red-50 rounded-lg p-4 border border-red-100 text-sm text-red-700">Failed to generate suggestions. Please try again.</div>`;
            console.error(err);
        })
        .finally(() => {
            btn.disabled = false;
            spinner.classList.add('hidden');
            btnText.textContent = 'Regenerate';
        });
    });
</script>
@endpush
@endsection