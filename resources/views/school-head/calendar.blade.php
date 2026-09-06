@extends('layouts.teacher')

@section('title', 'Calendar')

@section('content')
    @include('partials.calendar', [
        'intro' => 'See observations you conduct, co-observations, and your own supervised observations.',
        'scheduleRoute' => route('school-head.observations.create'),
        'scheduleLabel' => 'Schedule Observation',
    ])
@endsection