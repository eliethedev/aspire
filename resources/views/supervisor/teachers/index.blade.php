@extends('layouts.supervisor')

@section('title', 'Teachers List')

@push('styles')
<style>
    .teacher-card {
        transition: all 0.2s ease;
    }
    .teacher-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06);
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-dark-900">Teachers List</h1>
            <p class="text-dark-500 mt-1">View and manage teachers under your supervision.</p>
        </div>
        <a href="{{ route('supervisor.observations.create') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            New Observation
        </a>
    </div>

    <!-- Search & Filter -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 mb-6">
        <form method="GET" action="{{ route('supervisor.teachers.index') }}">
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
                    <a href="{{ route('supervisor.teachers.index') }}"
                       class="px-4 py-2 text-sm text-dark-500 hover:text-dark-700 transition-colors">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Results Summary -->
    <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-dark-500">
            Showing <span class="font-medium text-dark-700">{{ $teachers->firstItem() }}</span>
            to <span class="font-medium text-dark-700">{{ $teachers->lastItem() }}</span>
            of <span class="font-medium text-dark-700">{{ $teachers->total() }}</span> teachers
        </p>
    </div>

    <!-- Teachers Grid -->
    @forelse($teachers as $teacher)
        @php
            $initial = strtoupper(substr($teacher->user->name, 0, 1));
            $obsCount = $teacher->observations_count ?? 0;
        @endphp
        <div class="teacher-card bg-white rounded-xl border border-gray-100 shadow-sm p-5 mb-4">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                <!-- Avatar + Info -->
                <div class="flex items-center gap-3 min-w-0 flex-1">
                    <div class="w-11 h-11 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-base font-bold shrink-0">
                        {{ $initial }}
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <a href="{{ route('supervisor.teachers.show', $teacher) }}" class="font-semibold text-dark-900 truncate hover:text-indigo-600 transition-colors">{{ $teacher->user->name }}</a>
                            @if($teacher->position)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-gray-100 text-gray-600">
                                    {{ $teacher->position }}
                                </span>
                            @endif
                        </div>
                        <p class="text-sm text-dark-500 truncate">{{ $teacher->user->email }}</p>
                    </div>
                </div>

                <!-- Details -->
                <div class="flex items-center gap-6 text-sm text-dark-500 shrink-0 flex-wrap">
                    @if($teacher->school)
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            <span>{{ $teacher->school->name }}</span>
                        </div>
                    @endif
                    @if($teacher->subject)
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            <span>{{ $teacher->subject }}</span>
                        </div>
                    @endif
                    @if($teacher->grade_level)
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                            <span>Grade {{ $teacher->grade_level }}</span>
                        </div>
                    @endif
                </div>

                <!-- Observation Count + Actions -->
                <div class="flex items-center gap-3 shrink-0">
                    <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg {{ $obsCount > 0 ? 'bg-indigo-50 text-indigo-700' : 'bg-gray-50 text-gray-400' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <span class="text-sm font-medium">{{ $obsCount }}</span>
                    </div>
                    <a href="{{ route('supervisor.observations.create') }}?teacher_id={{ $teacher->id }}"
                       class="px-4 py-1.5 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
                        Observe
                    </a>
                </div>
            </div>
        </div>
    @empty
        <!-- Empty State -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-12 text-center">
            <div class="w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-dark-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>
            </div>
            <h3 class="text-lg font-semibold text-dark-900 mb-1">No teachers found</h3>
            <p class="text-sm text-dark-500">
                @if(request('search'))
                    No teachers match your search criteria. Try a different search term.
                @else
                    There are no teachers assigned to your supervision yet.
                @endif
            </p>
        </div>
    @endforelse

    <!-- Pagination -->
    @if($teachers->hasPages())
        <div class="mt-8">
            {{ $teachers->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@endsection
