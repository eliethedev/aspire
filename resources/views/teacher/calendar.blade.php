@extends('layouts.teacher')

@section('title', 'Calendar')

@include('partials.dashboard.mock-styles')

@section('content')
<div class="mock-wrap max-w-7xl mx-auto px-1 py-1">
    <div class="mock-topbar">
        <div class="mock-crumbs">Teacher <span>/</span> <b>Calendar</b></div>
        <span class="mock-pill"><span class="pulse"></span>Observation calendar</span>
    </div>

    <div class="mock-title">
        <div>
            <h1>Calendar</h1>
            <p>Your scheduled observations, conferences, and completed activities</p>
        </div>
        <time>{{ now()->format('l, F j, Y') }}</time>
    </div>

    @include('partials.calendar', [
        'intro' => 'See your scheduled observations, conferences, and completed activities at a glance.',
        'scheduleRoute' => null,
        'scheduleLabel' => null,
    ])
</div>
@endsection