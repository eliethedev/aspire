@extends('layouts.supervisor')

@section('title', 'Career Monitor')

@push('styles')
<style>
.hero-card{background:linear-gradient(135deg,#eef2ff 0%,#f8fafc 55%,#ffffff 100%)}
.section-card{transition:all .18s ease}
.section-card:hover{box-shadow:0 8px 24px rgba(15,23,42,.06)}
</style>
@endpush

@section('content')
@php
    $toneMap = [
        'emerald' => ['bg-emerald-50 text-emerald-700 border-emerald-200', 'bg-emerald-100 text-emerald-700'],
        'amber' => ['bg-amber-50 text-amber-700 border-amber-200', 'bg-amber-100 text-amber-700'],
        'red' => ['bg-red-50 text-red-700 border-red-200', 'bg-red-100 text-red-700'],
        'slate' => ['bg-slate-50 text-slate-600 border-slate-200', 'bg-slate-100 text-slate-600'],
    ];
@endphp
<div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-0"
    x-data="{
        actionOpen: false,
        actionType: 'announce',
        actionUrl: '',
        actionTeacher: '',
        actionStage: '',
        openAction(type, url, teacher, stage) {
            this.actionType = type;
            this.actionUrl = url;
            this.actionTeacher = teacher;
            this.actionStage = stage;
            this.actionOpen = true;
            document.body.classList.add('overflow-y-hidden');
        },
        closeAction() {
            this.actionOpen = false;
            document.body.classList.remove('overflow-y-hidden');
        }
    }">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-xs text-slate-500">
        <a href="{{ route('supervisor.dashboard') }}" class="hover:text-indigo-600 inline-flex items-center gap-1"><i class="fas fa-house text-[11px]"></i> Dashboard</a>
        <span class="text-slate-300">/</span>
        <span class="font-semibold text-slate-700">Career Monitor</span>
    </nav>

    {{-- Hero --}}
    <div class="hero-card rounded-[20px] border border-slate-200 shadow-sm p-6 lg:p-7">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Career Monitor</h1>
                <p class="text-sm text-slate-500 mt-1 max-w-2xl">See whether each teacher's performance aligns with their position and career stage, and allow or announce a higher stage when they're ready.</p>
            </div>
            <a href="{{ route('supervisor.career.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 transition-colors shrink-0">
                <i class="fas fa-arrow-right text-xs"></i> Career Progression
            </a>
        </div>

        {{-- KPI summary --}}
        <div class="mt-6 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
            <div class="bg-white border border-emerald-200 rounded-xl px-4 py-3">
                <p class="text-[11px] font-semibold tracking-widest uppercase text-slate-500">Aligned</p>
                <p class="text-2xl font-extrabold text-emerald-600 mt-1">{{ $counts['aligned'] }}</p>
            </div>
            <div class="bg-white border border-amber-200 rounded-xl px-4 py-3">
                <p class="text-[11px] font-semibold tracking-widest uppercase text-slate-500">Partially aligned</p>
                <p class="text-2xl font-extrabold text-amber-600 mt-1">{{ $counts['partial'] }}</p>
            </div>
            <div class="bg-white border border-red-200 rounded-xl px-4 py-3">
                <p class="text-[11px] font-semibold tracking-widest uppercase text-slate-500">Needs development</p>
                <p class="text-2xl font-extrabold text-red-600 mt-1">{{ $counts['not_aligned'] }}</p>
            </div>
            <div class="bg-white border border-slate-200 rounded-xl px-4 py-3">
                <p class="text-[11px] font-semibold tracking-widest uppercase text-slate-500">Insufficient data</p>
                <p class="text-2xl font-extrabold text-slate-600 mt-1">{{ $counts['insufficient'] }}</p>
            </div>
            <div class="bg-white border border-indigo-200 bg-indigo-50/60 rounded-xl px-4 py-3">
                <p class="text-[11px] font-semibold tracking-widest uppercase text-slate-500">Ready to advance</p>
                <p class="text-2xl font-extrabold text-indigo-700 mt-1">{{ $counts['ready'] }}</p>
                <p class="text-[11px] text-indigo-600/80 mt-0.5">Aligned with a next stage</p>
            </div>
        </div>
    </div>

    {{-- Alignment filter tabs --}}
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('supervisor.career.monitor') }}"
           class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full text-xs font-semibold border transition-colors {{ ! $filter ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50' }}">
            All <span class="opacity-70">({{ $counts['aligned'] + $counts['partial'] + $counts['not_aligned'] + $counts['insufficient'] }})</span>
        </a>
        @foreach($alignmentOptions as $key => $label)
            <a href="{{ route('supervisor.career.monitor', ['alignment' => $key]) }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full text-xs font-semibold border transition-colors {{ $filter === $key ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                {{ $label }} <span class="opacity-70">({{ $counts[$key] ?? 0 }})</span>
            </a>
        @endforeach
    </div>

    {{-- Search --}}
    <form method="GET" action="{{ route('supervisor.career.monitor') }}" class="flex flex-col sm:flex-row gap-2">
        @if($filter)<input type="hidden" name="alignment" value="{{ $filter }}">@endif
        <div class="relative flex-1">
            <i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
            <input type="text" name="search" value="{{ $search }}" placeholder="Search by teacher name or email&hellip;"
                   class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-slate-300 bg-white text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500">
        </div>
        <button type="submit" class="px-5 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-700 transition-colors">Search</button>
    </form>

    {{-- Teacher rows --}}
    @php
        $visible = $rows;
        if ($search) {
            $needle = strtolower($search);
            $visible = $rows->filter(function (array $row) use ($needle) {
                $name = strtolower($row['teacher']->user->name ?? '');
                $email = strtolower($row['teacher']->user->email ?? '');
                return str_contains($name, $needle) || str_contains($email, $needle);
            })->values();
        }
    @endphp

    @forelse($visible as $row)
        @php
            $teacher = $row['teacher'];
            $tone = $row['alignment_tone'];
            $pill = $toneMap[$tone][0] ?? $toneMap['slate'][0];
            $dot = $toneMap[$tone][1] ?? $toneMap['slate'][1];
            $ready = $row['next_stage'] !== null;
            $adv = $row['latest_advancement'];
            $pending = $adv && $adv->isPendingApproval();
        @endphp
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 section-card">
            <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                {{-- Teacher --}}
                <div class="flex items-center gap-3 min-w-0 lg:w-72">
                    <span class="w-11 h-11 rounded-full bg-indigo-100 text-indigo-700 border border-indigo-100 flex items-center justify-center text-base font-bold shrink-0">
                        {{ strtoupper(mb_substr($teacher->user->name, 0, 1)) }}
                    </span>
                    <div class="min-w-0">
                        <a href="{{ route('supervisor.teachers.show', $teacher) }}#readiness" class="font-semibold text-slate-900 hover:text-indigo-600 inline-flex items-center gap-1">
                            {{ $teacher->user->name }} <i class="fas fa-arrow-up-right-from-square text-[10px] text-slate-300"></i>
                        </a>
                        <p class="text-xs text-slate-500 truncate">{{ $teacher->position ?? 'Teacher' }}</p>
                    </div>
                </div>

                {{-- Alignment --}}
                <div class="min-w-0 lg:w-44">
                    <p class="text-[11px] font-semibold tracking-widest uppercase text-slate-400">Alignment</p>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border {{ $pill }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $dot }}"></span> {{ $row['alignment_label'] }}
                    </span>
                </div>

                {{-- Stages --}}
                <div class="min-w-0 lg:w-52">
                    <p class="text-[11px] font-semibold tracking-widest uppercase text-slate-400">Career stage</p>
                    <div class="flex items-center gap-2 text-sm">
                        <span class="font-semibold text-slate-700">{{ $row['current_stage_label'] ?: '—' }}</span>
                        @if($ready)
                            <i class="fas fa-arrow-right text-[11px] text-indigo-400"></i>
                            <span class="font-semibold text-indigo-700">{{ $row['next_stage_label'] }}</span>
                        @endif
                    </div>
                    @if($ready)
                        <p class="text-[11px] text-indigo-500/80">Next stage available</p>
                    @else
                        <p class="text-[11px] text-slate-400">Top of track</p>
                    @endif
                </div>

                {{-- Evidence --}}
                <div class="min-w-0 lg:w-40">
                    <p class="text-[11px] font-semibold tracking-widest uppercase text-slate-400">Evidence</p>
                    <p class="text-sm text-slate-700">
                        @if($row['avg_score'] !== null)
                            <span class="font-extrabold {{ $row['avg_score'] >= 4.5 ? 'text-emerald-600' : ($row['avg_score'] >= 3.5 ? 'text-amber-600' : 'text-red-600') }}">{{ number_format($row['avg_score'], 2) }}/6</span>
                            <span class="text-slate-400">· {{ $row['observations_count'] }} obs</span>
                        @else
                            <span class="text-slate-400">No scored data</span>
                        @endif
                    </p>
                    @if($row['last_observation_date'])
                        <p class="text-[11px] text-slate-400">{{ $row['last_observation_date'] }}</p>
                    @endif
                </div>

                {{-- Latest advancement --}}
                <div class="min-w-0 lg:w-48">
                    <p class="text-[11px] font-semibold tracking-widest uppercase text-slate-400">Last action</p>
                    @if($adv)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold border {{ $adv->statusBadgeClass() }}">
                            <i class="fas {{ $adv->type === 'allow' ? 'fa-check' : 'fa-bullhorn' }} text-[10px]"></i> {{ $adv->statusLabel() }}
                        </span>
                        <p class="text-[11px] text-slate-400 mt-0.5">{{ $adv->typeLabel() }} to {{ $adv->toStageLabel() }}</p>
                    @else
                        <span class="text-xs text-slate-400">None yet</span>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-2 lg:ml-auto shrink-0">
                    @if($pending)
                        <span class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-amber-50 border border-amber-200 text-amber-700 text-xs font-semibold">
                            <i class="fas fa-hourglass-half text-[11px]"></i> Awaiting school head approval
                        </span>
                    @elseif($ready)
                        <button type="button"
                            @click="openAction('announce', '{{ route('supervisor.career.announce', $teacher) }}', '{{ $teacher->user->name }}', '{{ $row['next_stage_label'] }}')"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 transition-colors">
                            <i class="fas fa-bullhorn text-[11px]"></i> Announce
                        </button>
                        <button type="button"
                            @click="openAction('allow', '{{ route('supervisor.career.allow', $teacher) }}', '{{ $teacher->user->name }}', '{{ $row['next_stage_label'] }}')"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-indigo-200 bg-indigo-50 text-indigo-700 text-xs font-semibold hover:bg-indigo-100 transition-colors">
                            <i class="fas fa-check text-[11px]"></i> Allow
                        </button>
                    @else
                        <span class="text-xs text-slate-400 italic">No next stage</span>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="text-center py-16 border-2 border-dashed border-slate-200 rounded-2xl bg-slate-50/50">
            <div class="w-14 h-14 rounded-full bg-white border border-slate-200 flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-chart-line text-slate-400 text-lg"></i>
            </div>
            <p class="text-sm font-semibold text-slate-700">No teachers found</p>
            <p class="text-xs text-slate-500 mt-1">Teachers assigned to your school will appear here.</p>
            @if($search || $filter)
                <a href="{{ route('supervisor.career.monitor') }}" class="mt-3 inline-block text-sm font-semibold text-indigo-600 hover:underline">Clear filters</a>
            @endif
        </div>
    @endforelse

    <p class="text-xs text-slate-400">
        Alignment is a support estimate based on COT evidence. Allowing or announcing a stage records the decision and notifies the teacher and school head &mdash; it does not change the teacher's position or salary.
    </p>

    {{-- Action modal --}}
    <div x-show="actionOpen" x-cloak class="fixed inset-0 z-[90] overflow-y-auto">
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="closeAction()"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-md bg-white rounded-2xl shadow-xl border border-slate-200 my-6"
                x-show="actionOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                    <div>
                        <h4 class="text-base font-bold text-slate-900 flex items-center gap-2">
                            <i class="fas fa-bullhorn text-emerald-600 text-xs"></i> <span x-text="actionType === 'announce' ? 'Announce Career Stage' : 'Allow Career Progression'"></span>
                        </h4>
                        <p class="text-xs text-slate-500 mt-0.5">Notify the teacher and school head.</p>
                    </div>
                    <button type="button" class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors" @click="closeAction()">
                        <i class="fas fa-xmark text-lg"></i>
                    </button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-3.5 text-sm">
                        <div class="flex justify-between gap-3"><span class="text-slate-500">Teacher</span><span class="font-semibold text-slate-900" x-text="actionTeacher"></span></div>
                        <div class="flex justify-between gap-3 mt-1"><span class="text-slate-500">Next career stage</span><span class="font-semibold text-indigo-700" x-text="actionStage"></span></div>
                    </div>
                    <form method="POST" :action="actionUrl" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Remarks <span class="font-normal text-slate-400">(optional)</span></label>
                            <textarea name="remarks" rows="3" placeholder="Add context for this decision&hellip;" class="w-full rounded-xl border border-slate-300 bg-white text-slate-900 text-sm px-3 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"></textarea>
                        </div>
                        <div class="flex items-center justify-end gap-2">
                            <button type="button" class="px-4 py-2.5 border border-slate-300 text-slate-700 bg-white rounded-xl text-sm font-semibold hover:bg-slate-50 transition-colors" @click="closeAction()">Cancel</button>
                            <button type="submit"
                                :class="actionType === 'announce' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-indigo-600 hover:bg-indigo-700'"
                                class="inline-flex items-center gap-1.5 px-5 py-2.5 text-white rounded-xl text-sm font-semibold shadow-sm transition-colors">
                                <i :class="actionType === 'announce' ? 'fas fa-bullhorn' : 'fas fa-check'" class="text-xs"></i>
                                <span x-text="actionType === 'announce' ? 'Announce & Notify' : 'Allow & Notify'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
