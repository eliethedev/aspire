@extends('errors.layout')

@section('content')
    @slot('title', 'Session Expired')
    @slot('heading', '419 - Session Expired')
    @slot('message', 'For your security, your session ended after a period of inactivity or after a password change. Please sign in again to continue using ASPIRE.')
    @slot('iconBg', 'bg-blue-50 border border-blue-100')
    @slot('icon')
        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
    @endslot

    @push('actions')
        <div class="space-y-3">
            <a href="{{ route('login') }}" class="btn-primary w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl font-semibold text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                </svg>
                Go to Login
            </a>

            <button type="button" onclick="window.location.reload()" class="btn-secondary w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl font-semibold text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Refresh Page
            </button>
        </div>
    @endpush
@endsection
