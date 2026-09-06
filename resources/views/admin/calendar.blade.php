@extends('layouts.admin')

@section('title', 'Calendar')

@section('content')
    @include('partials.calendar', [
        'intro' => 'Overview of all observations and conferences across schools.',
        'scheduleRoute' => null,
        'scheduleLabel' => null,
    ])
@endsection