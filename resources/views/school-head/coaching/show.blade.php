@extends('layouts.teacher')

@section('title', 'Coaching Agreement')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6">
    <div class="mb-8">
        <a href="{{ route('school-head.coaching.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:text-gray-300 transition-colors mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Coaching Agreements
        </a>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Coaching Agreement</h1>
                    <p class="text-gray-500 dark:text-gray-400">{{ $agreement->teacher->user->name }} &middot; {{ $agreement->created_at->format('M d, Y') }}</p>
                </div>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                {{ $agreement->status === 'completed' ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400' : ($agreement->status === 'active' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400' : 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300') }}">
                {{ ucfirst($agreement->status) }}
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
            <div>
                <p class="text-xs text-gray-400 dark:text-gray-500 uppercase tracking-wider font-medium">Teacher</p>
                <p class="text-sm text-gray-900 dark:text-gray-100 mt-1">{{ $agreement->teacher->user->name }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 dark:text-gray-500 uppercase tracking-wider font-medium">Supervisor</p>
                <p class="text-sm text-gray-900 dark:text-gray-100 mt-1">{{ $agreement->supervisor?->name ?? 'N/A' }}</p>
            </div>
            @if($agreement->timeline)
            <div>
                <p class="text-xs text-gray-400 dark:text-gray-500 uppercase tracking-wider font-medium">Timeline</p>
                <p class="text-sm text-gray-900 dark:text-gray-100 mt-1">{{ $agreement->timeline }}</p>
            </div>
            @endif
            @if($agreement->observation)
            <div>
                <p class="text-xs text-gray-400 dark:text-gray-500 uppercase tracking-wider font-medium">Related Observation</p>
                <p class="text-sm text-gray-900 dark:text-gray-100 mt-1">{{ $agreement->observation->subject ?? 'Observation #' . $agreement->observation_id }}</p>
            </div>
            @endif
        </div>

        @if($agreement->focus_areas)
        <div class="border-t border-gray-100 dark:border-gray-700 pt-6 mb-6">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Focus Areas</h3>
            <div class="flex flex-wrap gap-2">
                @foreach(is_array($agreement->focus_areas) ? $agreement->focus_areas : [$agreement->focus_areas] as $area)
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800">{{ $area }}</span>
                @endforeach
            </div>
        </div>
        @endif

        @if($agreement->action_steps)
        <div class="border-t border-gray-100 dark:border-gray-700 pt-6 mb-6">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Action Steps</h3>
            <ul class="space-y-2">
                @foreach(is_array($agreement->action_steps) ? $agreement->action_steps : [$agreement->action_steps] as $step)
                <li class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <svg class="w-4 h-4 text-indigo-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    {{ $step }}
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        @if($agreement->resources_needed)
        <div class="border-t border-gray-100 dark:border-gray-700 pt-6 mb-6">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Resources Needed</h3>
            <p class="text-sm text-gray-700 dark:text-gray-300">{{ $agreement->resources_needed }}</p>
        </div>
        @endif

        @if($agreement->success_indicators)
        <div class="border-t border-gray-100 dark:border-gray-700 pt-6 mb-6">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Success Indicators</h3>
            <p class="text-sm text-gray-700 dark:text-gray-300">{{ $agreement->success_indicators }}</p>
        </div>
        @endif

        @if($agreement->supervisor_notes || $agreement->teacher_notes)
        <div class="border-t border-gray-100 dark:border-gray-700 pt-6 grid grid-cols-1 sm:grid-cols-2 gap-6">
            @if($agreement->supervisor_notes)
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Supervisor Notes</h3>
                <p class="text-sm text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-800 rounded-lg p-3">{{ $agreement->supervisor_notes }}</p>
            </div>
            @endif
            @if($agreement->teacher_notes)
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Teacher Notes</h3>
                <p class="text-sm text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-800 rounded-lg p-3">{{ $agreement->teacher_notes }}</p>
            </div>
            @endif
        </div>
        @endif

        @if($agreement->teacher_signed_at || $agreement->supervisor_signed_at)
        <div class="border-t border-gray-100 dark:border-gray-700 pt-6 mt-6 grid grid-cols-1 sm:grid-cols-2 gap-6">
            @if($agreement->teacher_signed_at)
            <div class="flex items-center gap-2 text-sm text-green-600 dark:text-green-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Signed by Teacher on {{ \Carbon\Carbon::parse($agreement->teacher_signed_at)->format('M d, Y') }}
            </div>
            @endif
            @if($agreement->supervisor_signed_at)
            <div class="flex items-center gap-2 text-sm text-green-600 dark:text-green-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Signed by Supervisor on {{ \Carbon\Carbon::parse($agreement->supervisor_signed_at)->format('M d, Y') }}
            </div>
            @endif
        </div>
        @endif
    </div>
</div>
@endsection
