@extends('layouts.supervisor')
@section('title', 'Teacher Profile - ' . $teacher->user->name)
@push('styles')
<style>
.hero-card{background:linear-gradient(135deg,#eef2ff 0%,#f8fafc 55%,#ffffff 100%)}
.section-card{transition:all .18s ease}
.section-card:hover{box-shadow:0 8px 24px rgba(15,23,42,.06)}
</style>
@endpush
@section('content')
@php
    $attentionLevel = $teacherAttention['level'] ?? 'ok';
    $attentionFlags = $teacherAttention['flags'] ?? [];
    $needsAttention = $attentionLevel !== 'ok';
    $levelLabel = ['high'=>'High priority','medium'=>'Medium','low'=>'Watch','ok'=>'On track'][$attentionLevel] ?? 'On track';
    $levelTone = ['high'=>'red','medium'=>'orange','low'=>'amber','ok'=>'slate'][$attentionLevel] ?? 'slate';
    $avatarTone = match($attentionLevel){'high'=>'bg-red-100 text-red-700 border-red-200','medium'=>'bg-orange-100 text-orange-700 border-orange-200','low'=>'bg-amber-100 text-amber-700 border-amber-200',default=>'bg-indigo-100 text-indigo-700 border-indigo-100'};
    $initial = strtoupper(substr($teacher->user->name,0,1));
    $avgScore = $stats['avg_score'] ?? $rateeProfile['stats']['average_rating'] ?? null;
@endphp
<div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-0">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-xs text-slate-500">
        <a href="{{ route('supervisor.dashboard') }}" class="hover:text-indigo-600 inline-flex items-center gap-1"><i class="fas fa-house text-[11px]"></i> Dashboard</a>
        <span class="text-slate-300">/</span>
        <a href="{{ route('supervisor.teachers.index') }}" class="hover:text-indigo-600">Teachers</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-700 truncate">{{ $teacher->user->name }}</span>
        @if($needsAttention)<span class="hidden sm:inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-50 border border-amber-200 text-amber-700 text-xs font-semibold"><i class="fas fa-bell text-[10px]"></i> Needs attention</span>@endif
    </nav>

    {{-- Hero --}}
    <div class="hero-card rounded-[20px] border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 lg:p-7">
            <div class="flex flex-col lg:flex-row lg:items-start gap-6">
                <div class="flex items-start gap-4 min-w-0 flex-1">
                    <div class="w-20 h-20 rounded-2xl {{ $avatarTone }} border flex items-center justify-center text-2xl font-bold shrink-0 shadow-sm">
                        {{ $initial }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-2xl font-bold text-slate-900 leading-tight">{{ $rateeProfile['name'] }}</h1>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold border {{ $needsAttention ? ($attentionLevel==='high'?'bg-red-50 border-red-200 text-red-700':($attentionLevel==='medium'?'bg-orange-50 border-orange-200 text-orange-700':'bg-amber-50 border-amber-200 text-amber-700')) : 'bg-emerald-50 border-emerald-200 text-emerald-700' }}">
                                <i class="fas {{ $needsAttention ? 'fa-triangle-exclamation' : 'fa-circle-check' }} text-[11px]"></i> {{ $levelLabel }}
                            </span>
                        </div>
                        <p class="text-sm text-slate-500 mt-1">{{ $teacher->user->email }} · {{ $rateeProfile['position'] }}</p>
                        <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                            @if($rateeProfile['career_stage_label'])
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 font-medium"><i class="fas fa-award text-[11px]"></i> {{ $rateeProfile['career_stage_label'] }}</span>
                            @endif
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-white border border-slate-200 text-slate-700"><i class="fas fa-school text-[11px] text-slate-400"></i> {{ $teacher->school?->name ?? '—' }}</span>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-white border border-slate-200 text-slate-700"><i class="fas fa-book-open text-[11px] text-slate-400"></i> {{ $teacher->subjectsLabel ?? 'No subject' }}</span>
                            @if($teacher->grade_level)<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-white border border-slate-200 text-slate-700"><i class="fas fa-layer-group text-[11px] text-slate-400"></i> Grade {{ $teacher->grade_level }}</span>@endif
                        </div>
                        @if(!empty($attentionFlags))
                        <div class="mt-3 flex flex-wrap gap-1.5">
                            @foreach($attentionFlags as $f)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border {{ $f['tone']==='red'?'bg-red-50 border-red-200 text-red-700':($f['tone']==='orange'?'bg-orange-50 border-orange-200 text-orange-700':($f['tone']==='amber'?'bg-amber-50 border-amber-200 text-amber-700':'bg-indigo-50 border-indigo-200 text-indigo-700')) }}" title="{{ $f['description'] }}">
                                    <i class="fas {{ $f['icon'] }} text-[11px]"></i> {{ $f['label'] }}
                                </span>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2 lg:justify-end shrink-0">
                    <a href="{{ route('supervisor.teachers.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition-colors">
                        <i class="fas fa-arrow-left text-xs"></i> Back to list
                    </a>
                    <a href="{{ route('supervisor.observations.teacher-history', ['observeeId' => $teacher->id, 'type' => 'App\\Models\\Teacher']) }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition-colors">
                        <i class="fas fa-clock-rotate-left text-xs"></i> History
                    </a>
                    <a href="{{ route('supervisor.observations.create') }}?teacher_id={{ $teacher->id }}" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl {{ $needsAttention && $attentionLevel==='high' ? 'bg-red-600 hover:bg-red-700' : 'bg-indigo-600 hover:bg-indigo-700' }} text-white text-sm font-semibold shadow-sm transition-colors">
                        <i class="fas fa-plus text-xs"></i> New Observation
                    </a>
                </div>
            </div>

            {{-- Stats strip --}}
            <div class="mt-6 grid grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="bg-white border border-slate-200 rounded-xl px-4 py-3">
                    <p class="text-[11px] font-semibold tracking-widest uppercase text-slate-500">Total Observations</p>
                    <p class="text-xl font-extrabold text-slate-900 mt-1">{{ $rateeProfile['stats']['total'] }}</p>
                    <p class="text-xs text-slate-500">{{ $rateeProfile['stats']['completed'] }} completed · {{ $rateeProfile['stats']['in_progress'] }} in progress</p>
                </div>
                <div class="bg-white border border-slate-200 rounded-xl px-4 py-3">
                    <p class="text-[11px] font-semibold tracking-widest uppercase text-slate-500">Average Rating</p>
                    <p class="text-xl font-extrabold {{ $avgScore!==null && $avgScore<4 ? 'text-amber-600' : 'text-indigo-600' }} mt-1">{{ $avgScore!==null ? number_format($avgScore,2).' / 6' : '—' }}</p>
                    <p class="text-xs text-slate-500">Across scored observations</p>
                </div>
                <div class="bg-white border border-slate-200 rounded-xl px-4 py-3">
                    <p class="text-[11px] font-semibold tracking-widest uppercase text-slate-500">Latest Observation</p>
                    @if(!empty($rateeProfile['stats']['latest_observation']))
                        <p class="text-sm font-semibold text-slate-900 mt-1">{{ $rateeProfile['stats']['latest_observation']['date'] }} @if(!empty($rateeProfile['stats']['latest_observation']['rating']))· {{ number_format($rateeProfile['stats']['latest_observation']['rating'],2) }}@endif</p>
                        <p class="text-xs text-slate-500">Most recent scored</p>
                    @else
                        <p class="text-sm font-semibold text-slate-400 mt-1">None yet</p>
                        <p class="text-xs text-slate-500">Awaiting first observation</p>
                    @endif
                </div>
                <div class="bg-white border border-slate-200 rounded-xl px-4 py-3">
                    <p class="text-[11px] font-semibold tracking-widest uppercase text-slate-500">Upcoming</p>
                    @if(!empty($rateeProfile['stats']['upcoming_observation']))
                        <p class="text-sm font-semibold text-slate-900 mt-1">{{ $rateeProfile['stats']['upcoming_observation']['date'] }}</p>
                        <p class="text-xs text-slate-500">Scheduled</p>
                    @else
                        <p class="text-sm font-semibold text-slate-400 mt-1">None scheduled</p>
                        <a href="{{ route('supervisor.observations.create') }}?teacher_id={{ $teacher->id }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">Schedule now →</a>
                    @endif
                </div>
            </div>
        </div>

        @if($needsAttention)
        <div class="px-6 lg:px-7 py-3 bg-amber-50 border-t border-amber-200 flex flex-col sm:flex-row sm:items-center gap-2 text-sm">
            <span class="inline-flex items-center gap-1.5 font-semibold text-amber-800"><i class="fas fa-lightbulb text-amber-600"></i> Suggested next step:</span>
            <span class="text-amber-800">{{ $attentionFlags[0]['description'] ?? 'Review this teacher’s recent performance and schedule a supportive observation.' }}</span>
            <a href="{{ route('supervisor.observations.create') }}?teacher_id={{ $teacher->id }}" class="sm:ml-auto inline-flex items-center gap-1 text-amber-800 font-semibold hover:text-amber-900">Take action <i class="fas fa-arrow-right text-xs"></i></a>
        </div>
        @endif
    </div>

    <div class="grid lg:grid-cols-12 gap-6">
        {{-- Main --}}
        <div class="lg:col-span-8 space-y-6">
            @include('partials.ratee.observation-summary', ['rateeProfile' => $rateeProfile])
            @include('partials.ratee.performance-by-domain', ['rateeProfile' => $rateeProfile])
            @include('partials.ratee.areas-attention', ['rateeProfile' => $rateeProfile])

            {{-- Recent Observations --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 section-card">
                <div class="flex items-center justify-between gap-3 mb-5">
                    <h2 class="text-sm font-bold tracking-widest uppercase text-slate-700 flex items-center gap-2"><span class="w-1.5 h-5 rounded-full bg-indigo-600"></span> Recent Observations</h2>
                    <a href="{{ route('supervisor.observations.teacher-history', ['observeeId' => $teacher->id, 'type' => 'App\\Models\\Teacher']) }}" class="text-xs font-semibold px-3 py-1.5 rounded-full bg-indigo-50 border border-indigo-200 text-indigo-700 hover:bg-indigo-100">View all</a>
                </div>
                @forelse($observations as $observation)
                    @php
                        $stageLabels = ['pre_observation_planning' => 'Planning', 'pre_conference' => 'Pre-Conference', 'observation' => 'Observation', 'post_conference' => 'Post-Conference'];
                        $statusTone = match($observation->status) {
                            'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'scheduled' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                            'cancelled' => 'bg-slate-100 text-slate-600 border-slate-200',
                            default => 'bg-amber-50 text-amber-700 border-amber-200',
                        };
                        $instrumentLabel = $observation->cotIndicatorVersion?->label;
                    @endphp
                    <div class="flex items-center justify-between gap-3 py-3.5 {{ !$loop->last ? 'border-b border-slate-100' : '' }}">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="text-center shrink-0 w-12">
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">{{ $observation->observation_date->format('M') }}</p>
                                <p class="text-lg font-extrabold text-indigo-600 leading-tight">{{ $observation->observation_date->format('d') }}</p>
                                <p class="text-[11px] text-slate-400">{{ $observation->observation_date->format('Y') }}</p>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-semibold text-slate-900 text-sm truncate">{{ $observation->subject ?? 'Observation' }}</span>
                                    @if($instrumentLabel)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 border border-slate-200 text-slate-600">{{ $instrumentLabel }}</span>
                                    @endif
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border {{ $statusTone }}">
                                        {{ ucwords(str_replace('_', ' ', $observation->status)) }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 mt-1 flex items-center gap-2 flex-wrap">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-slate-50 border border-slate-200">{{ $stageLabels[$observation->stage] ?? ucwords(str_replace('_', ' ', $observation->stage)) }}</span>
                                    @if($observation->overall_score)<span class="font-medium text-slate-700">Score {{ number_format($observation->overall_score, 2) }}</span>@endif
                                </p>
                            </div>
                        </div>
                        <a href="{{ route('supervisor.observations.show', $observation) }}" class="px-3.5 py-1.5 text-sm font-semibold text-indigo-600 bg-white border border-slate-200 rounded-xl hover:bg-indigo-50 transition-colors shrink-0">
                            View
                        </a>
                    </div>
                @empty
                    <div class="text-center py-10 border-2 border-dashed border-slate-200 rounded-xl bg-slate-50/50">
                        <div class="w-12 h-12 rounded-full bg-white border border-slate-200 flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-clipboard-list text-slate-400"></i>
                        </div>
                        <p class="text-sm font-semibold text-slate-700">No observations recorded yet.</p>
                        <p class="text-xs text-slate-500 mt-1">Start with a pre-observation plan to begin the cycle.</p>
                        <a href="{{ route('supervisor.observations.create') }}?teacher_id={{ $teacher->id }}" class="mt-3 inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-700">
                            <i class="fas fa-plus text-xs"></i> Start an Observation
                        </a>
                    </div>
                @endforelse
                @if($observations->hasPages())
                    <div class="mt-5">
                        {{ $observations->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="lg:col-span-4 space-y-6">
            <div class="sticky top-6 space-y-6">
                @include('partials.ratee.supervisor-actions', ['rateeProfile' => $rateeProfile])
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
                    <h3 class="text-sm font-bold tracking-widest uppercase text-slate-700 flex items-center gap-2"><span class="w-1.5 h-4 rounded-full bg-slate-400"></span> At a glance</h3>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Email</dt><dd class="font-medium text-slate-900 truncate">{{ $teacher->user->email }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Role</dt><dd class="font-medium text-slate-900">{{ $rateeProfile['role_label'] ?? 'Teacher' }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">School</dt><dd class="font-medium text-slate-900 truncate">{{ $teacher->school?->name ?? '—' }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-slate-500">Career stage</dt><dd class="font-medium text-slate-900">{{ $rateeProfile['career_stage_label'] ?: '—' }}</dd></div>
                    </dl>
                </div>
                <div class="bg-indigo-50 border border-indigo-200 rounded-2xl p-4">
                    <p class="text-xs font-semibold text-indigo-800 flex items-center gap-1.5"><i class="fas fa-circle-info text-indigo-600"></i> How we flag attention</p>
                    <p class="text-xs text-indigo-700/80 mt-1 leading-relaxed">Below Satisfactory (&lt; 4), declining trend, never-observed, or has a pending observation waiting on you. Flags are supportive — not punitive.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Career Readiness (full width) --}}
    <div class="mt-2">
        @include('partials.career-readiness', [
            'ratee' => $teacher,
            'careerContext' => $careerContext,
            'careerEvidence' => $careerEvidence,
            'careerReadiness' => $careerReadiness,
            'careerRoute' => $careerRoute,
            'canAssess' => $canAssess,
            'canEditAssessment' => true,
        ])
    </div>
</div>
@endsection
