@extends('layouts.admin')

@section('title', 'Form Templates')

@push('styles')
<style>
    .template-card { transition: all 0.2s ease; }
    .template-card:hover { transform: translateY(-1px); }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 space-y-4">
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Observation Form Templates</h1>
                <p class="text-gray-500 dark:text-gray-400 dark:text-gray-500 mt-1">Manage dynamic form templates for classroom observations.</p>
            </div>
            <a href="{{ route('admin.form-templates.create') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Template
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($templates as $template)
            <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 template-card {{ $template->is_active ? 'ring-2 ring-indigo-400' : '' }}">
                <div class="p-5">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ $template->name }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 dark:text-gray-500">SY {{ $template->school_year }}</p>
                        </div>
                        @if($template->is_active)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300">Active</span>
                        @endif
                    </div>
                    @if($template->description)
                        <p class="text-sm text-gray-600 dark:text-gray-400 dark:text-gray-500 mb-3 line-clamp-2">{{ $template->description }}</p>
                    @endif
                    <div class="flex items-center flex-wrap gap-2 text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500 mb-4">
                        <span class="px-1.5 py-0.5 bg-gray-100 rounded">{{ \App\Models\FormTemplate::OBSERVATION_TYPES[$template->observation_type] ?? 'All Types' }}</span>
                        <span>{{ $template->sections_count }} section(s)</span>
                        <span>v{{ $template->version }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.form-templates.edit', $template) }}"
                           class="flex-1 text-center px-3 py-1.5 text-sm font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 hover:bg-indigo-100 dark:bg-indigo-900/30 rounded-lg transition-colors">Edit</a>
                        @if(!$template->is_active)
                            <form method="POST" action="{{ route('admin.form-templates.activate', $template) }}" class="inline">
                                @csrf
                                <button type="submit"
                                        class="px-3 py-1.5 text-sm font-medium text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:bg-green-900/30 rounded-lg transition-colors"
                                        onclick="return confirm('Activate this template for SY {{ $template->school_year }} ({{ \App\Models\FormTemplate::OBSERVATION_TYPES[$template->observation_type] ?? 'All Types' }})? This will deactivate others for the same school year and type.')">Activate</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('admin.form-templates.destroy', $template) }}" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="px-3 py-1.5 text-sm font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:bg-red-900/30 rounded-lg transition-colors"
                                    onclick="return confirm('Delete this template? This action cannot be undone.')">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 bg-white dark:bg-gray-900 rounded-xl shadow-sm">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <p class="text-gray-500 dark:text-gray-400 dark:text-gray-500 font-medium">No form templates yet.</p>
                <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Create your first template to define observation forms dynamically.</p>
                <a href="{{ route('admin.form-templates.create') }}" class="inline-flex items-center mt-4 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm transition-colors">Create Template</a>
            </div>
        @endforelse
    </div>

    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-6 border border-blue-100 dark:border-blue-800">
        <h3 class="text-sm font-semibold text-blue-800 mb-2">How Form Templates Work</h3>
        <ul class="text-sm text-blue-700 dark:text-blue-400 space-y-1 list-disc list-inside">
            <li>Templates define the form fields for each observation stage (Pre-Conference, Observation, Post-Conference).</li>
            <li>Only <strong>one template per school year per observation type</strong> can be active at a time.</li>
            <li>Assign a template to <strong>Teacher</strong> or <strong>School Head</strong> observations, or leave as <strong>All Types</strong> to serve both.</li>
            <li>New observations use the active template matching their school year and observation type.</li>
            <li>Duplicate a template to create next year's version, then modify as needed.</li>
            <li>Fields with a <strong>column map</strong> save to dedicated database columns for reporting.</li>
            <li>Fields without a column map save to the <code>form_responses</code> JSON column.</li>
        </ul>
    </div>
</div>
@endsection
