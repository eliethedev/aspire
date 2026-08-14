@extends('layouts.admin')

@section('title', 'PPST Standards')

@section('content')
<div class="max-w-7xl mx-auto px-4 space-y-5">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Manage PPST Standards</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1">The Philippine Professional Standards for Teachers library, organized by Domain → Strand → Indicator.</p>
            </div>
            <a href="{{ route('admin.ppst-standards.create') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors self-start">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Standard
            </a>
        </div>

        <div class="mt-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                PPST standards are maintained as a shared library. COT Templates select the applicable indicators for a specific school year, ratee position, and career stage.
            </p>
        </div>

        <div class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="rounded-xl border border-gray-100 dark:border-gray-700 p-4">
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $totals['domains'] }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Domains</p>
            </div>
            <div class="rounded-xl border border-gray-100 dark:border-gray-700 p-4">
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $totals['strands'] }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Strands</p>
            </div>
            <div class="rounded-xl border border-gray-100 dark:border-gray-700 p-4">
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $totals['indicators'] }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Indicators</p>
            </div>
        </div>
    </div>

    <!-- Domains -->
    @forelse($domains as $domain => $strands)
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden" x-data="{ open: true }">
            <button type="button" @click="open = !open" class="w-full flex items-center justify-between gap-4 px-6 py-4 text-left hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                <div class="flex items-center gap-3 min-w-0">
                    <svg class="w-5 h-5 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                    <div class="min-w-0">
                        <h2 class="font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $domain }}</h2>
                        <p class="text-xs text-gray-400 dark:text-gray-500">{{ $strands->count() }} strand(s) · {{ $strands->flatten()->count() }} indicator(s)</p>
                    </div>
                </div>
                <svg class="w-5 h-5 text-gray-400 shrink-0 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>

            <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="border-t border-gray-100 dark:border-gray-700">
                @foreach($strands as $strand => $indicators)
                    <div class="px-6 py-4 {{ $loop->first ? '' : 'border-t border-gray-100 dark:border-gray-700' }}">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 font-mono font-semibold text-sm">Strand {{ $strand }}</span>
                                <span class="text-xs text-gray-400 dark:text-gray-500">{{ $indicators->count() }} indicator(s)</span>
                            </div>
                        </div>

                        <div class="space-y-3">
                            @foreach($indicators as $standard)
                                <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="text-xs font-mono font-semibold text-indigo-600 dark:text-indigo-400 px-1.5 py-0.5 bg-indigo-50 dark:bg-indigo-900/20 rounded">{{ $standard->indicator_code }}</span>
                                                @if($standard->is_active)
                                                    <span class="text-xs px-1.5 py-0.5 bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 rounded">Active</span>
                                                @else
                                                    <span class="text-xs px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 rounded">Inactive</span>
                                                @endif
                                                @php($usage = $usageByStandard[$standard->id] ?? [])
                                                @if(count($usage) > 0)
                                                    <span class="relative group inline-flex">
                                                        <span class="text-xs px-1.5 py-0.5 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded cursor-help">
                                                            Used in {{ count($usage) }} COT {{ Str::plural('Template', count($usage)) }}
                                                        </span>
                                                        <span class="absolute left-0 top-full z-10 mt-1 hidden group-hover:block w-64 rounded-lg bg-gray-900 dark:bg-gray-800 text-white text-xs shadow-lg p-3">
                                                            <span class="block font-semibold mb-1 text-gray-300">Used in COT:</span>
                                                            @foreach($usage as $template)
                                                                <span class="block py-0.5">{{ $template['label'] }} — {{ $template['school_year'] }}</span>
                                                            @endforeach
                                                        </span>
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="mt-2 text-sm text-gray-800 dark:text-gray-200">{{ $standard->description }}</p>
                                        </div>
                                        <div class="flex items-center gap-2 shrink-0">
                                            <a href="{{ route('admin.ppst-standards.edit', $standard) }}"
                                               class="px-2.5 py-1.5 text-sm font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 hover:bg-indigo-100 dark:bg-indigo-900/30 rounded-lg transition-colors">Edit</a>
                                            <form method="POST" action="{{ route('admin.ppst-standards.toggle-active', $standard) }}" class="inline">
                                                @csrf
                                                <button type="submit"
                                                        class="px-2.5 py-1.5 text-sm font-medium {{ $standard->is_active ? 'text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 hover:bg-amber-100 dark:bg-amber-900/30' : 'text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:bg-green-900/30' }} rounded-lg transition-colors"
                                                        onclick="return confirm('{{ $standard->is_active ? 'Deactivate' : 'Activate' }} PPST indicator {{ $standard->indicator_code }}?')">
                                                    {{ $standard->is_active ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>
                                            @if(count($usage) === 0)
                                                <form method="POST" action="{{ route('admin.ppst-standards.destroy', $standard) }}" class="inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                            class="px-2.5 py-1.5 text-sm font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:bg-red-900/30 rounded-lg transition-colors"
                                                            onclick="return confirm('Delete PPST indicator {{ $standard->indicator_code }}? This cannot be undone.')">Delete</button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="text-center py-12 bg-white dark:bg-gray-900 rounded-xl shadow-sm">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p class="text-gray-500 dark:text-gray-400 font-medium">No PPST standards yet.</p>
            <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Run <code>php artisan db:seed --class=PpstStandardSeeder</code> or add the first standard.</p>
            <a href="{{ route('admin.ppst-standards.create') }}" class="inline-flex items-center mt-4 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm transition-colors">Add Standard</a>
        </div>
    @endforelse

    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-6 border border-blue-100 dark:border-blue-800">
        <h3 class="text-sm font-semibold text-blue-800 mb-2">How PPST Standards Relate to COT</h3>
        <ul class="text-sm text-blue-700 dark:text-blue-400 space-y-1 list-disc list-inside">
            <li>PPST is the standards foundation: <strong>Domain → Strand → Indicator</strong>.</li>
            <li>COT instruments (under COT Templates) assemble a subset of these indicators into per-school-year rating sheets.</li>
            <li>Deactivating a standard keeps it in the library but removes it from new COT instrument picks. Historical observations keep their own snapshot and are never rewritten.</li>
        </ul>
    </div>
</div>
@endsection
