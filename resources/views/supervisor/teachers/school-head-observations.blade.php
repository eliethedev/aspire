@extends('layouts.supervisor')

@section('title', 'Observations - ' . $schoolHead->user->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <a href="{{ route('supervisor.school-heads.index') }}" class="inline-flex items-center gap-1.5 text-sm text-indigo-500 hover:text-indigo-600 dark:text-indigo-400 mb-2 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to School Heads
            </a>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $schoolHead->user->name }}</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Observation history for this school head.</p>
        </div>
        <a href="{{ route('supervisor.observations.create') }}?school_head={{ $schoolHead->id }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            New Observation
        </a>
    </div>

    @forelse($observations as $obs)
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-5 mb-4">
            <div class="flex items-start justify-between">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 flex items-center justify-center text-sm font-bold shrink-0 mt-0.5">
                        {{ strtoupper(substr($obs->observer->name ?? '?', 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                            Observed by {{ $obs->observer->name ?? 'Unknown' }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            {{ $obs->observation_date ? \Carbon\Carbon::parse($obs->observation_date)->format('M d, Y') : 'No date set' }}
                            @if($obs->observation_time)
                                &middot; {{ \Carbon\Carbon::parse($obs->observation_time)->format('h:i A') }}
                            @endif
                        </p>
                        <div class="flex flex-wrap items-center gap-2 mt-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium
                                @switch($obs->status)
                                    @case('scheduled') bg-blue-50 text-blue-700 @break
                                    @case('in_progress') bg-yellow-50 text-yellow-700 @break
                                    @case('completed') bg-green-50 dark:bg-green-900/20 text-green-700 @break
                                    @case('cancelled') bg-red-50 dark:bg-red-900/20 text-red-700 @break
                                    @default bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-400
                                @endswitch">
                                {{ str_replace('_', ' ', ucfirst($obs->status)) }}
                            </span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium
                                @switch($obs->stage)
                                    @case('pre_observation_planning') bg-purple-50 dark:bg-purple-900/20 text-purple-700 @break
                                    @case('pre_conference') bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 @break
                                    @case('observation') bg-orange-50 text-orange-700 @break
                                    @case('post_conference') bg-teal-50 text-teal-700 @break
                                    @default bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-400
                                @endswitch">
                                {{ str_replace('_', ' ', ucfirst($obs->stage)) }}
                            </span>
                            @if($obs->confirmation_status === 'pending' && $obs->stage === 'pre_observation_planning')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-amber-50 dark:bg-amber-900/20 text-amber-700">
                                    Awaiting Confirmation
                                </span>
                            @elseif($obs->confirmation_status === 'confirmed')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-emerald-50 text-emerald-700">
                                    Confirmed
                                </span>
                            @elseif($obs->confirmation_status === 'rejected')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-red-50 dark:bg-red-900/20 text-red-700">
                                    Rejected
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <a href="{{ route('supervisor.observations.show', $obs) }}"
                   class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 bg-indigo-50 dark:bg-indigo-900/20 hover:bg-indigo-100 dark:bg-indigo-900/30 rounded-lg transition-colors">
                    View Details
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    @empty
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-12 text-center">
            <div class="w-16 h-16 rounded-full bg-gray-50 dark:bg-gray-800 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">No observations yet</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">This school head has not been observed yet.</p>
            <a href="{{ route('supervisor.observations.create') }}?school_head={{ $schoolHead->id }}"
               class="inline-flex items-center gap-2 mt-4 px-5 py-2.5 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Schedule First Observation
            </a>
        </div>
    @endforelse

    @if($observations->hasPages())
        <div class="mt-8">
            {{ $observations->links() }}
        </div>
    @endif
</div>
@endsection
