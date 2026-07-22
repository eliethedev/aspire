@extends('layouts.supervisor')

@section('title', 'Cancel Observation')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6">
    <div class="mb-8">
        <a href="{{ route('supervisor.observations.show', $observation) }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:text-gray-300 transition-colors mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Observation
        </a>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Cancel Observation</h1>
        <p class="text-gray-500 dark:text-gray-400 dark:text-gray-500 mt-1">This action will cancel the observation for {{ $observation->observee?->user?->name ?? 'Unknown' }}.</p>
    </div>

    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4 mb-6">
        <div class="flex gap-3">
            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-1.962-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            <div>
                <p class="text-sm font-medium text-amber-800 dark:text-amber-300">Are you sure you want to cancel this observation?</p>
                <p class="text-sm text-amber-700 dark:text-amber-400 mt-1">This will notify the observee and cannot be undone. A cancellation log will be recorded for DepEd compliance.</p>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Observation Details</h2>
        <dl class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="text-gray-500 dark:text-gray-400 dark:text-gray-500">Observee</dt>
                <dd class="font-medium text-gray-900 dark:text-gray-100 mt-0.5">{{ $observation->observee?->user?->name ?? 'Unknown' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400 dark:text-gray-500">Type</dt>
                <dd class="font-medium text-gray-900 dark:text-gray-100 mt-0.5 capitalize">{{ str_replace('_', ' ', $observation->observation_type) }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400 dark:text-gray-500">Current Stage</dt>
                <dd class="font-medium text-gray-900 dark:text-gray-100 mt-0.5 capitalize">{{ str_replace('_', ' ', $observation->stage) }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400 dark:text-gray-500">Current Status</dt>
                <dd class="font-medium text-gray-900 dark:text-gray-100 mt-0.5">{{ ucwords(str_replace('_', ' ', $observation->status)) }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400 dark:text-gray-500">Observation Date</dt>
                <dd class="font-medium text-gray-900 dark:text-gray-100 mt-0.5">{{ $observation->observation_date?->format('M d, Y') ?? 'No date' }}</dd>
            </div>
            @if($observation->subject)
            <div>
                <dt class="text-gray-500 dark:text-gray-400 dark:text-gray-500">Subject</dt>
                <dd class="font-medium text-gray-900 dark:text-gray-100 mt-0.5">{{ $observation->subject }}</dd>
            </div>
            @endif
        </dl>
    </div>

    <form method="POST" action="{{ route('supervisor.observations.cancel', $observation) }}" class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6"
          x-data="{ submitting: false }" x-on:submit="submitting = true">
        @csrf

        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-3">Reason for Cancellation <span class="text-red-500 dark:text-red-400">*</span></label>
            <div class="space-y-2.5" x-data="{ reason: '' }">
                @php
                    $reasons = [
                        'teacher_request' => 'Teacher Requested Cancellation',
                        'supervisor_initiative' => 'Supervisor Initiative',
                        'conflict_in_schedule' => 'Conflict in Schedule',
                        'health_reason' => 'Health Reason',
                        'insufficient_documentation' => 'Insufficient Documentation',
                        'technical_issues' => 'Technical Issues',
                        'weather_emergency' => 'Weather / Emergency',
                        'other' => 'Other',
                    ];
                @endphp
                @foreach($reasons as $value => $label)
                <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 cursor-pointer hover:border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 transition-colors has-[:checked]:border-red-300 has-[:checked]:bg-red-50 dark:bg-red-900/20">
                    <input type="radio" name="cancellation_reason" value="{{ $value }}"
                           class="w-4 h-4 text-red-600 dark:text-red-400 border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 focus:ring-red-500"
                           x-on:change="reason = '{{ $value }}'" {{ old('cancellation_reason') === $value ? 'checked' : '' }} required>
                    <span class="text-sm text-gray-900 dark:text-gray-100">{{ $label }}</span>
                </label>
                @endforeach

                <div x-show="reason === 'other'" x-cloak class="ml-7 mt-2">
                    <input type="text" name="cancellation_other_reason" value="{{ old('cancellation_other_reason') }}"
                           class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none"
                           placeholder="Please specify the reason...">
                </div>
            </div>
            @error('cancellation_reason')
                <p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-6">
            <label for="internal_note" class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-1.5">Internal Note (Optional)</label>
            <textarea name="internal_note" id="internal_note" rows="3"
                      class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                      placeholder="Any internal notes for audit trail...">{{ old('internal_note') }}</textarea>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">This note is for internal purposes only and will be stored in the audit log.</p>
            @error('internal_note')
                <p class="text-sm text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-700">
            <a href="{{ route('supervisor.observations.show', $observation) }}"
               class="px-5 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:text-gray-100 transition-colors">
                Go Back
            </a>
            <button type="submit" :disabled="submitting"
                    :class="submitting ? 'opacity-60 cursor-not-allowed' : ''"
                    class="px-5 py-2.5 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition-colors"
                    onclick="return confirm('Are you sure you want to cancel this observation? This action will notify the observee and cannot be undone.')">
                <span x-show="!submitting">Confirm Cancellation</span>
                <span x-show="submitting" class="flex items-center gap-2">
                    <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Processing...
                </span>
            </button>
        </div>
    </form>
</div>
@endsection