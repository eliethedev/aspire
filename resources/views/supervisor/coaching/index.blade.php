@extends('layouts.supervisor')

@section('title', 'Coaching Agreements')
@include('partials.dashboard.mock-styles')

@push('styles')
<style>
    .agreement-card { transition: all 0.2s ease; }
    .agreement-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.06); }
</style>
@endpush

@section('content')
<div class="mock-wrap max-w-7xl mx-auto px-1 py-1">
    <div class="mock-topbar">
        <div class="mock-crumbs">Supervisor <span>/</span> <b>Coaching Agreements</b></div>
        <span class="mock-pill"><span class="pulse"></span>{{ $agreements->total() }} agreements</span>
        <div class="mock-actions">
            <a class="mock-btn" href="{{ route('supervisor.observations.index') }}">Browse Observations</a>
        </div>
    </div>

    <div class="mock-title">
        <div>
            <h1>Coaching Agreements</h1>
            <p>Create and manage coaching agreements for your teachers.</p>
        </div>
        <time>{{ now()->format('l, F j, Y') }}</time>
    </div>

    @if($agreements->isEmpty())
        <section class="mock-panel" style="padding:32px 16px;text-align:center" aria-label="No agreements">
            <div class="w-16 h-16 rounded-full bg-gray-50 dark:bg-gray-800 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">No Coaching Agreements Yet</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 dark:text-gray-500 mb-6">Create a coaching agreement from a completed observation's post-conference.</p>
            <a href="{{ route('supervisor.observations.index') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Browse Observations
            </a>
        </section>
    @else
        <section class="mock-panel" aria-label="Coaching agreements">
            <div class="mock-panel-head"><h2>Agreements</h2><span class="hint">{{ $agreements->total() }} total</span></div>
        <div class="space-y-4" style="padding:14px 16px">
            @foreach($agreements as $agreement)
                <div class="agreement-card bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                    <div class="p-5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $agreement->teacher?->user?->name ?? 'Teacher' }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500">{{ $agreement->observation->observation_date->format('M d, Y') }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $agreement->statusBadgeClass() }}">
                                    {{ ucfirst($agreement->status) }}
                                </span>
                                <a href="{{ route('supervisor.coaching.show', $agreement) }}"
                                   class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:bg-gray-800 rounded-lg text-xs font-medium transition-colors">
                                    View
                                </a>
                            </div>
                        </div>

                        @if($agreement->focus_areas)
                        <div class="flex flex-wrap gap-1.5 mt-3">
                            @foreach($agreement->focus_areas as $area)
                                <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 dark:text-gray-500">{{ $area }}</span>
                            @endforeach
                        </div>
                        @endif

                        <div class="flex flex-wrap items-center gap-4 mt-3 text-xs text-gray-500 dark:text-gray-400 dark:text-gray-500">
                            @if($agreement->teacher_signed_at)
                                <span class="flex items-center gap-1 text-green-600 dark:text-green-400"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 12.75l6 6 9-13.5"/></svg> Teacher Signed</span>
                            @endif
                            @if($agreement->supervisor_signed_at)
                                <span class="flex items-center gap-1 text-green-600 dark:text-green-400"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 12.75l6 6 9-13.5"/></svg> You Signed</span>
                            @endif
                            <span>{{ $agreement->created_at->format('M d, Y') }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        </section>

        <div class="mock-panel" style="padding:8px 12px">
            {{ $agreements->links() }}
        </div>
    @endif
</div>
@endsection
