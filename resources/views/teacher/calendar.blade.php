@extends('layouts.teacher')

@section('title', 'Calendar')

@section('content')
    @include('partials.calendar', [
        'intro' => 'See your scheduled observations, conferences, and completed activities at a glance.',
        'scheduleRoute' => null,
        'scheduleLabel' => null,
    ])
@endsection