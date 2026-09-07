@extends('layouts.teacher')

@section('title', 'Schedule Teacher Observation')

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
    <!-- Breadcrumb -->
    <nav class="mb-6 text-sm">
        <ol class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
            <li><a href="{{ route('school-head.observations.index') }}" class="hover:text-indigo-600 dark:text-indigo-400 transition-colors">Observations</a></li>
            <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"/></svg></li>
            <li class="text-gray-900 dark:text-gray-100 font-medium">Schedule Observation</li>
        </ol>
    </nav>

    <x-page-header title="Schedule Teacher Observation" subtitle="Schedule a classroom observation for a teacher in your school." />

    <form method="POST" action="{{ route('school-head.observations.store') }}" @submit="submitting = true">
        @csrf

        <!-- Progress Steps -->
        <div class="flex items-center gap-2 mb-4 text-xs">
            <template x-for="(step, i) in steps" :key="i">
                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-1.5">
                        <div :class="step.status === 'complete' ? 'bg-indigo-600 text-white' : step.status === 'active' ? 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 border-2 border-indigo-600' : 'bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-500'"
                             class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-semibold shrink-0 transition-colors">
                            <svg x-show="step.status === 'complete'" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span x-show="step.status !== 'complete'" x-text="i + 1"></span>
                        </div>
                        <span :class="step.status === 'complete' ? 'text-indigo-600 dark:text-indigo-400' : step.status === 'active' ? 'text-gray-900 dark:text-gray-100 font-medium' : 'text-gray-400 dark:text-gray-500'" class="text-xs hidden sm:inline transition-colors" x-text="step.label"></span>
                    </div>
                    <div x-show="i < steps.length - 1"
                         :class="step.status === 'complete' ? 'bg-indigo-300' : 'bg-gray-200 dark:bg-gray-700'"
                         class="w-6 sm:w-10 h-0.5 rounded transition-colors"></div>
                </div>
            </template>
        </div>

        <!-- ===== STEP 1: SELECT TEACHER ===== -->
        <div x-show="currentStep === 1" class="fade-in">
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-1">Select a Teacher</h2>
                <p class="text-gray-500 dark:text-gray-400 text-xs mb-3">Search or browse to find the teacher you want to observe.</p>

                <!-- COT Badge -->
                <div class="mb-5 bg-purple-50 dark:bg-purple-900/20 rounded-lg p-3 border border-purple-100 dark:border-purple-800 flex items-center gap-2">
                    <svg class="w-4 h-4 text-purple-600 dark:text-purple-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-xs font-medium text-purple-700 dark:text-purple-300">This observation will use the <strong>Classroom Observation Tool (COT)</strong> with PPST indicators.</span>
                </div>

                <!-- Search -->
                <div class="relative mb-5">
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" x-model="searchQuery" @input="searchQuery = $event.target.value"
                           class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                           placeholder="Type name, subject, grade level, or department...">
                </div>

                <!-- Results List -->
                <div x-show="filteredList.length > 0 && !selectedObservee" class="space-y-2 max-h-72 overflow-y-auto pr-1">
                    <template x-for="item in filteredList" :key="item.id">
                        <button type="button" @click="selectObservee(item)"
                                class="observee-card w-full text-left rounded-lg px-4 py-3 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 hover:border-indigo-400 dark:hover:border-indigo-500 hover:bg-indigo-50/30 dark:hover:bg-indigo-900/20 flex items-center gap-4">
                            <div class="w-9 h-9 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 flex items-center justify-center text-sm font-semibold shrink-0" x-text="item.name.charAt(0).toUpperCase()"></div>
                            <div class="min-w-0 flex-1">
                                <div class="font-medium text-gray-900 dark:text-gray-100 text-sm" x-text="item.name"></div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 truncate flex items-center gap-2 mt-0.5">
                                    <span x-text="item.position_label || item.position"></span>
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

                <!-- Selected Observee Card -->
                <div x-show="selectedObservee" class="fade-in">
                    <div class="rounded-xl border-2 border-indigo-200 dark:border-indigo-900/40 bg-indigo-50/40 dark:bg-indigo-900/20 p-5">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 flex items-center justify-center text-lg font-bold" x-text="selectedObservee.name.charAt(0).toUpperCase()"></div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-semibold text-gray-900 dark:text-gray-100 text-base" x-text="selectedObservee.name"></h3>
                                    </div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400" x-text="selectedObservee.position_label || selectedObservee.position"></p>
                                </div>
                            </div>
                            <button type="button" @click="selectedObservee = null; searchQuery = ''"
                                    class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:text-gray-400 p-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-6 gap-y-2.5 text-sm">
                            <div><span class="text-gray-500 dark:text-gray-400">Department</span><p class="font-medium text-gray-800 dark:text-gray-100" x-text="selectedObservee.department"></p></div>
                            <div><span class="text-gray-500 dark:text-gray-400">Subject</span><p class="font-medium text-gray-800 dark:text-gray-100" x-text="selectedObservee.subject"></p></div>
                            <div><span class="text-gray-500 dark:text-gray-400">Grade Level</span><p class="font-medium text-gray-800 dark:text-gray-100" x-text="selectedObservee.grade_level"></p></div>
                            <div><span class="text-gray-500 dark:text-gray-400">Employee No.</span><p class="font-medium text-gray-800 dark:text-gray-100" x-text="selectedObservee.employee_number"></p></div>

                            <!-- Recent Observations -->
                            <template x-if="selectedObservee.recent_observations && selectedObservee.recent_observations.length > 0">
                                <div class="col-span-2 sm:col-span-3 mt-2 pt-3 border-t border-indigo-200/60">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">Recent Observations</h4>
                                        <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                                            <span><span class="font-medium text-gray-700 dark:text-gray-300" x-text="selectedObservee.obs_stats?.total || 0"></span> total</span>
                                            <span><span class="font-medium text-green-600 dark:text-green-400" x-text="selectedObservee.obs_stats?.completed || 0"></span> done</span>
                                        </div>
                                    </div>
                                    <div class="space-y-1">
                                        <template x-for="obs in selectedObservee.recent_observations" :key="obs.id">
                                            <div class="flex items-center justify-between rounded-lg px-3 py-2 bg-white/70 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-900/40">
                                                <div class="flex items-center gap-2.5 min-w-0">
                                                    <span class="text-xs text-gray-400 dark:text-gray-500 shrink-0 w-16" x-text="obs.date"></span>
                                                    <span class="text-sm text-gray-700 dark:text-gray-300 truncate" x-text="obs.subject || 'Observation'"></span>
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium"
                                                          :class="obs.status === 'completed' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' : obs.status === 'cancelled' ? 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300' : 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300'"
                                                          x-text="obs.status.charAt(0).toUpperCase() + obs.status.slice(1)"></span>
                                                </div>
                                                <template x-if="obs.score">
                                                    <span class="text-xs font-medium text-indigo-600 dark:text-indigo-400 shrink-0" x-text="obs.score"></span>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                @error('observee_id')
                    <p class="mt-3 text-sm text-red-500 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end mt-6">
                <button type="button" @click="autoFillDetails(); currentStep = 2" :disabled="!selectedObservee"
                        class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
                    Continue →
                </button>
            </div>
        </div>

        <!-- ===== STEP 2: OBSERVATION DETAILS ===== -->
        <div x-show="currentStep === 2" class="fade-in">
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-1">Observation Details</h2>
                <p class="text-gray-500 dark:text-gray-400 text-xs mb-3">Configure the schedule and observation parameters.</p>

                <div class="grid sm:grid-cols-2 gap-x-4 gap-y-3">
                    <!-- School Year -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">School Year</label>
                        <input type="text" name="school_year" x-model="form.school_year"
                               class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                               placeholder="e.g., 2024-2025">
                    </div>

                    <!-- Term -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Term</label>
                        <select name="quarter" x-model="form.quarter"
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                            <option value="">Select term</option>
                            <option value="1">1st Term</option>
                            <option value="2">2nd Term</option>
                            <option value="3">3rd Term</option>
                        </select>
                    </div>

                    <!-- Observation Number -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Observation Number</label>
                        <select name="observation_number" x-model="form.observation_number"
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                            <option value="1">1st Observation</option>
                            <option value="2">2nd Observation</option>
                        </select>
                    </div>

                    <!-- Observation Mode -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Observation Mode</label>
                        <select name="observation_mode" x-model="form.observation_mode"
                                class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                            <option value="in_person">In-Person</option>
                            <option value="virtual">Virtual</option>
                            <option value="hybrid">Hybrid</option>
                        </select>
                    </div>

                    <!-- Subject (auto-filled) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Subject</label>
                        <div class="relative">
                            <select name="subject" x-model="form.subject" x-show="selectedSubjList.length"
                                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                                <option value="" disabled>Select subject</option>
                                <template x-for="s in selectedSubjList" :key="s">
                                    <option :value="s" x-text="s"></option>
                                </template>
                            </select>
                            <input type="text" name="subject" x-model="form.subject" x-show="!selectedSubjList.length"
                                   class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
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
                                   class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                                   placeholder="Auto-filled from profile">
                            <template x-if="selectedObservee && selectedObservee.grade_level && selectedObservee.grade_level !== 'Not set'">
                                <span class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[10px] font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 px-2 py-0.5 rounded-full">Auto</span>
                            </template>
                        </div>
                    </div>

                    <!-- Observation Date -->
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Observation Date</label>
                        <input type="date" name="observation_date" x-model="form.observation_date" required
                               class="w-full sm:max-w-xs px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                    </div>
                </div>
            </div>

            <div class="flex justify-between mt-6">
                <button type="button" @click="currentStep = 1"
                        class="px-6 py-2.5 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:text-gray-100 font-medium transition-colors">
                    ← Back
                </button>
                <button type="button" @click="currentStep = 3"
                        class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors">
                    Continue →
                </button>
            </div>
        </div>

        <!-- ===== STEP 3: SCHEDULE TYPE & NOTES ===== -->
        <div x-show="currentStep === 3" class="fade-in">
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-1">Schedule & Notes</h2>
                <p class="text-gray-500 dark:text-gray-400 text-xs mb-3">Choose when to conduct the observation and add notes.</p>

                <div class="space-y-6">
                    <!-- Schedule Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Schedule Type</label>
                        <div class="grid sm:grid-cols-2 gap-3">
                            <label class="relative rounded-xl border-2 p-4 cursor-pointer transition-all"
                                   :class="form.schedule_type === 'scheduled' ? 'border-indigo-600 bg-indigo-50/40 dark:bg-indigo-900/20' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 hover:border-gray-300 dark:hover:border-gray-500'">
                                <input type="radio" name="schedule_type" value="scheduled"
                                       x-model="form.schedule_type" class="sr-only">
                                <div class="flex items-start gap-3">
                                    <div class="w-5 h-5 rounded-full border-2 shrink-0 mt-0.5 flex items-center justify-center transition-colors"
                                         :class="form.schedule_type === 'scheduled' ? 'border-indigo-600' : 'border-gray-300 dark:border-gray-600'">
                                        <div x-show="form.schedule_type === 'scheduled'" class="w-2.5 h-2.5 rounded-full bg-indigo-600"></div>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-gray-100 text-sm">Scheduled</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Teacher will be notified. You can prepare in advance.</p>
                                    </div>
                                </div>
                            </label>
                            <label class="relative rounded-xl border-2 p-4 cursor-pointer transition-all"
                                   :class="form.schedule_type === 'immediate' ? 'border-indigo-600 bg-indigo-50/40 dark:bg-indigo-900/20' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 hover:border-gray-300 dark:hover:border-gray-500'">
                                <input type="radio" name="schedule_type" value="immediate"
                                       x-model="form.schedule_type" class="sr-only">
                                <div class="flex items-start gap-3">
                                    <div class="w-5 h-5 rounded-full border-2 shrink-0 mt-0.5 flex items-center justify-center transition-colors"
                                         :class="form.schedule_type === 'immediate' ? 'border-indigo-600' : 'border-gray-300 dark:border-gray-600'">
                                        <div x-show="form.schedule_type === 'immediate'" class="w-2.5 h-2.5 rounded-full bg-indigo-600"></div>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-gray-100 text-sm">Immediate</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Start the evaluation right away.</p>
                                    </div>
                                </div>
                            </label>
                        </div>
                        @error('schedule_type')
                            <p class="mt-2 text-sm text-red-500 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Notes <span class="text-gray-400 dark:text-gray-500 font-normal">(optional)</span></label>
                        <textarea name="notes" x-model="form.notes" rows="3"
                                  class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                                  placeholder="Add any additional notes or context..."></textarea>
                    </div>
                </div>
            </div>

            <div class="flex justify-between mt-6">
                <button type="button" @click="currentStep = 2"
                        class="px-6 py-2.5 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:text-gray-100 font-medium transition-colors">
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
            <div class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-xl max-h-[90vh] overflow-y-auto fade-in">
                <div class="p-6 sm:p-8">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                                <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">Confirm Observation</h2>
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
                        <div class="rounded-xl bg-indigo-50/60 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800 p-4">
                            <p class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider mb-3">Teacher</p>
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 flex items-center justify-center text-base font-bold shrink-0" x-text="selectedObservee?.name?.charAt(0) || '?'"></div>
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-gray-100" x-text="selectedObservee?.name"></p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400" x-text="selectedObservee?.department + ' · ' + selectedObservee?.subject"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Observation Details -->
                        <div class="rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-4">
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Observation Info</p>
                            <div class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                                <div><span class="text-gray-500 dark:text-gray-400">Type</span><p class="font-medium text-gray-800 dark:text-gray-100">Teacher Observation</p></div>
                                <div><span class="text-gray-500 dark:text-gray-400">Date</span><p class="font-medium text-gray-800 dark:text-gray-100" x-text="form.observation_date"></p></div>
                                <div><span class="text-gray-500 dark:text-gray-400">School Year</span><p class="font-medium text-gray-800 dark:text-gray-100" x-text="form.school_year"></p></div>
                                <div><span class="text-gray-500 dark:text-gray-400">Term</span><p class="font-medium text-gray-800 dark:text-gray-100" x-text="'Term ' + form.quarter"></p></div>
                                <div><span class="text-gray-500 dark:text-gray-400">Subject</span><p class="font-medium text-gray-800 dark:text-gray-100" x-text="form.subject || 'Not set'"></p></div>
                                <div><span class="text-gray-500 dark:text-gray-400">Grade Level</span><p class="font-medium text-gray-800 dark:text-gray-100" x-text="form.grade_level || 'Not set'"></p></div>
                                <div><span class="text-gray-500 dark:text-gray-400">Observation #</span><p class="font-medium text-gray-800 dark:text-gray-100" x-text="form.observation_number === '2' ? '2nd' : '1st'"></p></div>
                                <div><span class="text-gray-500 dark:text-gray-400">Mode</span><p class="font-medium text-gray-800 dark:text-gray-100 capitalize" x-text="form.observation_mode?.replace('_', ' ')"></p></div>
                                <div><span class="text-gray-500 dark:text-gray-400">Tool</span><p class="font-medium text-gray-800 dark:text-gray-100">Classroom Observation Tool (COT)</p></div>
                            </div>
                        </div>

                        <!-- Schedule Type -->
                        <div class="rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-4">
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Schedule</p>
                            <div class="flex items-center gap-2">
                                <template x-if="form.schedule_type === 'scheduled'">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-900/30 text-amber-800 dark:text-amber-300">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        Scheduled
                                    </span>
                                </template>
                                <template x-if="form.schedule_type === 'immediate'">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        Immediate
                                    </span>
                                </template>
                                <span class="text-sm text-gray-600 dark:text-gray-400" x-text="form.schedule_type === 'scheduled' ? 'Teacher will be notified in advance' : 'Start evaluation right away'"></span>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div x-show="form.notes" class="rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-4">
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Notes</p>
                            <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap" x-text="form.notes"></p>
                        </div>
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex justify-end gap-3 mt-8 pt-5 border-t border-gray-100 dark:border-gray-800">
                        <button type="button" @click="showConfirmModal = false"
                                class="px-5 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:text-gray-100 transition-colors">
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

        <!-- Hidden fields -->
        <input type="hidden" name="observee_id" x-model="observeeId">
    </form>
</div>

@push('scripts')
<script>
    function observationForm() {
        return {
            steps: [
                { label: 'Select Teacher', status: 'active' },
                { label: 'Details', status: 'pending' },
                { label: 'Schedule', status: 'pending' },
            ],
            currentStep: 1,
            selectedObservee: null,
            observeeId: @json(old('observee_id')),
            searchQuery: '',
            showConfirmModal: false,
            submitting: false,

            teacherData: @json($teacherData),

            get filteredList() {
                if (!this.searchQuery) return this.teacherData;
                const q = this.searchQuery.toLowerCase();
                return this.teacherData.filter(item =>
                    item.name?.toLowerCase().includes(q) ||
                    (item.subjects || []).join(' ').toLowerCase().includes(q) ||
                    item.subject?.toLowerCase().includes(q) ||
                    item.grade_level?.toLowerCase().includes(q) ||
                    item.department?.toLowerCase().includes(q) ||
                    item.position?.toLowerCase().includes(q) ||
                    item.email?.toLowerCase().includes(q)
                );
            },

            get selectedSubjList() {
                const s = this.selectedObservee && this.selectedObservee.subjects;
                return s && s.length ? s : [];
            },

            selectObservee(item) {
                this.selectedObservee = item;
                this.observeeId = item.id;
                this.searchQuery = '';
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

            init() {
                const oldObserveeId = @json(old('observee_id'));
                if (oldObserveeId && this.teacherData.length) {
                    const match = this.teacherData.find(item => item.id == oldObserveeId);
                    if (match) {
                        this.selectObservee(match);
                        this.autoFillDetails();
                    }
                } else if (this.teacherData.length === 1) {
                    this.selectObservee(this.teacherData[0]);
                }

                if (this.selectedObservee) {
                    this.currentStep = 2;
                }
            }
        };
    }
</script>
@endpush
@endsection
