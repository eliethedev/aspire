@extends('layouts.supervisor')

@section('title', 'School Heads List')

@push('styles')
<style>
    .hero-card{background:linear-gradient(135deg,#eef2ff 0%,#f8fafc 55%,#ffffff 100%)}
    .dark .hero-card{background:linear-gradient(135deg,#0b1220 0%,#111827 55%,#0b1220 100%)}
    .teacher-card{transition:all .2s ease}
    .teacher-card:hover{transform:translateY(-2px);box-shadow:0 10px 28px rgba(15,23,42,.08)}
    .section-card{transition:all .18s ease}
</style>
@endpush
@section('content')
<div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-0"
     x-data="{
        view: (function () { try { return localStorage.getItem('supervisorSchoolHeadsView') || 'list'; } catch (e) { return 'list'; } })(),
        setView(v) { this.view = v; try { localStorage.setItem('supervisorSchoolHeadsView', v); } catch (e) {} },
     }">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-xs text-slate-500 dark:text-gray-400">
        <a href="{{ route('supervisor.dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 inline-flex items-center gap-1"><i class="fas fa-house text-[11px]"></i> Dashboard</a>
        <span class="text-slate-300 dark:text-gray-600">/</span>
        <span class="font-semibold text-slate-700 dark:text-gray-200">School Heads</span>
    </nav>

    {{-- Hero --}}
    <div class="hero-card rounded-[20px] border border-slate-200 dark:border-gray-800 dark:bg-gray-900 p-6 lg:p-7 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-6">
            <div class="min-w-0">
                <p class="text-slate-500 dark:text-gray-400 text-xs tracking-widest uppercase font-semibold">Supervisor Workspace · {{ now()->format('l, F j, Y') }}</p>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white leading-tight mt-1">School Heads</h1>
                <p class="text-slate-500 dark:text-gray-400 text-sm mt-1 max-w-2xl">View the school heads under your supervision and schedule leadership observations.</p>
                <div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-700 dark:text-gray-200"><span class="w-2 h-2 rounded-full bg-indigo-500"></span> {{ $schoolHeads->total() }} total</span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-700 dark:text-gray-200"><i class="fas fa-building-columns text-[11px] text-slate-400 dark:text-gray-500"></i> School leaders</span>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('supervisor.observations.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white rounded-xl font-semibold hover:bg-indigo-700 transition-colors shadow-sm">
                    <i class="fas fa-plus text-xs"></i> New Observation
                </a>
            </div>
        </div>
    </div>

    {{-- Search & Filters --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-slate-200 dark:border-gray-800 shadow-sm overflow-hidden section-card" x-data="{ open: @json(true) }">
        <button type="button" @click="open = !open"
                class="w-full flex items-center justify-between gap-2 px-4 py-3.5 text-left hover:bg-slate-50 dark:hover:bg-gray-800 transition-colors"
                :aria-expanded="open.toString()">
            <span class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-100 dark:border-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <i class="fas fa-sliders text-xs"></i>
                </span>
                <span>
                    <span class="block text-sm font-bold text-slate-900 dark:text-white leading-none">Search & Filters</span>
                    <span class="block text-xs font-medium text-slate-500 dark:text-gray-400 leading-none mt-1">Find school head by name or email · adjust page size</span>
                </span>
                @if(request('search'))<span class="hidden sm:inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 dark:bg-amber-500/10 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-500/20">Active</span>@endif
            </span>
            <span class="flex items-center gap-2 shrink-0">
                <span class="hidden sm:inline text-xs font-semibold text-indigo-600 dark:text-indigo-400" x-text="open ? 'Hide' : 'Show'"></span>
                <span class="w-7 h-7 rounded-full bg-slate-100 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 flex items-center justify-center">
                    <i class="fas fa-chevron-down text-xs text-slate-600 dark:text-gray-300 transition-transform" :class="open ? 'rotate-180' : ''"></i>
                </span>
            </span>
        </button>
        <div x-show="open" x-transition>
            <form method="GET" action="{{ route('supervisor.school-heads.index') }}" class="px-4 py-4 border-t border-slate-200 dark:border-gray-800 bg-slate-50/50 dark:bg-gray-800/20">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                    <div class="md:col-span-7">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-gray-300 mb-1">Search</label>
                        <div class="relative">
                            <i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 dark:text-gray-500 text-xs"></i>
                            <input type="text" name="search" value="{{ request('search') }}"
                                   class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-slate-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm text-slate-900 dark:text-gray-100 placeholder:text-slate-400 dark:placeholder:text-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                                   placeholder="Search by name or email...">
                        </div>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-gray-300 mb-1">Per page</label>
                        <select name="per_page" onchange="this.form.submit()"
                                class="w-full px-3 py-2.5 rounded-xl border border-slate-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-sm text-slate-900 dark:text-gray-100 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                            <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15 per page</option>
                            <option value="30" {{ request('per_page') == 30 ? 'selected' : '' }}>30 per page</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 per page</option>
                        </select>
                    </div>
                    <div class="md:col-span-3 flex items-end gap-2">
                        <button type="submit"
                                class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-700 transition-colors shadow-sm">
                            <i class="fas fa-filter text-xs"></i> Apply
                        </button>
                        @if(request('search'))
                            <a href="{{ route('supervisor.school-heads.index') }}"
                               class="inline-flex items-center justify-center px-4 py-2.5 bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-700 dark:text-gray-200 rounded-xl text-sm font-semibold hover:bg-slate-50 dark:hover:bg-gray-700 transition-colors">
                                Clear
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Results Summary --}}
    <div class="flex items-center justify-between">
        <p class="text-sm text-slate-500 dark:text-gray-400">
            Showing <span class="font-semibold text-slate-700 dark:text-gray-200">{{ $schoolHeads->firstItem() ?? 0 }}</span>
            to <span class="font-semibold text-slate-700 dark:text-gray-200">{{ $schoolHeads->lastItem() ?? 0 }}</span>
            of <span class="font-semibold text-slate-700 dark:text-gray-200">{{ $schoolHeads->total() }}</span> school heads
            @if(request('search'))<span class="ml-1 px-2 py-0.5 rounded-full bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 text-amber-700 dark:text-amber-300 text-xs font-semibold">filtered</span>@endif
        </p>
        <div class="inline-flex rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-0.5" role="group" aria-label="List layout">
            <button type="button" @click="setView('list')" :aria-pressed="(view === 'list').toString()" title="List view" aria-label="List view"
                    class="inline-flex items-center justify-center w-8 h-8 rounded-md transition-colors"
                    :class="view === 'list' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <button type="button" @click="setView('table')" :aria-pressed="(view === 'table').toString()" title="Table view" aria-label="Table view"
                    class="inline-flex items-center justify-center w-8 h-8 rounded-md transition-colors"
                    :class="view === 'table' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
            </button>
        </div>
    </div>

    {{-- School heads — List view --}}
    <div x-show="view === 'list'">
    @forelse($schoolHeads as $schoolHead)
        @php
            $shName = $schoolHead->user?->name ?? $schoolHead->display_name ?? 'Unnamed School Head';
            $shEmail = $schoolHead->user?->email ?? '';
            $initial = strtoupper(substr($shName, 0, 1));
            $obsCount = $schoolHead->total_observations ?? 0;
            $avatarClass = 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-500/20';
        @endphp
        <div class="teacher-card bg-white dark:bg-gray-900 rounded-2xl border border-slate-200 dark:border-gray-800 shadow-sm p-5">
            <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                <div class="flex items-center gap-3 min-w-0 flex-1">
                    <div class="w-11 h-11 rounded-full {{ $avatarClass }} flex items-center justify-center text-base font-bold shrink-0">
                        {{ $initial }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <a href="{{ route('supervisor.school-heads.show', $schoolHead) }}" class="font-semibold text-slate-900 dark:text-white truncate hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">{{ $shName }}</a>
                            @if($schoolHead->position_level)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-600 dark:text-gray-300">
                                    {{ $schoolHead->position_level_label }}
                                </span>
                            @endif
                            @if($schoolHead->current_designation)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-700 dark:text-gray-200">
                                    <i class="fas fa-id-badge text-[10px] mr-1 text-indigo-500 dark:text-indigo-400"></i> {{ $schoolHead->current_designation_label }}
                                </span>
                            @endif
                        </div>
                        <p class="text-sm text-slate-500 dark:text-gray-400 truncate">{{ $shEmail }}</p>
                        <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500 dark:text-gray-400">
                            @if($schoolHead->school)<span class="inline-flex items-center gap-1"><i class="fas fa-school text-[11px] text-slate-400 dark:text-gray-500"></i> {{ $schoolHead->school->name }}</span>@endif
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold border {{ $obsCount > 0 ? 'bg-indigo-50 dark:bg-indigo-500/10 border-indigo-200 dark:border-indigo-500/20 text-indigo-700 dark:text-indigo-300' : 'bg-slate-50 dark:bg-gray-800 border-slate-200 dark:border-gray-700 text-slate-500 dark:text-gray-400' }}" title="{{ $obsCount }} observations">
                        <i class="fas fa-clipboard-list text-[11px]"></i> {{ $obsCount }}
                    </span>
                    <a href="{{ route('supervisor.school-heads.show', $schoolHead) }}"
                       class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition-colors shadow-sm inline-flex items-center gap-1.5">
                        <i class="fas fa-user text-xs"></i> View
                    </a>
                    <a href="{{ route('supervisor.school-heads.observations', $schoolHead) }}"
                       class="px-4 py-2 bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-700 dark:text-gray-200 rounded-xl text-sm font-semibold hover:bg-slate-50 dark:hover:bg-gray-700 transition-colors">
                        History
                    </a>
                    <a href="{{ route('supervisor.observations.create', ['school_head' => $schoolHead->id]) }}"
                       class="px-4 py-2 bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-700 dark:text-gray-200 rounded-xl text-sm font-semibold hover:bg-slate-50 dark:hover:bg-gray-700 transition-colors">
                        Schedule
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-slate-200 dark:border-gray-800 shadow-sm p-10 text-center">
            <div class="w-14 h-14 rounded-2xl bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-building-columns text-slate-400 dark:text-gray-500"></i>
            </div>
            <h3 class="text-base font-semibold text-slate-900 dark:text-white">No school heads found</h3>
            <p class="text-sm text-slate-500 dark:text-gray-400 mt-1 max-w-md mx-auto">
                @if(request('search'))
                    No school heads match “{{ request('search') }}”. Try a different name or email.
                @else
                    There are no school heads registered in the system yet.
                @endif
            </p>
            @if(request('search'))
                <a href="{{ route('supervisor.school-heads.index') }}" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-700 dark:text-gray-200 rounded-xl text-sm font-semibold hover:bg-slate-50 dark:hover:bg-gray-700">Clear search</a>
            @endif
        </div>
    @endforelse
    </div>

    {{-- School heads — Table view --}}
    <div x-show="view === 'table'" class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden" aria-label="School heads table">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/60 border-b border-gray-200 dark:border-gray-700">
                        <th scope="col" class="px-3 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">School Head</th>
                        <th scope="col" class="px-3 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">School</th>
                        <th scope="col" class="px-3 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Position</th>
                        <th scope="col" class="px-3 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Obs</th>
                        <th scope="col" class="px-3 py-2.5 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($schoolHeads as $schoolHead)
                    @php
                        $shName = $schoolHead->user?->name ?? $schoolHead->display_name ?? 'Unnamed School Head';
                        $shEmail = $schoolHead->user?->email ?? '';
                        $initial = strtoupper(substr($shName, 0, 1));
                        $obsCount = $schoolHead->total_observations ?? 0;
                    @endphp
                    <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-800/40 transition-colors">
                        <td class="px-3 py-2.5">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-7 h-7 rounded-full bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-500/20 flex items-center justify-center text-xs font-bold shrink-0" aria-hidden="true">{{ $initial }}</div>
                                <div class="min-w-0">
                                    <a href="{{ route('supervisor.school-heads.show', $schoolHead) }}" class="block text-sm font-semibold text-gray-900 dark:text-gray-100 truncate hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">{{ $shName }}</a>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate leading-tight">{{ $shEmail }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-2.5">
                            <span class="text-xs font-medium text-gray-600 dark:text-gray-300">{{ $schoolHead->school ? $schoolHead->school->name : '—' }}</span>
                        </td>
                        <td class="px-3 py-2.5 whitespace-nowrap">
                            <span class="text-xs font-medium text-gray-600 dark:text-gray-300">{{ $schoolHead->position_level ? $schoolHead->position_level_label : '—' }}</span>
                        </td>
                        <td class="px-3 py-2.5 whitespace-nowrap">
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $obsCount }}</span>
                        </td>
                        <td class="px-3 py-2.5 text-right whitespace-nowrap">
                            <a href="{{ route('supervisor.school-heads.show', $schoolHead) }}" class="inline-flex items-center px-2.5 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-xs font-semibold transition-colors">View</a>
                            <a href="{{ route('supervisor.school-heads.observations', $schoolHead) }}" class="inline-flex items-center px-2.5 py-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 rounded-md text-xs font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">History</a>
                            <a href="{{ route('supervisor.observations.create', ['school_head' => $schoolHead->id]) }}" class="inline-flex items-center px-2.5 py-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 rounded-md text-xs font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Schedule</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-3 py-8 text-center">
                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">No school heads found</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">There are no school heads registered in the system yet.</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($schoolHeads->hasPages())
        <div class="pt-2">
            {{ $schoolHeads->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@endsection