@extends('layouts.admin')

@section('title', 'Edit EPOC Template')
@include('partials.dashboard.mock-styles')

@section('content')
<div class="mock-wrap max-w-7xl mx-auto px-1 py-1">
    <nav class="text-sm">
        <ol class="flex items-center gap-2 text-gray-500">
            <li><a href="{{ route('admin.epoc-templates.index') }}" class="hover:text-indigo-600 transition-colors">EPOC Templates</a></li>
            <li><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"/></svg></li>
            <li class="text-gray-900 dark:text-gray-100 font-medium">{{ $epocTemplate->name }}</li>
        </ol>
    </nav>

    <div class="mock-topbar"><div class="mock-crumbs">Admin <span>/</span> <b>Edit EPOC Template</b></div></div>
    <div class="mock-title"><div><h1>Edit EPOC Template</h1></div><time>{{ now()->format('l, F j, Y') }}</time></div>

    @if ($errors->any())
    <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4 mb-4">
        <ul class="text-sm text-red-800 dark:text-red-300 list-disc list-inside space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <section class="mock-panel"><form method="POST" action="{{ route('admin.epoc-templates.update', $epocTemplate) }}" class="space-y-6">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Template Settings</h2>
                        @if($epocTemplate->is_active)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300">Active</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Inactive</span>
                        @endif
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $epocTemplate->name) }}"
                                   class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" required>
                        </div>
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                            <textarea name="description" id="description" rows="2"
                                      class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">{{ old('description', $epocTemplate->description) }}</textarea>
                        </div>
                        <div>
                            <label for="school_year" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">School Year</label>
                            <select name="school_year" id="school_year"
                                    class="w-full px-3 py-2 rounded-lg border border-gray-300 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                                @foreach($schoolYears as $value => $label)
                                    <option value="{{ $value }}" {{ old('school_year', $epocTemplate->school_year) == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <hr class="my-6 border-gray-200 dark:border-gray-700">

                    <div class="space-y-3">
                        <button type="submit"
                                class="w-full px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-semibold text-sm shadow-sm transition-colors">
                            Save Template
                        </button>
                        <button type="button" onclick="addDomain()"
                                class="w-full px-4 py-2.5 border-2 border-dashed border-gray-300 text-gray-600 hover:border-indigo-400 hover:text-indigo-600 rounded-lg font-medium text-sm transition-colors">
                            Add Domain
                        </button>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 space-y-4" id="domains-container">
                @foreach($grouped as $domain => $indicators)
                    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700" data-domain-block>
                        <div class="flex items-center gap-3 p-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 rounded-t-xl">
                            <input type="text" name="domains[{{ $loop->index }}][name]" value="{{ $domain }}" placeholder="Domain name"
                                   class="flex-1 px-2 py-1 text-sm font-semibold border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500" required>
                            <button type="button" onclick="this.closest('[data-domain-block]').remove()" class="text-red-400 hover:text-red-600 p-1" title="Remove domain">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                        <div class="p-4">
                            <label class="text-xs text-gray-500 mb-1 block">Indicators — one per line</label>
                            <textarea name="domains[{{ $loop->index }}][indicators]" rows="4"
                                      class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">{{ $indicators->pluck('indicator')->implode("\n") }}</textarea>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </form></section>

    <form id="duplicateEpocForm" method="POST" action="{{ route('admin.epoc-templates.duplicate', $epocTemplate) }}" class="hidden">
        @csrf
        <input type="hidden" name="school_year" value="">
    </form>

    <div class="mt-4 flex flex-wrap items-center gap-3">
        @if(!$epocTemplate->is_active)
            <form method="POST" action="{{ route('admin.epoc-templates.activate', $epocTemplate) }}" class="inline">
                @csrf
                <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 hover:bg-green-100 rounded-lg transition-colors"
                        onclick="return confirm('Activate this EPOC template for SY {{ $epocTemplate->school_year }}? This will deactivate others for the same school year.')">Activate</button>
            </form>
        @endif
        <div class="flex items-center gap-2">
            <select id="duplicate_school_year"
                    class="px-2 py-2 rounded-lg border border-gray-300 text-gray-900 dark:text-gray-100 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @foreach($schoolYears as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            <button type="button" onclick="duplicateEpocTemplate()"
                    class="px-4 py-2 text-sm font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 hover:bg-indigo-100 rounded-lg transition-colors">Duplicate</button>
        </div>
        <form method="POST" action="{{ route('admin.epoc-templates.destroy', $epocTemplate) }}" class="inline ml-auto">
            @csrf @method('DELETE')
            <button type="submit"
                    class="px-4 py-2 text-sm font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 rounded-lg transition-colors"
                    onclick="return confirm('Delete this EPOC template? This action cannot be undone.')">Delete</button>
        </form>
    </div>
</div>

<script>
let domainIndex = {{ $grouped->count() }};
function addDomain(name = '', indicators = '') {
    const i = domainIndex++;
    const safeName = String(name).replace(/</g, '&lt;');
    const safeIndicators = String(indicators).replace(/</g, '&lt;');
    document.getElementById('domains-container').insertAdjacentHTML('beforeend', `<div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700" data-domain-block>
        <div class="flex items-center gap-3 p-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 rounded-t-xl">
            <input type="text" name="domains[${i}][name]" value="${safeName}" placeholder="Domain name"
                   class="flex-1 px-2 py-1 text-sm font-semibold border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500" required>
            <button type="button" onclick="this.closest('[data-domain-block]').remove()" class="text-red-400 hover:text-red-600 p-1" title="Remove domain">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
        </div>
        <div class="p-4">
            <label class="text-xs text-gray-500 mb-1 block">Indicators — one per line</label>
            <textarea name="domains[${i}][indicators]" rows="4"
                      class="w-full px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">${safeIndicators}</textarea>
        </div>
    </div>`);
}
function duplicateEpocTemplate() {
    if (!confirm('Duplicate this template?')) return;
    const sel = document.getElementById('duplicate_school_year');
    const f = document.getElementById('duplicateEpocForm');
    f.querySelector('input[name=school_year]').value = sel.value;
    f.submit();
}
</script>
@endsection
