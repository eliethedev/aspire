@extends('layouts.supervisor')

@section('title', 'Calendar')

@section('content')
    @include('partials.calendar', [
        'intro' => 'Track your scheduled observations and conferences across your schools.',
        'scheduleRoute' => route('supervisor.observations.create'),
        'scheduleLabel' => 'Schedule Observation',
    ])
@endsection