@extends('layouts.teacher')

@section('title', 'Improvement Plan')

@push('styles')
<style>
    .agreement-card { transition: all 0.2s ease; }
    .agreement-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.06); }
    .dark .agreement-card:hover { box-shadow: 0 8px 25px rgba(0,0,0,0.3); }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Improvement Plan</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Coaching agreements and action plans from your observations.</p>
        </div>
    </div>

    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
             class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 rounded-xl p-4 flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
    @endif

    @if($agreements->isEmpty())
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-12 text-center">
            <div class="w-16 h-16 rounded-full bg-gray-50 dark:bg-gray-800 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">No Improvement Plans Yet</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Coaching agreements from your supervisor will appear here once created.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($agreements as $agreement)
                @php $obs = $agreement->observation; @endphp
                <div class="agreement-card bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
                    <div class="p-5">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">Coaching Agreement</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $obs->observation_date->format('M d, Y') }} &middot; {{ $agreement->supervisor?->name ?? 'Supervisor' }}</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $agreement->statusBadgeClass() }}">
                                {{ ucfirst($agreement->status) }}
                            </span>
                        </div>

                        @if($agreement->focus_areas)
                        <div class="flex flex-wrap gap-1.5 mb-3">
                            @foreach($agreement->focus_areas as $area)
                                <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">{{ $area }}</span>
                            @endforeach
                        </div>
                        @endif

                        <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                            @if($agreement->teacher_signed_at)
                                <span class="flex items-center gap-1 text-green-600 dark:text-green-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                    You Signed
                                </span>
                            @else
                                <span class="flex items-center gap-1 text-amber-600 dark:text-amber-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01"/></svg>
                                    Awaiting Your Signature
                                </span>
                            @endif
                            @if($agreement->supervisor_signed_at)
                                <span class="flex items-center gap-1 text-green-600 dark:text-green-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                    Supervisor Signed
                                </span>
                            @endif
                        </div>

                        <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                            <a href="{{ route('teacher.coaching.show', $agreement) }}"
                               class="inline-flex items-center gap-1 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300">
                                View Agreement
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $agreements->links() }}
        </div>
    @endif
</div>
@endsection
