@extends('layouts.supervisor')

@section('title', 'Create Coaching Agreement')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6">
    <!-- Breadcrumb -->
    <nav class="mb-6 text-sm">
        <ol class="flex items-center gap-2 text-gray-500">
            <li><a href="{{ route('supervisor.observations.index') }}" class="hover:text-indigo-600 transition-colors">Observations</a></li>
            <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"/></svg></li>
            <li><a href="{{ route('supervisor.observations.show', $observation) }}" class="hover:text-indigo-600 transition-colors">Observation Details</a></li>
            <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"/></svg></li>
            <li class="text-gray-900 font-medium">Create Coaching Agreement</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Create Coaching Agreement</h1>
        <p class="text-gray-500 mt-1">
            {{ $observation->observee->user->name ?? 'Unknown' }}
            &middot; {{ $observation->observation_date->format('M d, Y') }}
        </p>
    </div>

    <form method="POST" action="{{ route('supervisor.coaching.store') }}" class="space-y-6"
          x-data="{ submitting: false }" x-on:submit="submitting = true">
        @csrf
        <input type="hidden" name="observation_id" value="{{ $observation->id }}">

        <!-- Post-Conference Context -->
        @if($observation->postConference)
        <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-5">
            <h3 class="text-sm font-semibold text-indigo-900 mb-2">Post-Conference Reference</h3>
            <div class="space-y-2 text-sm text-indigo-800">
                @if($observation->postConference->challenges_facing_teacher)
                    <p><strong>Challenges:</strong> {{ Str::limit($observation->postConference->challenges_facing_teacher, 150) }}</p>
                @endif
                @if($observation->postConference->prioritized_next_steps)
                    <p><strong>Next Steps:</strong> {{ Str::limit($observation->postConference->prioritized_next_steps, 150) }}</p>
                @endif
            </div>
        </div>
        @endif

        <!-- Focus Areas -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6" x-data="{ areas: [''] }">
            <label class="block text-sm font-semibold text-gray-900 mb-3">Focus Areas</label>
            <p class="text-xs text-gray-500 mb-3">Key areas the teacher should focus on improving.</p>
            <template x-for="(area, index) in areas" :key="index">
                <div class="flex items-center gap-2 mb-2">
                    <input type="text" :name="'focus_areas[' + index + ']'" x-model="areas[index]"
                           class="flex-1 px-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                           placeholder="e.g., Classroom Management">
                    <button type="button" @click="areas.splice(index, 1)" x-show="areas.length > 1"
                            class="p-2 text-red-400 hover:text-red-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </template>
            <button type="button" @click="areas.push('')"
                    class="mt-1 inline-flex items-center gap-1 text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Add Focus Area
            </button>
        </div>

        <!-- Action Steps -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6" x-data="{ steps: [''] }">
            <label class="block text-sm font-semibold text-gray-900 mb-3">Action Steps</label>
            <p class="text-xs text-gray-500 mb-3">Specific actions the teacher will take to improve.</p>
            <template x-for="(step, index) in steps" :key="index">
                <div class="flex items-center gap-2 mb-2">
                    <span class="flex items-center justify-center w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 text-xs font-bold shrink-0" x-text="index + 1"></span>
                    <input type="text" :name="'action_steps[' + index + ']'" x-model="steps[index]"
                           class="flex-1 px-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                           placeholder="e.g., Implement a positive behavior support system">
                    <button type="button" @click="steps.splice(index, 1)" x-show="steps.length > 1"
                            class="p-2 text-red-400 hover:text-red-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </template>
            <button type="button" @click="steps.push('')"
                    class="mt-1 inline-flex items-center gap-1 text-sm text-indigo-600 hover:text-indigo-700 font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Add Action Step
            </button>
        </div>

        <!-- Resources & Timeline -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                <label class="block text-sm font-semibold text-gray-900 mb-2">Resources Needed</label>
                <textarea name="resources_needed" rows="3"
                          class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                          placeholder="Materials, training, or support required..."></textarea>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                <label class="block text-sm font-semibold text-gray-900 mb-2">Timeline</label>
                <input type="text" name="timeline"
                       class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                       placeholder="e.g., 4 weeks, End of Quarter 2">
            </div>
        </div>

        <!-- Success Indicators & Notes -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                <label class="block text-sm font-semibold text-gray-900 mb-2">Success Indicators</label>
                <textarea name="success_indicators" rows="3"
                          class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                          placeholder="How will success be measured?"></textarea>
            </div>
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
                <label class="block text-sm font-semibold text-gray-900 mb-2">Supervisor Notes</label>
                <textarea name="supervisor_notes" rows="3"
                          class="w-full px-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                          placeholder="Internal notes about this agreement..."></textarea>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('supervisor.observations.show', $observation) }}"
               class="px-5 py-2.5 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition-colors">
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
</div>
@endsection
