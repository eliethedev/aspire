@extends('layouts.supervisor')

@section('title', 'Calendar')
@include('partials.dashboard.mock-styles')

@section('content')
<div class="mock-wrap max-w-7xl mx-auto px-1 py-1">
    <div class="mock-topbar">
        <div class="mock-crumbs">Supervisor <span>/</span> <b>Calendar</b></div>
        <div class="mock-actions">
            <a class="mock-btn primary" href="{{ route('supervisor.observations.create') }}">＋ Schedule Observation</a>
        </div>
    </div>
    @include('partials.calendar', [
        'intro' => 'Track your scheduled observations and conferences across your schools.',
        'scheduleRoute' => route('supervisor.observations.create'),
        'scheduleLabel' => 'Schedule Observation',
    ])
</div>
@endsection