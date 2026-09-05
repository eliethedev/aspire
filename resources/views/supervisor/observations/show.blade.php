@extends('layouts.supervisor')

@section('title', 'Observation Details')

@push('styles')
<style>
    [x-cloak]{display:none !important}
    .hero-card{ background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border:1px solid #e2e8f0; }
    .pill-tabs{ scrollbar-width: none; -ms-overflow-style:none; }
    .pill-tabs::-webkit-scrollbar{ display:none; }
    .pill{ transition: all .18s ease; }
    .pill-active{ background:#4f46e5; color:#fff; border-color:#4f46e5; box-shadow: 0 4px 12px rgba(79,70,229,.25); }
    .section-card{ transition: box-shadow .2s ease, transform .15s ease; }
    .section-card:hover{ box-shadow: 0 8px 24px rgba(15,23,42,.06); }
    .stepper-scroll{ scrollbar-width: thin; }
    .sticky-filter{ backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); }
</style>
@endpush

@section('content')
@php
    $stageConfig = [
        'pre_observation_planning' => ['label'=>'Prepare','desc'=>'Get ready for the observation','icon'=>'<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>','accent'=>'blue'],
        'pre_conference' => ['label'=>'Pre-Observation Conversation','desc'=>'Pre-observation discussion with teacher','icon'=>'<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/></svg>','accent'=>'amber'],
        'observation' => ['label'=>'Classroom Observation','desc'=>'Live classroom observation','icon'=>'<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>','accent'=>'indigo'],
        'post_conference' => ['label'=>'Post-Observation Conference','desc'=>'Feedback discussion & action plan','icon'=>'<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>','accent'=>'green'],
    ];
    $currentStage = $observation->stage;
    $cfg = $stageConfig[$currentStage] ?? null;
    $stageRoutes = [
        'pre_observation_planning' => 'supervisor.observations.preObservationPlanning',
        'pre_conference' => 'supervisor.observations.preConference',
        'observation' => 'supervisor.observations.observation',
        'post_conference' => 'supervisor.observations.postConference',
        // School head observations render the EPOC rating sheet on the
        // observation page, so the EPOC step links there as well.
        'epoc' => 'supervisor.observations.observation',
    ];
    $stageLabels = [
        'pre_observation_planning' => 'Prepare',
        'pre_conference' => 'Pre-Observation Conversation',
        'observation' => 'Classroom Observation',
        'post_conference' => 'Post-Observation Conference',
        'epoc' => 'EPOC Evaluation',
    ];
    $stageCompleted = [
        'pre_observation_planning' => (bool) $observation->preObservationPlanning,
        'pre_conference' => (bool) $observation->preConference,
        'observation' => $observation->isSchoolHeadObservation()
            ? ($observation->epocEvaluation?->ratings->isNotEmpty() ?? false)
            : ($observation->cotRatings && $observation->cotRatings->count() > 0),
        'epoc' => $observation->epocEvaluation?->ratings->isNotEmpty() ?? false,
        'post_conference' => (bool) $observation->postConference,
    ];
    $stageKeys = $observation->isSchoolHeadObservation()
        ? ['pre_observation_planning', 'pre_conference', 'observation', 'epoc', 'post_conference']
        : ['pre_observation_planning', 'pre_conference', 'observation', 'post_conference'];
    $initialFilter = request('detail_filter') ?? 'all';
    $observeeName = $observation->observee->user->name ?? 'Unknown';
    $initials = collect(explode(' ', $observeeName))->map(fn($p)=>mb_substr($p,0,1))->take(2)->implode('');
    $overallScore = $observation->overall_score;
    $scorePct = $overallScore ? ($overallScore/6)*100 : 0;

    $filterTabs = [
        ['key'=>'all','label'=>'All','icon'=>'<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>'],
        ['key'=>'pre_observation','label'=>'Prepare','icon'=>'<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>'],
        ['key'=>'pre_conference','label'=>'Pre-Observation Conversation','icon'=>'<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>'],
        ['key'=>'observation','label'=>'Ratings','icon'=>'<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>'],
        ['key'=>'epoc','label'=>'Conference Evaluation','icon'=>'<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>'],
        ['key'=>'post_conference','label'=>'Post-Observation Conference','icon'=>'<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'],
    ];
@endphp

<div class="obs-show max-w-7xl mx-auto px-4 sm:px-6 lg:px-0" x-data="{ detailFilter: '{{ $initialFilter }}' }">

    {{-- Breadcrumb + Back --}}
    <nav class="flex items-center justify-between gap-4 mb-4 text-sm" aria-label="Breadcrumb">
        <ol class="flex items-center gap-1.5 text-gray-500 min-w-0">
            <li><a href="{{ route('supervisor.observations.index') }}" class="hover:text-indigo-600 transition-colors inline-flex items-center gap-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg> Observations</a></li>
            <li class="text-gray-300">/</li>
            <li class="text-gray-900 font-medium truncate">{{ $observeeName }}</li>
            <li class="hidden sm:inline text-gray-300">/</li>
            <li class="hidden sm:inline text-gray-400 truncate">Details</li>
        </ol>
        <a href="{{ route('supervisor.observations.index') }}" class="shrink-0 hidden sm:inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-gray-200 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg> Back to list
        </a>
    </nav>

    {{-- Hero Card --}}
    <div class="hero-card rounded-2xl p-5 sm:p-6 shadow-sm mb-5">
        <div class="flex flex-col lg:flex-row lg:items-start gap-5">
            <div class="flex gap-4 min-w-0 flex-1">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-600 text-white flex items-center justify-center font-bold text-lg shrink-0 shadow-lg shadow-indigo-500/20">{{ $initials }}</div>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-gray-900 truncate">{{ $observeeName }}</h1>
                        @if($observation->status === 'cancelled')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 ring-1 ring-red-200"><span class="w-2 h-2 rounded-full bg-red-500"></span> Cancelled</span>
                        @elseif($observation->isFinalized())
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Finalized</span>
                        @elseif($observation->status === 'completed')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 ring-1 ring-blue-200">Completed</span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 ring-1 ring-amber-200">{{ $stageConfig[$observation->stage]['label'] ?? ucfirst(str_replace('_',' ', $observation->status)) }}</span>
                        @endif
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200 border border-indigo-100">{{ $observation->isTeacherObservation() ? 'Teacher' : 'School Head' }}</span>
                    </div>
                    <div class="mt-2 flex flex-wrap items-center gap-2 text-sm">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white border border-gray-200 text-gray-600">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            {{ $observation->observation_date?->format('M d, Y') ?? 'No date' }} @if($observation->start_time_label) · {{ $observation->start_time_label }}@endif
                        </span>
                        @if($observation->isTeacherObservation() && $observation->subject)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-700 text-xs font-medium">{{ $observation->subject }}@if($observation->grade_level) · {{ $observation->grade_level }}@endif</span>
                        @endif
                        @if($observation->schoolHead)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-purple-50 border border-purple-100 text-purple-700 text-xs font-medium"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg> SH: {{ $observation->schoolHead->name }}</span>
                        @endif
                    </div>
                    <div class="mt-3 flex flex-wrap gap-2 text-xs text-gray-500">
                        <span>ID #{{ $observation->id }}</span>
                        <span class="text-gray-300">·</span>
                        <span>Stage: <strong class="text-gray-700">{{ $stageConfig[$observation->stage]['label'] ?? ucfirst(str_replace('_',' ', $observation->stage)) }}</strong></span>
                        @if($observation->confirmation_status) <span class="text-gray-300">·</span><span>Confirm: <strong class="capitalize {{ $observation->confirmation_status==='confirmed'?'text-emerald-600':($observation->confirmation_status==='rejected'?'text-red-600':'text-amber-600') }}">{{ $observation->confirmation_status }}</strong></span> @endif
                    </div>
                </div>
            </div>

            {{-- Score + Stage pill + Actions --}}
            <div class="flex flex-col sm:flex-row lg:flex-col gap-4 lg:items-end shrink-0">
                @if($overallScore)
                <div class="flex items-center gap-3 p-3 rounded-2xl bg-white border border-gray-200 shadow-sm">
                    <div class="text-right">
                        <p class="text-[11px] font-semibold tracking-widest uppercase text-gray-400">Overall</p>
                        <div class="flex items-baseline gap-1"><span class="text-2xl font-bold text-gray-900">{{ number_format($overallScore,1) }}</span><span class="text-sm text-gray-400">/ 6</span></div>
                    </div>
                    <div class="w-14 h-14 rounded-xl flex items-center justify-center text-sm font-bold {{ $scorePct>=80?'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200':($scorePct>=60?'bg-amber-50 text-amber-700 ring-1 ring-amber-200':'bg-red-50 text-red-700 ring-1 ring-red-200') }}">
                        {{ number_format($scorePct,0) }}%
                    </div>
                </div>
                @endif
                @if($cfg)
                <div class="inline-flex items-center gap-2.5 px-3.5 py-2.5 rounded-2xl bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200 border border-indigo-100 text-sm font-medium shadow-sm">
                    <span class="w-8 h-8 rounded-xl bg-white/15 flex items-center justify-center">{!! $cfg['icon'] !!}</span>
                    <div class="text-left leading-tight">
                        <p class="text-xs opacity-70">Current stage</p>
                        <p class="font-semibold -mt-0.5">{{ $cfg['label'] }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Quick workflow progress inside hero --}}
        @include('partials.observation-progress', ['stageKeys'=>$stageKeys,'stageLabels'=>$stageLabels,'currentStage'=>$currentStage])
    </div>

    {{-- Sticky Filter Bar --}}
    <div class="sticky top-[65px] z-20 -mx-4 sm:mx-0 sm:rounded-2xl sticky-filter bg-white/95 border-y sm:border border-gray-200 shadow-sm mb-6">
        <div class="px-4 sm:px-3 py-3 flex items-center gap-3">
            <div class="hidden sm:flex items-center gap-2 text-xs font-semibold tracking-widest uppercase text-gray-400 shrink-0"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg> Filter</div>
            <div class="pill-tabs flex items-center gap-2 overflow-x-auto flex-1">
                @foreach($filterTabs as $tab)
                    <button @click="detailFilter='{{ $tab['key'] }}'"
                        :class="detailFilter==='{{ $tab['key'] }}' ? 'pill-active' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'"
                        class="pill inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full border text-sm font-medium whitespace-nowrap shrink-0">
                        <span x-show="detailFilter==='{{ $tab['key'] }}'" class="w-1.5 h-1.5 rounded-full bg-white"></span>
                        {!! $tab['icon'] !!} {{ $tab['label'] }}
                    </button>
                @endforeach
                <button @click="detailFilter='all'" x-show="detailFilter!=='all'" x-transition class="ml-auto hidden sm:inline-flex items-center gap-1 text-xs font-medium text-gray-500 hover:text-gray-700">Clear <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>
            <span class="hidden lg:inline-flex items-center gap-1.5 text-xs text-gray-400 shrink-0"><span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Live filter</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        {{-- Main Content --}}
        <div class="lg:col-span-8 space-y-6 min-w-0">
            @if($observation->confirmation_status === 'confirmed')
            <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-4 flex items-start gap-3">
                <span class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg></span>
                <div class="text-sm leading-relaxed"><p class="font-semibold text-emerald-800">Teacher confirmed</p><p class="text-emerald-700">on {{ $observation->confirmed_at?->format('M d, Y \a\t h:i A') }}</p></div>
            </div>
            @elseif($observation->confirmation_status === 'rejected')
            <div class="rounded-2xl bg-red-50 border border-red-200 p-5">
                <div class="flex items-start gap-3">
                    <span class="w-8 h-8 rounded-xl bg-red-600 text-white flex items-center justify-center shrink-0"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-red-800">Teacher requested reschedule</p>
                        <p class="text-sm text-red-700">on {{ $observation->rejected_at?->format('M d, Y \a\t h:i A') }}</p>
                        @if($observation->rejection_reason)<p class="text-sm mt-2"><span class="font-semibold">Reason:</span> {{ str_replace('_',' ', ucwords($observation->rejection_reason)) }}</p>@endif
                        @if($observation->rejection_notes)<p class="text-sm text-red-700 mt-1"><span class="font-semibold">Notes:</span> {{ $observation->rejection_notes }}</p>@endif
                        <a href="{{ route('supervisor.observations.cancel-form', $observation) }}" class="mt-3 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-red-600 text-white text-sm font-medium hover:bg-red-700 transition-colors">Cancel & Reschedule</a>
                    </div>
                </div>
            </div>
            @endif

            @if($observation->status === 'cancelled')
            <div class="rounded-2xl bg-red-50 border border-red-200 p-5 flex gap-3">
                <svg class="w-5 h-5 text-red-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M4.293 4.293a1 1 0 011.414 0L12 10.586l6.293-6.293a1 1 0 111.414 1.414L13.414 12l6.293 6.293a1 1 0 01-1.414 1.414L12 13.414l-6.293 6.293a1 1 0 01-1.414-1.414L10.586 12 4.293 5.707a1 1 0 010-1.414z"/></svg>
                <div><h3 class="font-semibold text-red-800">Observation Cancelled</h3><p class="text-sm text-red-700">Cancelled on {{ $observation->cancelled_at?->format('M d, Y \a\t h:i A') }} by {{ $observation->cancelledBy?->name ?? 'Unknown' }} · {{ ucwords(str_replace('_',' ', $observation->cancellation_reason ?? '')) }}</p></div>
            </div>
            @endif

            {{-- Stepper Cards --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach($stageKeys as $i => $key)
                    @php
                        $done = $stageCompleted[$key];
                        $active = $key === $observation->stage;
                        $canAccess = $done || $active || ($i>0 && $stageCompleted[$stageKeys[$i-1]]);
                        $icon = match($key){ 'pre_observation_planning'=>'<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>','pre_conference'=>'<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>','observation'=>'<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>','post_conference'=>'<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',};
                    @endphp
                    @if($canAccess)
                        <a href="{{ route($stageRoutes[$key], $observation) }}" class="group flex items-center gap-3 p-3.5 rounded-2xl border {{ $active?'bg-indigo-50 border-indigo-200 ring-1 ring-indigo-200':($done?'bg-white border-emerald-200':'bg-white border-gray-200') }} hover:shadow-md transition-all">
                            <span class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 {{ $done?'bg-emerald-600 text-white':($active?'bg-indigo-600 text-white':'bg-gray-100 text-gray-500') }}">{!! $done ? '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>' : $icon !!}</span>
                            <span class="min-w-0"><span class="block text-xs font-semibold tracking-widest uppercase {{ $done?'text-emerald-600':($active?'text-indigo-600':'text-gray-400') }}">{{ $done?'Completed':($active?'Current':'Ready') }}</span><span class="block text-sm font-semibold text-gray-900 truncate">{{ $stageLabels[$key] }}</span></span>
                            <svg class="w-4 h-4 text-gray-300 group-hover:text-gray-500 ml-auto shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @else
                        <div class="flex items-center gap-3 p-3.5 rounded-2xl bg-gray-50 border border-dashed border-gray-200 opacity-70">
                            <span class="w-9 h-9 rounded-xl bg-gray-200 text-gray-400 flex items-center justify-center">{!! $icon !!}</span>
                            <span class="min-w-0"><span class="block text-xs font-semibold tracking-widest uppercase text-gray-400">Locked</span><span class="block text-sm font-semibold text-gray-500 truncate">{{ $stageLabels[$key] }}</span></span>
                            <svg class="w-4 h-4 text-gray-300 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                    @endif
                @endforeach
            </div>

            {{-- Stage Details - filtered --}}
            <div class="space-y-5">
                {{-- Pre-Observation --}}
                <div x-show="detailFilter==='all' || detailFilter==='pre_observation'" x-transition.opacity class="section-card page-card overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-3">
                        <span class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></span>
                        <div class="flex-1 min-w-0"><h2 class="font-semibold text-gray-900">Prepare</h2><p class="text-xs text-gray-500">Lesson plan & focus</p></div>
                        @if($observation->preObservationPlanning)<span class="hidden sm:inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Completed</span>@endif
                    </div>
                    <div class="p-5">
                        @if($observation->preObservationPlanning)
                            <div class="space-y-4">
                                @if($observation->preObservationPlanning->lesson_plan_file)
                                <div class="flex items-center justify-between gap-3 p-3 rounded-xl bg-emerald-50/70 border border-emerald-100">
                                    <div class="flex items-center gap-3 min-w-0"><span class="w-9 h-9 rounded-xl bg-white border border-gray-200 flex items-center justify-center shrink-0">📄</span><span class="text-sm font-medium truncate">{{ preg_replace('/^\d+_/', '', basename($observation->preObservationPlanning->lesson_plan_file)) }}</span></div>
                                    <a href="{{ asset('storage/' . $observation->preObservationPlanning->lesson_plan_file) }}" target="_blank" class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-white border border-gray-200 text-xs font-semibold hover:bg-gray-50">View</a>
                                </div>
                                @endif
                                @if($observation->preObservationPlanning->ai_insights)
                                <div class="rounded-xl bg-gradient-to-br from-violet-50 to-indigo-50 border border-violet-100 p-4">
                                    <p class="text-xs font-semibold tracking-widest uppercase text-violet-700 mb-2">AI Observation Assistant</p>
                                    @php $insightSections = $observation->preObservationPlanning->insightsSections(); @endphp
                                    @if(isset($insightSections['raw']))<p class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed">{{ $insightSections['raw'] }}</p>@else{!! view('partials.ai-insights-display', ['sections'=>$insightSections])->render() !!}@endif
                                </div>
                                @endif
                                @if($observation->preObservationPlanning->suggested_focus)<div class="rounded-xl bg-blue-50 border border-blue-100 p-4"><p class="text-xs font-semibold tracking-widest uppercase text-blue-700 mb-1">Suggested Focus</p><p class="text-sm text-gray-700">{{ $observation->preObservationPlanning->suggested_focus }}</p></div>@endif
                            </div>
                        @else
                            <div class="text-center py-8"><div class="w-12 h-12 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-3">📋</div><p class="text-sm font-medium text-gray-600">No preparation data yet</p><p class="text-xs text-gray-400">Complete the workflow stage to populate this section.</p></div>
                        @endif
                    </div>
                </div>

                {{-- Pre-Conference --}}
                <div x-show="detailFilter==='all' || detailFilter==='pre_conference'" x-transition.opacity class="section-card page-card overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-3">
                        <span class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg></span>
                        <div class="flex-1"><h2 class="font-semibold text-gray-900">Pre-Observation Conversation</h2><p class="text-xs text-gray-500">Discussion & agreed focus</p></div>
                        @if($observation->preObservationPlanning?->ai_insights_reviewed)<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-violet-100 text-violet-700">AI Reviewed</span>@endif
                    </div>
                    <div class="p-5">
                        @if($observation->preConference)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @if($observation->preObservationPlanning?->ai_insights)<div class="md:col-span-2 rounded-xl bg-gradient-to-br from-violet-50 to-indigo-50 border border-violet-100 p-4"><p class="text-xs font-semibold tracking-widest uppercase text-violet-700 mb-2">AI Observation Assistant</p>@php $insightSections=$observation->preObservationPlanning->insightsSections(); @endphp @if(isset($insightSections['raw']))<p class="text-sm whitespace-pre-wrap">{{ $insightSections['raw'] }}</p>@else{!! view('partials.ai-insights-display',['sections'=>$insightSections])->render() !!}@endif</div>@endif
                                @if($observation->preConference->conference_date)<div class="rounded-xl bg-gray-50 border border-gray-100 p-4"><p class="text-xs font-semibold tracking-widest uppercase text-gray-400">Conference Date</p><p class="font-medium text-gray-900 mt-1">{{ $observation->preConference->conference_date->format('M d, Y') }}</p></div>@endif
                                @if($observation->preConference->topic)<div class="rounded-xl bg-gray-50 border border-gray-100 p-4"><p class="text-xs font-semibold tracking-widest uppercase text-gray-400">Topic</p><p class="font-medium mt-1">{{ $observation->preConference->topic }}</p></div>@endif
                                @if($observation->preConference->learning_objectives)<div class="md:col-span-2 rounded-xl bg-gray-50 border border-gray-100 p-4"><p class="text-xs font-semibold tracking-widest uppercase text-gray-400">Learning Objectives</p><p class="text-sm mt-1 whitespace-pre-wrap">{{ $observation->preConference->learning_objectives }}</p></div>@endif
                                @if($observation->preConference->teaching_strategies)<div class="rounded-xl bg-gray-50 border border-gray-100 p-4"><p class="text-xs font-semibold tracking-widest uppercase text-gray-400">Teaching Strategies</p><p class="text-sm mt-1 whitespace-pre-wrap">{{ $observation->preConference->teaching_strategies }}</p></div>@endif
                                @if($observation->preConference->assessment_activity)<div class="rounded-xl bg-gray-50 border border-gray-100 p-4"><p class="text-xs font-semibold tracking-widest uppercase text-gray-400">Assessment / Activity</p><p class="text-sm mt-1 whitespace-pre-wrap">{{ $observation->preConference->assessment_activity }}</p></div>@endif
                                @if($observation->preConference->discussion_notes)<div class="md:col-span-2 rounded-xl bg-amber-50 border border-amber-100 p-4"><p class="text-sm font-semibold text-amber-800 mb-1">Discussion Notes</p><p class="text-sm">{{ $observation->preConference->discussion_notes }}</p></div>@endif
                                @if($observation->preConference->finalized_focus)<div class="md:col-span-2 rounded-xl bg-blue-50 border border-blue-100 p-4"><p class="text-sm font-semibold text-blue-800 mb-1">Agreed Focus</p><p class="text-sm">{{ $observation->preConference->finalized_focus }}</p></div>@endif
                                @if($observation->preConference->expected_challenges)<div class="rounded-xl bg-gray-50 border p-4"><p class="text-xs font-semibold tracking-widest uppercase text-gray-400">Expected Challenges</p><p class="text-sm mt-1 whitespace-pre-wrap">{{ $observation->preConference->expected_challenges }}</p></div>@endif
                                @if($observation->preConference->feedback_areas)<div class="rounded-xl bg-gray-50 border p-4"><p class="text-xs font-semibold tracking-widest uppercase text-gray-400">Feedback Areas</p><p class="text-sm mt-1 whitespace-pre-wrap">{{ $observation->preConference->feedback_areas }}</p></div>@endif
                                @if($observation->preConference->teacher_reflection)<div class="md:col-span-2 rounded-xl bg-emerald-50 border border-emerald-100 p-4"><p class="text-sm font-semibold text-emerald-800 mb-1">Teacher Reflection</p><p class="text-sm">{{ $observation->preConference->teacher_reflection }}</p></div>@endif
                            </div>
                        @else
                            <div class="text-center py-8 text-sm text-gray-500">No pre-observation conversation recorded yet.</div>
                        @endif
                    </div>
                </div>

                {{-- Observation Ratings --}}
                @if($observation->cotRatings && $observation->cotRatings->count() > 0)
                <div x-show="detailFilter==='all' || detailFilter==='observation' || detailFilter==='ratings'" x-transition.opacity class="section-card page-card overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3"><span class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg></span><div><h2 class="font-semibold text-gray-900">Observation Ratings</h2><p class="text-xs text-gray-500">Observation-based assessment &middot; {{ $observation->cotRatings->count() }} indicators</p></div></div>
                        @if($overallScore)<div class="hidden sm:block text-right"><p class="text-xs tracking-widest uppercase font-semibold text-gray-400">Score</p><p class="text-xl font-bold text-gray-900">{{ number_format($overallScore,1) }} <span class="text-sm font-medium text-gray-400">/ 6</span></p>@php $descTotal='Outstanding'; $descClass='bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400'; if($overallScore>=5.5){$descTotal='Outstanding';$descClass='bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400';}elseif($overallScore>=4.5){$descTotal='Very Satisfactory';$descClass='bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400';}elseif($overallScore>=3.5){$descTotal='Satisfactory';$descClass='bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400';}elseif($overallScore>=2.5){$descTotal='Poor';$descClass='bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400';}else{$descTotal='Needs Improvement';$descClass='bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400';} @endphp<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider mt-1.5 {{ $descClass }}">{{ $descTotal }}</span></div>@endif
                    </div>
                    <div class="p-5 space-y-2.5">
                        @foreach($observation->cotRatings as $rating)
                            @php $na=$rating->not_applicable; $r=$na?null:($rating->not_observed?null:$rating->rating); $rPct=$r?($r/6)*100:0; $rWrap=!$r?'border-gray-200 bg-gray-50':($r>=5?'border-emerald-200 bg-emerald-50/70':($r>=4?'border-blue-200 bg-blue-50/70':($r>=3?'border-amber-200 bg-amber-50/70':'border-red-200 bg-red-50/70'))); $rBadge=!$r?($na?'bg-amber-50 text-amber-600':'bg-gray-100 text-gray-500'):($r>=5?'bg-emerald-600 text-white':($r>=4?'bg-blue-600 text-white':($r>=3?'bg-amber-500 text-white':'bg-red-600 text-white'))); @endphp
                            <div class="rounded-xl border {{ $rWrap }} p-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0 flex-1"><p class="text-sm font-semibold text-gray-900 leading-snug">{{ $rating->indicator }}</p><p class="text-xs text-gray-500 mt-0.5">{{ $rating->domain }}</p>@if($rating->comments)<p class="text-sm text-gray-600 mt-2 pt-2 border-t border-black/5">{{ $rating->comments }}</p>@endif</div>
                                    <div class="shrink-0 text-center"><div class="w-14 h-14 rounded-xl {{ $rBadge }} flex items-center justify-center text-sm font-bold shadow-sm">{{ $r?number_format($r,1):($na?'N/A':'NO') }}</div><p class="text-[10px] text-gray-400 mt-1">{{ $na ? 'Not recorded' : '/ 6' }}</p></div>
                                </div>
                                @if($r)<div class="mt-3 h-1.5 bg-gray-100 rounded-full overflow-hidden"><div class="h-full rounded-full {{ $r>=5?'bg-emerald-500':($r>=4?'bg-blue-500':($r>=3?'bg-amber-500':'bg-red-500')) }}" style="width: {{ $rPct }}%"></div></div>@endif
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- EPOC --}}
                <div x-show="detailFilter==='all' || detailFilter==='epoc'" x-transition.opacity>
                @if($observation->epocEvaluation)
                <div class="section-card page-card overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-3"><span class="w-9 h-9 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg></span><div><h2 class="font-semibold text-gray-900">Post-Observation Conference Evaluation</h2><p class="text-xs text-gray-500">School Head assessment &middot; DepEd CID</p></div></div>
                        @if($observation->epocEvaluation->overall_score)<div class="text-right hidden sm:block"><p class="text-xs tracking-widest uppercase font-semibold text-gray-400">Score</p><p class="text-xl font-bold">{{ number_format($observation->epocEvaluation->overall_score,1) }} <span class="text-sm text-gray-400">/ 5</span></p></div>@endif
                    </div>
                    <div class="p-5 space-y-4">
                        @if($observation->epocEvaluation->school_head_name)<p class="text-sm text-gray-600">School Head: <span class="font-semibold text-gray-900">{{ $observation->epocEvaluation->school_head_name }}</span></p>@endif
                        @php $groupedEpoc=$observation->epocEvaluation->ratings->groupBy('domain'); @endphp
                        @foreach($groupedEpoc as $domain=>$ratings)
                        <div class="rounded-xl border border-violet-100 overflow-hidden">
                            <div class="bg-violet-50 px-4 py-2"><p class="text-sm font-semibold text-violet-800">{{ $domain }}</p></div>
                            <div class="divide-y divide-gray-100">
                                @foreach($ratings as $rating)<div class="px-4 py-3 flex items-center justify-between gap-3"><p class="text-sm text-gray-700 flex-1">{{ $rating->indicator }}</p>@if($rating->rating)<span class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shrink-0 {{ $rating->rating>=4?'bg-emerald-100 text-emerald-700':($rating->rating>=3?'bg-amber-100 text-amber-700':'bg-red-100 text-red-700') }}">{{ $rating->rating }}</span>@else<span class="w-8 h-8 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center text-xs">—</span>@endif</div>@endforeach
                            </div>
                        </div>
                        @endforeach
                        @if($observation->epocEvaluation->narrative_observation)<div class="rounded-xl bg-gray-50 border p-4"><p class="text-xs font-semibold tracking-widest uppercase text-gray-400 mb-1">Narrative Observation</p><p class="text-sm whitespace-pre-wrap">{{ $observation->epocEvaluation->narrative_observation }}</p></div>@endif
                        @if($observation->epocEvaluation->agreement)<div class="rounded-xl bg-gray-50 border p-4"><p class="text-xs font-semibold tracking-widest uppercase text-gray-400 mb-1">Agreement</p><p class="text-sm whitespace-pre-wrap">{{ $observation->epocEvaluation->agreement }}</p></div>@endif
                        @if(!$observation->isFinalized())
                        <div class="flex flex-wrap gap-2 pt-2"><a href="{{ route('supervisor.observations.epoc', $observation) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-sm font-medium">Edit Evaluation</a><a href="{{ route('supervisor.observations.epoc.download', $observation) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border text-sm font-medium">Download DOCX</a></div>
                        @endif
                    </div>
                </div>
                @elseif($observation->schoolHead && !$observation->isFinalized())
                <div class="section-card rounded-2xl border-2 border-dashed border-violet-200 bg-violet-50/40 p-8 text-center">
                    <div class="w-12 h-12 rounded-2xl bg-violet-600 text-white flex items-center justify-center mx-auto mb-3"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg></div>
                    <h3 class="font-semibold text-gray-900">Post-Observation Conference not yet completed</h3><p class="text-sm text-gray-500 mt-1 max-w-md mx-auto">Evaluate the School Head's post-observation conference practices.</p>
                    <a href="{{ route('supervisor.observations.epoc', $observation) }}" class="mt-4 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold">Complete Evaluation</a>
                </div>
                @endif
                </div>

                {{-- Post-Conference --}}
                <div x-show="detailFilter==='all' || detailFilter==='post_conference'" x-transition.opacity class="section-card page-card overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-3">
                        <span class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></span>
                        <div><h2 class="font-semibold text-gray-900">Post-Observation Conference</h2><p class="text-xs text-gray-500">Feedback & next steps</p></div>
                    </div>
                    <div class="p-5">
                        @if($observation->postConference)
                            <div class="space-y-3">
                                @if($observation->postConference->conference_date)<div class="rounded-xl bg-gray-50 border p-4"><p class="text-xs font-semibold tracking-widest uppercase text-gray-400">Conference Date</p><p class="font-medium mt-1">{{ $observation->postConference->conference_date->format('M d, Y') }}</p></div>@endif
                                @if($observation->postConference->ai_comparison)<div class="rounded-xl bg-indigo-50 border border-indigo-100 p-4"><p class="text-xs font-semibold tracking-widest uppercase text-indigo-700 mb-1">AI Comparison (Plan vs Actual)</p><p class="text-sm">{{ $observation->postConference->ai_comparison }}</p></div>@endif
                                @if($observation->postConference->feedback)<div class="rounded-xl bg-gray-50 border p-4"><p class="text-xs font-semibold tracking-widest uppercase text-gray-400 mb-1">Feedback</p><p class="text-sm whitespace-pre-wrap">{{ $observation->postConference->feedback }}</p></div>@endif
                                @if(!$observation->postConference->conference_date && !$observation->postConference->feedback && !$observation->postConference->ai_comparison)<p class="text-sm text-gray-500">Details will appear once post-conference is recorded.</p>@endif
                            </div>
                        @else
                            <div class="text-center py-8 text-sm text-gray-500">No post-observation conference recorded yet.</div>
                        @endif
                    </div>
                </div>

                {{-- Evidence --}}
                @if(!empty($observation->evidence_files))
                <div class="section-card page-card overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="font-semibold text-gray-900 flex items-center gap-2"><svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg> Evidence <span class="ml-1 px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100 text-xs">{{ count($observation->evidence_files) }}</span></h2>
                    </div>
                    <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($observation->evidence_files as $file)
                            @php $mime=$file['mime_type']??''; $isImage=str_starts_with($mime,'image/'); $isVideo=str_starts_with($mime,'video/'); $isPdf=$mime==='application/pdf'; $label=$isImage?'Image':($isVideo?'Video':($isPdf?'PDF':'File')); @endphp
                            <a href="{{ asset('storage/'.$file['path']) }}" target="_blank" class="group flex items-center gap-3 p-3 rounded-xl bg-gray-50 border border-gray-200 hover:border-indigo-200 hover:bg-white transition-colors">
                                <span class="w-10 h-10 rounded-xl bg-white border flex items-center justify-center text-sm">{{ $isImage?'🖼️':($isVideo?'🎬':($isPdf?'📄':'📎')) }}</span>
                                <span class="min-w-0 flex-1"><span class="block text-sm font-medium truncate group-hover:text-indigo-600">{{ $file['original_name'] ?? basename($file['path']) }}</span><span class="text-xs text-gray-500">{{ $label }}@if(isset($file['size'])) · {{ number_format($file['size']/1024,1) }} KB @endif</span></span>
                                <svg class="w-4 h-4 text-gray-400 group-hover:text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Aside --}}
        <aside class="lg:col-span-4 space-y-4 lg:sticky lg:top-[124px]">
            {{-- Action Card --}}
            @if($observation->status !== 'cancelled' && $observation->status !== 'completed')
            <div class="page-card p-5">
                <h3 class="font-semibold text-gray-900 text-sm mb-3 flex items-center gap-2"><span class="w-7 h-7 rounded-lg bg-indigo-600 text-white flex items-center justify-center"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg></span> Next actions</h3>
                <div class="space-y-2">
                    @php $continueLabel=match($observation->stage){'pre_observation_planning'=>'Prepare','pre_conference'=>'Pre-Observation Conversation','observation'=>'Classroom Observation','post_conference'=>'Post-Observation Conference', default=>null}; $continueRoute=match($observation->stage){'pre_observation_planning'=>'supervisor.observations.preObservationPlanning','pre_conference'=>'supervisor.observations.preConference','observation'=>'supervisor.observations.observation','post_conference'=>'supervisor.observations.postConference', default=>null}; @endphp
                    @if($continueRoute)<a href="{{ route($continueRoute,$observation) }}" class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold shadow-sm">Continue · {{ $continueLabel }} <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></a>@endif
                    @if($observation->canFinalize())<form method="POST" action="{{ route('supervisor.observations.finalize',$observation) }}" onsubmit="return confirm('Mark this observation as complete? The teacher will be notified.')">@csrf<button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold">Complete Observation</button></form>@endif
                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('supervisor.feedback.index',$observation) }}" class="inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-white border border-gray-200 text-sm font-medium hover:bg-gray-50">Feedback</a>
                        @if($observation->postConference)<a href="{{ route('supervisor.coaching.create',$observation) }}" class="inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-white border text-sm font-medium hover:bg-gray-50">Coaching</a>@endif
                    </div>
                    @if($observation->canCancel())<a href="{{ route('supervisor.observations.cancel-form',$observation) }}" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-red-200 text-red-600 text-sm font-medium hover:bg-red-50">Cancel Observation</a>@endif
                </div>
            </div>
            @endif

            {{-- At a glance --}}
            <div class="page-card p-5">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">At a glance</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-4"><dt class="text-gray-500">Date</dt><dd class="font-medium text-gray-900">{{ $observation->observation_date?->format('M d, Y') ?? '—' }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-gray-500">Time</dt><dd class="font-medium">{{ $observation->start_time_label ?? '—' }}@if($observation->end_time_label) – {{ $observation->end_time_label }}@endif</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-gray-500">Type</dt><dd class="font-medium">{{ $observation->isTeacherObservation()?'Teacher':'School Head' }}</dd></div>
                    @if($observation->isTeacherObservation() && $observation->subject)<div class="flex justify-between gap-4"><dt class="text-gray-500">Subject</dt><dd class="font-medium truncate max-w-[150px] text-right">{{ $observation->subject }} · {{ $observation->grade_level }}</dd></div>@endif
                    <div class="flex justify-between gap-4"><dt class="text-gray-500">Status</dt><dd><span class="px-2 py-1 rounded-full text-xs font-semibold {{ $observation->status==='cancelled'?'bg-red-50 text-red-700 border border-red-200':($observation->status==='completed'?'bg-emerald-50 text-emerald-700 border border-emerald-200':'bg-amber-50 text-amber-700 border border-amber-200') }}">{{ $stageConfig[$observation->stage]['label'] ?? ucfirst(str_replace('_',' ', $observation->status)) }}</span></dd></div>
                </dl>
                <a href="{{ route('supervisor.observations.teacher-history',$observation->observee_id) }}?type={{ $observation->observee_type }}" class="mt-4 inline-flex items-center gap-2 text-sm font-medium text-indigo-600 hover:text-indigo-700">View all for {{ $observeeName }} <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></a>
            </div>

            @if($observation->status === 'completed')
            <div class="page-card p-5">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Reports</h3>
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('supervisor.observations.report-pdf',$observation) }}" class="inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-medium">PDF</a>
                    <a href="{{ route('supervisor.observations.report',$observation) }}" class="inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium">Markdown</a>
                    <a href="{{ route('supervisor.observations.indicator-trends',$observation) }}" class="inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-white border text-sm font-medium">Trends</a>
                    <a href="{{ route('supervisor.observations.progress-comparison',$observation) }}" class="inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-white border text-sm font-medium">Compare</a>
                    <a href="{{ route('supervisor.observations.pd-recommendations',$observation) }}" class="col-span-2 inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium">PD Recommendations</a>
                </div>
                <div class="mt-4 rounded-xl border border-gray-200 p-4 bg-gray-50">
                    <p class="text-sm font-semibold">Observation Document</p><p class="text-xs text-gray-500 mt-1">Official form populated from ratings.</p>
                    @if($observation->cot_document_generated_at)<p class="text-xs text-gray-400 mt-1">Generated {{ \Carbon\Carbon::parse($observation->cot_document_generated_at)->format('M d, Y h:i A') }}</p>@endif
                    <form method="POST" action="{{ route('supervisor.observations.cot-document.generate',$observation) }}" class="mt-3">@csrf<button type="submit" class="w-full px-3 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium">{{ $observation->cot_document_path?'Regenerate':'Generate' }} Document</button></form>
                    @if($observation->cot_document_path)<div class="mt-2 grid grid-cols-3 gap-2"><a target="_blank" href="{{ route('supervisor.observations.cot-document.preview',$observation) }}" class="px-2 py-2 rounded-xl bg-white border text-xs font-medium text-center">Preview</a><a href="{{ route('supervisor.observations.cot-document.download',$observation) }}" class="px-2 py-2 rounded-xl bg-white border text-xs font-medium text-center">DOCX</a><a href="{{ route('supervisor.observations.cot-document.pdf',$observation) }}" class="px-2 py-2 rounded-xl bg-red-600 text-white text-xs font-medium text-center">PDF</a></div>@endif
                </div>
            </div>
            @endif
        </aside>
    </div>

    {{-- Mobile back --}}
    <a href="{{ route('supervisor.observations.index') }}" class="sm:hidden mt-6 inline-flex items-center gap-2 text-sm font-medium text-gray-600"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg> Back to list</a>
</div>
@endsection
