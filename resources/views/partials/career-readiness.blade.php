@php
    $assessment = $careerReadiness['assessment'] ?? null;
    $status = $careerReadiness['status'] ?? 'not_yet_assessed';
    $history = $careerReadiness['history'] ?? collect();
    $canAssess = $canAssess ?? false;
@endphp

<div id="readiness" class="scroll-mt-24 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3 flex-wrap">
            <h3 class="text-gray-900 dark:text-gray-100 font-semibold text-lg">Career Progression Readiness</h3>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ \App\Models\CareerProgressionAssessment::statusBadgeFor($status) }}">
                {{ \App\Models\CareerProgressionAssessment::statusLabelFor($status) }}
            </span>
            @if($assessment?->target_career_stage)
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    Target: {{ $assessment->targetStageLabel() }}
                </span>
            @endif
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400">Assessment &amp; support tool &middot; no automatic promotion</p>
    </div>

    <!-- Ratee context -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-slate-50 dark:bg-gray-800 rounded-lg p-4">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Current Position</p>
            <p class="mt-1 font-semibold text-gray-900 dark:text-gray-100">{{ $careerContext['position'] }}</p>
        </div>
        <div class="bg-slate-50 dark:bg-gray-800 rounded-lg p-4">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Career Stage</p>
            <p class="mt-1 font-semibold text-gray-900 dark:text-gray-100">{{ $careerContext['career_stage_label'] ?: 'N/A' }}</p>
        </div>
        <div class="bg-slate-50 dark:bg-gray-800 rounded-lg p-4">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Framework</p>
            <p class="mt-1 font-semibold text-gray-900 dark:text-gray-100">{{ $careerContext['framework_label'] }}</p>
        </div>
        <div class="bg-slate-50 dark:bg-gray-800 rounded-lg p-4">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Career Track</p>
            <p class="mt-1 font-semibold text-gray-900 dark:text-gray-100">{{ $careerContext['career_track_label'] }}</p>
        </div>
    </div>

    <!-- COT evidence summary -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $careerEvidence['total_observations'] }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Total Observations</p>
        </div>
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $careerEvidence['average_rating'] !== null ? number_format($careerEvidence['average_rating'], 2) . ' / 6' : 'N/A' }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Average Rating</p>
        </div>
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $careerEvidence['recent_observation_rating'] !== null ? number_format($careerEvidence['recent_observation_rating'], 2) : 'N/A' }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Recent Observation @if($careerEvidence['recent_observation_date'])({{ $careerEvidence['recent_observation_date'] }})@endif</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div>
            <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Indicator Strengths</h4>
            @forelse($careerEvidence['strengths'] as $strength)
                <div class="flex items-start gap-2 py-1.5">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 shrink-0">{{ $strength['code'] }}</span>
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $strength['indicator'] }}</p>
                </div>
            @empty
                <p class="text-sm text-gray-500 dark:text-gray-400">No clear strengths recorded yet.</p>
            @endforelse
        </div>
        <div>
            <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Indicators Needing Development</h4>
            @forelse($careerEvidence['needs_development'] as $needed)
                <div class="flex items-start gap-2 py-1.5">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 shrink-0">{{ $needed['code'] }}</span>
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ $needed['indicator'] }}</p>
                </div>
            @empty
                <p class="text-sm text-gray-500 dark:text-gray-400">No indicators flagged for development.</p>
            @endforelse
        </div>
    </div>

    <!-- Assessment form -->
    @if($canAssess)
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Readiness Assessment</h4>
            <form method="POST" action="{{ $careerRoute }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                        <select name="status" required
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm px-3 py-2">
                            @foreach(\App\Models\CareerProgressionAssessment::statusOptions() as $value => $label)
                                <option value="{{ $value }}" @selected($assessment && $assessment->status === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Target Career Stage</label>
                        @php $selectedTarget = old('target_career_stage', $assessment?->target_career_stage); @endphp
                        <select name="target_career_stage"
                                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm px-3 py-2">
                            <option value="">Auto &mdash; next career stage</option>
                            @foreach($careerNextStages ?? [] as $value => $label)
                                <option value="{{ $value }}" @selected($selectedTarget === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                            @if(empty($careerNextStages))
                                This ratee is already at the top of the current track.
                            @else
                                Defaults to the next stage after the ratee's current one.
                            @endif
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Assessment Date</label>
                        <input type="date" name="assessed_at"
                               value="{{ old('assessed_at', $assessment?->assessed_at?->toDateString() ?? now()->toDateString()) }}"
                               class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm px-3 py-2">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Remarks</label>
                    <textarea name="remarks" rows="2" placeholder="Optional remarks to support this assessment"
                              class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 text-sm px-3 py-2">{{ old('remarks', $assessment?->remarks) }}</textarea>
                </div>
                <div class="flex items-center justify-end gap-3">
                    @if($assessment)
                        <span class="text-xs text-gray-500 dark:text-gray-400">Last assessed {{ $assessment->assessed_at?->format('M d, Y') }} by {{ $assessment->evaluator?->name ?? 'N/A' }}</span>
                    @endif
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
                        Save Assessment
                    </button>
                </div>
                @error('status')
                    <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
                @error('target_career_stage')
                    <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </form>
        </div>
    @endif

    <!-- History -->
    @if($history->isNotEmpty())
        <div class="mt-6">
            <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Assessment History</h4>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                            <th class="py-2 pr-4 font-medium">Date</th>
                            <th class="py-2 pr-4 font-medium">Status</th>
                            <th class="py-2 pr-4 font-medium">Target Stage</th>
                            <th class="py-2 pr-4 font-medium">Position at Assessment</th>
                            <th class="py-2 pr-4 font-medium">Evaluator</th>
                            <th class="py-2 font-medium">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($history as $entry)
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td class="py-2 pr-4 text-gray-700 dark:text-gray-300">{{ $entry->assessed_at?->format('M d, Y') }}</td>
                                <td class="py-2 pr-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium {{ $entry->statusBadgeClass() }}">{{ $entry->statusLabel() }}</span>
                                </td>
                                <td class="py-2 pr-4 text-gray-700 dark:text-gray-300">{{ $entry->targetStageLabel() ?: '—' }}</td>
                                <td class="py-2 pr-4 text-gray-700 dark:text-gray-300">
                                    {{ $entry->position ?: 'N/A' }}@if($entry->career_stage)<span class="text-gray-400"> &middot; {{ $entry->career_stage }}</span>@endif
                                </td>
                                <td class="py-2 pr-4 text-gray-700 dark:text-gray-300">{{ $entry->evaluator?->name ?? 'N/A' }}</td>
                                <td class="py-2 text-gray-700 dark:text-gray-300">{{ $entry->remarks ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
