@extends('layouts.supervisor')

@section('title', 'Supervisor Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-6 space-y-8">
    <!-- Welcome Section -->
    <div class="bg-white rounded-xl shadow-sm glass-card p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-dark">Welcome back, {{ Auth::user()->name }}!</h1>
                <p class="text-dark mt-1">Here is your supervision overview.</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-dark/60">{{ now()->format('l, F j, Y') }}</p>
            </div>
        </div>
    </div>

    <!-- Performance Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Teachers -->
        <div class="bg-white rounded-xl shadow-sm glass-card p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-blue-500/20">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-dark/80">Total Teachers</p>
                    <p class="text-2xl font-bold text-dark">{{ $stats['total_teachers'] }}</p>
                </div>
            </div>
        </div>

        <!-- Pending Observations -->
        <div class="bg-white rounded-xl shadow-sm glass-card p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-yellow-500/20">
                    <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-dark/80">Pending Observations</p>
                    <p class="text-2xl font-bold text-dark">{{ $stats['pending_observations'] }}</p>
                </div>
            </div>
        </div>

        <!-- Completed Observations -->
        <div class="bg-white rounded-xl shadow-sm glass-card p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-green-500/20">
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-dark/80">Completed Observations</p>
                    <p class="text-2xl font-bold text-dark">{{ $stats['completed_observations'] }}</p>
                </div>
            </div>
        </div>

        <!-- Average Score -->
        <div class="bg-white rounded-xl shadow-sm glass-card p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-purple-500/20">
                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-dark/80">Average COT Score</p>
                    <p class="text-2xl font-bold text-dark">{{ number_format($stats['average_score'], 1) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-xl shadow-sm glass-card p-6">
        <h2 class="text-lg font-semibold text-dark mb-4">Quick Actions</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('supervisor.observations.create') }}" class="flex items-center p-4 rounded-lg bg-blue-500/10 hover:bg-blue-500/20 transition-colors">
                <svg class="w-6 h-6 text-blue-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                <span class="text-dark font-medium">New Observation</span>
            </a>
            <a href="{{ route('supervisor.teachers.index') }}" class="flex items-center p-4 rounded-lg bg-green-500/10 hover:bg-green-500/20 transition-colors">
                <svg class="w-6 h-6 text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span class="text-dark font-medium">View Teachers</span>
            </a>
            <a href="{{ route('supervisor.reports.index') }}" class="flex items-center p-4 rounded-lg bg-purple-500/10 hover:bg-purple-500/20 transition-colors">
                <svg class="w-6 h-6 text-purple-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span class="text-dark font-medium">View Reports</span>
            </a>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="bg-white rounded-xl shadow-sm glass-card p-6">
        <h2 class="text-lg font-semibold text-dark mb-4">Recent Activity</h2>
        @if($recentObservations->count() > 0)
            <div class="space-y-4">
                @foreach($recentObservations as $observation)
                    <div class="flex items-center justify-between p-4 rounded-lg bg-white/5">
                        <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 bg-indigo-500/20 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-dark font-medium">{{ $observation->teacher->user->name }}</p>
                                <p class="text-dark/60 text-sm">{{ $observation->observation_date->format('M d, Y') }} - {{ $observation->status }}</p>
                            </div>
                        </div>
                        <a href="{{ route('supervisor.observations.show', $observation) }}" class="text-indigo-400 hover:text-indigo-300 text-sm">
                            View Details
                        </a>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <p class="text-dark/60">No recent activity to display.</p>
            </div>
        @endif
    </div>
</div>
@endsection
