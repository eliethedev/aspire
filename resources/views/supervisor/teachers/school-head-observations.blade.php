@extends('layouts.supervisor')

@section('title', 'Observations - ' . $schoolHead->user->name)
@include('partials.dashboard.mock-styles')

@section('content')
<div class="mock-wrap max-w-7xl mx-auto px-1 py-1">
    <div class="mock-topbar">
        <div class="mock-crumbs">Supervisor <span>/</span> <b>School Head Observations</b></div>
        <div class="mock-actions">
            <a class="mock-btn" href="{{ route('supervisor.school-heads.index') }}">← Back to School Heads</a>
            <a class="mock-btn primary" href="{{ route('supervisor.observations.create') }}?school_head={{ $schoolHead->id }}">＋ New Observation</a>
        </div>
    </div>

    <div class="mock-title">
        <div>
            <h1>{{ $schoolHead->user->name }}</h1>
            <p>Observation history.</p>
        </div>
        <time>{{ $observations->total() }} observations</time>
    </div>

    <section class="mock-panel" aria-label="Observations">
        <div class="mock-panel-head"><h2>Observations</h2><span class="hint">{{ $observations->total() }} total</span></div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5" style="padding:14px 16px">
    @forelse($observations as $obs)
        <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-3 flex flex-col gap-2 min-h-[140px] h-full">
            <div class="flex items-start justify-between gap-2 flex-1">
                <div class="flex gap-2.5 min-w-0 flex-1">
                    <div class="w-8 h-8 rounded-md bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 flex items-center justify-center text-sm font-bold shrink-0">{{ strtoupper(substr($obs->observer->name ?? '?', 0, 1)) }}</div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate leading-tight">Observed by {{ Str::limit($obs->observer->name ?? 'Unknown', 18) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-none mt-0.5 truncate">
                            {{ $obs->observation_date ? \Carbon\Carbon::parse($obs->observation_date)->format('M d, Y') : 'No date' }}
                            @if($obs->observation_time) · {{ \Carbon\Carbon::parse($obs->observation_time)->format('h:i A') }} @endif
                        </p>
                        <div class="flex flex-wrap items-center gap-1 mt-1">
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium border
                                @switch($obs->status)
                                    @case('scheduled') bg-blue-50 text-blue-700 border-blue-200 @break
                                    @case('in_progress') bg-yellow-50 text-yellow-700 border-yellow-200 @break
                                    @case('completed') bg-green-50 text-green-700 border-green-200 @break
                                    @case('cancelled') bg-red-50 text-red-700 border-red-200 @break
                                    @default bg-gray-50 text-gray-600 border-gray-200
                                @endswitch">{{ str_replace('_',' ',ucfirst($obs->status)) }}</span>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium border
                                @switch($obs->stage)
                                    @case('pre_observation_planning') bg-purple-50 text-purple-700 border-purple-200 @break
                                    @case('pre_conference') bg-indigo-50 text-indigo-700 border-indigo-200 @break
                                    @case('observation') bg-orange-50 text-orange-700 border-orange-200 @break
                                    @case('post_conference') bg-teal-50 text-teal-700 border-teal-200 @break
                                    @default bg-gray-50 text-gray-500 border-gray-200
                                @endswitch">{{ Str::limit(str_replace('_',' ',ucfirst($obs->stage)),12) }}</span>
                            @if($obs->confirmation_status === 'pending' && $obs->stage === 'pre_observation_planning')
                                <span class="inline-flex px-1.5 py-0.5 rounded text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">Awaiting</span>
                            @elseif($obs->confirmation_status === 'confirmed')
                                <span class="inline-flex px-1.5 py-0.5 rounded text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">Confirmed</span>
                            @elseif($obs->confirmation_status === 'rejected')
                                <span class="inline-flex px-1.5 py-0.5 rounded text-xs font-medium bg-red-50 text-red-700 border border-red-200">Rejected</span>
                            @endif
                        </div>
                    </div>
                </div>
                <a href="{{ route('supervisor.observations.show', $obs) }}" class="shrink-0 inline-flex items-center gap-1 px-2.5 py-1.5 text-sm font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/20 rounded-md">View <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></a>
            </div>
        </div>
    @empty
        <div class="col-span-full bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-6 text-center">
            <div class="w-10 h-10 rounded-lg bg-gray-50 dark:bg-gray-800 flex items-center justify-center mx-auto mb-2">
                <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">No observations yet</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400">Not observed yet.</p>
            <a href="{{ route('supervisor.observations.create') }}?school_head={{ $schoolHead->id }}" class="inline-flex gap-1 mt-3 px-3 py-1.5 bg-indigo-600 text-white rounded-md text-xs font-semibold">Schedule First</a>
        </div>
    @endforelse
    </div>
    </section>

    @if($observations->hasPages())
        <div class="mock-panel" style="padding:8px 12px">
            {{ $observations->links() }}
        </div>
    @endif
</div>
@endsection
