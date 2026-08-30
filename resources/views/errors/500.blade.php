@extends('errors.layout')

@section('content')
    @slot('title', 'Something Went Wrong')
    @slot('heading', 'Server Error')
    @slot('message', 'We couldn\'t complete your request right now. Please try again in a moment. If the problem continues, contact your administrator.')
    @slot('iconBg', 'bg-red-50 dark:bg-red-500/10 border border-red-100 dark:border-red-500/20')
    @slot('icon')
        <svg class="w-10 h-10 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
    @endslot

    @push('actions')
        <div class="space-y-3">
            <button type="button" onclick="window.history.back()" class="btn-primary w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl font-semibold text-sm">
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
