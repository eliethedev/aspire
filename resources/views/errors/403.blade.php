@extends('errors.layout')

@section('content')
    @slot('title', 'Access Denied')
    @slot('heading', '403 - Forbidden')
    @slot('message', 'You don\'t have permission to view this page. If you believe this is a mistake, please contact your administrator.')
    @slot('iconBg', 'bg-amber-50 border border-amber-100')
    @slot('icon')
        <svg class="w-10 h-10 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
    @endslot

    @push('actions')
        <div class="space-y-3">
            <a href="{{ route('home') }}" class="btn-primary w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl font-semibold text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Go to Homepage
            </a>

            <button type="button" onclick="window.history.back()" class="btn-secondary w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl font-semibold text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Go Back
            </button>
        </div>
    @endpush
@endsection
