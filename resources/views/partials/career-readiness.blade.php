@php
    $assessment = $careerReadiness['assessment'] ?? null;
    $status = $careerReadiness['status'] ?? 'not_yet_assessed';
    $history = $careerReadiness['history'] ?? collect();
    $canAssess = $canAssess ?? false;
    $canEditAssessment = $canEditAssessment ?? false;
@endphp
<div id="readiness" class="scroll-mt-24 bg-white dark:bg-gray-900 rounded-2xl border border-slate-200 dark:border-gray-800 shadow-sm p-6 section-card"
    x-data="{
        editOpen: false,
        editAction: '',
        editStatus: '',
        editTarget: '',
        editRemarks: '',
        editDate: '',
        openEdit(id, status, target, remarks, date) {
            this.editAction = '{{ $canEditAssessment ? route('supervisor.teachers.career-assessment.update', [$ratee ?? '__tid__', '__id__']) : '' }}'.replace('__id__', id);
            this.editStatus = status;
            this.editTarget = target;
            this.editRemarks = remarks || '';
            this.editDate = date || '';
            this.editOpen = true;
            document.body.classList.add('overflow-y-hidden');
        },
        closeEdit() {
            this.editOpen = false;
            document.body.classList.remove('overflow-y-hidden');
        }
    }">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <div class="flex items-center gap-2 flex-wrap">
            <h3 class="text-sm font-bold tracking-widest uppercase text-slate-700 dark:text-gray-200 flex items-center gap-2"><span class="w-1.5 h-5 rounded-full bg-emerald-500"></span> Career Progression Readiness</h3>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ \App\Models\CareerProgressionAssessment::statusBadgeFor($status) }}">
                {{ \App\Models\CareerProgressionAssessment::statusLabelFor($status) }}
            </span>
            @if($assessment?->target_career_stage)
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-200 dark:border-indigo-500/30 text-indigo-700 dark:text-indigo-400">
                    <i class="fas fa-arrow-right text-[11px]"></i> Target: {{ $assessment->targetStageLabel() }}
                </span>
            @endif
        </div>
        <p class="text-xs px-2.5 py-1 rounded-full bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-600 dark:text-gray-300">Support tool · no auto promotion</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <div class="bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl p-4">
            <p class="text-xs font-semibold tracking-widest uppercase text-slate-500 dark:text-gray-400">Current Position</p>
            <p class="mt-1 font-semibold text-slate-900 dark:text-gray-100">{{ $careerContext['position'] }}</p>
        </div>
        <div class="bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl p-4">
            <p class="text-xs font-semibold tracking-widest uppercase text-slate-500 dark:text-gray-400">Career Stage</p>
            <p class="mt-1 font-semibold text-slate-900 dark:text-gray-100">{{ $careerContext['career_stage_label'] ?: '—' }}</p>
        </div>
        <div class="bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl p-4">
            <p class="text-xs font-semibold tracking-widest uppercase text-slate-500 dark:text-gray-400">Framework</p>
            <p class="mt-1 font-semibold text-slate-900 dark:text-gray-100">{{ $careerContext['framework_label'] }}</p>
        </div>
        <div class="bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 rounded-xl p-4">
            <p class="text-xs font-semibold tracking-widest uppercase text-slate-500 dark:text-gray-400">Career Track</p>
            <p class="mt-1 font-semibold text-slate-900 dark:text-gray-100">{{ $careerContext['career_track_label'] }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
        <div class="rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
            <p class="text-2xl font-extrabold text-slate-900 dark:text-gray-100">{{ $careerEvidence['total_observations'] }}</p>
            <p class="text-xs font-medium text-slate-500 dark:text-gray-400">Total Observations</p>
        </div>
        <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-4">
            <p class="text-xl font-extrabold text-indigo-700">{{ $careerEvidence['average_rating'] !== null ? number_format($careerEvidence['average_rating'], 2) . ' / 6' : '—' }}</p>
            <p class="text-xs font-medium text-indigo-700/70">Average Rating</p>
        </div>
        <div class="rounded-xl border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
            <p class="text-lg font-extrabold text-slate-900 dark:text-gray-100">{{ $careerEvidence['recent_observation_rating'] !== null ? number_format($careerEvidence['recent_observation_rating'], 2) : '—' }}</p>
            <p class="text-xs font-medium text-slate-500 dark:text-gray-400">Recent @if($careerEvidence['recent_observation_date'])<span class="text-slate-400 dark:text-gray-500">({{ $careerEvidence['recent_observation_date'] }})</span>@endif</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="rounded-xl border border-slate-200 dark:border-gray-700 p-4 bg-slate-50/30 dark:bg-gray-800">
            <h4 class="text-xs font-bold tracking-widest uppercase text-slate-700 dark:text-gray-200 mb-3 flex items-center gap-1.5"><span class="w-1 h-4 rounded-full bg-emerald-500"></span> Strengths</h4>
            @forelse($careerEvidence['strengths'] as $strength)
                <div class="flex items-start gap-2 py-1.5">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 text-emerald-700 dark:text-emerald-400 shrink-0">{{ $strength['code'] }}</span>
                    <p class="text-sm text-slate-700 dark:text-gray-200">{{ $strength['indicator'] }}</p>
                </div>
            @empty
                <p class="text-sm text-slate-500 dark:text-gray-400">No clear strengths recorded yet.</p>
            @endforelse
        </div>
        <div class="rounded-xl border border-slate-200 dark:border-gray-700 p-4 bg-slate-50/30 dark:bg-gray-800">
            <h4 class="text-xs font-bold tracking-widest uppercase text-slate-700 dark:text-gray-200 mb-3 flex items-center gap-1.5"><span class="w-1 h-4 rounded-full bg-amber-500"></span> Needs Development</h4>
            @forelse($careerEvidence['needs_development'] as $needed)
                <div class="flex items-start gap-2 py-1.5">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-50 dark:bg-yellow-500/10 border border-amber-200 dark:border-yellow-500/30 text-amber-700 dark:text-yellow-400 shrink-0">{{ $needed['code'] }}</span>
                    <p class="text-sm text-slate-700 dark:text-gray-200">{{ $needed['indicator'] }}</p>
                </div>
            @empty
                <p class="text-sm text-slate-500 dark:text-gray-400">No indicators flagged for development.</p>
            @endforelse
        </div>
    </div>

    @if($canAssess)
        <div class="rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50/50 dark:bg-gray-800 p-5">
            <h4 class="text-sm font-bold text-slate-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                <i class="fas fa-plus text-indigo-600 text-xs"></i> New Readiness Assessment
            </h4>
            <p class="text-xs text-slate-500 dark:text-gray-400 mb-3 flex items-center gap-1.5"><i class="fas fa-circle-info text-slate-400 dark:text-gray-500"></i> Add a new readiness assessment. Existing entries can be edited from the history below.</p>
            <form method="POST" id="career-assessment-form" action="{{ $careerRoute }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-gray-200 mb-1">Status</label>
                        <select name="status" required class="w-full rounded-xl border border-slate-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-slate-900 dark:text-gray-100 text-sm px-3 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                            @foreach(\App\Models\CareerProgressionAssessment::statusOptions() as $value => $label)
                                <option value="{{ $value }}" @selected(old('status') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-gray-200 mb-1">Target Stage</label>
                        <select name="target_career_stage" class="w-full rounded-xl border border-slate-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-slate-900 dark:text-gray-100 text-sm px-3 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                            <option value="">Auto — next stage</option>
                            @foreach($careerNextStages ?? [] as $value => $label)
                                <option value="{{ $value }}" @selected(old('target_career_stage') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-slate-500 dark:text-gray-400">@if(empty($careerNextStages))Already at top of track.@else Defaults to next stage.@endif</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-gray-200 mb-1">Assessment Date</label>
                        <input type="date" name="assessed_at" value="{{ old('assessed_at', now()->toDateString()) }}" class="w-full rounded-xl border border-slate-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-slate-900 dark:text-gray-100 text-sm px-3 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-gray-200 mb-1">Remarks</label>
                    <textarea name="remarks" rows="2" placeholder="Optional remarks to support this assessment" class="w-full rounded-xl border border-slate-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-slate-900 dark:text-gray-100 text-sm px-3 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">{{ old('remarks') }}</textarea>
                </div>
                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <span class="text-xs text-slate-500 dark:text-gray-400">Support tool · no auto promotion</span>
                    <div class="flex items-center gap-2">
                        <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-700 shadow-sm transition-colors">
                            <i class="fas fa-floppy-disk text-xs"></i> Save Assessment
                        </button>
                    </div>
                </div>
                @error('status')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                @error('target_career_stage')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            </form>
        </div>
    @endif

    @if($history->isNotEmpty())
        <div class="mt-6">
            <h4 class="text-xs font-bold tracking-widest uppercase text-slate-700 dark:text-gray-200 mb-3">Assessment History</h4>
            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-gray-700">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-gray-800">
                        <tr class="text-left text-xs font-semibold tracking-widest uppercase text-slate-500 dark:text-gray-400 border-b border-slate-200 dark:border-gray-700">
                            <th class="py-2.5 px-3 font-semibold">Date</th>
                            <th class="py-2.5 px-3 font-semibold">Status</th>
                            <th class="py-2.5 px-3 font-semibold">Target</th>
                            <th class="py-2.5 px-3 font-semibold">Position</th>
                            <th class="py-2.5 px-3 font-semibold">Evaluator</th>
                            <th class="py-2.5 px-3 font-semibold">Remarks</th>
                            @if($canEditAssessment)<th class="py-2.5 px-3 font-semibold">Actions</th>@endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-gray-800 bg-white dark:bg-gray-900">
                        @foreach($history as $entry)
                            <tr>
                                <td class="py-2.5 px-3 text-slate-700 dark:text-gray-200">{{ $entry->assessed_at?->format('M d, Y') }}</td>
                                <td class="py-2.5 px-3"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border {{ $entry->statusBadgeClass() }}">{{ $entry->statusLabel() }}</span></td>
                                <td class="py-2.5 px-3 text-slate-700 dark:text-gray-200">{{ $entry->targetStageLabel() ?: '—' }}</td>
                                <td class="py-2.5 px-3 text-slate-700 dark:text-gray-200">{{ $entry->position ?: '—' }}@if($entry->career_stage)<span class="text-slate-400 dark:text-gray-500"> · {{ $entry->career_stage }}</span>@endif</td>
                                <td class="py-2.5 px-3 text-slate-700 dark:text-gray-200">{{ $entry->evaluator?->name ?? '—' }}</td>
                                <td class="py-2.5 px-3 text-slate-700 dark:text-gray-200">{{ $entry->remarks ?: '—' }}</td>
                                @if($canEditAssessment)
                                    <td class="py-2.5 px-3">
                                        <button type="button"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold border border-slate-300 dark:border-gray-600 text-slate-600 dark:text-gray-300 bg-white dark:bg-gray-900 hover:bg-indigo-50 dark:hover:bg-gray-800 hover:text-indigo-700 dark:hover:text-indigo-400 hover:border-indigo-300 transition-colors"
                                            @click="openEdit(
                                                {{ $entry->id }},
                                                '{{ $entry->status }}',
                                                '{{ $entry->target_career_stage ?? '' }}',
                                                @js($entry->remarks),
                                                '{{ $entry->assessed_at?->toDateString() }}'
                                            )">
                                            <i class="fas fa-pen text-[11px]"></i> Edit
                                        </button>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Edit Assessment Modal --}}
    @if($canEditAssessment)
    <div x-show="editOpen" x-cloak class="fixed inset-0 z-[90] overflow-y-auto">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="closeEdit()"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-2xl bg-white dark:bg-gray-900 rounded-2xl shadow-xl border border-slate-200 dark:border-gray-700 my-6"
                x-show="editOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-gray-700">
                    <div>
                        <h4 class="text-base font-bold text-slate-900 dark:text-gray-100 flex items-center gap-2"><i class="fas fa-pen text-indigo-600 text-xs"></i> Edit Readiness Assessment</h4>
                        <p class="text-xs text-slate-500 dark:text-gray-400 mt-0.5">Update this entry in the assessment history.</p>
                    </div>
                    <button type="button" class="p-2 rounded-lg text-slate-400 dark:text-gray-500 hover:text-slate-600 dark:hover:text-gray-300 hover:bg-slate-100 dark:hover:bg-gray-800 transition-colors" @click="closeEdit()">
                        <i class="fas fa-xmark text-lg"></i>
                    </button>
                </div>
                <form method="POST" :action="editAction" class="p-6 space-y-4">
                    @csrf
                    <input type="hidden" name="_method" value="PUT">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-gray-200 mb-1">Status</label>
                            <select name="status" x-model="editStatus" required class="w-full rounded-xl border border-slate-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-slate-900 dark:text-gray-100 text-sm px-3 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                                @foreach(\App\Models\CareerProgressionAssessment::statusOptions() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-gray-200 mb-1">Target Stage</label>
                            <select name="target_career_stage" x-model="editTarget" class="w-full rounded-xl border border-slate-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-slate-900 dark:text-gray-100 text-sm px-3 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                                <option value="">Auto — next stage</option>
                                @foreach($careerNextStages ?? [] as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-gray-200 mb-1">Assessment Date</label>
                            <input type="date" name="assessed_at" x-model="editDate" class="w-full rounded-xl border border-slate-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-slate-900 dark:text-gray-100 text-sm px-3 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-gray-200 mb-1">Remarks</label>
                        <textarea name="remarks" x-model="editRemarks" rows="3" placeholder="Optional remarks to support this assessment" class="w-full rounded-xl border border-slate-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-slate-900 dark:text-gray-100 text-sm px-3 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"></textarea>
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" class="px-4 py-2.5 border border-slate-300 dark:border-gray-700 text-slate-700 dark:text-gray-200 bg-white dark:bg-gray-900 rounded-xl text-sm font-semibold hover:bg-slate-50 dark:hover:bg-gray-800 transition-colors" @click="closeEdit()">
                            Cancel
                        </button>
                        <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-700 shadow-sm transition-colors">
                            <i class="fas fa-floppy-disk text-xs"></i> Update Assessment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
