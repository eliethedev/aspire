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

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <p class="text-gray-500">School head dashboard content coming soon.</p>
    </div>
</div>
@endsection
