@extends('errors.layout')

@section('content')
    @slot('title', 'Service Unavailable')
    @slot('heading', '503 - Maintenance')
    @slot('message', 'ASPIRE is temporarily undergoing maintenance. Please try again shortly.')
    @slot('iconBg', 'bg-purple-50 dark:bg-purple-500/10 border border-purple-100 dark:border-purple-500/20')
    @slot('icon')
        <svg class="w-10 h-10 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
    @endslot

    @push('actions')
        <div class="space-y-3">
            <button type="button" onclick="window.location.reload()" class="btn-primary w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl font-semibold text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Try Again
            </button>

            <a href="{{ route('home') }}" class="btn-secondary w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl font-semibold text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Go to Homepage
            </a>
        </div>
    @endpush
@endsection
