@extends('layouts.admin')

@section('title', 'Add PPST Standard')

@section('content')
<div class="max-w-3xl mx-auto px-4 space-y-6">
    <nav class="text-sm">
        <ol class="flex items-center gap-2 text-gray-500 dark:text-gray-400">
            <li><a href="{{ route('admin.ppst-standards.index') }}" class="hover:text-indigo-600 dark:text-indigo-400 transition-colors">PPST Standards</a></li>
            <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"/></svg></li>
            <li class="text-gray-900 dark:text-gray-100 font-medium">Add Standard</li>
        </ol>
    </nav>

    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-gray-700">
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-1">Add PPST Standard</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">The strand is derived automatically from the indicator code (e.g. 1.1.2 → Strand 1.1).</p>

        @if ($errors->any())
            <div class="mb-4 bg-red-50 dark:bg-red-900/20 rounded-lg p-4">
                <ul class="text-sm text-red-800 dark:text-red-300 list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.ppst-standards.store') }}" class="space-y-5">
            @csrf
            <div>
                <label for="domain" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Domain</label>
                <input type="text" name="domain" id="domain" list="domain-options" value="{{ old('domain') }}"
                       placeholder="e.g. Domain 1: Content Knowledge and Pedagogy"
                       class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" required>
                <datalist id="domain-options">
                    @foreach($domainOptions as $domain)
                        <option value="{{ $domain }}"></option>
                    @endforeach
                </datalist>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="indicator_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Indicator Code</label>
                    <input type="text" name="indicator_code" id="indicator_code" value="{{ old('indicator_code') }}"
                           placeholder="e.g. 1.1.2"
                           class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" required>
                </div>
                <div>
                    <label for="sort_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sort Order</label>
                    <input type="number" name="sort_order" id="sort_order" min="0" value="{{ old('sort_order', $defaultSortOrder) }}"
                           class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </div>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                <textarea name="description" id="description" rows="3"
                          placeholder="Full indicator description"
                          class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" required>{{ old('description') }}</textarea>
            </div>

            <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                Active
            </label>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm transition-colors">Add Standard</button>
                <a href="{{ route('admin.ppst-standards.index') }}" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
