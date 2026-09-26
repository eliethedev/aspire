@extends('layouts.admin')

@section('title', 'PPST Standards')
@include('partials.dashboard.mock-styles')

@section('content')
@php
    // Fallbacks in case controller is cached without new keys
    $domainMeta = $domainMeta ?? [];
    $totals['active'] = $totals['active'] ?? $standards->where('is_active', true)->count();
    $totals['inactive'] = $totals['inactive'] ?? $standards->where('is_active', false)->count();
    $colorMap = [
        'indigo' => ['bg' => 'bg-indigo-50 dark:bg-indigo-900/20', 'text' => 'text-indigo-600 dark:text-indigo-400', 'border' => 'border-indigo-200 dark:border-indigo-800', 'ring' => 'ring-indigo-200', 'bar' => 'bg-indigo-600'],
        'emerald' => ['bg' => 'bg-emerald-50 dark:bg-emerald-900/20', 'text' => 'text-emerald-600 dark:text-emerald-400', 'border' => 'border-emerald-200 dark:border-emerald-800', 'ring' => 'ring-emerald-200', 'bar' => 'bg-emerald-600'],
        'amber' => ['bg' => 'bg-amber-50 dark:bg-amber-900/20', 'text' => 'text-amber-600 dark:text-amber-400', 'border' => 'border-amber-200 dark:border-amber-800', 'ring' => 'ring-amber-200', 'bar' => 'bg-amber-600'],
        'violet' => ['bg' => 'bg-violet-50 dark:bg-violet-900/20', 'text' => 'text-violet-600 dark:text-violet-400', 'border' => 'border-violet-200 dark:border-violet-800', 'ring' => 'ring-violet-200', 'bar' => 'bg-violet-600'],
        'rose' => ['bg' => 'bg-rose-50 dark:bg-rose-900/20', 'text' => 'text-rose-600 dark:text-rose-400', 'border' => 'border-rose-200 dark:border-rose-800', 'ring' => 'ring-rose-200', 'bar' => 'bg-rose-600'],
        'teal' => ['bg' => 'bg-teal-50 dark:bg-teal-900/20', 'text' => 'text-teal-600 dark:text-teal-400', 'border' => 'border-teal-200 dark:border-teal-800', 'ring' => 'ring-teal-200', 'bar' => 'bg-teal-600'],
        'blue' => ['bg' => 'bg-blue-50 dark:bg-blue-900/20', 'text' => 'text-blue-600 dark:text-blue-400', 'border' => 'border-blue-200 dark:border-blue-800', 'ring' => 'ring-blue-200', 'bar' => 'bg-blue-600'],
    ];
    $getMeta = function($domain) use ($domainMeta) {
        // Try exact match, then fuzzy contains
        if (isset($domainMeta[$domain])) return $domainMeta[$domain];
        foreach ($domainMeta as $key => $meta) {
            if (str_contains($domain, $key) || str_contains($key, $domain)) return $meta;
        }
        // Fallback by domain number extracted from "Domain X: ..."
        if (preg_match('/Domain\s+(\d+)/i', $domain, $m)) {
            $n = (int)$m[1];
            $fallback = array_values($domainMeta);
            return $fallback[($n-1) % count($fallback)] ?? ['number' => $n, 'color' => 'indigo', 'icon' => 'fa-layer-group'];
        }
        return ['number' => '?', 'color' => 'indigo', 'icon' => 'fa-layer-group'];
    };
@endphp

<div class="mock-wrap max-w-7xl mx-auto px-1 py-1" x-data="ppstStandardsPage()" x-init="init()">


    <div class="mock-topbar"><div class="mock-crumbs">Admin <span>/</span> <b>PPST Standards</b></div><div class="mock-actions"><a class="mock-btn" href="{{ route('admin.cot-indicators.index') }}">COT Templates</a><a class="mock-btn primary" href="{{ route('admin.ppst-standards.create') }}">＋ Add Standard</a></div></div>
    <div class="mock-title"><div><h1>PPST Standards</h1><p>The Philippine Professional Standards for Teachers library — your single source of truth for Domains, Strands and Indicators.</p></div><time>{{ now()->format('l, F j, Y') }}</time></div>

    <div class="mock-grid">
    <div class="mock-kpis">
        <div class="mock-kpi hot">
            <label>Domains</label>
            <div class="val">{{ $totals['domains'] }}</div>
            <div class="delta mock-flat">Broad competency areas</div>
        </div>
        <div class="mock-kpi">
            <label>Strands</label>
            <div class="val">{{ $totals['strands'] }}</div>
            <div class="delta mock-flat">Dimensions per domain</div>
        </div>
        <div class="mock-kpi">
            <label>Indicators</label>
            <div class="val">{{ $totals['indicators'] }}</div>
            <div class="delta mock-flat">Observable practices</div>
        </div>
        <div class="mock-kpi">
            <label>Active</label>
            <div class="val">{{ $totals['active'] }} <small>/ {{ $totals['indicators'] }}</small></div>
            <div class="delta mock-flat">{{ $totals['indicators'] > 0 ? round($totals['active'] / $totals['indicators'] * 100) : 0 }}% ready for COT picks</div>
        </div>
    </div>

    <div class="min-w-0">
    <!-- Controls -->
    <section class="mock-panel bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200/60 dark:border-gray-800 p-2 lg:p-3 sticky top-[64px] z-20 backdrop-blur supports-[backdrop-filter]:bg-white/80 dark:supports-[backdrop-filter]:bg-gray-900/80">
        <div class="flex flex-col lg:flex-row gap-2">

            <!-- Filters -->
            <div class="flex flex-wrap items-center gap-2">
                 <div class="flex-1 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" x-model="search" @input="applyFilters()" placeholder="Search by code, strand or description — e.g. 1.1.2 or literacy" class="w-full pl-9 pr-9 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 text-sm text-gray-900 dark:text-gray-100 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white dark:focus:bg-gray-900 transition-all">
                <button x-show="search.length > 0" @click="search=''; applyFilters()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-2 h-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
                <div class="relative">
                    <select x-model="domainFilter" @change="applyFilters()" class="appearance-none pl-3 pr-8 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="all">All domains</option>
                        @foreach($domains as $domain => $strands)
                            <option value="{{ $domain }}">{{ Str::limit($domain, 38) }}</option>
                        @endforeach
                    </select>
                    <svg class="w-4 h-4 text-gray-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>

                <div class="hidden sm:flex items-center gap-1 ml-1 pl-1 border-l border-gray-200 dark:border-gray-700">
                    <select x-model="statusFilter" @change="applyFilters()" class="appearance-none pl-3 pr-8 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="all">All status</option>
                        <option value="active">Active only</option>
                        <option value="inactive">Inactive only</option>
                    </select>
                    <svg class="w-4 h-4 text-gray-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                     <select x-model="usageFilter" @change="applyFilters()" class="appearance-none pl-3 pr-8 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="all">All usage</option>
                        <option value="used">Used in COT</option>
                        <option value="unused">Unused</option>
                    </select>
                    <svg class="w-4 h-4 text-gray-400 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    <button @click="expandAll()" class="px-3 py-2.5 text-xs font-semibold text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl hover:bg-white dark:hover:bg-gray-700 transition-colors">Expand all</button>
                    <button @click="collapseAll()" class="px-3 py-2.5 text-xs font-semibold text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Collapse</button>
                </div>

                <button @click="clearFilters()" class="px-3 py-2.5 text-xs font-semibold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800 rounded-xl hover:bg-indigo-100 dark:hover:bg-indigo-900/30 transition-colors">Clear</button>
            </div>
        </div>

        <div class="mt-3 flex flex-wrap items-center justify-between gap-2 text-xs">
            <p class="text-gray-500 dark:text-gray-400">
                <span class="font-semibold text-gray-700 dark:text-gray-200" x-text="visibleCount"></span> of <span x-text="totalCount"></span> indicators visible
                <span x-show="search || domainFilter !== 'all' || statusFilter !== 'all' || usageFilter !== 'all'" class="ml-1 text-indigo-600 dark:text-indigo-400">· filtered</span>
            </p>
            <p class="hidden sm:flex items-center gap-1.5 text-gray-400 dark:text-gray-500">
                <i class="fas fa-lightbulb text-amber-500"></i>
                Tip: pick an indicator in the panel on the right, or search <span class="font-mono bg-gray-50 dark:bg-gray-800 px-1 py-0.5 rounded border">1.4.2</span>
            </p>
        </div>
    </section>

    <!-- No results (filtered) -->
    <div x-show="hasNoResults" x-cloak class="bg-white dark:bg-gray-900 rounded-2xl border border-dashed border-gray-300 dark:border-gray-700 p-8 text-center">
        <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-900/20 border border-amber-100 dark:border-amber-800 flex items-center justify-center text-amber-600 dark:text-amber-400 mx-auto">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>
        <h3 class="mt-3 font-semibold text-gray-900 dark:text-white">No matching standards</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-md mx-auto">Try adjusting your search or filters. You can search by indicator code (e.g. 2.1.2), strand (2.1) or keywords in the description.</p>
        <button @click="clearFilters()" class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 text-sm font-semibold transition-colors">Clear filters</button>
    </div>

    <!-- Domains -->
    <div id="domains-container" class="space-y-4">
    @forelse($domains as $domain => $strands)
        @php
            $meta = $getMeta($domain);
            $colors = $colorMap[$meta['color']] ?? $colorMap['indigo'];
            $domainSlug = Str::slug($domain);
            $domainActive = $standards->where('domain', $domain)->where('is_active', true)->count();
            $domainInactive = $standards->where('domain', $domain)->where('is_active', false)->count();
            $domainTotal = $strands->flatten()->count();
        @endphp
        <section id="domain-{{ $domainSlug }}" data-domain-section data-domain="{{ $domain }}" class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200/60 dark:border-gray-800 overflow-hidden scroll-mt-28" x-data="{ open: true }">
            <!-- Domain header -->
            <div class="relative">
                <div class="absolute inset-x-0 top-0 h-1 {{ $colors['bar'] }}"></div>
                <button type="button" @click="open = !open" class="w-full flex items-center gap-4 px-5 lg:px-5 py-4 text-left hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition-colors">
                    <div class="w-11 h-11 rounded-2xl {{ $colors['bg'] }} border {{ $colors['border'] }} flex items-center justify-center {{ $colors['text'] }} shadow-sm shrink-0">
                        <i class="fas {{ $meta['icon'] }} text-sm"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full {{ $colors['bg'] }} {{ $colors['text'] }} border {{ $colors['border'] }} text-xs font-bold">{{ $meta['number'] }}</span>
                            <h2 class="font-bold text-gray-900 dark:text-white truncate pr-2">{{ $domain }}</h2>
                            <span class="hidden sm:inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700">Domain {{ $meta['number'] }}</span>
                        </div>
                        <div class="mt-1 flex flex-wrap items-center gap-2 text-xs">
                            <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300">
                                <span class="w-1.5 h-1.5 rounded-full {{ $colors['bar'] }}"></span> {{ $strands->count() }} strand(s)
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300">{{ $domainTotal }} indicator(s)</span>
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 border border-emerald-100 dark:border-emerald-800">{{ $domainActive }} active</span>
                            @if($domainInactive > 0)
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-700">{{ $domainInactive }} inactive</span>
                            @endif
                        </div>
                    </div>
                    <div class="hidden sm:flex items-center gap-2 shrink-0">
                        <span class="text-xs text-gray-400 dark:text-gray-500" x-text="open ? 'Collapse' : 'Expand'"></span>
                        <span class="w-8 h-8 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-400 dark:text-gray-500">
                            <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </span>
                    </div>
                    <svg class="sm:hidden w-5 h-5 text-gray-400 shrink-0 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
            </div>

            <div x-show="open" x-collapse class="border-t border-gray-100 dark:border-gray-800">
                @foreach($strands as $strand => $indicators)
                    @php
                        $strandActive = $indicators->where('is_active', true)->count();
                    @endphp
                    <div data-strand-group data-strand="{{ $strand }}" class="px-5 lg:px-5 py-5 {{ $loop->first ? '' : 'border-t border-gray-100 dark:border-gray-800' }} bg-gradient-to-b from-white to-gray-50/30 dark:from-gray-900 dark:to-gray-800/20">
                        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                            <div class="flex items-center gap-2.5">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl {{ $colors['bg'] }} {{ $colors['text'] }} border {{ $colors['border'] }} font-mono font-bold text-xs">
                                    <i class="fas fa-code-branch text-[10px] opacity-70"></i> Strand {{ $strand }}
                                </span>
                                <span class="text-xs px-2 py-1 rounded-full bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300">{{ $indicators->count() }} indicator(s)</span>
                                @if($strandActive !== $indicators->count())
                                    <span class="hidden sm:inline-flex text-xs px-2 py-1 rounded-full bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 border border-emerald-100 dark:border-emerald-800">{{ $strandActive }} active</span>
                                @endif
                            </div>
                            <span class="text-xs text-gray-400 dark:text-gray-500 hidden sm:inline">Strand {{ $strand }} · Domain {{ $meta['number'] }}</span>
                        </div>

                        <div class="grid grid-cols-1 gap-3">
                            @foreach($indicators as $standard)
                                @php
                                    $usage = $usageByStandard[$standard->id] ?? [];
                                    $usageCount = count($usage);
                                @endphp
                                <div data-indicator-card
                                     id="indicator-{{ Str::slug($standard->indicator_code) }}"
                                     data-code="{{ $standard->indicator_code }}"
                                     data-strand="{{ $standard->strand }}"
                                     data-domain="{{ $standard->domain }}"
                                     data-description="{{ $standard->description }}"
                                     data-active="{{ $standard->is_active ? '1' : '0' }}"
                                     data-usage="{{ $usageCount }}"
                                     class="group relative rounded-2xl border {{ $standard->is_active ? 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900' : 'border-dashed border-gray-300 dark:border-gray-600 bg-gray-50/50 dark:bg-gray-800/40' }} p-4 hover:shadow-sm hover:border-indigo-200 dark:hover:border-indigo-800 transition-all">
                                    <div class="flex items-start gap-3">
                                        <div class="hidden sm:flex w-9 h-9 rounded-xl {{ $standard->is_active ? $colors['bg'].' border '.$colors['border'].' '.$colors['text'] : 'bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-400' }} items-center justify-center font-mono text-xs font-bold shrink-0">
                                            {{ Str::afterLast($standard->indicator_code, '.') }}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex flex-wrap items-center gap-1.5">
                                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg font-mono font-bold text-xs {{ $standard->is_active ? 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800' : 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-700' }}">{{ $standard->indicator_code }}</span>
                                                <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium {{ $standard->is_active ? 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 border border-emerald-100 dark:border-emerald-800' : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-600' }}">
                                                    <span class="w-1.5 h-1.5 rounded-full {{ $standard->is_active ? 'bg-emerald-500' : 'bg-gray-400' }} mr-1"></span> {{ $standard->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                                <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-700">
                                                    <i class="fas fa-hashtag text-[10px] mr-1 opacity-60"></i> #{{ $standard->sort_order }}
                                                </span>
                                                @if($usageCount > 0)
                                                    <div class="relative group/usage">
                                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border border-blue-100 dark:border-blue-800 cursor-help">
                                                            <i class="fas fa-link text-[10px]"></i> Used in {{ $usageCount }} COT {{ Str::plural('Template', $usageCount) }}
                                                        </span>
                                                        <div class="absolute left-0 top-full z-10 mt-2 hidden group-hover/usage:block w-72 rounded-2xl bg-gray-900 dark:bg-gray-800 text-white text-xs shadow-xl border border-gray-800 p-3">
                                                            <p class="font-semibold text-gray-200 mb-2 flex items-center gap-1.5"><i class="fas fa-clipboard-list text-[11px]"></i> COT Templates</p>
                                                            @foreach($usage as $template)
                                                                 <span class="block py-0.5">{{ $template['label'] }} — {{ $template['school_year'] }}</span>
                                                             @endforeach
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-1 rounded-lg text-xs font-medium bg-gray-50 dark:bg-gray-800 text-gray-400 dark:text-gray-500 border border-dashed border-gray-200 dark:border-gray-700">
                                                        <i class="fas fa-unlink text-[10px] mr-1"></i> Unused
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="mt-2.5 text-sm leading-relaxed {{ $standard->is_active ? 'text-gray-800 dark:text-gray-200' : 'text-gray-500 dark:text-gray-400' }}">{{ $standard->description }}</p>
                                            <div class="mt-2 flex flex-wrap items-center gap-1.5 text-xs text-gray-400 dark:text-gray-500">
                                                <span class="inline-flex items-center gap-1"><i class="fas fa-layer-group text-[10px]"></i> {{ $standard->domain }}</span>
                                                <span class="opacity-40">·</span>
                                                <span>Strand {{ $standard->strand }}</span>
                                            </div>
                                        </div>
                                        <div class="hidden sm:flex items-center gap-1.5 shrink-0 ml-2">
                                            <a href="{{ route('admin.ppst-standards.edit', $standard) }}"
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 hover:bg-indigo-100 dark:hover:bg-indigo-900/30 border border-indigo-100 dark:border-indigo-800 rounded-xl transition-colors">
                                                <i class="fas fa-pen text-[10px]"></i> Edit
                                            </a>
                                            <form method="POST" action="{{ route('admin.ppst-standards.toggle-active', $standard) }}" class="inline">
                                                @csrf
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold {{ $standard->is_active ? 'text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-900/20 hover:bg-amber-100 dark:hover:bg-amber-900/30 border-amber-100 dark:border-amber-800' : 'text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-900/20 hover:bg-emerald-100 dark:hover:bg-emerald-900/30 border-emerald-100 dark:border-emerald-800' }} border rounded-xl transition-colors"
                                                        onclick="return confirm('{{ $standard->is_active ? 'Deactivate' : 'Activate' }} PPST indicator {{ $standard->indicator_code }}?')">
                                                    <i class="fas {{ $standard->is_active ? 'fa-pause' : 'fa-play' }} text-[10px]"></i> {{ $standard->is_active ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>
                                            @if($usageCount === 0)
                                                <form method="POST" action="{{ route('admin.ppst-standards.destroy', $standard) }}" class="inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                            class="inline-flex items-center justify-center w-8 h-8 text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/30 border border-red-100 dark:border-red-800 rounded-xl transition-colors"
                                                            onclick="return confirm('Delete PPST indicator {{ $standard->indicator_code }}? This cannot be undone.')" title="Delete">
                                                        <i class="fas fa-trash text-xs"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                    <!-- Mobile actions -->
                                    <div class="flex sm:hidden items-center gap-2 mt-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                                        <a href="{{ route('admin.ppst-standards.edit', $standard) }}" class="flex-1 text-center px-3 py-2.5 text-xs font-semibold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800 rounded-xl">Edit</a>
                                        <form method="POST" action="{{ route('admin.ppst-standards.toggle-active', $standard) }}" class="flex-1">
                                            @csrf
                                            <button type="submit" class="w-full px-3 py-2.5 text-xs font-semibold {{ $standard->is_active ? 'text-amber-700 bg-amber-50 border-amber-100' : 'text-emerald-700 bg-emerald-50 border-emerald-100' }} border rounded-xl" onclick="return confirm('{{ $standard->is_active ? 'Deactivate' : 'Activate' }} {{ $standard->indicator_code }}?')">{{ $standard->is_active ? 'Deactivate' : 'Activate' }}</button>
                                        </form>
                                        @if($usageCount === 0)
                                            <form method="POST" action="{{ route('admin.ppst-standards.destroy', $standard) }}">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="px-3 py-2.5 text-xs font-semibold text-red-600 bg-red-50 border border-red-100 rounded-xl" onclick="return confirm('Delete {{ $standard->indicator_code }}?')">Delete</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @empty
        <div class="text-center py-12 bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-dashed border-gray-300 dark:border-gray-700">
            <div class="w-14 h-14 rounded-2xl bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-400 mx-auto">
                <i class="fas fa-inbox text-xl"></i>
            </div>
            <p class="text-gray-900 dark:text-white font-semibold mt-4">No PPST standards yet.</p>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-md mx-auto">Seed the library or add the first indicator. PPST is the foundation for all COT Templates.</p>
            <code class="mt-3 inline-flex px-2.5 py-1 rounded-lg bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-xs font-mono text-gray-600 dark:text-gray-300">php artisan db:seed --class=PpstStandardSeeder</code>
            <div class="mt-4">
                <a href="{{ route('admin.ppst-standards.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 text-sm font-semibold transition-colors"><i class="fas fa-plus text-xs"></i> Add Standard</a>
            </div>
        </div>
    @endforelse
    </div>

    <!-- Help -->
    <div class="bg-gradient-to-br from-blue-50 to-indigo-50/50 dark:from-blue-900/10 dark:to-indigo-900/10 rounded-2xl p-6 border border-blue-100 dark:border-blue-900/30">
        <h3 class="text-sm font-bold text-blue-900 dark:text-blue-200 flex items-center gap-2"><span class="w-7 h-7 rounded-xl bg-blue-600 text-white flex items-center justify-center text-xs"><i class="fas fa-circle-info"></i></span> How PPST Standards Relate to COT</h3>
        <ul class="mt-3 text-sm text-blue-800 dark:text-blue-300 space-y-2">
            <li class="flex gap-2"><span class="w-1.5 h-1.5 rounded-full bg-blue-600 mt-2 shrink-0"></span><span><strong>PPST is the single library</strong> — Domain → Strand → Indicator. Not scoped by school year.</span></li>
            <li class="flex gap-2"><span class="w-1.5 h-1.5 rounded-full bg-blue-600 mt-2 shrink-0"></span><span>COT Templates (per school year, ratee role & career stage) <strong>assemble a subset</strong> of these indicators into the rating sheet.</span></li>
            <li class="flex gap-2"><span class="w-1.5 h-1.5 rounded-full bg-blue-600 mt-2 shrink-0"></span><span>Deactivating keeps the indicator in the library and in historical observations, but <strong>hides it from new COT picks</strong>.</span></li>
        </ul>
    </div>
    </div>

    <aside class="mock-rail" aria-label="Quick navigation">
        <div class="mock-mod">
            <div class="mock-mod-head"><h3>Jump to indicator</h3><span class="tick"></span></div>
            <div class="mock-mod-body">
                <select @change="scrollToIndicator($event.target.value)" class="w-full mb-2 px-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Pick an indicator…</option>
                    @foreach($domains as $domain => $strands)
                        @php $meta = $getMeta($domain); @endphp
                        <optgroup label="Domain {{ $meta['number'] }} — {{ Str::limit($domain, 32) }}">
                            @foreach($strands as $strand => $indicators)
                                @foreach($indicators as $standard)
                                    <option value="{{ $standard->indicator_code }}">{{ $standard->indicator_code }} — {{ Str::limit($standard->description, 42) }}</option>
                                @endforeach
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                @foreach($domains as $domain => $strands)
                    @php $meta = $getMeta($domain); @endphp
                    <button type="button" @click="scrollToDomain('{{ Str::slug($domain) }}')" class="mock-act">
                        <span class="w-6 h-6 rounded-full bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800 text-indigo-700 dark:text-indigo-300 flex items-center justify-center text-[10px] font-bold shrink-0">{{ $meta['number'] }}</span>
                        <span class="truncate">{{ Str::limit($domain, 30) }}</span>
                        <span class="ml-auto text-[10px] font-semibold text-gray-400 dark:text-gray-500 shrink-0">{{ $strands->flatten()->count() }}</span>
                    </button>
                @endforeach
            </div>
        </div>
        <div class="mock-mod">
            <div class="mock-mod-head"><h3>Tips</h3></div>
            <div class="mock-mod-body">
                <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-2 list-disc list-inside">
                    <li>Use search to quickly find an indicator by code or keyword.</li>
                    <li>Filter by <strong>Unused</strong> to find candidates for cleanup.</li>
                    <li>Codes must be <span class="font-mono bg-gray-50 dark:bg-gray-800 px-1 py-0.5 rounded border text-xs">D.S.I</span> (e.g. 1.1.2).</li>
                    <li>Strand is auto-derived from the code — no need to set it manually.</li>
                </ul>
            </div>
        </div>
    </aside>
    </div>
</div>
@endsection

@push('styles')
<style>
    .indicator-flash { outline: 2px solid #2563eb !important; outline-offset: 2px; box-shadow: 0 0 0 4px rgba(37,99,235,.15); }
</style>
@endpush

@push('scripts')
<script>
function ppstStandardsPage() {
    return {
        search: '',
        domainFilter: 'all',
        statusFilter: 'all',
        usageFilter: 'all',
        totalCount: 0,
        visibleCount: 0,
        hasNoResults: false,
        init() {
            this.totalCount = document.querySelectorAll('[data-indicator-card]').length;
            this.visibleCount = this.totalCount;
            // Restore from URL if present
            const params = new URLSearchParams(window.location.search);
            if (params.get('search')) this.search = params.get('search');
            if (params.get('domain')) this.domainFilter = params.get('domain');
            if (params.get('status')) this.statusFilter = params.get('status');
            if (params.get('usage')) this.usageFilter = params.get('usage');
            this.$nextTick(() => this.applyFilters());
        },
        applyFilters() {
            const q = this.search.trim().toLowerCase();
            const d = this.domainFilter;
            const s = this.statusFilter;
            const u = this.usageFilter;
            let visible = 0;

            document.querySelectorAll('[data-indicator-card]').forEach(card => {
                const code = (card.dataset.code || '').toLowerCase();
                const strand = (card.dataset.strand || '').toLowerCase();
                const domain = card.dataset.domain || '';
                const desc = (card.dataset.description || '').toLowerCase();
                const active = card.dataset.active;
                const usage = parseInt(card.dataset.usage || '0', 10);

                let show = true;
                if (q && !(code.includes(q) || strand.includes(q) || domain.toLowerCase().includes(q) || desc.includes(q))) show = false;
                if (d !== 'all' && domain !== d) show = false;
                if (s !== 'all') {
                    if (s === 'active' && active !== '1') show = false;
                    if (s === 'inactive' && active !== '0') show = false;
                }
                if (u !== 'all') {
                    if (u === 'used' && usage === 0) show = false;
                    if (u === 'unused' && usage !== 0) show = false;
                }

                card.style.display = show ? '' : 'none';
                if (show) visible++;
            });

            // Hide empty strands
            document.querySelectorAll('[data-strand-group]').forEach(group => {
                const hasVisible = Array.from(group.querySelectorAll('[data-indicator-card]')).some(c => c.style.display !== 'none');
                group.style.display = hasVisible ? '' : 'none';
            });

            // Hide empty domains
            document.querySelectorAll('[data-domain-section]').forEach(section => {
                const hasVisible = Array.from(section.querySelectorAll('[data-indicator-card]')).some(c => c.style.display !== 'none');
                section.style.display = hasVisible ? '' : 'none';
            });

            this.visibleCount = visible;
            this.hasNoResults = visible === 0 && this.totalCount > 0;

            // Sync to URL (without reload)
            const params = new URLSearchParams();
            if (q) params.set('search', this.search.trim());
            if (d !== 'all') params.set('domain', d);
            if (s !== 'all') params.set('status', s);
            if (u !== 'all') params.set('usage', u);
            const qs = params.toString();
            history.replaceState(null, '', qs ? window.location.pathname + '?' + qs : window.location.pathname);
        },
        clearFilters() {
            this.search = '';
            this.domainFilter = 'all';
            this.statusFilter = 'all';
            this.usageFilter = 'all';
            this.applyFilters();
        },
        expandAll() {
            document.querySelectorAll('[data-domain-section]').forEach(el => {
                const alpine = Alpine.$data(el);
                if (alpine && 'open' in alpine) alpine.open = true;
            });
        },
        collapseAll() {
            document.querySelectorAll('[data-domain-section]').forEach(el => {
                const alpine = Alpine.$data(el);
                if (alpine && 'open' in alpine) alpine.open = false;
            });
        },
        scrollToDomain(slug) {
            const el = document.getElementById('domain-' + slug);
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        },
        scrollToIndicator(code) {
            if (!code) return;
            const slug = code.toLowerCase().replace(/[^a-z0-9]+/g, '');
            this.search = '';
            this.domainFilter = 'all';
            this.statusFilter = 'all';
            this.usageFilter = 'all';
            this.applyFilters();
            document.querySelectorAll('[data-domain-section]').forEach(el => {
                const alpine = Alpine.$data(el);
                if (alpine && 'open' in alpine) alpine.open = true;
            });
            this.$nextTick(() => this.$nextTick(() => {
                const el = document.getElementById('indicator-' + slug);
                if (!el) return;
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                el.classList.add('indicator-flash');
                setTimeout(() => el.classList.remove('indicator-flash'), 2200);
            }));
        }
    }
}
</script>
@endpush
