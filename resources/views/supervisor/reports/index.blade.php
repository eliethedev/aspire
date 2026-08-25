@extends('layouts.supervisor')

@section('title', $view === 'analytics' ? 'Analytics' : ($view === 'performance' ? 'Teacher Performance' : 'Observation Reports'))

@if(in_array($view, ['overview', 'analytics']))
    @push('styles')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @endpush
@endif

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Reports</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Observation insights across your ratees.</p>
        </div>
        <a href="{{ route('supervisor.reports.export') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17v2a2 2 0 002 2h14a2 2 0 002-2v-2M7 9l5-5 5 5"/></svg>
            Export CSV
        </a>
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
