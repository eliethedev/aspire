@extends('layouts.supervisor')
@section('title', 'Teachers List')
@push('styles')
<style>
    .hero-card{background:linear-gradient(135deg,#eef2ff 0%,#f8fafc 55%,#ffffff 100%)}
    .dark .hero-card{background:linear-gradient(135deg,#0b1220 0%,#111827 55%,#0b1220 100%)}
    .teacher-card{transition:all .2s ease}
    .teacher-card:hover{transform:translateY(-2px);box-shadow:0 10px 28px rgba(15,23,42,.08)}
    .attention-pill{transition:all .15s ease}
    .section-card{transition:all .18s ease}
</style>
@endpush
@section('content')
@php
    $attentionFilter = request('attention') === 'needs';
    $levelColor = ['high' => 'red', 'medium' => 'orange', 'low' => 'amber', 'ok' => 'slate'];
    $levelLabel = ['high' => 'High priority', 'medium' => 'Medium', 'low' => 'Watch', 'ok' => 'On track'];
    $todayStr = now()->format('l, F j, Y');
@endphp
<div class="max-w-7xl mx-auto {{ $attentionFilter ? 'space-y-3' : 'space-y-6' }} px-4 sm:px-6 lg:px-0"
     x-data="{
        view: (function () { try { return localStorage.getItem('supervisorTeachersView') || 'list'; } catch (e) { return 'list'; } })(),
        setView(v) { this.view = v; try { localStorage.setItem('supervisorTeachersView', v); } catch (e) {} },
     }">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-xs text-slate-500 dark:text-gray-400">
        <a href="{{ route('supervisor.dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 inline-flex items-center gap-1"><i class="fas fa-house text-[11px]"></i> Dashboard</a>
        <span class="text-slate-300 dark:text-gray-600">/</span>
        <span class="font-semibold text-slate-700 dark:text-gray-200">Teachers</span>
        @if($attentionFilter)<span class="text-slate-300 dark:text-gray-600">/</span><span class="px-2 py-0.5 rounded-full bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 text-amber-700 dark:text-amber-300 font-semibold">Needs attention</span>@endif
    </nav>

    {{-- Hero --}}
    <div class="hero-card rounded-[20px] border border-slate-200 dark:border-gray-800 dark:bg-gray-900 {{ $attentionFilter ? 'p-4 lg:p-5' : 'p-6 lg:p-7' }} shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-start justify-between {{ $attentionFilter ? 'gap-3' : 'gap-6' }}">
            <div class="min-w-0">
                <p class="text-slate-500 dark:text-gray-400 text-xs tracking-widest uppercase font-semibold">Supervisor Workspace · {{ $todayStr }}</p>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white leading-tight mt-1">Teachers</h1>
                <p class="text-slate-500 dark:text-gray-400 text-sm mt-1 max-w-2xl">Manage your faculty roster. Teachers flagged on the left need your focus first — sorted by priority.</p>
                <div class="{{ $attentionFilter ? 'mt-2' : 'mt-3' }} flex flex-wrap items-center gap-2 text-xs">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-700 dark:text-gray-200"><span class="w-2 h-2 rounded-full bg-indigo-500"></span> {{ $teachers->total() }} total</span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white dark:bg-gray-800 border {{ $needsAttentionCount>0?'border-amber-200 dark:border-amber-500/20 text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-500/10':'border-slate-200 dark:border-gray-700 text-slate-700 dark:text-gray-200' }}"><span class="w-2 h-2 rounded-full {{ $needsAttentionCount>0?'bg-amber-500':'bg-emerald-500' }}"></span> {{ $needsAttentionCount }} needs attention</span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-700 dark:text-gray-200"><i class="fas fa-magnifying-glass text-[11px] text-slate-400 dark:text-gray-500"></i> Sorted by priority</span>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('supervisor.teachers.index', ['attention'=>'needs']) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-700 dark:text-gray-200 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-gray-700 transition-colors {{ $attentionFilter?'ring-2 ring-amber-200 border-amber-300':'' }}">
                    <i class="fas fa-bell text-amber-500 dark:text-amber-400 text-xs"></i> Needs attention
                    <span class="px-1.5 py-0.5 rounded-full bg-amber-100 dark:bg-amber-500/10 text-amber-700 dark:text-amber-300 text-xs font-bold">{{ $needsAttentionCount }}</span>
                </a>
                <a href="{{ route('supervisor.observations.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white rounded-xl font-semibold hover:bg-indigo-700 transition-colors shadow-sm">
                    <i class="fas fa-plus text-xs"></i> New Observation
                </a>
            </div>
        </div>
    </div>

    {{-- Attention summary banner --}}
    <div class="rounded-2xl overflow-hidden border shadow-sm {{ $needsAttentionCount > 0 ? 'bg-gradient-to-r from-amber-50 dark:from-amber-500/10 via-amber-50/60 dark:via-amber-500/5 to-white dark:to-gray-900 border-amber-200 dark:border-amber-500/20' : 'bg-white dark:bg-gray-900 border-slate-200 dark:border-gray-800' }}">
        <div class="flex flex-col sm:flex-row sm:items-center {{ $attentionFilter ? 'gap-3 px-4 py-3' : 'gap-4 px-5 py-4' }}">
            <div class="{{ $attentionFilter ? 'w-9 h-9' : 'w-11 h-11' }} rounded-xl {{ $needsAttentionCount > 0 ? 'bg-amber-500 text-white shadow-sm' : 'bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 text-emerald-600 dark:text-emerald-400' }} flex items-center justify-center shrink-0">
                <i class="fas {{ $needsAttentionCount > 0 ? 'fa-triangle-exclamation' : 'fa-circle-check' }}"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $needsAttentionCount > 0 ? "{$needsAttentionCount} teacher(s) need your attention" : 'All teachers are on track' }}</p>
                <p class="text-xs text-slate-600 dark:text-gray-400 mt-0.5">
                    @if($needsAttentionCount > 0)
                        Flags are based on average COT score, trend, and pending work. Use the filter below to focus only on them.
                    @else
                        No flags right now. We’ll surface anyone who drops below Satisfactory, trends down, or waits on you.
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('supervisor.teachers.index', $attentionFilter ? request()->except(['attention','page']) : array_merge(request()->except('page'), ['attention'=>'needs'])) }}"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold {{ $attentionFilter ? 'bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-700 dark:text-gray-200 hover:bg-slate-50 dark:hover:bg-gray-700' : 'bg-amber-500 text-white hover:bg-amber-600 shadow-sm' }} transition-colors">
                    <i class="fas {{ $attentionFilter ? 'fa-users' : 'fa-filter' }} text-xs"></i> {{ $attentionFilter ? 'Show all' : 'Focus on attention' }}
                </a>
                @if($needsAttentionCount>0 && !$attentionFilter)
                <a href="{{ route('supervisor.teachers.index', array_merge(request()->except('page'), ['attention'=>'needs'])) }}" class="hidden sm:inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">View flagged <i class="fas fa-arrow-right text-[10px]"></i></a>
                @endif
            </div>
        </div>
    </div>

    {{-- Filter tabs --}}
    <div class="flex items-center gap-2 flex-wrap">
        <a href="{{ route('supervisor.teachers.index', request()->except(['attention', 'page'])) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold border transition-colors {{ !$attentionFilter ? 'bg-indigo-600 border-indigo-600 text-white shadow-sm' : 'bg-white dark:bg-gray-900 border-slate-200 dark:border-gray-700 text-slate-700 dark:text-gray-200 hover:bg-slate-50 dark:hover:bg-gray-800' }}">
            <i class="fas fa-users text-xs"></i> All
            <span class="text-xs px-1.5 py-0.5 rounded-full {{ !$attentionFilter ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-gray-800 text-slate-600 dark:text-gray-300' }}">{{ $teachers->total() }}</span>
        </a>
        <a href="{{ route('supervisor.teachers.index', array_merge(request()->except(['attention', 'page']), ['attention' => 'needs'])) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold border transition-colors {{ $attentionFilter ? 'bg-amber-500 border-amber-500 text-white shadow-sm' : 'bg-white dark:bg-gray-900 border-slate-200 dark:border-gray-700 text-slate-700 dark:text-gray-200 hover:bg-amber-50 dark:hover:bg-gray-800' }}">
            <i class="fas fa-bell text-xs"></i> Needs attention
            <span class="text-xs px-1.5 py-0.5 rounded-full {{ $attentionFilter ? 'bg-white/20 text-white' : 'bg-amber-100 dark:bg-amber-500/10 text-amber-700 dark:text-amber-300' }}">{{ $needsAttentionCount }}</span>
        </a>
        @if(request('search'))<span class="ml-1 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-200 dark:border-indigo-500/20 text-indigo-700 dark:text-indigo-300 text-xs font-medium"><i class="fas fa-magnifying-glass text-[11px]"></i> “{{ request('search') }}”</span>@endif
    </div>

    {{-- Search & Filters --}}
    @php $hasTeacherFilters = request()->anyFilled(['search', 'per_page']) && request('search'); @endphp
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-slate-200 dark:border-gray-800 shadow-sm overflow-hidden section-card" x-data="{ open: @json($hasTeacherFilters || true) }">
        <button type="button" @click="open = !open"
                class="w-full flex items-center justify-between gap-2 px-4 py-3.5 text-left hover:bg-slate-50 dark:hover:bg-gray-800 transition-colors"
                :aria-expanded="open.toString()">
            <span class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-100 dark:border-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                    <i class="fas fa-sliders text-xs"></i>
                </span>
                <span>
                    <span class="block text-sm font-bold text-slate-900 dark:text-white leading-none">Search & Filters</span>
                    <span class="block text-xs font-medium text-slate-500 dark:text-gray-400 leading-none mt-1">Find teacher by name or email · adjust page size</span>
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
            <form method="GET" action="{{ route('supervisor.teachers.index') }}" class="px-4 py-4 border-t border-slate-200 dark:border-gray-800 bg-slate-50/50 dark:bg-gray-800/20">
                @if($attentionFilter)<input type="hidden" name="attention" value="needs">@endif
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
                        @if(request('search') || $attentionFilter)
                            <a href="{{ route('supervisor.teachers.index') }}"
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
            Showing <span class="font-semibold text-slate-700 dark:text-gray-200">{{ $teachers->firstItem() ?? 0 }}</span>
            to <span class="font-semibold text-slate-700 dark:text-gray-200">{{ $teachers->lastItem() ?? 0 }}</span>
            of <span class="font-semibold text-slate-700 dark:text-gray-200">{{ $teachers->total() }}</span> teachers
            @if($attentionFilter)<span class="ml-1 px-2 py-0.5 rounded-full bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 text-amber-700 dark:text-amber-300 text-xs font-semibold">filtered</span>@endif
        </p>
        <div class="flex items-center gap-2 shrink-0">
        <span class="hidden sm:inline-flex items-center gap-1.5 text-xs text-slate-500 dark:text-gray-400"><span class="w-2 h-2 rounded-full bg-amber-400"></span> Flagged first</span>
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
    </div>

    {{-- Teachers — List view --}}
    <div x-show="view === 'list'">
    @forelse($teachers as $teacher)
        @php
            $initial = strtoupper(substr($teacher->user->name, 0, 1));
            $obsCount = $teacher->observations_count ?? 0;
            $level = $teacher->attention_level ?? 'ok';
            $flags = $teacher->attention_flags ?? [];
            $border = $level !== 'ok' ? 'border-l-4 ' . match($level){'high'=>'border-red-400','medium'=>'border-orange-400',default=>'border-amber-300'} : '';
            $avatarClass = match ($level) {
                'high' => 'bg-red-100 dark:bg-red-500/10 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-500/20',
                'medium' => 'bg-orange-100 dark:bg-orange-500/10 text-orange-700 dark:text-orange-300 border border-orange-200 dark:border-orange-500/20',
                'low' => 'bg-amber-100 dark:bg-amber-500/10 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-500/20',
                default => 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-500/20',
            };
            $badgeClass = $level !== 'ok'
                ? match($level){'high'=>'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-300 border-red-200 dark:border-red-500/20','medium'=>'bg-orange-50 dark:bg-orange-500/10 text-orange-700 dark:text-orange-300 border-orange-200 dark:border-orange-500/20',default=>'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-500/20'}
                : 'bg-slate-50 dark:bg-gray-800 text-slate-600 dark:text-gray-300 border-slate-200 dark:border-gray-700';
            $badgeIcon = match($level){'high'=>'fa-triangle-exclamation','medium'=>'fa-exclamation','low'=>'fa-circle-info',default=>'fa-check'};
        @endphp
        <div class="teacher-card bg-white dark:bg-gray-900 rounded-2xl border border-slate-200 dark:border-gray-800 shadow-sm {{ $attentionFilter ? 'p-4' : 'p-5' }} {{ $border }}">
            <div class="flex flex-col lg:flex-row lg:items-center {{ $attentionFilter ? 'gap-3' : 'gap-4' }}">
                {{-- Avatar + Identity --}}
                <div class="flex items-center gap-3 min-w-0 flex-1">
                    <div class="w-11 h-11 rounded-full {{ $avatarClass }} flex items-center justify-center text-base font-bold shrink-0">
                        {{ $initial }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <a href="{{ route('supervisor.teachers.show', $teacher) }}" class="font-semibold text-slate-900 dark:text-white truncate hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">{{ $teacher->user->name }}</a>
                            @if($teacher->position)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-600 dark:text-gray-300">
                                    {{ $teacher->position_label ?? $teacher->position }}
                                </span>
                            @endif
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold border {{ $badgeClass }}">
                                <i class="fas {{ $badgeIcon }} text-[10px]"></i> {{ $levelLabel[$level] }}
                            </span>
                        </div>
                        <p class="text-sm text-slate-500 dark:text-gray-400 truncate">{{ $teacher->user->email }}</p>
                        <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-slate-500 dark:text-gray-400">
                            @if($teacher->school)<span class="inline-flex items-center gap-1"><i class="fas fa-school text-[11px] text-slate-400 dark:text-gray-500"></i> {{ $teacher->school->name }}</span>@endif
                            @if($teacher->subjectsLabel)<span class="inline-flex items-center gap-1"><i class="fas fa-book-open text-[11px] text-slate-400 dark:text-gray-500"></i> {{ $teacher->subjectsLabel }}</span>@endif
                            @if($teacher->grade_level)<span class="inline-flex items-center gap-1"><i class="fas fa-layer-group text-[11px] text-slate-400 dark:text-gray-500"></i> {{ $teacher->grade_level_label }}</span>@endif
                        </div>
                    </div>
                </div>

                {{-- Attention reasons --}}
                @if(!empty($flags))
                <div class="flex items-center gap-2 flex-wrap lg:max-w-[320px]">
                    @foreach($flags as $f)
                        <span class="attention-pill inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border
                            {{ $f['tone'] === 'red' ? 'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-300 border-red-200 dark:border-red-500/20'
                              : ($f['tone'] === 'orange' ? 'bg-orange-50 dark:bg-orange-500/10 text-orange-700 dark:text-orange-300 border-orange-200 dark:border-orange-500/20'
                              : ($f['tone'] === 'amber' ? 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-500/20'
                              : 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-500/20')) }}"
                             title="{{ $f['description'] }}">
                            <i class="fas {{ $f['icon'] }} text-[11px]"></i> {{ $f['label'] }}
                        </span>
                    @endforeach
                </div>
                @endif

                {{-- Actions --}}
                <div class="flex items-center gap-2 shrink-0">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold border {{ $obsCount > 0 ? 'bg-indigo-50 dark:bg-indigo-500/10 border-indigo-200 dark:border-indigo-500/20 text-indigo-700 dark:text-indigo-300' : 'bg-slate-50 dark:bg-gray-800 border-slate-200 dark:border-gray-700 text-slate-500 dark:text-gray-400' }}" title="{{ $obsCount }} observations">
                        <i class="fas fa-clipboard-list text-[11px]"></i> {{ $obsCount }}
                    </span>
                    <a href="{{ route('supervisor.teachers.show', $teacher) }}"
                       class="px-4 py-2 bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-700 dark:text-gray-200 rounded-xl text-sm font-semibold hover:bg-slate-50 dark:hover:bg-gray-700 transition-colors">
                        View
                    </a>
                    <a href="{{ route('supervisor.observations.create') }}?teacher_id={{ $teacher->id }}"
                       class="px-4 py-2 {{ $level === 'high' ? 'bg-red-600 hover:bg-red-700' : 'bg-indigo-600 hover:bg-indigo-700' }} text-white rounded-xl text-sm font-semibold transition-colors shadow-sm inline-flex items-center gap-1.5">
                        <i class="fas fa-eye text-xs"></i> Observe
                    </a>
                </div>
            </div>
            @if(!empty($flags) && $level !== 'ok')
            <div class="{{ $attentionFilter ? 'mt-2 pt-2' : 'mt-3 pt-3' }} border-t border-slate-100 dark:border-gray-800 flex flex-wrap items-center gap-2 text-xs text-slate-600 dark:text-gray-400">
                <i class="fas fa-circle-info text-slate-400 dark:text-gray-500"></i>
                <span>{{ $flags[0]['description'] ?? '' }}</span>
                <a href="{{ route('supervisor.teachers.show', $teacher) }}" class="ml-auto text-indigo-600 dark:text-indigo-400 font-semibold hover:text-indigo-700 dark:hover:text-indigo-300">Open profile →</a>
            </div>
            @endif
        </div>
    @empty
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-slate-200 dark:border-gray-800 shadow-sm p-10 text-center">
            <div class="w-14 h-14 rounded-2xl bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-users text-slate-400 dark:text-gray-500"></i>
            </div>
            <h3 class="text-base font-semibold text-slate-900 dark:text-white">{{ $attentionFilter ? 'No teachers need attention' : 'No teachers found' }}</h3>
            <p class="text-sm text-slate-500 dark:text-gray-400 mt-1 max-w-md mx-auto">
                @if($attentionFilter)
                    Every teacher under your supervision is currently on track. Nice work — check back after new evaluations.
                @elseif(request('search'))
                    No teachers match “{{ request('search') }}”. Try a different name or email.
                @else
                    There are no teachers assigned to your supervision yet.
                @endif
            </p>
            @if($attentionFilter)
                <a href="{{ route('supervisor.teachers.index') }}" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-700 transition-colors">Show all teachers</a>
            @elseif(request('search'))
                <a href="{{ route('supervisor.teachers.index', $attentionFilter?['attention'=>'needs']:[]) }}" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-700 dark:text-gray-200 rounded-xl text-sm font-semibold hover:bg-slate-50 dark:hover:bg-gray-700">Clear search</a>
            @endif
        </div>
    @endforelse
    </div>

    {{-- Teachers — Table view --}}
    <div x-show="view === 'table'" class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden" aria-label="Teachers table">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/60 border-b border-gray-200 dark:border-gray-700">
                        <th scope="col" class="px-3 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Ratee</th>
                        <th scope="col" class="px-3 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Position</th>
                        <th scope="col" class="px-3 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Priority</th>
                        <th scope="col" class="px-3 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Obs</th>
                        <th scope="col" class="px-3 py-2.5 text-right text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($teachers as $teacher)
                    @php
                        $initial = strtoupper(substr($teacher->user->name, 0, 1));
                        $obsCount = $teacher->observations_count ?? 0;
                        $level = $teacher->attention_level ?? 'ok';
                        $badgeClass = $level !== 'ok'
                            ? match($level){'high'=>'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-300 border-red-200 dark:border-red-500/20','medium'=>'bg-orange-50 dark:bg-orange-500/10 text-orange-700 dark:text-orange-300 border-orange-200 dark:border-orange-500/20',default=>'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-300 border-amber-200 dark:border-amber-500/20'}
                            : 'bg-slate-50 dark:bg-gray-800 text-slate-600 dark:text-gray-300 border-slate-200 dark:border-gray-700';
                        $badgeIcon = match($level){'high'=>'fa-triangle-exclamation','medium'=>'fa-exclamation','low'=>'fa-circle-info',default=>'fa-check'};
                        $avatarClass = match ($level) {
                            'high' => 'bg-red-100 dark:bg-red-500/10 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-500/20',
                            'medium' => 'bg-orange-100 dark:bg-orange-500/10 text-orange-700 dark:text-orange-300 border border-orange-200 dark:border-orange-500/20',
                            'low' => 'bg-amber-100 dark:bg-amber-500/10 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-500/20',
                            default => 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-500/20',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-800/40 transition-colors">
                        <td class="px-3 py-2.5">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <div class="w-7 h-7 rounded-full {{ $avatarClass }} flex items-center justify-center text-xs font-bold shrink-0" aria-hidden="true">{{ $initial }}</div>
                                <div class="min-w-0">
                                    <a href="{{ route('supervisor.teachers.show', $teacher) }}" class="block text-sm font-semibold text-gray-900 dark:text-gray-100 truncate hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">{{ $teacher->user->name }}</a>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 truncate leading-tight">{{ $teacher->user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-2.5 whitespace-nowrap">
                            <span class="text-xs font-medium text-gray-600 dark:text-gray-300">{{ $teacher->position ? ($teacher->position_label ?? $teacher->position) : '—' }}</span>
                        </td>
                        <td class="px-3 py-2.5 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold border {{ $badgeClass }}">
                                <i class="fas {{ $badgeIcon }} text-[10px]"></i> {{ $levelLabel[$level] }}
                            </span>
                        </td>
                        <td class="px-3 py-2.5 whitespace-nowrap">
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $obsCount }}</span>
                        </td>
                        <td class="px-3 py-2.5 text-right whitespace-nowrap">
                            <a href="{{ route('supervisor.teachers.show', $teacher) }}" class="inline-flex items-center px-2.5 py-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 rounded-md text-xs font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">View</a>
                            <a href="{{ route('supervisor.observations.create') }}?teacher_id={{ $teacher->id }}" class="inline-flex items-center gap-1 px-2.5 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-xs font-semibold transition-colors">Observe</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-3 py-8 text-center">
                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $attentionFilter ? 'No teachers need attention' : 'No teachers found' }}</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">@if($attentionFilter) Every teacher under your supervision is currently on track. @elseif(request('search')) No teachers match “{{ request('search') }}”. @else There are no teachers assigned to your supervision yet. @endif</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($teachers->hasPages())
        <div class="pt-2">
            {{ $teachers->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@endsection
