@extends('layouts.teacher')

@section('title', 'School Head Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8 space-y-8">
    <!-- Welcome Section -->
    <div class="relative overflow-hidden bg-gradient-to-br from-gray-900 via-gray-800 to-gray-700 rounded-2xl shadow-lg p-8">
        <div class="relative z-10">
            <h1 class="text-2xl font-bold text-white">School Head Dashboard</h1>
            <p class="text-gray-300 mt-1">Welcome back, {{ Auth::user()->name }}!</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] ?? 0 }}</p>
                    <p class="text-xs text-gray-500">Total Observations</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['scheduled'] ?? 0 }}</p>
                    <p class="text-xs text-gray-500">Scheduled</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['pending_confirmation'] ?? 0 }}</p>
                    <p class="text-xs text-gray-500">Awaiting Confirmation</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['completed'] ?? 0 }}</p>
                    <p class="text-xs text-gray-500">Completed</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Upcoming Observation Card -->
    @if($nextObservation)
    <div class="bg-gradient-to-br from-indigo-50 to-blue-50 rounded-xl border border-indigo-100 shadow-sm p-6">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-100 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            </div>
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-indigo-900">Upcoming Leadership Observation</h3>
                <p class="text-sm text-indigo-700 mt-1">
                    Scheduled for <strong>{{ $nextObservation->observation_date?->format('M d, Y \a\t h:i A') ?? 'No date' }}</strong>
                    @if($nextObservation->canConfirm())
                        &middot; <span class="text-amber-600 font-medium">Awaiting your confirmation</span>
                    @elseif($nextObservation->confirmation_status === 'confirmed')
                        &middot; <span class="text-emerald-600 font-medium">Confirmed</span>
                    @endif
                </p>
                @if($nextObservation->subject)
                <p class="text-sm text-indigo-600 mt-1">{{ $nextObservation->subject }} &middot; {{ $nextObservation->observer?->name }}</p>
                @endif
                <div class="flex items-center gap-3 mt-4">
                    <a href="{{ route('school-head.observations.show', $nextObservation) }}"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition-colors">
                        View Details
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </a>
                    <a href="{{ route('school-head.observations.index') }}"
                       class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">All Observations &rarr;</a>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 text-center">
        <div class="w-14 h-14 rounded-full bg-gray-50 flex items-center justify-center mx-auto mb-3">
            <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
        </div>
        <p class="text-gray-500">No upcoming observations scheduled.</p>
    </div>
    @endif

    <!-- Recent Observations -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">Recent Observations</h2>
            </div>
            @if(isset($recentObservations) && $recentObservations->count() > 0)
                <a href="{{ route('school-head.observations.index') }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">View All &rarr;</a>
            @endif
        </div>
        @if(isset($recentObservations) && $recentObservations->count() > 0)
            <div class="divide-y divide-gray-100">
                @foreach($recentObservations as $observation)
                <div class="flex items-center justify-between py-3 first:pt-0 last:pb-0">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-indigo-50 flex items-center justify-center">
                            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $observation->observer?->name ?? 'Unknown' }}</p>
                            <p class="text-xs text-gray-500">{{ $observation->observation_date?->format('M d, Y') ?? 'No date' }} &middot; {{ $observation->subject ?? 'Leadership Observation' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium
                            {{ $observation->status === 'completed' ? 'bg-green-100 text-green-700' : ($observation->status === 'scheduled' ? 'bg-blue-100 text-blue-700' : ($observation->status === 'cancelled' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700')) }}">
                            {{ ucwords(str_replace('_', ' ', $observation->status)) }}
                        </span>
                        <a href="{{ route('school-head.observations.show', $observation) }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">View</a>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="flex flex-col items-center py-12 text-center">
                <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-sm text-gray-400">No observations recorded yet.</p>
            </div>
        @endif
    </div>
</div>
@endsection
