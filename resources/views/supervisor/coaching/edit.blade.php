@extends('layouts.supervisor')

@section('title', 'Edit Coaching Agreement')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6">
    <nav class="mb-6 text-sm">
        <ol class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
            <li><a href="{{ route('supervisor.coaching.index') }}" class="hover:text-indigo-600 dark:text-indigo-400 transition-colors">Coaching Agreements</a></li>
            <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"/></svg></li>
            <li class="text-gray-900 dark:text-gray-100 font-medium">Edit Agreement</li>
        </ol>
    </nav>

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Edit Coaching Agreement</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-1">{{ $agreement->observation->observee->user->name ?? 'Teacher' }}</p>
    </div>

    <form method="POST" action="{{ route('supervisor.coaching.update', $agreement) }}" class="space-y-6"
          x-data="{ submitting: false }" x-on:submit="submitting = true">
        @csrf @method('PATCH')

        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6" x-data="{ areas: {{ json_encode($agreement->focus_areas ?? ['']) }} }">
            <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Focus Areas</label>
            <template x-for="(area, index) in areas" :key="index">
                <div class="flex items-center gap-2 mb-2">
                    <input type="text" :name="'focus_areas[' + index + ']'" x-model="areas[index]"
                           class="flex-1 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                           placeholder="e.g., Classroom Management">
                    <button type="button" @click="areas.splice(index, 1)" x-show="areas.length > 1"
                            class="p-2 text-red-400 hover:text-red-600 dark:text-red-400 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </template>
            <button type="button" @click="areas.push('')"
                    class="mt-1 inline-flex items-center gap-1 text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Add Focus Area
            </button>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6" x-data="{ steps: {{ json_encode($agreement->action_steps ?? ['']) }} }">
            <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Action Steps</label>
            <template x-for="(step, index) in steps" :key="index">
                <div class="flex items-center gap-2 mb-2">
                    <span class="flex items-center justify-center w-6 h-6 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 text-xs font-bold shrink-0" x-text="index + 1"></span>
                    <input type="text" :name="'action_steps[' + index + ']'" x-model="steps[index]"
                           class="flex-1 px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                           placeholder="e.g., Implement a positive behavior support system">
                    <button type="button" @click="steps.splice(index, 1)" x-show="steps.length > 1"
                            class="p-2 text-red-400 hover:text-red-600 dark:text-red-400 transition-colors">
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

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Resources Needed</label>
                <textarea name="resources_needed" rows="3"
                          class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">{{ $agreement->resources_needed }}</textarea>
            </div>
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Timeline</label>
                <input type="text" name="timeline" value="{{ $agreement->timeline }}"
                       class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                       placeholder="e.g., 4 weeks, End of Quarter 2">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Success Indicators</label>
                <textarea name="success_indicators" rows="3"
                          class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">{{ $agreement->success_indicators }}</textarea>
            </div>
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
                <label class="block text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Supervisor Notes</label>
                <textarea name="supervisor_notes" rows="3"
                          class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">{{ $agreement->supervisor_notes }}</textarea>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('supervisor.coaching.show', $agreement) }}"
               class="px-5 py-2.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg font-medium hover:bg-gray-50 dark:bg-gray-800 transition-colors">
                Cancel
            </a>
            <button type="submit" :disabled="submitting"
                    :class="submitting ? 'opacity-60 cursor-not-allowed' : ''"
                    class="px-5 py-2.5 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors">
                <span x-show="!submitting">Update Agreement</span>
                <span x-show="submitting" class="flex items-center gap-2">
                    <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Updating...
                </span>
            </button>
        </div>
    </form>
</div>
@endsection
