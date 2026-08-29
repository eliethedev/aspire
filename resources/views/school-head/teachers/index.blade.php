@extends('layouts.teacher')

@section('title', 'Teachers')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6">
    <x-page-header title="Teachers" subtitle="View all teachers in your school." />

    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5 mb-6">
        <form method="GET" action="{{ route('school-head.teachers.index') }}">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Search</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" name="search" value="{{ request('search') }}"
                               class="w-full pl-9 pr-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                               placeholder="Search by name or email...">
                    </div>
                </div>
                <button type="submit"
                        class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
                    Search
                </button>
                @if(request('search'))
                    <a href="{{ route('school-head.teachers.index') }}"
                       class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:text-gray-300 transition-colors">Clear</a>
                @endif
            </div>
        </form>
    </div>

    <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Showing <span class="font-medium text-gray-700 dark:text-gray-300">{{ $teachers->firstItem() }}</span>
            to <span class="font-medium text-gray-700 dark:text-gray-300">{{ $teachers->lastItem() }}</span>
            of <span class="font-medium text-gray-700 dark:text-gray-300">{{ $teachers->total() }}</span> teachers
        </p>
    </div>

    @forelse($teachers as $teacher)
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5 mb-4 hover:shadow-md transition-shadow">
        <div class="flex items-center gap-4">
            <div class="w-11 h-11 rounded-full bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center shrink-0">
                <span class="text-indigo-600 dark:text-indigo-400 font-semibold text-sm">{{ strtoupper(substr($teacher->user->name, 0, 1)) }}</span>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ $teacher->user->name }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $teacher->user->email }}</p>
                @if($teacher->department || $teacher->subjectsLabel)
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                    {{ $teacher->department ?? '' }}{{ $teacher->department && $teacher->subjectsLabel ? ' · ' : '' }}{{ $teacher->subjectsLabel ?? '' }}
                </p>
                @endif
            </div>
            <div class="flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                <span class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    {{ $teacher->observations_count }} observations
                </span>
            </div>
            <a href="{{ route('school-head.teachers.show', $teacher) }}"
               class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors shrink-0">
                View Profile
            </a>
        </div>
    </div>
    @empty
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-12 text-center">
        <div class="w-16 h-16 rounded-full bg-gray-50 dark:bg-gray-800 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/></svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">No teachers found</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">There are no teachers assigned to your school yet.</p>
    </div>
    @endforelse

    @if($teachers->hasPages())
        <div class="mt-8">{{ $teachers->links() }}</div>
    @endif
</div>
@endsection
