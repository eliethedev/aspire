@extends('layouts.supervisor')

@section('title', 'Schedule Observation')

@push('styles')
<style>
    .observee-card {
        transition: all 0.2s ease;
    }
    .observee-card:hover {
        transform: translateX(4px);
        border-color: #6366f1;
    }
    .observee-card.selected {
        border-color: #6366f1;
        background: #eef2ff;
    }
    .dark .observee-card.selected {
        background: rgba(79, 70, 229, 0.15);
    }
    .type-card {
        cursor: pointer;
        transition: all 0.2s ease;
        border: 2px solid transparent;
    }
    .type-card:hover {
        border-color: #c7d2fe;
        transform: translateY(-2px);
    }
    .type-card.selected {
        border-color: #6366f1;
        background: #eef2ff;
    }
    .dark .type-card.selected {
        background: rgba(79, 70, 229, 0.15);
    }
    .search-input:focus ~ .search-results,
    .search-results:not(:empty) {
        display: block;
    }
    .fade-in {
        animation: fadeIn 0.3s ease;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .modal-backdrop {
        animation: fadeInBackdrop 0.2s ease;
    }
    @keyframes fadeInBackdrop {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-3 sm:px-6" x-data="observationForm()" x-cloak>
    <div class="mb-4">
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Schedule Observation</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Set up a classroom or leadership evaluation — 5 quick steps.</p>

        <!-- Live context chips — compact -->
        <div x-show="selectedObservee" x-cloak class="mt-2 flex flex-wrap items-center gap-1.5">
            <span class="text-[10px] font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">Observing</span>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 text-xs font-medium">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <span x-text="selectedObservee?.name"></span>
            </span>
            <template x-if="selectedCotTemplate">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 text-xs font-medium">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span x-text="selectedCotTemplate.label"></span>
                </span>
            </template>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start">
        <form method="POST" action="{{ route('supervisor.observations.store') }}" class="lg:col-span-2" novalidate>
        @csrf

        <!-- Progress Steps — minimized -->
        <div class="flex items-center gap-1.5 mb-4 text-xs overflow-x-auto pb-1">
            <template x-for="(step, i) in steps" :key="i">
                <div class="flex items-center gap-2">
                    <button type="button" @click="jumpToStep(i + 1)"
                            :disabled="!(step.status === 'complete' || step.status === 'active')"
                            :class="step.status === 'complete' || step.status === 'active' ? 'cursor-pointer' : 'cursor-default'"
                            class="flex items-center gap-1.5 group" :title="step.status === 'complete' ? 'Back to ' + step.label : step.label">
                        <div :class="step.status === 'complete' ? 'bg-indigo-600 text-white' : step.status === 'active' ? 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 border-2 border-indigo-600' : 'bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500'"
                             class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-semibold shrink-0 transition-colors">
                            <svg x-show="step.status === 'complete'" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span x-show="step.status !== 'complete'" x-text="i + 1"></span>
                        </div>
                        <span :class="step.status === 'complete' ? 'text-indigo-600 dark:text-indigo-400 group-hover:text-indigo-500' : step.status === 'active' ? 'text-gray-900 dark:text-gray-100 font-medium' : 'text-gray-400 dark:text-gray-500'" class="text-xs hidden sm:inline transition-colors whitespace-nowrap" x-text="step.label"></span>
                    </button>
                    <div x-show="i < steps.length - 1"
                         :class="step.status === 'complete' ? 'bg-indigo-300' : 'bg-gray-200 dark:bg-gray-700'"
                         class="w-6 sm:w-8 h-0.5 rounded transition-colors shrink-0"></div>
                </div>
            </template>
        </div>

        <!-- ===== STEP 1: WHO WILL BE OBSERVED? ===== -->
        <div x-show="currentStep === 1" class="fade-in">
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center gap-2 mb-3">
                    <span class="w-7 h-7 rounded-lg bg-indigo-600 text-white flex items-center justify-center text-xs font-bold">1</span>
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Who will be observed?</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Choose observation type</p>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-3">
                    <!-- Teacher Card -->
                    <label class="type-card rounded-xl p-3.5 bg-white dark:bg-gray-900 border-2 border-gray-200 dark:border-gray-700 has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/50 cursor-pointer"
                           :class="selectedType === 'teacher_observation' ? 'selected ring-1 ring-indigo-200' : ''">
                        <input type="radio" name="observation_type" value="teacher_observation"
                               x-model="selectedType" @change="onTypeChange()" class="sr-only">
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-lg bg-indigo-600 text-white flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Teacher <span class="text-xs font-normal text-gray-400">TI–TIII</span></h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 leading-snug">COT Rating Sheet (Annex E-2)</p>
                                <span x-show="selectedType==='teacher_observation'" class="mt-1.5 inline-flex text-[10px] font-semibold text-indigo-600 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/30 px-1.5 py-0.5 rounded">Selected</span>
                            </div>
                        </div>
                    </label>

                    <!-- School Head Card -->
                    <label class="type-card rounded-xl p-3.5 bg-white dark:bg-gray-900 border-2 border-gray-200 dark:border-gray-700 has-[:checked]:border-emerald-600 has-[:checked]:bg-emerald-50/50 cursor-pointer"
                           :class="selectedType === 'school_head_observation' ? 'selected ring-1 ring-emerald-200' : ''">
                        <input type="radio" name="observation_type" value="school_head_observation"
                               x-model="selectedType" @change="onTypeChange()" class="sr-only">
                        <div class="flex items-start gap-3">
                            <div class="w-9 h-9 rounded-lg bg-emerald-600 text-white flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">School Head/Principal</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 leading-snug">Leadership evaluation</p>
                                <span x-show="selectedType==='school_head_observation'" class="mt-1.5 inline-flex text-[10px] font-semibold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded">Selected</span>
                            </div>
                        </div>
                    </label>
                </div>

                @error('observation_type')
                    <p class="mt-2 text-xs text-red-500 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end mt-4">
                <button type="button" @click="goToStep(2)" :disabled="!selectedType"
                        class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed inline-flex items-center gap-1.5">
                    Continue <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>

        <!-- ===== STEP 2: SELECT COT TEMPLATE ===== -->
        <div x-show="currentStep === 2" class="fade-in">
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-7 h-7 rounded-lg bg-indigo-600 text-white flex items-center justify-center text-xs font-bold">2</span>
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Select COT Template</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            <template x-if="selectedType === 'teacher_observation'">For teacher observation</template>
                            <template x-if="selectedType === 'school_head_observation'">For school head observation</template>
                            · SY {{ $schoolYear }}
                        </p>
                    </div>
                </div>

                <div x-show="templateOptions.length === 0" class="text-center py-6 text-gray-400 dark:text-gray-500">
                    <p class="text-xs">No published COT templates for SY {{ $schoolYear }}. Ask admin to publish one.</p>
                </div>

                <div class="space-y-2">
                    <template x-for="template in templateOptions" :key="template.id">
                        <label class="type-card block rounded-xl p-3 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/50 cursor-pointer"
                               :class="selectedCotTemplateId === template.id ? 'selected ring-1 ring-indigo-200' : ''">
                            <input type="radio" name="cot_indicator_version_id" :value="template.id"
                                   x-model="selectedCotTemplateId" @change="onTemplateChange()" class="sr-only">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <h3 class="font-semibold text-gray-900 dark:text-gray-100 text-sm" x-text="template.label"></h3>
                                        <span x-show="template.is_default" class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300">Default</span>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[13px] font-medium bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300" x-text="template.indicators_count + ' indicators'"></span>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 truncate">
                                        <span x-text="template.framework_label"></span> · <span x-text="template.instrument_label"></span>
                                        <template x-if="template.career_stage_label"><span x-text="' · ' + template.career_stage_label"></span></template>
                                        <span x-show="template.ratee_role==='school_head'" x-text="template.requires_post_conference ? ' · With Post-Conference' : ' · No Post-Conference'"></span>
                                    </p>
                                </div>
                                <button type="button"
                                        @click.prevent.stop="openTemplatePreview(template)"
                                        class="shrink-0 inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-indigo-200 dark:border-indigo-800 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:border-indigo-300 text-xs font-medium transition-colors"
                                        title="Preview this template's indicators">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    View
                                </button>
                            </div>
                        </label>
                    </template>
                </div>

                @error('cot_indicator_version_id')
                    <p class="mt-2 text-xs text-red-500 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-between items-center mt-4">
                <button type="button" @click="goToStep(1)"
                        class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 font-medium inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Back
                </button>
                <button type="button" @click="goToStep(3)" :disabled="!selectedCotTemplateId"
                        class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed inline-flex items-center gap-1.5">
                    Continue <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>

        <!-- ===== STEP 3: BROWSE & SELECT OBSERVEE ===== -->
        <div x-show="currentStep === 3" class="fade-in">
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-7 h-7 rounded-lg bg-indigo-600 text-white flex items-center justify-center text-xs font-bold">3</span>
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                            <template x-if="selectedType === 'teacher_observation'">Select a Teacher</template>
                            <template x-if="selectedType === 'school_head_observation'">Select a School Head</template>
                        </h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Search or browse</p>
                    </div>
                </div>

                <!-- Search -->
                <div class="relative mb-3">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" x-model="searchQuery" @input="searchQuery = $event.target.value"
                           class="w-full pl-8 pr-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 outline-none"
                           placeholder="Name, subject, grade...">
                </div>

                <!-- Results List -->
                <div x-show="filteredList.length > 0 && !selectedObservee" class="space-y-1.5 max-h-64 overflow-y-auto pr-1">
                    <template x-for="item in filteredList" :key="item.id">
                        <button type="button" @click="selectObservee(item)"
                                class="observee-card w-full text-left rounded-lg px-4 py-3 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 hover:border-indigo-400 hover:bg-indigo-50/30 flex items-center gap-4">
                            <div class="w-9 h-9 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 flex items-center justify-center text-sm font-semibold shrink-0" x-text="item.name.charAt(0).toUpperCase()"></div>
                            <div class="min-w-0 flex-1">
                                <div class="font-medium text-gray-900 dark:text-gray-100 text-sm" x-text="item.name"></div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 truncate flex items-center gap-2 mt-0.5">
                                    <span x-text="item.position"></span>
                                    <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                                    <span class="inline-flex items-center gap-1"><svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg><span x-text="item.school_name"></span></span>
                                    <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                                    <span x-text="item.subject"></span>
                                    <template x-if="item.department">
                                        <span class="inline-flex items-center gap-1"><span class="w-1 h-1 rounded-full bg-gray-300"></span><span x-text="item.department"></span></span>
                                    </template>
                                </div>
                            </div>
                            <svg class="w-4 h-4 text-gray-400 dark:text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </template>
                </div>

                <!-- No results -->
                <div x-show="filteredList.length === 0 && searchQuery.length > 0 && !selectedObservee"
                     class="text-center py-10 text-gray-400 dark:text-gray-500">
                    <svg class="w-10 h-10 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <p class="text-sm">No matches found. Try a different search term.</p>
                </div>

                <!-- Selected Observee Card — compact -->
                <div x-show="selectedObservee" class="fade-in">
                    <div class="rounded-xl border border-indigo-200 dark:border-indigo-900/40 bg-indigo-50/40 dark:bg-indigo-900/20 p-3.5">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 rounded-full bg-indigo-600 text-white flex items-center justify-center text-sm font-bold" x-text="selectedObservee.name.charAt(0).toUpperCase()"></div>
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100" x-text="selectedObservee.name"></h3>
                                        <template x-if="selectedObservee.profile_url">
                                            <a :href="selectedObservee.profile_url" target="_blank" class="text-xs font-medium text-indigo-600 hover:underline">View Profile →</a>
                                        </template>
                                    </div>
                                    <p class="text-xs text-gray-500" x-text="selectedObservee.position"></p>
                                </div>
                            </div>
                            <button type="button" @click="selectedObservee = null; searchQuery = ''" class="text-gray-400 hover:text-gray-600 p-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-4 gap-y-1.5 text-xs">
                            <div><span class="text-gray-400">School</span><p class="font-medium text-gray-800 dark:text-gray-100 truncate" x-text="selectedObservee.school_name"></p></div>
                            <template x-if="selectedType === 'teacher_observation'">
                                <span class="contents">
                                    <div><span class="text-gray-400">Department</span><p class="font-medium text-gray-800 dark:text-gray-100 truncate" x-text="selectedObservee.department"></p></div>
                                    <div><span class="text-gray-400">Subject</span><p class="font-medium text-gray-800 dark:text-gray-100 truncate" x-text="selectedObservee.subject"></p></div>
                                    <div><span class="text-gray-400">Grade Level</span><p class="font-medium text-gray-800 dark:text-gray-100 truncate" x-text="selectedObservee.grade_level"></p></div>
                                    <div><span class="text-gray-400">Employee No.</span><p class="font-medium text-gray-800 dark:text-gray-100 truncate" x-text="selectedObservee.employee_number"></p></div>
                                </span>
                            </template>
                            <template x-if="selectedType !== 'teacher_observation'">
                                <span class="contents">
                                    <div><span class="text-gray-400">Position Level</span><p class="font-medium text-gray-800 dark:text-gray-100 truncate" x-text="selectedObservee.position_level"></p></div>
                                    <div><span class="text-gray-400">Subject</span><p class="font-medium text-gray-800 dark:text-gray-100 truncate" x-text="selectedObservee.subject"></p></div>
                                    <div><span class="text-gray-400">Grade Level</span><p class="font-medium text-gray-800 dark:text-gray-100 truncate" x-text="selectedObservee.grade_level"></p></div>
                                </span>
                            </template>
                            <div><span class="text-gray-400">Email</span><p class="font-medium text-gray-800 dark:text-gray-100 truncate" x-text="selectedObservee.email"></p></div>
                        </div>

                        <!-- Recent Observations -->
                        <template x-if="selectedType === 'teacher_observation' && selectedObservee.recent_observations && selectedObservee.recent_observations.length > 0">
                            <div class="mt-4 pt-4 border-t border-indigo-200/60">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">Recent Observations</h4>
                                    <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                                        <span><span class="font-medium text-gray-700 dark:text-gray-300" x-text="selectedObservee.obs_stats?.total || 0"></span> total</span>
                                        <span><span class="font-medium text-green-600 dark:text-green-400" x-text="selectedObservee.obs_stats?.completed || 0"></span> done</span>
                                        <span><span class="font-medium text-amber-600 dark:text-amber-400" x-text="selectedObservee.obs_stats?.in_progress || 0"></span> active</span>
                                    </div>
                                </div>
                                <div class="space-y-1.5">
                                    <template x-for="obs in selectedObservee.recent_observations" :key="obs.id">
                                        <a :href="obs.url"
                                           class="flex items-center justify-between rounded-lg px-3 py-2 bg-white/70 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-900/40 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 transition-colors group">
                                            <div class="flex items-center gap-2.5 min-w-0">
                                                <span class="text-xs text-gray-400 dark:text-gray-500 shrink-0 w-16" x-text="obs.date"></span>
                                                <span class="text-sm text-gray-700 dark:text-gray-300 truncate" x-text="obs.subject || 'Observation'"></span>
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium"
                                                      :class="obs.status === 'completed' ? 'bg-green-100 dark:bg-green-900/30 text-green-700' : obs.status === 'cancelled' ? 'bg-red-100 dark:bg-red-900/30 text-red-700' : 'bg-amber-100 dark:bg-amber-900/30 text-amber-700'"
                                                      x-text="obs.status.charAt(0).toUpperCase() + obs.status.slice(1)"></span>
                                            </div>
                                            <div class="flex items-center gap-2 shrink-0">
                                                <template x-if="obs.score">
                                                    <span class="text-xs font-medium text-indigo-600 dark:text-indigo-400" x-text="obs.score"></span>
                                                </template>
                                                <svg class="w-3.5 h-3.5 text-gray-300 group-hover:text-indigo-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                            </div>
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                </div>

                @error('observee_id')
                    <p class="mt-3 text-sm text-red-500 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-between items-center mt-6">
                <button type="button" @click="selectedObservee = null; goToStep(2)"
                        class="px-5 py-2.5 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 font-medium transition-colors inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Back
                </button>
                <button type="button" @click="autoFillDetails(); goToStep(4)" :disabled="!selectedObservee"
                        class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors inline-flex items-center gap-2">
                    Continue
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>

        <!-- ===== STEP 4: OBSERVATION SCHEDULE ===== -->
        <div x-show="currentStep === 4" class="fade-in">
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center gap-2 mb-3">
                    <span class="w-7 h-7 rounded-lg bg-indigo-600 text-white flex items-center justify-center text-xs font-bold">4</span>
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Observation Schedule</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Date, time & location</p>
                    </div>
                </div>

                <!-- Section: When & Where — minimized -->
                <div class="mb-4">
                    <div class="flex items-center gap-1.5 mb-2">
                        <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <h3 class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">When &amp; Where</h3>
                    </div>

                    <!-- Observation Date -->
                    <div class="sm:col-span-2 mb-3">
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Observation Date</label>
                        <input type="date" name="observation_date" x-model="form.observation_date" :min="today"
                               class="w-full sm:max-w-xs px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>

                    <div class="grid sm:grid-cols-2 gap-x-4 gap-y-3">
                        <!-- Start Time -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Start Time</label>
                            <input type="time" name="start_time" x-model="form.start_time"
                                   class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                            @error('start_time')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- End Time -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">End Time</label>
                            <input type="time" name="end_time" x-model="form.end_time"
                                   class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                            @error('end_time')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Observation Mode -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Observation Mode</label>
                            <select name="observation_mode" x-model="form.observation_mode"
                                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                <option value="in_person">In-Person</option>
                                <option value="virtual">Virtual</option>
                                <option value="hybrid">Hybrid</option>
                            </select>
                        </div>

                        <!-- Location -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Location <span class="text-gray-400 font-normal">(optional)</span></label>
                            <input type="text" name="location" x-model="form.location"
                                   class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 outline-none"
                                   placeholder="Room 204 / Online link">
                        </div>
                    </div>
                </div>

                <!-- Academic Context — minimized -->
                <div class="border-t border-gray-100 dark:border-gray-800 pt-4 mb-4">
                    <div class="flex items-center gap-1.5 mb-2">
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h10M4 18h6"/></svg>
                        <h3 class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Academic Context</h3>
                        <span class="text-[10px] text-gray-400 font-normal">Auto-filled — edit if needed</span>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-x-4 gap-y-3">
                        <!-- School Year -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">School Year</label>
                            <input type="text" name="school_year" x-model="form.school_year"
                                   class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 outline-none"
                                   placeholder="2024-2025">
                        </div>

                        <!-- Quarter -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Quarter</label>
                            <select name="quarter" x-model="form.quarter"
                                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                <option value="">Select quarter</option>
                                <option value="1">1st Quarter</option>
                                <option value="2">2nd Quarter</option>
                                <option value="3">3rd Quarter</option>
                                <option value="4">4th Quarter</option>
                            </select>
                        </div>

                        <!-- Observation Number -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Observation Number</label>
                            <select name="observation_number" x-model="form.observation_number"
                                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                <option value="1">1st Observation</option>
                                <option value="2">2nd Observation</option>
                            </select>
                        </div>

                        <!-- Subject (auto-filled) -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Subject</label>
                            <div class="relative">
                                <select name="subject" x-model="form.subject" x-show="selectedSubjList.length"
                                        class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                                    <option value="" disabled>Select subject</option>
                                    <template x-for="s in selectedSubjList" :key="s">
                                        <option :value="s" x-text="s"></option>
                                    </template>
                                </select>
                                <input type="text" name="subject" x-model="form.subject" x-show="!selectedSubjList.length"
                                       class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 outline-none"
                                       placeholder="Auto-filled">
                                <template x-if="selectedObservee && selectedObservee.subject && selectedObservee.subject !== 'Not set'">
                                    <span class="absolute right-2 top-1/2 -translate-y-1/2 text-[10px] font-medium text-indigo-600 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/30 px-1.5 py-0.5 rounded-full">Auto</span>
                                </template>
                            </div>
                        </div>

                        <!-- Grade Level (auto-filled) -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Grade Level</label>
                            <div class="relative">
                                <input type="text" name="grade_level" x-model="form.grade_level"
                                       class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 outline-none"
                                       placeholder="Auto-filled">
                                <template x-if="selectedObservee && selectedObservee.grade_level && selectedObservee.grade_level !== 'Not set'">
                                    <span class="absolute right-2 top-1/2 -translate-y-1/2 text-[10px] font-medium text-indigo-600 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/30 px-1.5 py-0.5 rounded-full">Auto</span>
                                </template>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Notes <span class="text-gray-400 font-normal">(optional)</span></label>
                            <textarea name="notes" x-model="form.notes" rows="2"
                                      class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 outline-none"
                                      placeholder="Additional notes..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Co-Observation — CLEAR FLOW, UX friendly -->
                <div class="rounded-xl border-2 p-3.5" :class="form.school_head_id ? 'border-amber-300 bg-amber-50/50 dark:bg-amber-900/10' : 'border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/30'">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-amber-600 text-white flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Co-Observation <span class="text-xs font-normal text-gray-400">— Optional</span></h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">You are the lead observer. Optionally, invite a School Head as co-observer — they'll be notified and submit their own ratings.</p>
                        </div>
                        <span class="text-[10px] font-medium px-2 py-1 rounded-full shrink-0" :class="form.school_head_id ? 'bg-amber-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300'"><span x-text="form.school_head_id ? 'Co-observer added' : 'Not included'"></span></span>
                    </div>

                    <!-- Clear choice — two cards -->
                    <div class="mt-3 grid grid-cols-2 gap-2">
                        <button type="button" @click="form.school_head_id=''" :class="!form.school_head_id ? 'border-indigo-600 bg-indigo-50 ring-1 ring-indigo-200' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'" class="rounded-xl border-2 p-3 text-left transition-all">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center" :class="!form.school_head_id ? 'border-indigo-600 bg-indigo-600' : 'border-gray-300'"><svg x-show="!form.school_head_id" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg></div>
                                <div>
                                    <p class="text-xs font-semibold" :class="!form.school_head_id ? 'text-indigo-700' : 'text-gray-700 dark:text-gray-300'">Supervisor only</p>
                                    <p class="text-[11px] text-gray-400">You observe alone</p>
                                </div>
                            </div>
                        </button>
                        <button type="button" @click="toggleSchoolHead()"
                                :disabled="!schoolHeadData || !schoolHeadData.length"
                                :class="form.school_head_id ? 'border-amber-600 bg-amber-50 ring-1 ring-amber-200' : (!schoolHeadData || !schoolHeadData.length) ? 'border-gray-200 dark:border-gray-700 opacity-50 cursor-not-allowed' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'"
                                class="rounded-xl border-2 p-3 text-left transition-all">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center" :class="form.school_head_id ? 'border-amber-600 bg-amber-600' : 'border-gray-300'"><svg x-show="form.school_head_id" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg></div>
                                <div>
                                    <p class="text-xs font-semibold" :class="form.school_head_id ? 'text-amber-700' : 'text-gray-700 dark:text-gray-300'">With School Head</p>
                                    <p class="text-[11px]" :class="(!schoolHeadData || !schoolHeadData.length) ? 'text-gray-400 italic' : 'text-gray-400'" x-text="(!schoolHeadData || !schoolHeadData.length) ? 'No school heads available' : 'School Head co-observes & co-rates'"></p>
                                </div>
                            </div>
                        </button>
                    </div>

                    <!-- Flow visualization -->
                    <div class="mt-3 flex items-center justify-center gap-1.5 text-xs">
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-indigo-600 text-white font-medium"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg> Supervisor</span>
                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full font-medium" :class="form.school_head_id ? 'bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800' : 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 border border-dashed dark:border-gray-700'"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg> <span class="truncate max-w-[80px]" x-text="selectedSchoolHead?.name?.split(' ')[0] || '—'"></span></span>
                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-emerald-600 text-white font-medium"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/></svg> <span class="truncate max-w-[80px]" x-text="selectedObservee?.name?.split(' ')[0] || 'Teacher'"></span></span>
                    </div>

                    <div x-show="!!form.school_head_id" x-transition class="mt-3">
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Select Co-Observer (School Head) <span class="text-amber-600">*</span></label>
                        <select name="school_head_id" x-model="form.school_head_id"
                                class="w-full px-3 py-2 rounded-lg border border-amber-300 dark:border-amber-700 bg-white dark:bg-gray-800 text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-amber-500 outline-none">
                            <option value="">— Choose School Head —</option>
                            @foreach($schoolHeadData as $sh)
                                <option value="{{ $sh['user_id'] }}">{{ $sh['name'] }} — {{ $sh['position'] }} · {{ $sh['school_name'] }}</option>
                            @endforeach
                        </select>
                        @error('school_head_id')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                        <template x-if="selectedSchoolHead">
                            <div class="mt-2 rounded-lg border border-amber-200 bg-white dark:bg-gray-900 p-3 flex items-start gap-2.5">
                                <div class="w-8 h-8 rounded-full bg-amber-600 text-white flex items-center justify-center text-xs font-bold shrink-0" x-text="selectedSchoolHead?.name?.charAt(0)?.toUpperCase() || '?'"></div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate" x-text="selectedSchoolHead?.name"></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                        <span x-text="selectedSchoolHead?.position"></span>
                                        <span class="text-gray-300">·</span>
                                        <span class="inline-flex items-center gap-1 font-medium text-amber-700 dark:text-amber-400"><svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg><span x-text="selectedSchoolHead?.school_name"></span></span>
                                    </p>
                                </div>
                            </div>
                        </template>
                        <p class="mt-1.5 text-xs text-emerald-600 flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Will be notified and can co-rate this observation.</p>
                    </div>
                    <div x-show="!form.school_head_id" class="mt-2 text-xs text-gray-400">Supervisor-only observation — continue to next step.</div>
                </div>
            </div>

            <div class="flex justify-between items-center mt-4">
                <button type="button" @click="goToStep(3)"
                        class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 font-medium inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Back
                </button>
                <button type="button" @click="goToStep(5)"
                        class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 inline-flex items-center gap-1.5">
                    Continue <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>

        <!-- ===== STEP 5: POST-OBSERVATION CONFERENCE ===== -->
        <div x-show="currentStep === 5" class="fade-in">
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center gap-2 mb-2">
                    <span class="w-7 h-7 rounded-lg bg-indigo-600 text-white flex items-center justify-center text-xs font-bold">5</span>
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Post-Observation Conference</h2>
                        <template x-if="selectedType === 'teacher_observation'">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Optional — schedule feedback later if needed</p>
                        </template>
                        <template x-if="selectedType === 'school_head_observation'">
                            <p class="text-xs text-gray-500 dark:text-gray-400" x-text="selectedCotTemplateRequiresPostConference ? 'Included by PPSSH template — will be scheduled' : 'Not included by this PPSSH template — workflow skips conference'"></p>
                        </template>
                    </div>
                </div>

                <!-- Teacher: manual toggle (existing workflow unchanged) -->
                <template x-if="selectedType === 'teacher_observation'">
                    <label class="flex items-start gap-3 cursor-pointer rounded-xl border-2 p-4 transition-all mb-3"
                           :class="scheduleConference ? 'border-indigo-600 bg-indigo-50/40 dark:bg-indigo-900/20' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 hover:border-gray-300'">
                        <input type="checkbox" name="schedule_conference" value="1" x-model="scheduleConference" class="sr-only">
                        <span class="w-5 h-5 rounded border-2 mt-0.5 flex items-center justify-center shrink-0 transition-colors"
                              :class="scheduleConference ? 'bg-indigo-600 border-indigo-600' : 'border-gray-300 dark:border-gray-600'">
                            <svg x-show="scheduleConference" class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        <span>
                            <span class="font-medium text-gray-900 dark:text-gray-100 text-sm block">Schedule a Post-Observation Conference</span>
                            <span class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 block">The ratee will be notified with the conference schedule. You can skip this and schedule later.</span>
                        </span>
                    </label>
                </template>

                <!-- School Head: template-driven notice (no manual checkbox) -->
                <template x-if="selectedType === 'school_head_observation'">
                    <div class="mb-3">
                        <div x-show="selectedCotTemplateRequiresPostConference" class="rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50/60 dark:bg-emerald-900/20 p-3 flex items-start gap-2.5">
                            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <div>
                                <p class="text-sm font-medium text-emerald-800 dark:text-emerald-300">Post-Observation Conference is part of this PPSSH template</p>
                                <p class="text-xs text-emerald-700/80 dark:text-emerald-400/80 mt-0.5">Workflow: Pre-Observation → Observation → Post-Conference → Feedback/Development → Finalize. Fill conference details below (optional at scheduling; can be set later).</p>
                            </div>
                        </div>
                        <div x-show="!selectedCotTemplateRequiresPostConference" class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-3 flex items-start gap-2.5">
                            <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <div>
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-200">This PPSSH template does not include Post-Observation Conference</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Workflow: Pre-Observation → Observation → Feedback/Development → Finalize. No conference scheduling needed.</p>
                            </div>
                        </div>
                    </div>
                </template>

                <div x-show="showConferenceFields" class="fade-in">
                    <div class="grid sm:grid-cols-2 gap-x-4 gap-y-3">
                        <!-- Conference Date -->
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Conference Date <span x-show="selectedType==='teacher_observation'" class="text-gray-400 font-normal">(optional)</span></label>
                            <input type="date" name="conference_date" x-model="form.conference_date"
                                   class="w-full sm:max-w-xs px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        </div>

                        <!-- Conference Start Time -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Start Time</label>
                            <input type="time" name="conference_start_time" x-model="form.conference_start_time"
                                   class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        </div>

                        <!-- Conference End Time -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">End Time</label>
                            <input type="time" name="conference_end_time" x-model="form.conference_end_time"
                                   class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        </div>

                        <!-- Conference Mode -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Conference Mode</label>
                            <select name="conference_mode" x-model="form.conference_mode"
                                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                                <option value="in_person">In-Person</option>
                                <option value="virtual">Virtual</option>
                                <option value="hybrid">Hybrid</option>
                            </select>
                        </div>

                        <!-- Conference Location -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Location</label>
                            <input type="text" name="conference_location" x-model="form.conference_location"
                                   class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                                   placeholder="e.g., Office, Meeting Room, or Online link">

                            @error('conference_start_time')
                                <p class="mt-2 text-sm text-red-500 dark:text-red-400">{{ $message }}</p>
                            @enderror
                            @error('conference_end_time')
                                <p class="mt-2 text-sm text-red-500 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-between items-center mt-6">
                <button type="button" @click="goToStep(4)"
                        class="px-5 py-2.5 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 font-medium transition-colors inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Back
                </button>
                <button type="button" @click="openConfirmModal()"
                        class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors inline-flex items-center gap-2">
                    Review &amp; Confirm
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>

        <!-- ===== CONFIRMATION MODAL ===== -->
        <div x-show="showConfirmModal" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             @click.self="showConfirmModal = false"
             @keydown.escape.window="showConfirmModal = false">
            <div class="modal-backdrop fixed inset-0 bg-black/40 pointer-events-none"></div>
            <div class="relative z-10 bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-xl max-h-[90vh] overflow-y-auto fade-in">
                <div class="p-6 sm:p-8">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                                <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">Review & Confirm</h2>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Please review before creating.</p>
                            </div>
                        </div>
                        <button type="button" @click="showConfirmModal = false" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:text-gray-400 p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Preview Content -->
                    <div class="space-y-5">
                        <!-- Observee Info -->
                        <div class="rounded-xl bg-indigo-50/60 border border-indigo-100 p-4">
                            <p class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider mb-3">Observee</p>
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 flex items-center justify-center text-base font-bold shrink-0" x-text="selectedObservee?.name?.charAt(0) || '?'"></div>
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-gray-100" x-text="selectedObservee?.name"></p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400" x-text="selectedObservee?.position + (selectedObservee?.department ? ' · ' + selectedObservee?.department : '')"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Observation Details -->
                        <div class="rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 p-4">
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Observation Info</p>
                            <div class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                                <div><span class="text-gray-500 dark:text-gray-400">Type</span><p class="font-medium text-gray-800 dark:text-gray-100"><template x-if="selectedType === 'teacher_observation'">Teacher Observation</template><template x-if="selectedType !== 'teacher_observation'">School Head Observation</template></p></div>
                                <div><span class="text-gray-500 dark:text-gray-400">Date</span><p class="font-medium text-gray-800 dark:text-gray-100" x-text="form.observation_date"></p></div>
                                <div><span class="text-gray-500 dark:text-gray-400">Time</span><p class="font-medium text-gray-800 dark:text-gray-100" x-text="timeLabel || '—'"></p></div>
                                <div><span class="text-gray-500 dark:text-gray-400">Location</span><p class="font-medium text-gray-800 dark:text-gray-100" x-text="form.location || '—'"></p></div>
                                <div><span class="text-gray-500 dark:text-gray-400">School Year</span><p class="font-medium text-gray-800 dark:text-gray-100" x-text="form.school_year"></p></div>
                                <div><span class="text-gray-500 dark:text-gray-400">Quarter</span><p class="font-medium text-gray-800 dark:text-gray-100" x-text="'Quarter ' + form.quarter"></p></div>
                                <div><span class="text-gray-500 dark:text-gray-400">Subject</span><p class="font-medium text-gray-800 dark:text-gray-100" x-text="form.subject || 'Not set'"></p></div>
                                <div><span class="text-gray-500 dark:text-gray-400">Grade Level</span><p class="font-medium text-gray-800 dark:text-gray-100" x-text="form.grade_level || 'Not set'"></p></div>
                                <div><span class="text-gray-500 dark:text-gray-400">Observation #</span><p class="font-medium text-gray-800 dark:text-gray-100" x-text="form.observation_number === '2' ? '2nd' : '1st'"></p></div>
                                <div><span class="text-gray-500 dark:text-gray-400">Mode</span><p class="font-medium text-gray-800 capitalize" x-text="form.observation_mode?.replace('_', ' ')"></p></div>
                            </div>
                        </div>

                        <!-- Template -->
                        <div class="rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 p-4">
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">COT Template</p>
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-100" x-text="selectedCotTemplate?.label || 'Default COT template (auto-selected)'"></p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                School Year <span x-text="selectedCotTemplate?.school_year || form.school_year"></span>
                                <template x-if="selectedCotTemplate">
                                    <span x-text="' · ' + selectedCotTemplate.indicators_count + ' indicators'"></span>
                                </template>
                                <template x-if="!selectedCotTemplate">
                                    <span> · resolved from the teacher's career stage</span>
                                </template>
                            </p>
                        </div>

                        <!-- Post-Conference -->
                        <div x-show="showConferenceFields" class="rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 p-4">
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Post-Observation Conference <span x-show="selectedType==='school_head_observation'" class="normal-case font-normal text-emerald-600">(via PPSSH template)</span></p>
                            <div class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                                <div><span class="text-gray-500 dark:text-gray-400">Date</span><p class="font-medium text-gray-800 dark:text-gray-100" x-text="form.conference_date || '—'"></p></div>
                                <div><span class="text-gray-500 dark:text-gray-400">Time</span><p class="font-medium text-gray-800 dark:text-gray-100" x-text="conferenceTimeLabel || '—'"></p></div>
                                <div><span class="text-gray-500 dark:text-gray-400">Mode</span><p class="font-medium text-gray-800 capitalize" x-text="form.conference_mode?.replace('_', ' ')"></p></div>
                                <div><span class="text-gray-500 dark:text-gray-400">Location</span><p class="font-medium text-gray-800 dark:text-gray-100" x-text="form.conference_location || '—'"></p></div>
                            </div>
                        </div>

                        <!-- Notes (if any) -->
                        <div x-show="form.notes" class="rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 p-4">
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Notes</p>
                            <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap" x-text="form.notes"></p>
                        </div>

                        <!-- Co-Observation (if selected) -->
                        <div x-show="form.school_head_id" class="rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 p-4">
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Co-Observation</p>
                            <p class="text-sm font-medium text-gray-800 dark:text-gray-200" x-text="selectedSchoolHead?.name"></p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5" x-text="[selectedSchoolHead?.position, selectedSchoolHead?.school_name].filter(Boolean).join(' · ')"></p>
                        </div>
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex justify-end gap-3 mt-8 pt-5 border-t border-gray-100 dark:border-gray-800">
                        <button type="button" @click="showConfirmModal = false"
                                class="px-5 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:text-gray-100 transition-colors">
                            Go Back
                        </button>
                        <button type="button"
                                @click="submitting = true; $el.closest('form').submit()"
                                class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
                            <span x-show="!submitting">Confirm &amp; Schedule</span>
                            <span x-show="submitting" class="flex items-center gap-2">
                                <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                Scheduling...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- COT Template Preview Modal -->
        <div x-show="previewTemplate" x-cloak @keydown.escape.window="closeTemplatePreview()"
             class="fixed inset-0 z-[70] overflow-y-auto" role="dialog" aria-modal="true" aria-label="COT Template Preview">
            <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="closeTemplatePreview()"></div>
            <div class="min-h-full flex items-center justify-center p-4">
            <div class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-xl w-full max-w-2xl max-h-[85vh] flex flex-col"
                 x-show="previewTemplate"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">
                <!-- Header -->
                <div class="flex items-start justify-between gap-3 px-5 py-4 border-b border-gray-100 dark:border-gray-800 shrink-0">
                    <div class="min-w-0">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 truncate" x-text="previewTemplate?.label"></h3>
                            <span x-show="previewTemplate?.is_default" class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-emerald-100 text-emerald-700">Default</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 flex flex-wrap items-center gap-x-1.5 gap-y-0.5">
                            <span class="inline-flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg><span x-text="'SY ' + previewTemplate?.school_year"></span></span>
                            <span class="text-gray-300">·</span>
                            <span x-text="previewTemplate?.framework_label"></span>
                            <span class="text-gray-300">·</span>
                            <span x-text="previewTemplate?.instrument_label"></span>
                            <template x-if="previewTemplate?.career_stage_label">
                                <span class="inline-flex items-center gap-x-1"><span class="text-gray-300">·</span><span x-text="previewTemplate?.career_stage_label"></span></span>
                            </template>
                            <span class="text-gray-300">·</span>
                            <span class="font-medium text-indigo-600 dark:text-indigo-400" x-text="(previewTemplate?.indicators_count || 0) + ' indicators'"></span>
                        </p>
                    </div>
                    <button type="button" @click="closeTemplatePreview()" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors shrink-0" aria-label="Close preview">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Body — indicators grouped by domain -->
                <div class="flex-1 overflow-y-auto px-5 py-4 space-y-4">
                    <template x-if="previewGroups.length === 0">
                        <p class="text-sm text-gray-400 dark:text-gray-500 text-center py-8">No indicators defined for this template yet.</p>
                    </template>
                    <template x-for="[domain, indicators] in previewGroups" :key="domain">
                        <div class="rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
                            <div class="px-4 py-2 bg-indigo-50/70 dark:bg-indigo-900/20 border-b border-indigo-100 dark:border-indigo-900/40 flex items-center justify-between gap-2">
                                <p class="text-xs font-semibold text-indigo-700 dark:text-indigo-300 uppercase tracking-wider truncate" x-text="domain"></p>
                                <span class="text-[10px] font-medium text-indigo-500 bg-white/70 dark:bg-gray-900/50 px-1.5 py-0.5 rounded-full shrink-0" x-text="indicators.length + (indicators.length === 1 ? ' indicator' : ' indicators')"></span>
                            </div>
                            <ol class="divide-y divide-gray-50 dark:divide-gray-800/60">
                                <template x-for="(indicator, idx) in indicators" :key="domain + '-' + idx">
                                    <li class="px-4 py-2.5 flex items-start gap-3">
                                        <span class="mt-0.5 inline-flex items-center justify-center min-w-[44px] h-5 px-1.5 rounded-md bg-gray-100 dark:bg-gray-800 text-[10px] font-bold text-gray-600 dark:text-gray-300 shrink-0" x-text="indicator.code || (idx + 1)"></span>
                                        <span class="text-sm text-gray-700 dark:text-gray-300 leading-snug" x-text="indicator.description"></span>
                                    </li>
                                </template>
                            </ol>
                        </div>
                    </template>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-between gap-3 px-5 py-3.5 border-t border-gray-100 dark:border-gray-800 shrink-0">
                    <button type="button" @click="closeTemplatePreview()"
                            class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 transition-colors">
                        Close
                    </button>
                    <button type="button" @click="usePreviewedTemplate()"
                            class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span x-text="selectedCotTemplateId === previewTemplate?.id ? 'Selected' : 'Use this template'"></span>
                    </button>
                </div>
            </div>
            </div>
        </div>

        <!-- Always-rendered hidden field for observee_id -->
        <input type="hidden" name="observee_id" x-model="observeeId">
        <!-- Always-rendered hidden field for the selected COT template -->
        <input type="hidden" name="cot_indicator_version_id" x-model="selectedCotTemplateId">
        <!-- This wizard only schedules observations (no immediate option) -->
        <input type="hidden" name="schedule_type" value="scheduled">
    </form>

    <!-- Sticky Summary Sidebar -->
    <aside class="lg:col-span-1">
        <div class="lg:sticky lg:top-24 space-y-4">
            <!-- Progress -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Progress</p>
                    <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400" x-text="'Step ' + currentStep + ' of ' + steps.length"></span>
                </div>
                <div class="h-1.5 rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
                    <div class="h-full bg-indigo-600 rounded-full transition-all duration-300" :style="'width: ' + progressPercent + '%'"></div>
                </div>
            </div>

            <!-- Live Summary -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5">
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Summary</p>

                <div x-show="selectedObservee" class="flex items-center gap-3 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-900/40 p-3">
                    <div class="w-9 h-9 rounded-full bg-indigo-600 text-white flex items-center justify-center text-sm font-bold shrink-0" x-text="selectedObservee?.name?.charAt(0)?.toUpperCase() || '?'"></div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate" x-text="selectedObservee?.name"></p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate" x-text="selectedObservee?.position"></p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate inline-flex items-center gap-1"><svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg><span x-text="selectedObservee?.school_name"></span></p>
                    </div>
                </div>
                <div x-show="!selectedObservee" class="rounded-lg bg-gray-50 dark:bg-gray-800 border border-dashed border-gray-200 dark:border-gray-700 p-4 text-center">
                    <p class="text-xs text-gray-400 dark:text-gray-500">No ratee selected yet.</p>
                </div>

                <template x-if="selectedSchoolHead">
                    <div class="mt-3 flex items-center gap-3 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-900/40 p-3">
                        <div class="w-9 h-9 rounded-full bg-amber-600 text-white flex items-center justify-center text-sm font-bold shrink-0" x-text="selectedSchoolHead?.name?.charAt(0)?.toUpperCase() || '?'"></div>
                        <div class="min-w-0">
                            <p class="text-[10px] font-semibold text-amber-700 dark:text-amber-400 uppercase tracking-wider">Co-Observer</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate" x-text="selectedSchoolHead?.name"></p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate" x-text="[selectedSchoolHead?.position, selectedSchoolHead?.school_name].filter(Boolean).join(' · ')"></p>
                        </div>
                    </div>
                </template>

                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-xs text-gray-400 dark:text-gray-500">Type</dt>
                        <dd class="font-medium text-gray-800 dark:text-gray-200 text-right" x-text="selectedTypeLabel"></dd>
                    </div>
                    <template x-if="selectedCotTemplate">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-xs text-gray-400 dark:text-gray-500">Template</dt>
                            <dd class="font-medium text-gray-800 dark:text-gray-200 text-right truncate" x-text="selectedCotTemplate.label"></dd>
                        </div>
                    </template>
                    <template x-if="form.observation_date">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-xs text-gray-400 dark:text-gray-500">Date</dt>
                            <dd class="font-medium text-gray-800 dark:text-gray-200 text-right" x-text="form.observation_date"></dd>
                        </div>
                    </template>
                    <template x-if="timeLabel">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-xs text-gray-400 dark:text-gray-500">Time</dt>
                            <dd class="font-medium text-gray-800 dark:text-gray-200 text-right" x-text="timeLabel"></dd>
                        </div>
                    </template>
                    <template x-if="form.location">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-xs text-gray-400 dark:text-gray-500">Location</dt>
                            <dd class="font-medium text-gray-800 dark:text-gray-200 text-right truncate" x-text="form.location"></dd>
                        </div>
                    </template>
                    <template x-if="form.subject">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-xs text-gray-400 dark:text-gray-500">Subject</dt>
                            <dd class="font-medium text-gray-800 dark:text-gray-200 text-right truncate" x-text="form.subject"></dd>
                        </div>
                    </template>
                    <div x-show="showConferenceFields" class="flex items-center justify-between gap-3">
                        <dt class="text-xs text-gray-400 dark:text-gray-500">Conference</dt>
                        <dd class="font-medium text-emerald-600 dark:text-emerald-400 text-right" x-text="selectedType==='school_head_observation' ? 'Via PPSSH Template' : 'Scheduled'"></dd>
                    </div>
                </dl>
            </div>

            <!-- Tip -->
            <div class="hidden lg:block rounded-xl bg-gradient-to-br from-indigo-50 to-violet-50 dark:from-indigo-900/20 dark:to-violet-900/20 border border-indigo-100 dark:border-indigo-900/40 p-5">
                <p class="text-xs font-semibold text-indigo-700 dark:text-indigo-300 mb-1.5 flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Tip
                </p>
                <p class="text-xs text-indigo-700/80 dark:text-indigo-300/80 leading-relaxed">
                    Details like subject and grade level are auto-filled from the ratee's profile.
                    The summary updates live as you fill in the schedule.
                </p>
            </div>
        </div>
    </aside>
</div>

@push('scripts')
<script>
    function observationForm() {
        return {
            steps: [
                { label: 'Who', status: 'active' },
                { label: 'Template', status: 'pending' },
                { label: 'Observee', status: 'pending' },
                { label: 'Schedule', status: 'pending' },
                { label: 'Conference', status: 'pending' },
                { label: 'Review', status: 'pending' },
            ],
            currentStep: 1,
            selectedType: @json(old('observation_type')),
            selectedCotTemplateId: @json(old('cot_indicator_version_id')),
            selectedObservee: null,
            observeeId: @json(old('observee_id')),
            searchQuery: '',
            showConfirmModal: false,
            submitting: false,
            scheduleConference: @json(old('schedule_conference') ? true : false),
            today: (() => {
                const d = new Date();
                const m = String(d.getMonth() + 1).padStart(2, '0');
                const day = String(d.getDate()).padStart(2, '0');
                return `${d.getFullYear()}-${m}-${day}`;
            })(),

            teacherData: @json($teacherData),
            schoolHeadData: @json($schoolHeadData),
            cotTemplates: @json($cotTemplates),

            form: {
                school_year: @json(old('school_year', $schoolYear)),
                quarter: @json(old('quarter')),
                observation_number: @json(old('observation_number', '1')),
                observation_mode: @json(old('observation_mode', 'in_person')),
                subject: @json(old('subject')),
                grade_level: @json(old('grade_level')),
                observation_date: @json(old('observation_date', now()->format('Y-m-d'))),
                start_time: @json(old('start_time')),
                end_time: @json(old('end_time')),
                location: @json(old('location')),
                notes: @json(old('notes')),
                conference_date: @json(old('conference_date')),
                conference_start_time: @json(old('conference_start_time')),
                conference_end_time: @json(old('conference_end_time')),
                conference_location: @json(old('conference_location')),
                conference_mode: @json(old('conference_mode', 'in_person')),
                school_head_id: @json(old('school_head_id')),
            },

            get templateOptions() {
                if (!this.selectedType) return [];
                const role = this.selectedType === 'teacher_observation' ? 'teacher' : 'school_head';
                return (this.cotTemplates || []).filter(t => t.ratee_role === role);
            },

            get selectedCotTemplate() {
                return (this.cotTemplates || []).find(t => String(t.id) === String(this.selectedCotTemplateId)) || null;
            },

            get selectedCotTemplateRequiresPostConference() {
                if (this.selectedType === 'teacher_observation') return this.scheduleConference;
                const t = this.selectedCotTemplate;
                return t ? !!t.requires_post_conference : true;
            },

            get showConferenceFields() {
                if (this.selectedType === 'school_head_observation') return this.selectedCotTemplateRequiresPostConference;
                return this.scheduleConference;
            },

            get selectedTypeLabel() {
                if (this.selectedType === 'teacher_observation') return 'Teacher Observation';
                if (this.selectedType === 'school_head_observation') return 'School Head Observation';
                return '—';
            },

            get progressPercent() {
                return Math.min(100, Math.max(0, Math.round(((this.currentStep - 1) / (this.steps.length - 1)) * 100)));
            },

            get observeeList() {
                return this.selectedType === 'teacher_observation' ? this.teacherData : this.schoolHeadData;
            },

            get selectedSchoolHead() {
                if (!this.form.school_head_id) return null;
                return (this.schoolHeadData || []).find(sh => String(sh.user_id) === String(this.form.school_head_id)) || null;
            },

            get selectedSubjList() {
                const s = this.selectedObservee && this.selectedObservee.subjects;
                return s && s.length ? s : [];
            },

            previewTemplate: null,

            openTemplatePreview(template) {
                this.previewTemplate = template;
            },

            closeTemplatePreview() {
                this.previewTemplate = null;
            },

            usePreviewedTemplate() {
                if (this.previewTemplate) {
                    this.selectedCotTemplateId = this.previewTemplate.id;
                }
                this.closeTemplatePreview();
            },

            get previewGroups() {
                return this.previewTemplate ? Object.entries(this.previewTemplate.indicator_groups || {}) : [];
            },

            get filteredList() {
                if (!this.searchQuery) return this.observeeList;
                const q = this.searchQuery.toLowerCase();
                return this.observeeList.filter(item =>
                    item.name?.toLowerCase().includes(q) ||
                    (item.subjects || []).join(' ').toLowerCase().includes(q) ||
                    item.subject?.toLowerCase().includes(q) ||
                    item.grade_level?.toLowerCase().includes(q) ||
                    item.department?.toLowerCase().includes(q) ||
                    item.position?.toLowerCase().includes(q) ||
                    item.school_name?.toLowerCase().includes(q) ||
                    item.email?.toLowerCase().includes(q)
                );
            },

            get timeLabel() {
                return this.formatTimeRange(this.form.start_time, this.form.end_time);
            },

            get conferenceTimeLabel() {
                return this.formatTimeRange(this.form.conference_start_time, this.form.conference_end_time);
            },

            formatTimeRange(start, end) {
                if (!start && !end) return '';
                const s = this.formatTime(start);
                const e = this.formatTime(end);
                if (s && e) return `${s} – ${e}`;
                return s || e || '';
            },

            formatTime(t) {
                if (!t) return '';
                const parts = t.split(':');
                if (parts.length < 2) return t;
                const h = parseInt(parts[0], 10);
                const m = parseInt(parts[1], 10);
                const ampm = h >= 12 ? 'PM' : 'AM';
                const hr = h % 12 || 12;
                return `${hr}:${String(m).padStart(2, '0')} ${ampm}`;
            },

            onTypeChange() {
                this.selectedObservee = null;
                this.observeeId = '';
                this.searchQuery = '';
                this.selectedCotTemplateId = '';
                this.updateSteps();
            },

            onTemplateChange() {
                this.updateSteps();
            },

            selectObservee(item) {
                this.selectedObservee = item;
                this.observeeId = item.id;
                this.searchQuery = '';
            },

            toggleSchoolHead() {
                if (this.schoolHeadData && this.schoolHeadData.length) {
                    this.form.school_head_id = String(this.schoolHeadData[0].user_id);
                } else {
                    this.form.school_head_id = '';
                }
            },

            autoFillDetails() {
                if (this.selectedObservee) {
                    const subjList = this.selectedObservee.subjects || [];
                    if (subjList.length) {
                        this.form.subject = subjList[0];
                    } else if (this.selectedObservee.subject && this.selectedObservee.subject !== 'Not set') {
                        this.form.subject = this.selectedObservee.subject;
                    }
                    if (this.selectedObservee.grade_level && this.selectedObservee.grade_level !== 'Not set') {
                        this.form.grade_level = this.selectedObservee.grade_level;
                    }
                }
            },

            openConfirmModal() {
                this.showConfirmModal = true;
            },

            goToStep(n) {
                if (n >= 1 && n <= this.steps.length) {
                    this.currentStep = n;
                    this.updateSteps();
                }
            },

            jumpToStep(n) {
                const target = this.steps[n - 1];
                if (target && n <= this.currentStep && (target.status === 'complete' || target.status === 'active')) {
                    this.goToStep(n);
                }
            },

            updateSteps() {
                const hasType = !!this.selectedType;
                const hasTemplate = !!this.selectedCotTemplateId;
                const hasObservee = !!this.selectedObservee;
                this.steps[0].status = hasType ? 'complete' : 'active';
                this.steps[1].status = hasTemplate ? 'complete' : (hasType ? 'active' : 'pending');
                this.steps[2].status = hasObservee ? 'complete' : (hasTemplate ? 'active' : 'pending');
                this.steps[3].status = hasObservee ? 'active' : 'pending';
                this.steps[4].status = 'pending';
                this.steps[5].status = 'pending';

                // Exactly one step is highlighted at a time: the one on screen.
                for (let i = 0; i < this.steps.length; i++) {
                    if (this.steps[i].status === 'active') this.steps[i].status = 'pending';
                }
                const idx = this.currentStep - 1;
                if (this.steps[idx]) this.steps[idx].status = 'active';
            },

            init() {
                const params = new URLSearchParams(window.location.search);
                const preselectTeacherId = params.get('teacher_id');
                const preselectSchoolHeadId = params.get('school_head');
                const preselected = preselectTeacherId || preselectSchoolHeadId;

                const oldObserveeId = @json(old('observee_id'));

                if (oldObserveeId && this.observeeList.length) {
                    const match = this.observeeList.find(item => item.id == oldObserveeId);
                    if (match) {
                        this.selectObservee(match);
                        this.autoFillDetails();
                    }
                } else if (preselected) {
                    const isTeacher = !!preselectTeacherId;
                    this.selectedType = isTeacher ? 'teacher_observation' : 'school_head_observation';

                    const list = isTeacher ? this.teacherData : this.schoolHeadData;
                    const match = (list || []).find(item => String(item.id) === String(isTeacher ? preselectTeacherId : preselectSchoolHeadId));
                    if (match) {
                        this.selectObservee(match);
                        this.autoFillDetails();
                    }

                    // Auto-select the default template when it is the only option
                    // so the schedule step can be reached without extra clicks.
                    if (this.selectedObservee && this.templateOptions.length === 1) {
                        this.selectedCotTemplateId = this.templateOptions[0].id;
                    }
                }

                this.updateSteps();

                if (preselected && this.selectedObservee && ! oldObserveeId) {
                    // Skip the "Who" and "Observee" steps: land on the next
                    // step that still needs input.
                    this.goToStep(this.selectedCotTemplateId ? 4 : 2);
                } else if (this.selectedType) {
                    // Restore the correct step when re-rendering after validation error
                    const typeErrors = @if($errors->has('observation_type')) true @else false @endif;
                    const templateErrors = @if($errors->has('cot_indicator_version_id')) true @else false @endif;
                    const observeeErrors = @if($errors->has('observee_id')) true @else false @endif;
                    const scheduleErrors = @if($errors->hasAny(['observation_date', 'start_time', 'end_time', 'school_head_id'])) true @else false @endif;
                    const conferenceErrors = @if($errors->hasAny(['conference_start_time', 'conference_end_time'])) true @else false @endif;

                    if (typeErrors) {
                        this.goToStep(1);
                    } else if (templateErrors) {
                        this.goToStep(2);
                    } else if (observeeErrors) {
                        this.goToStep(3);
                    } else if (scheduleErrors) {
                        this.goToStep(4);
                    } else if (conferenceErrors) {
                        this.goToStep(5);
                    } else if (this.selectedCotTemplateId && this.selectedObservee) {
                        this.goToStep(5);
                    } else if (this.selectedCotTemplateId) {
                        this.goToStep(3);
                    } else {
                        this.goToStep(2);
                    }
                }
            }
        };
    }
</script>
@endpush
@endsection
