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
<div class="max-w-7xl mx-auto px-4 sm:px-6" x-data="observationForm()" x-cloak>
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Schedule Observation</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-1">Set up a classroom observation or leadership evaluation.</p>

        <!-- Live context chips -->
        <div x-show="selectedObservee" x-cloak class="mt-3 flex flex-wrap items-center gap-2">
            <span class="text-[11px] font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">Observing</span>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 text-xs font-semibold">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <span x-text="selectedObservee?.name"></span>
            </span>
            <template x-if="selectedCotTemplate">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 text-xs font-semibold">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <span x-text="selectedCotTemplate.label"></span>
                </span>
            </template>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        <form method="POST" action="{{ route('supervisor.observations.store') }}" @submit="submitting = true" class="lg:col-span-2">
        @csrf

        <!-- Progress Steps -->
        <div class="flex items-center gap-2 mb-8 text-sm overflow-x-auto pb-2">
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
                         :class="step.status === 'complete' ? 'bg-indigo-300' : 'bg-gray-200'"
                         class="w-6 sm:w-8 h-0.5 rounded transition-colors shrink-0"></div>
                </div>
            </template>
        </div>

        <!-- ===== STEP 1: WHO WILL BE OBSERVED? ===== -->
        <div x-show="currentStep === 1" class="fade-in">
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 p-6 sm:p-8">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">Who will be observed?</h2>
                <p class="text-gray-500 dark:text-gray-400 text-sm mb-6">Choose the type of observation you want to conduct.</p>

                <div class="grid sm:grid-cols-2 gap-4">
                    <!-- Teacher Card -->
                    <label class="type-card rounded-xl p-5 bg-white dark:bg-gray-900 border-2 border-gray-200 dark:border-gray-700 has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/50 cursor-pointer hover:shadow-md"
                           :class="selectedType === 'teacher_observation' ? 'selected' : ''">
                        <input type="radio" name="observation_type" value="teacher_observation"
                               x-model="selectedType" @change="onTypeChange()" class="sr-only">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-gray-100">Teacher (TI – TIII)</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Classroom observation using COT Rating Sheet (Annex E-2).</p>
                            </div>
                        </div>
                    </label>

                    <!-- School Head Card -->
                    <label class="type-card rounded-xl p-5 bg-white dark:bg-gray-900 border-2 border-gray-200 dark:border-gray-700 has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/50 cursor-pointer hover:shadow-md"
                           :class="selectedType === 'school_head_observation' ? 'selected' : ''">
                        <input type="radio" name="observation_type" value="school_head_observation"
                               x-model="selectedType" @change="onTypeChange()" class="sr-only">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-lg bg-emerald-100 flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-gray-100">School Head / Principal</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Leadership & instructional leadership evaluation.</p>
                            </div>
                        </div>
                    </label>
                </div>

                @error('observation_type')
                    <p class="mt-3 text-sm text-red-500 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end mt-6">
                <button type="button" @click="goToStep(2)" :disabled="!selectedType"
                        class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors inline-flex items-center gap-2">
                    Continue
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>

        <!-- ===== STEP 2: SELECT COT TEMPLATE ===== -->
        <div x-show="currentStep === 2" class="fade-in">
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 p-6 sm:p-8">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">Select COT Template</h2>
                <p class="text-gray-500 dark:text-gray-400 text-sm mb-5">
                    The selected template determines the indicators used for the
                    <template x-if="selectedType === 'teacher_observation'">teacher observation.</template>
                    <template x-if="selectedType === 'school_head_observation'">school head observation.</template>
                    Only published templates for the current school year are shown.
                </p>

                <div x-show="templateOptions.length === 0" class="text-center py-8 text-gray-400 dark:text-gray-500">
                    <p class="text-sm">No published COT templates are available for the current school year ({{ $schoolYear }}).</p>
                    <p class="text-xs mt-1">Ask an admin to publish a COT template for this school year.</p>
                </div>

                <div class="space-y-3">
                    <template x-for="template in templateOptions" :key="template.id">
                        <label class="type-card block rounded-xl p-5 bg-white dark:bg-gray-900 border-2 border-gray-200 dark:border-gray-700 has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/50 cursor-pointer hover:shadow-md"
                               :class="selectedCotTemplateId === template.id ? 'selected' : ''">
                            <input type="radio" name="cot_indicator_version_id" :value="template.id"
                                   x-model="selectedCotTemplateId" @change="onTemplateChange()" class="sr-only">
                            <div class="flex items-start gap-4">
                                <div class="w-11 h-11 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h3 class="font-semibold text-gray-900 dark:text-gray-100 text-sm" x-text="template.label"></h3>
                                        <span x-show="template.is_default" class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300">
                                            Default
                                        </span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300">
                                            <span x-text="template.indicators_count"></span> indicators
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        <span x-text="template.framework_label"></span>
                                        <span x-text="' · ' + template.instrument_label"></span>
                                        <template x-if="template.career_stage_label">
                                            <span x-text="' · ' + template.career_stage_label"></span>
                                        </template>
                                    </p>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-1.5">
                                        School Year: <span x-text="template.school_year"></span>
                                        &middot; Ratee: <span x-text="template.ratee_role_label"></span>
                                    </p>
                                </div>
                            </div>
                        </label>
                    </template>
                </div>

                @error('cot_indicator_version_id')
                    <p class="mt-3 text-sm text-red-500 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-between items-center mt-6">
                <button type="button" @click="goToStep(1)"
                        class="px-5 py-2.5 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 font-medium transition-colors inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Back
                </button>
                <button type="button" @click="goToStep(3)" :disabled="!selectedCotTemplateId"
                        class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors inline-flex items-center gap-2">
                    Continue
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>

        <!-- ===== STEP 3: BROWSE & SELECT OBSERVEE ===== -->
        <div x-show="currentStep === 3" class="fade-in">
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 p-6 sm:p-8">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">
                    <template x-if="selectedType === 'teacher_observation'">Select a Teacher</template>
                    <template x-if="selectedType === 'school_head_observation'">Select a School Head</template>
                </h2>
                <p class="text-gray-500 dark:text-gray-400 text-sm mb-5">Search or browse to find the person you want to observe.</p>

                <!-- Search -->
                <div class="relative mb-5">
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" x-model="searchQuery" @input="searchQuery = $event.target.value"
                           class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                           placeholder="Type name, subject, grade level, or department...">
                </div>

                <!-- Results List -->
                <div x-show="filteredList.length > 0 && !selectedObservee" class="space-y-2 max-h-72 overflow-y-auto pr-1">
                    <template x-for="item in filteredList" :key="item.id">
                        <button type="button" @click="selectObservee(item)"
                                class="observee-card w-full text-left rounded-lg px-4 py-3 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 hover:border-indigo-400 hover:bg-indigo-50/30 flex items-center gap-4">
                            <div class="w-9 h-9 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 flex items-center justify-center text-sm font-semibold shrink-0" x-text="item.name.charAt(0).toUpperCase()"></div>
                            <div class="min-w-0 flex-1">
                                <div class="font-medium text-gray-900 dark:text-gray-100 text-sm" x-text="item.name"></div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 truncate flex items-center gap-2 mt-0.5">
                                    <span x-text="item.position"></span>
                                    <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                                    <span x-text="item.subject"></span>
                                    <template x-if="item.department">
                                        <><span class="w-1 h-1 rounded-full bg-gray-300"></span><span x-text="item.department"></span></>
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

                <!-- Selected Observee Card -->
                <div x-show="selectedObservee" class="fade-in">
                    <div class="rounded-xl border-2 border-indigo-200 bg-indigo-50/40 p-5">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 flex items-center justify-center text-lg font-bold" x-text="selectedObservee.name.charAt(0).toUpperCase()"></div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-semibold text-gray-900 dark:text-gray-100 text-base" x-text="selectedObservee.name"></h3>
                                        <template x-if="selectedObservee.profile_url">
                                            <a :href="selectedObservee.profile_url" target="_blank"
                                               class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 bg-indigo-50 dark:bg-indigo-900/20 hover:bg-indigo-100 dark:bg-indigo-900/30 px-2 py-0.5 rounded-full transition-colors">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                                                View Profile
                                            </a>
                                        </template>
                                    </div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400" x-text="selectedObservee.position"></p>
                                </div>
                            </div>
                            <button type="button" @click="selectedObservee = null; searchQuery = ''"
                                    class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:text-gray-400 p-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-6 gap-y-2.5 text-sm">
                            <template x-if="selectedType === 'teacher_observation'">
                                <>
                                    <div><span class="text-gray-500 dark:text-gray-400">Department</span><p class="font-medium text-gray-800" x-text="selectedObservee.department"></p></div>
                                    <div><span class="text-gray-500 dark:text-gray-400">Subject</span><p class="font-medium text-gray-800" x-text="selectedObservee.subject"></p></div>
                                    <div><span class="text-gray-500 dark:text-gray-400">Grade Level</span><p class="font-medium text-gray-800" x-text="selectedObservee.grade_level"></p></div>
                                    <div><span class="text-gray-500 dark:text-gray-400">Employee No.</span><p class="font-medium text-gray-800" x-text="selectedObservee.employee_number"></p></div>
                                </>
                            </template>
                            <template x-if="selectedType !== 'teacher_observation'">
                                <>
                                    <div><span class="text-gray-500 dark:text-gray-400">Position Level</span><p class="font-medium text-gray-800" x-text="selectedObservee.position_level"></p></div>
                                    <div><span class="text-gray-500 dark:text-gray-400">Subject</span><p class="font-medium text-gray-800" x-text="selectedObservee.subject"></p></div>
                                    <div><span class="text-gray-500 dark:text-gray-400">Grade Level</span><p class="font-medium text-gray-800" x-text="selectedObservee.grade_level"></p></div>
                                </>
                            </template>
                            <div><span class="text-gray-500 dark:text-gray-400">Email</span><p class="font-medium text-gray-800 truncate" x-text="selectedObservee.email"></p></div>
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
                                           class="flex items-center justify-between rounded-lg px-3 py-2 bg-white/70 border border-indigo-100 hover:bg-indigo-50 dark:bg-indigo-900/20 transition-colors group">
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
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 p-6 sm:p-8">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">Observation Schedule</h2>
                <p class="text-gray-500 dark:text-gray-400 text-sm mb-6">Set the date, time, and location for the observation.</p>

                <!-- Section: When & Where -->
                <div class="mb-7">
                    <div class="flex items-center gap-2 mb-4">
                        <svg class="w-4 h-4 text-indigo-500 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">When &amp; Where</h3>
                    </div>

                    <!-- Observation Date -->
                    <div class="sm:col-span-2 mb-5">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Observation Date</label>
                        <input type="date" name="observation_date" x-model="form.observation_date" required :min="today"
                               class="w-full sm:max-w-xs px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                    </div>

                    <div class="grid sm:grid-cols-2 gap-x-6 gap-y-5">
                        <!-- Start Time -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Start Time</label>
                            <input type="time" name="start_time" x-model="form.start_time"
                                   class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                            @error('start_time')
                                <p class="mt-2 text-sm text-red-500 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- End Time -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">End Time</label>
                            <input type="time" name="end_time" x-model="form.end_time"
                                   class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                            @error('end_time')
                                <p class="mt-2 text-sm text-red-500 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Observation Mode -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Observation Mode</label>
                            <select name="observation_mode" x-model="form.observation_mode"
                                    class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                                <option value="in_person">In-Person</option>
                                <option value="virtual">Virtual</option>
                                <option value="hybrid">Hybrid</option>
                            </select>
                        </div>

                        <!-- Location -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Location <span class="text-gray-400 dark:text-gray-500 font-normal">(optional)</span></label>
                            <input type="text" name="location" x-model="form.location"
                                   class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                                   placeholder="e.g., Room 204, LRC, or Online link">
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-100 dark:border-gray-800 pt-6 mb-7">
                    <div class="flex items-center gap-2 mb-4">
                        <svg class="w-4 h-4 text-emerald-500 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h10M4 18h6"/></svg>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Academic Context</h3>
                        <span class="text-[11px] text-gray-400 dark:text-gray-500 font-normal">Auto-filled from the ratee's profile — edit if needed</span>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-x-6 gap-y-5">
                        <!-- School Year -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">School Year</label>
                            <input type="text" name="school_year" x-model="form.school_year"
                                   class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                                   placeholder="e.g., 2024-2025">
                        </div>

                        <!-- Quarter -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Quarter</label>
                            <select name="quarter" x-model="form.quarter"
                                    class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                                <option value="">Select quarter</option>
                                <option value="1">1st Quarter</option>
                                <option value="2">2nd Quarter</option>
                                <option value="3">3rd Quarter</option>
                                <option value="4">4th Quarter</option>
                            </select>
                        </div>

                        <!-- Observation Number -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Observation Number</label>
                            <select name="observation_number" x-model="form.observation_number"
                                    class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                                <option value="1">1st Observation</option>
                                <option value="2">2nd Observation</option>
                            </select>
                        </div>

                        <!-- Subject (auto-filled) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Subject</label>
                            <div class="relative">
                                <input type="text" name="subject" x-model="form.subject"
                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                                       placeholder="Auto-filled from profile">
                                <template x-if="selectedObservee && selectedObservee.subject && selectedObservee.subject !== 'Not set'">
                                    <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[10px] font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 px-2 py-0.5 rounded-full">Auto</span>
                                </template>
                            </div>
                        </div>

                        <!-- Grade Level (auto-filled) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Grade Level</label>
                            <div class="relative">
                                <input type="text" name="grade_level" x-model="form.grade_level"
                                       class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                                       placeholder="Auto-filled from profile">
                                <template x-if="selectedObservee && selectedObservee.grade_level && selectedObservee.grade_level !== 'Not set'">
                                    <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[10px] font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 px-2 py-0.5 rounded-full">Auto</span>
                                </template>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Notes <span class="text-gray-400 dark:text-gray-500 font-normal">(optional)</span></label>
                            <textarea name="notes" x-model="form.notes" rows="3"
                                      class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                                      placeholder="Add any additional notes or context..."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-between items-center mt-6">
                <button type="button" @click="goToStep(3)"
                        class="px-5 py-2.5 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 font-medium transition-colors inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Back
                </button>
                <button type="button" @click="goToStep(5)"
                        class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors inline-flex items-center gap-2">
                    Continue
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>

        <!-- ===== STEP 5: POST-OBSERVATION CONFERENCE (OPTIONAL) ===== -->
        <div x-show="currentStep === 5" class="fade-in">
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 p-6 sm:p-8">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">Post-Observation Conference</h2>
                <p class="text-gray-500 dark:text-gray-400 text-sm mb-6">Optionally schedule a feedback conference after the observation.</p>

                <!-- Toggle -->
                <label class="flex items-start gap-3 cursor-pointer rounded-xl border-2 p-4 transition-all mb-6"
                       :class="scheduleConference ? 'border-indigo-600 bg-indigo-50/40' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 hover:border-gray-300'">
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

                <div x-show="scheduleConference" class="fade-in">
                    <div class="grid sm:grid-cols-2 gap-x-6 gap-y-5">
                        <!-- Conference Date -->
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Conference Date</label>
                            <input type="date" name="conference_date" x-model="form.conference_date"
                                   class="w-full sm:max-w-xs px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        </div>

                        <!-- Conference Start Time -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Start Time</label>
                            <input type="time" name="conference_start_time" x-model="form.conference_start_time"
                                   class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        </div>

                        <!-- Conference End Time -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">End Time</label>
                            <input type="time" name="conference_end_time" x-model="form.conference_end_time"
                                   class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        </div>

                        <!-- Conference Mode -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Conference Mode</label>
                            <select name="conference_mode" x-model="form.conference_mode"
                                    class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                                <option value="in_person">In-Person</option>
                                <option value="virtual">Virtual</option>
                                <option value="hybrid">Hybrid</option>
                            </select>
                        </div>

                        <!-- Conference Location -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Location</label>
                            <input type="text" name="conference_location" x-model="form.conference_location"
                                   class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
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
             @keydown.escape.window="showConfirmModal = false">
            <div class="modal-backdrop fixed inset-0 bg-black/40" @click="showConfirmModal = false"></div>
            <div class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-xl max-h-[90vh] overflow-y-auto fade-in">
                <div class="p-6 sm:p-8">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between mb-6">
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
                                <div><span class="text-gray-500 dark:text-gray-400">Type</span><p class="font-medium text-gray-800"><template x-if="selectedType === 'teacher_observation'">Teacher Observation</template><template x-if="selectedType !== 'teacher_observation'">School Head Observation</template></p></div>
                                <div><span class="text-gray-500 dark:text-gray-400">Date</span><p class="font-medium text-gray-800" x-text="form.observation_date"></p></div>
                                <div><span class="text-gray-500 dark:text-gray-400">Time</span><p class="font-medium text-gray-800" x-text="timeLabel || '—'"></p></div>
                                <div><span class="text-gray-500 dark:text-gray-400">Location</span><p class="font-medium text-gray-800" x-text="form.location || '—'"></p></div>
                                <div><span class="text-gray-500 dark:text-gray-400">School Year</span><p class="font-medium text-gray-800" x-text="form.school_year"></p></div>
                                <div><span class="text-gray-500 dark:text-gray-400">Quarter</span><p class="font-medium text-gray-800" x-text="'Quarter ' + form.quarter"></p></div>
                                <div><span class="text-gray-500 dark:text-gray-400">Subject</span><p class="font-medium text-gray-800" x-text="form.subject || 'Not set'"></p></div>
                                <div><span class="text-gray-500 dark:text-gray-400">Grade Level</span><p class="font-medium text-gray-800" x-text="form.grade_level || 'Not set'"></p></div>
                                <div><span class="text-gray-500 dark:text-gray-400">Observation #</span><p class="font-medium text-gray-800" x-text="form.observation_number === '2' ? '2nd' : '1st'"></p></div>
                                <div><span class="text-gray-500 dark:text-gray-400">Mode</span><p class="font-medium text-gray-800 capitalize" x-text="form.observation_mode?.replace('_', ' ')"></p></div>
                            </div>
                        </div>

                        <!-- Template -->
                        <div class="rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 p-4">
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">COT Template</p>
                            <p class="text-sm font-medium text-gray-800" x-text="selectedCotTemplate?.label || 'Default COT template (auto-selected)'"></p>
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
                        <div x-show="scheduleConference" class="rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 p-4">
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Post-Observation Conference</p>
                            <div class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                                <div><span class="text-gray-500 dark:text-gray-400">Date</span><p class="font-medium text-gray-800" x-text="form.conference_date || '—'"></p></div>
                                <div><span class="text-gray-500 dark:text-gray-400">Time</span><p class="font-medium text-gray-800" x-text="conferenceTimeLabel || '—'"></p></div>
                                <div><span class="text-gray-500 dark:text-gray-400">Mode</span><p class="font-medium text-gray-800 capitalize" x-text="form.conference_mode?.replace('_', ' ')"></p></div>
                                <div><span class="text-gray-500 dark:text-gray-400">Location</span><p class="font-medium text-gray-800" x-text="form.conference_location || '—'"></p></div>
                            </div>
                        </div>

                        <!-- Notes (if any) -->
                        <div x-show="form.notes" class="rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 p-4">
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Notes</p>
                            <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap" x-text="form.notes"></p>
                        </div>
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex justify-end gap-3 mt-8 pt-5 border-t border-gray-100">
                        <button type="button" @click="showConfirmModal = false"
                                class="px-5 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:text-gray-100 transition-colors">
                            Go Back
                        </button>
                        <button type="submit" :disabled="submitting"
                                :class="submitting ? 'opacity-60 cursor-not-allowed' : ''"
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
                    </div>
                </div>
                <div x-show="!selectedObservee" class="rounded-lg bg-gray-50 dark:bg-gray-800 border border-dashed border-gray-200 dark:border-gray-700 p-4 text-center">
                    <p class="text-xs text-gray-400 dark:text-gray-500">No ratee selected yet.</p>
                </div>

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
                    <div x-show="scheduleConference" class="flex items-center justify-between gap-3">
                        <dt class="text-xs text-gray-400 dark:text-gray-500">Conference</dt>
                        <dd class="font-medium text-emerald-600 dark:text-emerald-400 text-right">Scheduled</dd>
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
            },

            get templateOptions() {
                if (!this.selectedType) return [];
                const role = this.selectedType === 'teacher_observation' ? 'teacher' : 'school_head';
                return (this.cotTemplates || []).filter(t => t.ratee_role === role);
            },

            get selectedCotTemplate() {
                return (this.cotTemplates || []).find(t => String(t.id) === String(this.selectedCotTemplateId)) || null;
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

            get filteredList() {
                if (!this.searchQuery) return this.observeeList;
                const q = this.searchQuery.toLowerCase();
                return this.observeeList.filter(item =>
                    item.name?.toLowerCase().includes(q) ||
                    item.subject?.toLowerCase().includes(q) ||
                    item.grade_level?.toLowerCase().includes(q) ||
                    item.department?.toLowerCase().includes(q) ||
                    item.position?.toLowerCase().includes(q) ||
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

            autoFillDetails() {
                if (this.selectedObservee) {
                    if (this.selectedObservee.subject && this.selectedObservee.subject !== 'Not set') {
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
                    if (this.selectedCotTemplateId && this.selectedObservee) {
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
