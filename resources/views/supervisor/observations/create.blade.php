@extends('layouts.supervisor')

@section('title', 'Create Observation')

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
<div class="max-w-5xl mx-auto px-4 sm:px-6" x-data="observationForm()" x-cloak>
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Create New Observation</h1>
        <p class="text-gray-500 mt-1">Set up a classroom observation or leadership evaluation.</p>
    </div>

    <form method="POST" action="{{ route('supervisor.observations.store') }}" @submit="submitting = true">
        @csrf

        <!-- Progress Steps -->
        <div class="flex items-center gap-2 mb-8 text-sm">
            <template x-for="(step, i) in steps" :key="i">
                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-1.5">
                        <div :class="step.status === 'complete' ? 'bg-indigo-600 text-white' : step.status === 'active' ? 'bg-indigo-100 text-indigo-700 border-2 border-indigo-600' : 'bg-gray-100 text-gray-400'"
                             class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-semibold shrink-0 transition-colors">
                            <svg x-show="step.status === 'complete'" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span x-show="step.status !== 'complete'" x-text="i + 1"></span>
                        </div>
                        <span :class="step.status === 'complete' ? 'text-indigo-600' : step.status === 'active' ? 'text-gray-900 font-medium' : 'text-gray-400'" class="text-xs hidden sm:inline transition-colors" x-text="step.label"></span>
                    </div>
                    <div x-show="i < steps.length - 1"
                         :class="step.status === 'complete' ? 'bg-indigo-300' : 'bg-gray-200'"
                         class="w-6 sm:w-10 h-0.5 rounded transition-colors"></div>
                </div>
            </template>
        </div>

        <!-- ===== STEP 1: OBSERVATION TYPE ===== -->
        <div x-show="currentStep === 1" class="fade-in">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sm:p-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-1">Who would you like to observe?</h2>
                <p class="text-gray-500 text-sm mb-6">Choose the type of observation you want to conduct.</p>

                <div class="grid sm:grid-cols-2 gap-4">
                    <!-- Teacher Card -->
                    <label class="type-card rounded-xl p-5 bg-white border-2 border-gray-200 has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/50 cursor-pointer hover:shadow-md"
                           :class="selectedType === 'teacher_observation' ? 'selected' : ''">
                        <input type="radio" name="observation_type" value="teacher_observation"
                               x-model="selectedType" @change="onTypeChange()" class="sr-only">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-lg bg-indigo-100 flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">Teacher (TI – TIII)</h3>
                                <p class="text-sm text-gray-500 mt-1">Classroom observation using COT Rating Sheet (Annex E-2).</p>
                            </div>
                        </div>
                    </label>

                    <!-- School Head Card -->
                    <label class="type-card rounded-xl p-5 bg-white border-2 border-gray-200 has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/50 cursor-pointer hover:shadow-md"
                           :class="selectedType === 'school_head_observation' ? 'selected' : ''">
                        <input type="radio" name="observation_type" value="school_head_observation"
                               x-model="selectedType" @change="onTypeChange()" class="sr-only">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-lg bg-emerald-100 flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900">School Head / Principal</h3>
                                <p class="text-sm text-gray-500 mt-1">Leadership & instructional leadership evaluation.</p>
                            </div>
                        </div>
                    </label>
                </div>

                @error('observation_type')
                    <p class="mt-3 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end mt-6">
                <button type="button" @click="currentStep = 2" :disabled="!selectedType"
                        class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                    Continue →
                </button>
            </div>
        </div>

        <!-- ===== STEP 2: BROWSE & SELECT OBSERVEE ===== -->
        <div x-show="currentStep === 2" class="fade-in">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sm:p-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-1">
                    <template x-if="selectedType === 'teacher_observation'">Select a Teacher</template>
                    <template x-if="selectedType === 'school_head_observation'">Select a School Head</template>
                </h2>
                <p class="text-gray-500 text-sm mb-5">Search or browse to find the person you want to observe.</p>

                <!-- Search -->
                <div class="relative mb-5">
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" x-model="searchQuery" @input="searchQuery = $event.target.value"
                           class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                           placeholder="Type name, subject, grade level, or department...">
                </div>

                <!-- Results List -->
                <div x-show="filteredList.length > 0 && !selectedObservee" class="space-y-2 max-h-72 overflow-y-auto pr-1">
                    <template x-for="item in filteredList" :key="item.id">
                        <button type="button" @click="selectObservee(item)"
                                class="observee-card w-full text-left rounded-lg px-4 py-3 bg-white border border-gray-200 hover:border-indigo-400 hover:bg-indigo-50/30 flex items-center gap-4">
                            <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-sm font-semibold shrink-0" x-text="item.name.charAt(0).toUpperCase()"></div>
                            <div class="min-w-0 flex-1">
                                <div class="font-medium text-gray-900 text-sm" x-text="item.name"></div>
                                <div class="text-xs text-gray-500 truncate flex items-center gap-2 mt-0.5">
                                    <span x-text="item.position"></span>
                                    <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                                    <span x-text="item.subject"></span>
                                    <template x-if="item.department">
                                        <><span class="w-1 h-1 rounded-full bg-gray-300"></span><span x-text="item.department"></span></>
                                    </template>
                                </div>
                            </div>
                            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </template>
                </div>

                <!-- No results -->
                <div x-show="filteredList.length === 0 && searchQuery.length > 0 && !selectedObservee"
                     class="text-center py-10 text-gray-400">
                    <svg class="w-10 h-10 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <p class="text-sm">No matches found. Try a different search term.</p>
                </div>

                <!-- Selected Observee Card -->
                <div x-show="selectedObservee" class="fade-in">
                    <div class="rounded-xl border-2 border-indigo-200 bg-indigo-50/40 p-5">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-lg font-bold" x-text="selectedObservee.name.charAt(0).toUpperCase()"></div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-semibold text-gray-900 text-base" x-text="selectedObservee.name"></h3>
                                        <template x-if="selectedObservee.profile_url">
                                            <a :href="selectedObservee.profile_url" target="_blank"
                                               class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-2 py-0.5 rounded-full transition-colors">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                                                View Profile
                                            </a>
                                        </template>
                                    </div>
                                    <p class="text-sm text-gray-500" x-text="selectedObservee.position"></p>
                                </div>
                            </div>
                            <button type="button" @click="selectedObservee = null; searchQuery = ''"
                                    class="text-gray-400 hover:text-gray-600 p-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-6 gap-y-2.5 text-sm">
                            <template x-if="selectedType === 'teacher_observation'">
                                <>
                                    <div><span class="text-gray-500">Department</span><p class="font-medium text-gray-800" x-text="selectedObservee.department"></p></div>
                                    <div><span class="text-gray-500">Subject</span><p class="font-medium text-gray-800" x-text="selectedObservee.subject"></p></div>
                                    <div><span class="text-gray-500">Grade Level</span><p class="font-medium text-gray-800" x-text="selectedObservee.grade_level"></p></div>
                                    <div><span class="text-gray-500">Employee No.</span><p class="font-medium text-gray-800" x-text="selectedObservee.employee_number"></p></div>
                                </>
                            </template>
                            <template x-if="selectedType !== 'teacher_observation'">
                                <>
                                    <div><span class="text-gray-500">Position Level</span><p class="font-medium text-gray-800" x-text="selectedObservee.position_level"></p></div>
                                    <div><span class="text-gray-500">Subject</span><p class="font-medium text-gray-800" x-text="selectedObservee.subject"></p></div>
                                    <div><span class="text-gray-500">Grade Level</span><p class="font-medium text-gray-800" x-text="selectedObservee.grade_level"></p></div>
                                </>
                            </template>
                            <div><span class="text-gray-500">Email</span><p class="font-medium text-gray-800 truncate" x-text="selectedObservee.email"></p></div>
                        </div>

                        <!-- Recent Observations -->
                        <template x-if="selectedType === 'teacher_observation' && selectedObservee.recent_observations && selectedObservee.recent_observations.length > 0">
                            <div class="mt-4 pt-4 border-t border-indigo-200/60">
                                <div class="flex items-center justify-between mb-3">
                                    <h4 class="text-xs font-semibold text-indigo-600 uppercase tracking-wider">Recent Observations</h4>
                                    <div class="flex items-center gap-3 text-xs text-gray-500">
                                        <span><span class="font-medium text-gray-700" x-text="selectedObservee.obs_stats?.total || 0"></span> total</span>
                                        <span><span class="font-medium text-green-600" x-text="selectedObservee.obs_stats?.completed || 0"></span> done</span>
                                        <span><span class="font-medium text-amber-600" x-text="selectedObservee.obs_stats?.in_progress || 0"></span> active</span>
                                    </div>
                                </div>
                                <div class="space-y-1.5">
                                    <template x-for="obs in selectedObservee.recent_observations" :key="obs.id">
                                        <a :href="obs.url"
                                           class="flex items-center justify-between rounded-lg px-3 py-2 bg-white/70 border border-indigo-100 hover:bg-indigo-50 transition-colors group">
                                            <div class="flex items-center gap-2.5 min-w-0">
                                                <span class="text-xs text-gray-400 shrink-0 w-16" x-text="obs.date"></span>
                                                <span class="text-sm text-gray-700 truncate" x-text="obs.subject || 'Observation'"></span>
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium"
                                                      :class="obs.status === 'completed' ? 'bg-green-100 text-green-700' : obs.status === 'cancelled' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700'"
                                                      x-text="obs.status.charAt(0).toUpperCase() + obs.status.slice(1)"></span>
                                            </div>
                                            <div class="flex items-center gap-2 shrink-0">
                                                <template x-if="obs.score">
                                                    <span class="text-xs font-medium text-indigo-600" x-text="obs.score"></span>
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
                    <p class="mt-3 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-between mt-6">
                <button type="button" @click="currentStep = 1; selectedObservee = null"
                        class="px-6 py-2.5 text-gray-600 hover:text-gray-900 font-medium transition-colors">
                    ← Back
                </button>
                <button type="button" @click="autoFillDetails(); currentStep = 3" :disabled="!selectedObservee"
                        class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                    Continue →
                </button>
            </div>
        </div>

        <!-- ===== STEP 3: OBSERVATION DETAILS ===== -->
        <div x-show="currentStep === 3" class="fade-in">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sm:p-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-1">Observation Details</h2>
                <p class="text-gray-500 text-sm mb-6">Configure the schedule and observation parameters.</p>

                <div class="grid sm:grid-cols-2 gap-x-6 gap-y-5">
                    <!-- School Year -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">School Year</label>
                        <input type="text" name="school_year" x-model="form.school_year"
                               class="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                               placeholder="e.g., 2024-2025">
                    </div>

                    <!-- Quarter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Quarter</label>
                        <select name="quarter" x-model="form.quarter"
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                            <option value="">Select quarter</option>
                            <option value="1">1st Quarter</option>
                            <option value="2">2nd Quarter</option>
                            <option value="3">3rd Quarter</option>
                            <option value="4">4th Quarter</option>
                        </select>
                    </div>

                    <!-- Observation Number -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Observation Number</label>
                        <select name="observation_number" x-model="form.observation_number"
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                            <option value="1">1st Observation</option>
                            <option value="2">2nd Observation</option>
                        </select>
                    </div>

                    <!-- Observation Mode -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Observation Mode</label>
                        <select name="observation_mode" x-model="form.observation_mode"
                                class="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                            <option value="in_person">In-Person</option>
                            <option value="virtual">Virtual</option>
                            <option value="hybrid">Hybrid</option>
                        </select>
                    </div>

                    <!-- Subject (auto-filled) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Subject</label>
                        <div class="relative">
                            <input type="text" name="subject" x-model="form.subject"
                                   class="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                                   placeholder="Auto-filled from profile">
                            <template x-if="selectedObservee && selectedObservee.subject && selectedObservee.subject !== 'Not set'">
                                <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[10px] font-medium text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full">Auto</span>
                            </template>
                        </div>
                    </div>

                    <!-- Grade Level (auto-filled) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Grade Level</label>
                        <div class="relative">
                            <input type="text" name="grade_level" x-model="form.grade_level"
                                   class="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                                   placeholder="Auto-filled from profile">
                            <template x-if="selectedObservee && selectedObservee.grade_level && selectedObservee.grade_level !== 'Not set'">
                                <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[10px] font-medium text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full">Auto</span>
                            </template>
                        </div>
                    </div>

                    <!-- Observation Date -->
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Observation Date</label>
                        <input type="date" name="observation_date" x-model="form.observation_date" required
                               class="w-full sm:max-w-xs px-4 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                    </div>
                </div>
            </div>

            <div class="flex justify-between mt-6">
                <button type="button" @click="currentStep = 2"
                        class="px-6 py-2.5 text-gray-600 hover:text-gray-900 font-medium transition-colors">
                    ← Back
                </button>
                <button type="button" @click="currentStep = 4"
                        class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors">
                    Continue →
                </button>
            </div>
        </div>

        <!-- ===== STEP 4: SCHEDULE TYPE & NOTES ===== -->
        <div x-show="currentStep === 4" class="fade-in">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sm:p-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-1">Schedule & Notes</h2>
                <p class="text-gray-500 text-sm mb-6">Choose when to conduct the observation and add notes.</p>

                <div class="space-y-6">
                    <!-- Schedule Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">Schedule Type</label>
                        <div class="grid sm:grid-cols-2 gap-3">
                            <label class="relative rounded-xl border-2 p-4 cursor-pointer transition-all"
                                   :class="form.schedule_type === 'scheduled' ? 'border-indigo-600 bg-indigo-50/40' : 'border-gray-200 bg-white hover:border-gray-300'">
                                <input type="radio" name="schedule_type" value="scheduled"
                                       x-model="form.schedule_type" class="sr-only">
                                <div class="flex items-start gap-3">
                                    <div class="w-5 h-5 rounded-full border-2 shrink-0 mt-0.5 flex items-center justify-center transition-colors"
                                         :class="form.schedule_type === 'scheduled' ? 'border-indigo-600' : 'border-gray-300'">
                                        <div x-show="form.schedule_type === 'scheduled'" class="w-2.5 h-2.5 rounded-full bg-indigo-600"></div>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 text-sm">Scheduled</p>
                                        <p class="text-xs text-gray-500 mt-0.5">Teacher will be notified. You can prepare in advance.</p>
                                    </div>
                                </div>
                            </label>
                            <label class="relative rounded-xl border-2 p-4 cursor-pointer transition-all"
                                   :class="form.schedule_type === 'immediate' ? 'border-indigo-600 bg-indigo-50/40' : 'border-gray-200 bg-white hover:border-gray-300'">
                                <input type="radio" name="schedule_type" value="immediate"
                                       x-model="form.schedule_type" class="sr-only">
                                <div class="flex items-start gap-3">
                                    <div class="w-5 h-5 rounded-full border-2 shrink-0 mt-0.5 flex items-center justify-center transition-colors"
                                         :class="form.schedule_type === 'immediate' ? 'border-indigo-600' : 'border-gray-300'">
                                        <div x-show="form.schedule_type === 'immediate'" class="w-2.5 h-2.5 rounded-full bg-indigo-600"></div>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 text-sm">Immediate</p>
                                        <p class="text-xs text-gray-500 mt-0.5">Start the COT evaluation right away.</p>
                                    </div>
                                </div>
                            </label>
                        </div>
                        @error('schedule_type')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Notes <span class="text-gray-400 font-normal">(optional)</span></label>
                        <textarea name="notes" x-model="form.notes" rows="3"
                                  class="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                                  placeholder="Add any additional notes or context..."></textarea>
                    </div>
                </div>
            </div>

            <div class="flex justify-between mt-6">
                <button type="button" @click="currentStep = 3"
                        class="px-6 py-2.5 text-gray-600 hover:text-gray-900 font-medium transition-colors">
                    ← Back
                </button>
                <button type="button" @click="openConfirmModal()"
                        class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors">
                    Review & Confirm
                </button>
            </div>
        </div>

        <!-- ===== CONFIRMATION MODAL ===== -->
        <div x-show="showConfirmModal" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             @keydown.escape.window="showConfirmModal = false">
            <div class="modal-backdrop fixed inset-0 bg-black/40" @click="showConfirmModal = false"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-xl max-h-[90vh] overflow-y-auto fade-in">
                <div class="p-6 sm:p-8">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">Confirm Observation</h2>
                                <p class="text-sm text-gray-500">Please review before creating.</p>
                            </div>
                        </div>
                        <button type="button" @click="showConfirmModal = false" class="text-gray-400 hover:text-gray-600 p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Preview Content -->
                    <div class="space-y-5">
                        <!-- Observee Info -->
                        <div class="rounded-xl bg-indigo-50/60 border border-indigo-100 p-4">
                            <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wider mb-3">Observee</p>
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-base font-bold shrink-0" x-text="selectedObservee?.name?.charAt(0) || '?'"></div>
                                <div>
                                    <p class="font-semibold text-gray-900" x-text="selectedObservee?.name"></p>
                                    <p class="text-sm text-gray-500" x-text="selectedObservee?.position + (selectedObservee?.department ? ' · ' + selectedObservee?.department : '')"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Observation Details -->
                        <div class="rounded-xl bg-gray-50 border border-gray-100 p-4">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Observation Info</p>
                            <div class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                                <div><span class="text-gray-500">Type</span><p class="font-medium text-gray-800"><template x-if="selectedType === 'teacher_observation'">Teacher Observation</template><template x-if="selectedType !== 'teacher_observation'">School Head Observation</template></p></div>
                                <div><span class="text-gray-500">Date</span><p class="font-medium text-gray-800" x-text="form.observation_date"></p></div>
                                <div><span class="text-gray-500">School Year</span><p class="font-medium text-gray-800" x-text="form.school_year"></p></div>
                                <div><span class="text-gray-500">Quarter</span><p class="font-medium text-gray-800" x-text="'Quarter ' + form.quarter"></p></div>
                                <div><span class="text-gray-500">Subject</span><p class="font-medium text-gray-800" x-text="form.subject || 'Not set'"></p></div>
                                <div><span class="text-gray-500">Grade Level</span><p class="font-medium text-gray-800" x-text="form.grade_level || 'Not set'"></p></div>
                                <div><span class="text-gray-500">Observation #</span><p class="font-medium text-gray-800" x-text="form.observation_number === '2' ? '2nd' : '1st'"></p></div>
                                <div><span class="text-gray-500">Mode</span><p class="font-medium text-gray-800 capitalize" x-text="form.observation_mode?.replace('_', ' ')"></p></div>
                            </div>
                        </div>

                        <!-- Schedule Type -->
                        <div class="rounded-xl bg-gray-50 border border-gray-100 p-4">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Schedule</p>
                            <div class="flex items-center gap-2">
                                <template x-if="form.schedule_type === 'scheduled'">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        Scheduled
                                    </span>
                                </template>
                                <template x-if="form.schedule_type === 'immediate'">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        Immediate
                                    </span>
                                </template>
                                <span class="text-sm text-gray-600" x-text="form.schedule_type === 'scheduled' ? 'Teacher will be notified in advance' : 'Start evaluation right away'"></span>
                            </div>
                        </div>

                        <!-- Notes (if any) -->
                        <div x-show="form.notes" class="rounded-xl bg-gray-50 border border-gray-100 p-4">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Notes</p>
                            <p class="text-sm text-gray-700 whitespace-pre-wrap" x-text="form.notes"></p>
                        </div>
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex justify-end gap-3 mt-8 pt-5 border-t border-gray-100">
                        <button type="button" @click="showConfirmModal = false"
                                class="px-5 py-2.5 text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">
                            Go Back
                        </button>
                        <button type="submit" :disabled="submitting"
                                :class="submitting ? 'opacity-60 cursor-not-allowed' : ''"
                                class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
                            <span x-show="!submitting">Confirm &amp; Create</span>
                            <span x-show="submitting" class="flex items-center gap-2">
                                <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                Creating...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Always-rendered hidden field for observee_id -->
        <input type="hidden" name="observee_id" x-model="observeeId">
    </form>
</div>

@push('scripts')
<script>
    function observationForm() {
        return {
            steps: [
                { label: 'Type', status: 'active' },
                { label: 'Observee', status: 'pending' },
                { label: 'Details', status: 'pending' },
                { label: 'Schedule', status: 'pending' },
            ],
            currentStep: 1,
            selectedType: @json(old('observation_type')),
            selectedObservee: null,
            observeeId: @json(old('observee_id')),
            searchQuery: '',
            showConfirmModal: false,
            submitting: false,

            teacherData: @json($teacherData),
            schoolHeadData: @json($schoolHeadData),

            form: {
                school_year: @json(old('school_year', now()->year . '-' . (now()->year + 1))),
                quarter: @json(old('quarter')),
                observation_number: @json(old('observation_number', '1')),
                observation_mode: @json(old('observation_mode', 'in_person')),
                subject: @json(old('subject')),
                grade_level: @json(old('grade_level')),
                observation_date: @json(old('observation_date', now()->format('Y-m-d'))),
                schedule_type: @json(old('schedule_type', 'scheduled')),
                notes: @json(old('notes')),
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

            onTypeChange() {
                this.selectedObservee = null;
                this.observeeId = '';
                this.searchQuery = '';
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

            updateSteps() {
                const hasType = !!this.selectedType;
                const hasObservee = !!this.selectedObservee;
                this.steps[0].status = hasType ? 'complete' : 'active';
                this.steps[1].status = hasObservee ? 'complete' : (hasType ? 'active' : 'pending');
                this.steps[2].status = hasObservee ? 'pending' : 'pending';
                this.steps[3].status = 'pending';
            },

            init() {
                this.updateSteps();
                const oldObserveeId = @json(old('observee_id'));
                if (oldObserveeId && this.observeeList.length) {
                    const match = this.observeeList.find(item => item.id == oldObserveeId);
                    if (match) {
                        this.selectObservee(match);
                        this.autoFillDetails();
                    }
                } else if (this.selectedType && this.observeeList.length === 1) {
                    this.selectObservee(this.observeeList[0]);
                }

                // Restore the correct step when re-rendering after validation error
                if (this.selectedType) {
                    this.currentStep = this.selectedObservee ? 3 : 2;
                }
            }
        };
    }
</script>
@endpush
@endsection