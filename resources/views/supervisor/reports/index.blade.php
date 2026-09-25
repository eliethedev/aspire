@extends('layouts.supervisor')

@section('title', $view === 'analytics' ? 'Analytics' : ($view === 'performance' ? 'Teacher Performance' : 'Observation Reports'))
@include('partials.dashboard.mock-styles')

@if(in_array($view, ['overview', 'analytics']))
    @push('styles')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @endpush
@endif

@section('content')
<div class="mock-wrap max-w-7xl mx-auto px-1 py-1">
    <div class="mock-topbar">
        <div class="mock-crumbs">Supervisor <span>/</span> <b>Reports</b></div>
        <span class="mock-pill"><span class="pulse"></span>{{ $view === 'analytics' ? 'Analytics' : ($view === 'performance' ? 'Teacher Performance' : 'Observation Reports') }}</span>
        <div class="mock-actions">
            <a class="mock-btn primary" href="{{ route('supervisor.reports.export') }}">⭳ Export CSV</a>
        </div>
    </div>

    <div class="mock-title">
        <div>
            <h1>Reports</h1>
            <p>Observation insights across your ratees.</p>
        </div>
        <time>{{ now()->format('l, F j, Y') }}</time>
    </div>

    <!-- Tab navigation -->
    <nav class="flex flex-wrap items-center gap-1 p-1 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm mb-6 w-fit max-w-full overflow-x-auto">
        <a href="{{ route('supervisor.reports.index') }}"
           class="px-4 py-2 rounded-lg text-sm font-semibold whitespace-nowrap transition-colors {{ $view === 'overview' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-200' }}">
            Observation Reports
        </a>
        <a href="{{ route('supervisor.reports.index', ['view' => 'analytics']) }}"
           class="px-4 py-2 rounded-lg text-sm font-semibold whitespace-nowrap transition-colors {{ $view === 'analytics' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-200' }}">
            Analytics
        </a>
        <a href="{{ route('supervisor.reports.index', ['view' => 'performance']) }}"
           class="px-4 py-2 rounded-lg text-sm font-semibold whitespace-nowrap transition-colors {{ $view === 'performance' ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-200' }}">
            Teacher Performance
        </a>
    </nav>

    @include('supervisor.reports.partials.' . $view)
</div>
@endsection
