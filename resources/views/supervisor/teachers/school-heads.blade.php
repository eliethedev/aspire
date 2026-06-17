@extends('layouts.supervisor')

@section('title', 'School Heads List')

@push('styles')
<style>
    .teacher-card { transition: all 0.2s ease; }
    .teacher-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06); }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-dark-900">School Heads</h1>
            <p class="text-dark-500 mt-1">View school heads and schedule leadership observations.</p>
        </div>
        <a href="{{ route('supervisor.observations.create') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            New Observation
        </a>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 mb-6">
        <form method="GET" action="{{ route('supervisor.school-heads.index') }}">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-medium text-dark-500 mb-1.5">Search</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-dark-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" name="search" value="{{ request('search') }}"
                               class="w-full pl-9 pr-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                               placeholder="Search by name or email...">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-dark-500 mb-1.5">Per Page</label>
                    <select name="per_page" onchange="this.form.submit()"
                            class="px-3 py-2 rounded-lg border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
                        <option value="15" {{ request('per_page') == 15 ? 'selected' : '' }}>15</option>
                        <option value="30" {{ request('per_page') == 30 ? 'selected' : '' }}>30</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    </select>
                </div>
                <button type="submit"
                        class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
                    Search
                </button>
                @if(request('search'))
                    <a href="{{ route('supervisor.school-heads.index') }}"
                       class="px-4 py-2 text-sm text-dark-500 hover:text-dark-700 transition-colors">Clear</a>
                @endif
            </div>
        </form>
    </div>

    <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-dark-500">
            Showing <span class="font-medium text-dark-700">{{ $schoolHeads->firstItem() }}</span>
            to <span class="font-medium text-dark-700">{{ $schoolHeads->lastItem() }}</span>
            of <span class="font-medium text-dark-700">{{ $schoolHeads->total() }}</span> school heads
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($schoolHeads as $schoolHead)
            @php
                $initial = strtoupper(substr($schoolHead->user->name, 0, 1));
                $avatarColors = ['bg-indigo-500', 'bg-emerald-500', 'bg-blue-500', 'bg-violet-500', 'bg-rose-500', 'bg-amber-500', 'bg-cyan-500', 'bg-pink-500'];
                $avatarColor = $avatarColors[crc32($schoolHead->user->email) % count($avatarColors)];
            @endphp
            <div class="teacher-card bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full {{ $avatarColor }} flex items-center justify-center text-white text-lg font-bold shrink-0">
                        {{ $initial }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-dark-900 truncate">{{ $schoolHead->user->name }}</p>
                        <p class="text-xs text-dark-500 truncate">{{ $schoolHead->user->email }}</p>
                        @if($schoolHead->current_designation)
                            <p class="text-xs text-dark-400 mt-0.5">{{ $schoolHead->current_designation_label }}</p>
                        @endif
                    </div>
                </div>
                @if($schoolHead->school)
                <div class="mt-3 flex items-center gap-1.5 text-xs text-dark-500">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    {{ $schoolHead->school->name }}
                </div>
                @endif
                <div class="mt-4 flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[11px] font-medium bg-indigo-50 text-indigo-700">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        {{ $schoolHead->total_observations ?? 0 }} obs
                    </span>
                    @if($schoolHead->position_level)
                    <span class="inline-flex items-center px-2 py-1 rounded-md text-[11px] font-medium bg-gray-50 text-dark-500">
                        {{ $schoolHead->position_level_label }}
                    </span>
                    @endif
                </div>
                <div class="mt-4 flex items-center gap-2">
                    <a href="{{ route('supervisor.observations.create', ['school_head' => $schoolHead->id]) }}"
                       class="flex-1 text-center px-3 py-2 bg-indigo-600 text-white rounded-lg text-xs font-medium hover:bg-indigo-700 transition-colors">
                        Schedule Observation
                    </a>
                    <a href="{{ route('supervisor.school-heads.observations', $schoolHead) }}"
                       class="flex-1 text-center px-3 py-2 border border-gray-300 text-dark-700 rounded-lg text-xs font-medium hover:bg-gray-50 transition-colors">
                        View History
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-xl border border-gray-100 shadow-sm p-12 text-center">
                <div class="w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <h3 class="text-lg font-semibold text-dark-900 mb-1">No school heads found</h3>
                <p class="text-sm text-dark-500">There are no school heads registered in the system yet.</p>
            </div>
        @endforelse
    </div>

    @if($schoolHeads->hasPages())
        <div class="mt-8">
            {{ $schoolHeads->links() }}
        </div>
    @endif
</div>
@endsection
