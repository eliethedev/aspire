@extends('layouts.teacher')

@section('title', 'Calendar')

@include('partials.dashboard.mock-styles')

@section('content')
<div class="mock-wrap max-w-7xl mx-auto px-1 py-1">
    <div class="mock-topbar">
        <div class="mock-crumbs">School Head <span>/</span> <b>Calendar</b></div>
        <span class="mock-pill"><span class="pulse"></span>Observation calendar</span>
    </div>

    @include('partials.calendar', [
        'intro' => 'See observations you conduct, co-observations, and your own supervised observations.',
        'scheduleRoute' => route('school-head.observations.create'),
        'scheduleLabel' => 'Schedule Observation',
    ])
</div>
@endsection