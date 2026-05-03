@extends('layouts.app')

@section('title', 'Teacher Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Welcome Section -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Welcome back, {{ Auth::user()->name }}!</h1>
                <p class="text-slate-600 mt-1">Here is your latest supervision progress.</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-slate-500">{{ now()->format('l, F j, Y') }}</p>
            </div>
        </div>
    </div>

    <!-- Performance Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Observations -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-blue-100">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-slate-600">Total Observations</p>
                    <p class="text-2xl font-bold text-slate-900">5</p>
                </div>
            </div>
        </div>

        <!-- Latest Observation Score -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-green-100">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-slate-600">Latest Score</p>
                    <p class="text-2xl font-bold text-slate-900">3.8</p>
                </div>
            </div>
        </div>

        <!-- Growth Trend -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-purple-100">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0V15m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-slate-600">Growth Trend</p>
                    <p class="text-2xl font-bold text-slate-900">Improving</p>
                </div>
            </div>
        </div>

        <!-- Risk Level -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-lg bg-yellow-100">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-slate-600">Risk Level</p>
                    <p class="text-2xl font-bold text-slate-900">Low</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Trend Chart -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <h2 class="text-lg font-semibold text-slate-900 mb-4">Teacher Growth Trend</h2>
        <canvas id="" width="400" height="200"></canvas>
    </div>

    <!-- Recent Observation & AI Feedback -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Observation Results -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Recent Observation</h2>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-slate-600">Date:</span>
                    <span class="text-sm text-slate-900">Feb 21, 2026</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-slate-600">Supervisor:</span>
                    <span class="text-sm text-slate-900">Dr. Santos</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-slate-600">Stage:</span>
                    <span class="text-sm text-slate-900">Post Conference</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-slate-600">Overall Score:</span>
                    <span class="text-lg font-bold text-green-600">3.7</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-slate-600">Status:</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Feedback Available</span>
                </div>
            </div>
        </div>

        <!-- AI Feedback Section -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">AI Coaching Feedback</h2>
            <div class="space-y-4">
                <div>
                    <h3 class="text-sm font-medium text-green-700 mb-2">Strengths:</h3>
                    <ul class="text-sm text-slate-600 space-y-1">
                        <li>• Strong classroom engagement</li>
                        <li>• Clear lesson objectives</li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-sm font-medium text-orange-700 mb-2">Suggested Improvements:</h3>
                    <ul class="text-sm text-slate-600 space-y-1">
                        <li>• Improve questioning techniques</li>
                        <li>• Encourage more student participation</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Upcoming Supervision & Improvement Plan -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Upcoming Supervision Schedule -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Next Observation</h2>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-slate-600">Supervisor:</span>
                    <span class="text-sm text-slate-900">Mr. Reyes</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-slate-600">Date:</span>
                    <span class="text-sm text-slate-900">April 10, 2026</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-slate-600">Stage:</span>
                    <span class="text-sm text-slate-900">Pre-Conference</span>
                </div>
            </div>
        </div>

        <!-- Improvement Plan / Agreements -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">Instructional Agreements</h2>
            <div class="space-y-3">
                <div class="flex items-start space-x-3">
                    <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                    <p class="text-sm text-slate-600">Use more formative assessment strategies</p>
                </div>
                <div class="flex items-start space-x-3">
                    <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                    <p class="text-sm text-slate-600">Increase student participation activities</p>
                </div>
                <div class="flex items-start space-x-3">
                    <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                    <p class="text-sm text-slate-600">Implement differentiated instruction methods</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('growthChart').getContext('2d');
    const growthChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Observation 1', 'Observation 2', 'Observation 3', 'Observation 4', 'Observation 5'],
            datasets: [{
                label: 'COT Score',
                data: [3.2, 3.5, 3.7, 3.9, 3.8],
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: false,
                    min: 1,
                    max: 5,
                    ticks: {
                        stepSize: 0.5
                    }
                }
            }
        }
    });
</script>
@endsection