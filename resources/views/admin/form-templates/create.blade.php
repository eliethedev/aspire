@extends('layouts.admin')

@section('title', 'Create Form Template')

@section('content')
<div class="max-w-3xl mx-auto px-4 space-y-6">
    <nav class="text-sm">
        <ol class="flex items-center gap-2 text-gray-500">
            <li><a href="{{ route('admin.form-templates.index') }}" class="hover:text-indigo-600 transition-colors">Form Templates</a></li>
            <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"/></svg></li>
            <li class="text-gray-900 font-medium">Create Template</li>
        </ol>
    </nav>

    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
        <h1 class="text-2xl font-bold text-gray-900 mb-1">Create Form Template</h1>
        <p class="text-gray-500 text-sm">Create a new form template, then configure sections and fields.</p>
    </div>

    <form method="POST" action="{{ route('admin.form-templates.store') }}" class="space-y-6">
        @csrf

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 space-y-4">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Template Name</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}"
                       class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                       placeholder="e.g. COT Instrument SY 2025-2026" required>
                @error('name')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" id="description" rows="2"
                          class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                          placeholder="Optional description">{{ old('description') }}</textarea>
            </div>
            <div>
                <label for="school_year" class="block text-sm font-medium text-gray-700 mb-1">School Year</label>
                <select name="school_year" id="school_year"
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" required>
                    <option value="">Select School Year...</option>
                    @foreach($schoolYears as $value => $label)
                        <option value="{{ $value }}" {{ old('school_year') == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('school_year')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="observation_type" class="block text-sm font-medium text-gray-700 mb-1">Observation Type</label>
                <select name="observation_type" id="observation_type"
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                    @foreach($observationTypes as $value => $label)
                        <option value="{{ $value }}" {{ old('observation_type', '') == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-400">Assign this template to a specific observation type, or leave as "All Types".</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Default Sections</h2>
            <p class="text-sm text-gray-500 mb-4">You can add, remove, and configure sections and fields in the next step.</p>
            @foreach($sectionKeys as $key => $label)
                <div class="flex items-center gap-3 mb-2">
                    <input type="checkbox" name="sections[{{ $loop->index }}][key]" value="{{ $key }}" checked class="text-indigo-600 focus:ring-indigo-500 rounded" disabled>
                    <input type="hidden" name="sections[{{ $loop->index }}][key]" value="{{ $key }}">
                    <span class="text-sm text-gray-700">{{ $label }}</span>
                    <input type="hidden" name="sections[{{ $loop->index }}][label]" value="{{ $label }}">
                </div>
            @endforeach
        </div>

        <div class="flex items-center gap-3">
            <button type="submit"
                    class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold text-sm shadow-sm transition-colors">
                Create &amp; Configure Fields
            </button>
            <a href="{{ route('admin.form-templates.index') }}"
               class="px-6 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium text-sm transition-colors">Cancel</a>
        </div>
    </form>
</div>
@endsection
