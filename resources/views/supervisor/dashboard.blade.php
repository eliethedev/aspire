@extends('layouts.supervisor')
@section('title', 'Supervisor Dashboard')
@push('styles')
  <style>
    .kpi-card {
      transition: all .2s ease
    }

    .kpi-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 28px rgba(15, 23, 42, .08)
    }

    .hero-card {
      background: linear-gradient(135deg, #eef2ff 0%, #f8fafc 55%, #ffffff 100%)
    }

    .dark .hero-card {
      background: linear-gradient(135deg, #0b1220 0%, #111827 55%, #0b1220 100%)
    }

    .section-card {
      transition: all .18s ease
    }

    .section-card:hover {
      box-shadow: 0 8px 24px rgba(15, 23, 42, .06)
    }
  </style>
@endpush
@section('content')
  @php
    $user = Auth::user();
    $completion = $stats['total_observations'] > 0 ? round(($stats['completed'] / $stats['total_observations']) * 100) : 0;
    $pendingTotal = $stats['in_progress'] + $stats['stage_pre_planning'] + $stats['stage_pre_conference'] + $stats['stage_observation'];
    $todayStr = now()->format('l, F j, Y');
    $greeting = now()->hour < 12 ? 'Good morning' : (now()->hour < 18 ? 'Good afternoon' : 'Good evening');
    $firstName = explode(' ', $user->name)[0];
    $pendingBreakdown = [
      ['label' => 'Planning', 'value' => $stats['stage_pre_planning'], 'dot' => 'bg-slate-400'],
      ['label' => 'Pre-Conf', 'value' => $stats['stage_pre_conference'], 'dot' => 'bg-indigo-400'],
      ['label' => 'Observation', 'value' => $stats['stage_observation'], 'dot' => 'bg-amber-400'],
      ['label' => 'Post-Conf', 'value' => $stats['stage_post_conference'], 'dot' => 'bg-emerald-400'],
    ];
    $trendLabel = $trend > 0 ? '+' . number_format($trend, 1) : number_format($trend, 1);
    $trendTone = $trend > 0 ? 'emerald' : ($trend < 0 ? 'red' : 'slate');
  @endphp
  <div class="max-w-7xl mx-auto space-y-6 px-4 sm:px-6 lg:px-0">

    {{-- Hero --}}
    <div class="hero-card bg-white dark:bg-gray-900 rounded-[20px] border border-slate-200 dark:border-gray-800 p-6 lg:p-7 shadow-sm">
      <div class="flex flex-col xl:flex-row xl:items-start justify-between gap-6">
        <div class="flex items-start gap-4 min-w-0">
          <div
            class="w-14 h-14 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-bold text-lg shrink-0 shadow-sm">
            {{ strtoupper(substr($user->name, 0, 1)) }}</div>
          <div class="min-w-0">
            <p class="text-slate-500 dark:text-gray-400 text-xs tracking-widest uppercase font-semibold">{{ $greeting }}</p>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-gray-100 leading-tight">Welcome back, {{ $firstName }}!</h1>
            <p class="text-slate-500 dark:text-gray-400 text-sm mt-1">{{ $todayStr }} · Supervisor Workspace ·
              {{ $user->school->name ?? 'Your school' }}</p>
            <div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
              <span
                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-700 text-slate-700 dark:text-gray-200"><span
                  class="w-2 h-2 rounded-full bg-emerald-500"></span> {{ $stats['total_teachers'] }} Teachers in
                scope</span>
              <span
                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-700 text-slate-700 dark:text-gray-200"><span
                  class="w-2 h-2 rounded-full bg-indigo-500"></span> {{ $stats['total_observations'] }}
                Observations</span>
              <span
                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-700 text-slate-700 dark:text-gray-200"><span
                  class="w-2 h-2 rounded-full bg-amber-500"></span> Avg
                {{ number_format($stats['average_score'], 1) }}</span>
            </div>
          </div>
        </div>
        <div class="flex flex-col sm:flex-row gap-3 xl:shrink-0">
          <div class="bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-700 rounded-2xl px-4 py-3 flex items-center gap-3 min-w-[150px]">
            <div
              class="w-9 h-9 rounded-xl bg-amber-50 border border-amber-200 text-amber-600 flex items-center justify-center">
              <i class="fas fa-list-check text-sm"></i></div>
            <div>
              <p class="text-xs text-slate-500 dark:text-gray-400">Pending actions</p>
              <p class="text-xl font-extrabold leading-none text-slate-900 dark:text-gray-100">{{ $pendingTotal }}</p>
              <p class="text-xs text-slate-500 dark:text-gray-400 mt-0.5">Needs your next step</p>
            </div>
          </div>
          <div class="bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-700 rounded-2xl px-4 py-3 flex items-center gap-3 min-w-[150px]">
            <div
              class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center">
              <i class="fas fa-circle-check text-sm"></i></div>
            <div class="flex-1">
              <p class="text-xs text-slate-500 dark:text-gray-400">Completion</p>
              <p class="text-xl font-extrabold leading-none text-slate-900 dark:text-gray-100">{{ $completion }}%</p>
              <div class="mt-1.5 w-24 h-1.5 bg-slate-100 dark:bg-gray-800 rounded-full overflow-hidden">
                <div class="h-1.5 bg-emerald-500 rounded-full" style="width: {{ $completion }}%"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="mt-5 flex flex-wrap items-center gap-2">
        <a href="{{ route('supervisor.observations.create') }}"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition-colors"><i
            class="fas fa-plus text-xs"></i> New Observation</a>
        <a href="{{ route('supervisor.teachers.index', ['attention' => 'needs']) }}"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-700 text-slate-700 dark:text-gray-200 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-gray-800 transition-colors"><i
            class="fas fa-bell text-amber-500 text-xs"></i> Needs attention @if($needsAttention->isNotEmpty())<span
            class="px-1.5 py-0.5 rounded-full bg-amber-100 dark:bg-yellow-500/10 text-amber-700 dark:text-yellow-400 text-xs font-bold">{{ $needsAttention->count() }}</span>@endif</a>
        <a href="{{ route('supervisor.reports.index') }}"
          class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-700 text-slate-700 dark:text-gray-200 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-gray-800 transition-colors"><i
            class="fas fa-chart-line text-slate-500 dark:text-gray-400 text-xs"></i> View reports</a>
      </div>
      @if($pendingTotal > 0)
        <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-2">
          @foreach($pendingBreakdown as $b)
            <div class="bg-white/80 dark:bg-gray-800/80 border border-slate-200 dark:border-gray-700 rounded-xl px-3 py-2 flex items-center justify-between"><span
                class="flex items-center gap-2 text-xs text-slate-600 dark:text-gray-300"><span
                  class="w-2 h-2 rounded-full {{ $b['dot'] }}"></span>{{ $b['label'] }}</span><span
                class="text-sm font-bold text-slate-900 dark:text-gray-100">{{ $b['value'] }}</span></div>
          @endforeach
        </div>
      @endif
    </div>

    {{-- KPI grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
      <div class="kpi-card bg-white dark:bg-gray-900 rounded-2xl border border-slate-200 dark:border-gray-800 p-5">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-[11px] tracking-widest uppercase font-semibold text-slate-500 dark:text-gray-400">Teachers</p>
            <p class="text-3xl font-extrabold mt-1 text-slate-900 dark:text-gray-100">{{ $stats['total_teachers'] }}</p>
            <p class="text-xs text-slate-500 dark:text-gray-400 mt-1">Under supervision</p>
          </div>
          <div
            class="w-11 h-11 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600">
            <i class="fas fa-users"></i></div>
        </div>
        <a href="{{ route('supervisor.teachers.index') }}"
          class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-400">Manage
          roster <i class="fas fa-arrow-right text-[10px]"></i></a>
      </div>
      <div class="kpi-card bg-white dark:bg-gray-900 rounded-2xl border border-slate-200 dark:border-gray-800 p-5">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-[11px] tracking-widest uppercase font-semibold text-slate-500 dark:text-gray-400">Total Obs</p>
            <p class="text-3xl font-extrabold mt-1 text-slate-900 dark:text-gray-100">{{ $stats['total_observations'] }}</p>
            <p class="text-xs text-slate-500 dark:text-gray-400 mt-1">{{ $stats['scheduled'] }} scheduled · {{ $stats['in_progress'] }}
              active</p>
          </div>
          <div
            class="w-11 h-11 rounded-xl bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 flex items-center justify-center text-slate-600 dark:text-gray-300">
            <i class="fas fa-clipboard-list"></i></div>
        </div>
        <a href="{{ route('supervisor.observations.index') }}"
          class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-slate-700 dark:text-gray-200 hover:text-slate-900 dark:hover:text-gray-100">View
          observations <i class="fas fa-arrow-right text-[10px]"></i></a>
      </div>
      <div class="kpi-card bg-white dark:bg-gray-900 rounded-2xl border border-slate-200 dark:border-gray-800 p-5">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-[11px] tracking-widest uppercase font-semibold text-slate-500 dark:text-gray-400">Completed</p>
            <p class="text-3xl font-extrabold mt-1 text-slate-900 dark:text-gray-100">{{ $stats['completed'] }}</p>
          </div>
          <div
            class="w-11 h-11 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-600">
            <i class="fas fa-check-circle"></i></div>
        </div>
        <div class="mt-3">
          <div class="w-full bg-slate-100 dark:bg-gray-800 rounded-full h-2">
            <div class="h-2 rounded-full bg-emerald-500" style="width: {{ $completion }}%"></div>
          </div>
          <p class="text-xs text-slate-500 dark:text-gray-400 mt-1.5">{{ $completion }}% ·
            {{ $stats['completed'] }}/{{ $stats['total_observations'] }} done</p>
        </div>
      </div>
      <div class="kpi-card bg-white dark:bg-gray-900 rounded-2xl border border-slate-200 dark:border-gray-800 p-5">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-[11px] tracking-widest uppercase font-semibold text-slate-500 dark:text-gray-400">Avg Observation Score</p>
            <p class="text-3xl font-extrabold mt-1 text-slate-900 dark:text-gray-100">{{ number_format($stats['average_score'], 1) }}</p>
            <p class="text-xs text-slate-500 dark:text-gray-400 mt-1">Out of 7.0</p>
          </div>
          <div
            class="w-11 h-11 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-600">
            <i class="fas fa-star"></i></div>
        </div>
        <div class="mt-3 flex items-center gap-1.5 text-xs">@if($trend > 0)<span
          class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 font-semibold"><i
            class="fas fa-arrow-trend-up text-[10px]"></i> {{ $trendLabel }}</span><span class="text-slate-500 dark:text-gray-400">vs
        previous</span>@elseif($trend < 0)<span
              class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-red-50 border border-red-200 text-red-700 font-semibold"><i
                class="fas fa-arrow-trend-down text-[10px]"></i> {{ $trendLabel }}</span><span class="text-slate-500 dark:text-gray-400">vs
            previous</span>@else<span
              class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-500 dark:text-gray-400">No
            trend yet</span>@endif</div>
      </div>
      <div class="kpi-card bg-white dark:bg-gray-900 rounded-2xl border border-slate-200 dark:border-gray-800 p-5">
        <div class="flex justify-between items-start">
          <div>
            <p class="text-[11px] tracking-widest uppercase font-semibold text-slate-500 dark:text-gray-400">Pipeline</p>
            <p class="text-3xl font-extrabold mt-1 text-slate-900 dark:text-gray-100">{{ $pendingTotal }}</p>
            <p class="text-xs text-slate-500 dark:text-gray-400 mt-1">Awaiting action</p>
          </div>
          <div
            class="w-11 h-11 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-600">
            <i class="fas fa-clock"></i></div>
        </div>
        <p class="text-xs text-slate-500 dark:text-gray-400 mt-3">{{ $stats['stage_post_conference'] }} post-conf ·
          {{ $stats['stage_pre_planning'] }} planning</p>
      </div>
    </div>

    {{-- Teachers Needing Attention --}}
    <div class="rounded-2xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm overflow-hidden">
      <div
        class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-5 py-4 {{ $needsAttention->isNotEmpty() ? 'bg-gradient-to-r from-amber-50 via-amber-50/60 to-white dark:from-amber-900/20 dark:via-amber-900/10 dark:to-gray-900 border-b border-amber-100 dark:border-amber-900/40' : 'bg-slate-50 dark:bg-gray-800 border-b border-slate-200 dark:border-gray-700' }}">
        <div class="flex items-center gap-3 min-w-0">
          <div
            class="w-10 h-10 rounded-xl {{ $needsAttention->isNotEmpty() ? 'bg-amber-500 text-white shadow-sm' : 'bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-700 text-emerald-600' }} flex items-center justify-center shrink-0">
            <i class="fas {{ $needsAttention->isNotEmpty() ? 'fa-bell' : 'fa-circle-check' }}"></i></div>
          <div class="min-w-0">
            <h2 class="text-sm font-bold tracking-widest uppercase flex items-center gap-2 text-slate-800 dark:text-gray-100">Teachers
              Needing Attention
              @if($needsAttention->isNotEmpty())<span
              class="px-2 py-0.5 rounded-full text-xs font-bold {{ $needsAttention->where('level', 'high')->isNotEmpty() ? 'bg-red-100 dark:bg-red-500/10 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-500/30' : 'bg-amber-100 dark:bg-yellow-500/10 text-amber-700 dark:text-yellow-400 border border-amber-200 dark:border-yellow-500/30' }}">{{ $needsAttention->count() }}</span>@endif
            </h2>
            <p class="text-xs text-slate-500 dark:text-gray-400 mt-0.5 truncate">
              {{ $needsAttention->isNotEmpty() ? 'Prioritized by score, trend, and pending work — jump in where help matters most.' : 'All teachers are on track. Great job!' }}
            </p>
          </div>
        </div>
        <a href="{{ route('supervisor.teachers.index', ['attention' => 'needs']) }}"
          class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold {{ $needsAttention->isNotEmpty() ? 'bg-amber-500 text-white hover:bg-amber-600 shadow-sm' : 'bg-indigo-600 text-white hover:bg-indigo-700' }} transition-colors shrink-0 w-fit">
          <i class="fas fa-users text-xs"></i> {{ $needsAttention->isNotEmpty() ? 'Review teachers' : 'View teachers' }}
          <i class="fas fa-arrow-right text-xs"></i>
        </a>
      </div>
      @if($needsAttention->isNotEmpty())
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3 p-5 bg-white dark:bg-gray-900">
          @foreach($needsAttention->sortBy('level')->take(6) as $row)
            @php $t = $row['teacher'];
              $flags = $row['flags'];
              $level = $row['level'];
              $initial = strtoupper(substr($t->user->name, 0, 1));
            $border = $level === 'high' ? 'border-l-red-400' : ($level === 'medium' ? 'border-l-orange-400' : 'border-l-amber-300'); @endphp
            <div
              class="rounded-xl bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-700 border-l-4 {{ $border }} p-4 flex items-start gap-3 hover:shadow-sm transition-shadow">
              <div
                class="w-10 h-10 rounded-full {{ $level === 'high' ? 'bg-red-100 text-red-700 border border-red-200' : ($level === 'medium' ? 'bg-orange-100 text-orange-700 border border-orange-200' : 'bg-amber-100 text-amber-700 border border-amber-200') }} flex items-center justify-center font-bold shrink-0">
                {{ $initial }}</div>
              <div class="min-w-0 flex-1">
                <div class="flex items-center justify-between gap-2">
                  <p class="text-sm font-semibold text-slate-900 dark:text-gray-100 truncate">{{ $t->user->name }}</p><span
                    class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $level === 'high' ? 'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-500/30' : ($level === 'medium' ? 'bg-orange-50 dark:bg-orange-500/10 text-orange-700 dark:text-orange-400 border border-orange-200 dark:border-orange-500/30' : 'bg-amber-50 dark:bg-yellow-500/10 text-amber-700 dark:text-yellow-400 border border-amber-200 dark:border-yellow-500/30') }}">{{ ucfirst($level) }}</span>
                </div>
                <p class="text-xs text-slate-500 dark:text-gray-400 truncate">{{ $t->position_label ?? 'Teacher' }} · {{ $t->user->email }}</p>
                <div class="mt-2 space-y-1">
                  @foreach($flags as $f)
                    <div class="flex items-center gap-1.5 text-xs text-slate-600 dark:text-gray-300"><i
                        class="fas {{ $f['icon'] }} text-[11px] text-slate-400 dark:text-gray-500"></i><span>{{ $f['label'] }}</span></div>
                  @endforeach
                </div>
                <a href="{{ route('supervisor.teachers.show', $t) }}"
                  class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-400">Take
                  action <i class="fas fa-arrow-right text-[10px]"></i></a>
              </div>
            </div>
          @endforeach
          @if($needsAttention->count() > 6)
            <a href="{{ route('supervisor.teachers.index', ['attention' => 'needs']) }}"
              class="rounded-xl border-2 border-dashed border-slate-300 dark:border-gray-600 p-4 flex flex-col items-center justify-center text-center hover:border-indigo-400 dark:hover:border-indigo-500/50 hover:bg-indigo-50/40 dark:hover:bg-indigo-900/20 transition-colors">
              <p class="text-sm font-semibold text-indigo-600 dark:text-indigo-400">View all {{ $needsAttention->count() }} teachers</p>
              <p class="text-xs text-slate-500 dark:text-gray-400 mt-0.5">{{ $needsAttention->count() - 6 }} more needing attention</p>
            </a>
          @endif
        </div>
      @else
        <div class="flex flex-col items-center py-8 text-center bg-white dark:bg-gray-900">
          <div class="w-12 h-12 rounded-full bg-emerald-50 border border-emerald-200 flex items-center justify-center mb-2">
            <i class="fas fa-check text-emerald-600"></i></div>
          <p class="text-sm text-slate-600 dark:text-gray-300">No teachers currently need attention.</p>
          <p class="text-xs text-slate-500 dark:text-gray-400 mt-1">We’ll surface anyone who drops below Satisfactory, trends down, or waits on
            you.</p>
        </div>
      @endif
    </div>

    {{-- COT Trend + Quick Actions --}}
    <div class="grid lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 bg-white dark:bg-gray-900 rounded-2xl border border-slate-200 dark:border-gray-800 p-6 section-card">
        <div class="flex items-center justify-between gap-3 mb-4">
          <h2 class="text-sm font-bold tracking-widest uppercase text-slate-700 dark:text-gray-200 flex items-center gap-2"><span
              class="w-1.5 h-5 rounded-full bg-indigo-600"></span> Observation Score Trend</h2>
          <span
            class="text-xs px-2.5 py-1 rounded-full bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-200 dark:border-indigo-500/30 text-indigo-700 dark:text-indigo-400 font-medium">{{ count($cotScores) }}
            scored</span>
        </div>
        @if(count($cotScores) > 0)
          <div class="h-56"><canvas id="cotChart"></canvas></div>
          <div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
            <span class="px-2 py-1 rounded-full bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-700 dark:text-gray-200">Avg
              {{ number_format($stats['average_score'], 2) }}</span>
            @if($trend > 0)<span
              class="px-2 py-1 rounded-full bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 text-emerald-700 dark:text-emerald-400 font-semibold">Trending
              up {{ $trendLabel }}</span>
            @elseif($trend < 0)<span
              class="px-2 py-1 rounded-full bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 text-red-700 dark:text-red-400 font-semibold">Trending down
              {{ $trendLabel }}</span>
            @else<span class="px-2 py-1 rounded-full bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-600 dark:text-gray-300">Stable</span>@endif
            <span class="text-slate-500 dark:text-gray-400">Last {{ count($cotScores) }} scored observations</span>
            <a href="{{ route('supervisor.reports.index') }}"
              class="ml-auto text-indigo-600 dark:text-indigo-400 font-semibold hover:text-indigo-700 dark:hover:text-indigo-400">Open reports →</a>
          </div>
        @else
          <div
            class="flex flex-col items-center py-12 text-center border-2 border-dashed border-slate-200 dark:border-gray-700 rounded-xl bg-slate-50/60 dark:bg-gray-800">
            <div class="w-14 h-14 rounded-xl bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-700 flex items-center justify-center mb-3"><i
                class="fas fa-chart-line text-xl text-slate-400 dark:text-gray-500"></i></div>
            <p class="text-sm font-semibold text-slate-700 dark:text-gray-200">No scored observations yet</p>
            <p class="text-xs text-slate-500 dark:text-gray-400 mt-1 max-w-sm">Complete an observation and rate the indicators to see your trend over time.</p>
              time.</p>
            <a href="{{ route('supervisor.observations.create') }}"
              class="mt-3 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">Create
              observation <i class="fas fa-arrow-right text-xs"></i></a>
          </div>
        @endif
      </div>
      <div class="bg-white dark:bg-gray-900 rounded-2xl border border-slate-200 dark:border-gray-800 p-6 section-card">
        <h2 class="text-sm font-bold tracking-widest uppercase text-slate-700 dark:text-gray-200 flex items-center gap-2 mb-4"><span
            class="w-1.5 h-5 rounded-full bg-indigo-600"></span> Quick Actions</h2>
        <div class="space-y-3">
          <a href="{{ route('supervisor.observations.create') }}"
            class="flex items-center gap-3 p-3.5 rounded-xl border border-slate-200 dark:border-gray-700 hover:shadow-sm hover:border-indigo-200 dark:hover:border-indigo-500/50 bg-white dark:bg-gray-900 group">
            <div
              class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-sm"><i
                class="fas fa-plus"></i></div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-slate-900 dark:text-gray-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">New Observation</p>
              <p class="text-xs text-slate-500 dark:text-gray-400 truncate">Schedule an observation cycle</p>
            </div><i class="fas fa-chevron-right text-xs text-slate-300 dark:text-gray-500 group-hover:text-indigo-500 dark:group-hover:text-indigo-400"></i>
          </a>
          <a href="{{ route('supervisor.teachers.index') }}"
            class="flex items-center gap-3 p-3.5 rounded-xl border border-slate-200 dark:border-gray-700 hover:shadow-sm hover:border-indigo-200 dark:hover:border-indigo-500/50 bg-white dark:bg-gray-900 group">
            <div
              class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
              <i class="fas fa-users"></i></div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-slate-900 dark:text-gray-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Teachers</p>
              <p class="text-xs text-slate-500 dark:text-gray-400 truncate">{{ $stats['total_teachers'] }} faculty members · roster</p>
            </div><i class="fas fa-chevron-right text-xs text-slate-300 dark:text-gray-500 group-hover:text-indigo-500 dark:group-hover:text-indigo-400"></i>
          </a>
          <a href="{{ route('supervisor.career.monitor') }}"
            class="flex items-center gap-3 p-3.5 rounded-xl border border-slate-200 dark:border-gray-700 hover:shadow-sm hover:border-indigo-200 dark:hover:border-indigo-500/50 bg-white dark:bg-gray-900 group">
            <div
              class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-600 dark:text-gray-300 flex items-center justify-center shrink-0">
              <i class="fas fa-chart-line"></i></div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-slate-900 dark:text-gray-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Career Monitor</p>
              <p class="text-xs text-slate-500 dark:text-gray-400 truncate">Manage Career Stages</p>
            </div><i class="fas fa-chevron-right text-xs text-slate-300 dark:text-gray-500 group-hover:text-indigo-500 dark:group-hover:text-indigo-400"></i>
          </a>
          <a href="{{ route('supervisor.reports.index') }}"
            class="flex items-center gap-3 p-3.5 rounded-xl border border-slate-200 dark:border-gray-700 hover:shadow-sm hover:border-indigo-200 dark:hover:border-indigo-500/50 bg-white dark:bg-gray-900 group">
            <div
              class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-600 dark:text-gray-300 flex items-center justify-center shrink-0">
              <i class="fas fa-chart-column"></i></div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-slate-900 dark:text-gray-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Reports</p>
              <p class="text-xs text-slate-500 dark:text-gray-400 truncate">Analytics & exports</p>
            </div><i class="fas fa-chevron-right text-xs text-slate-300 dark:text-gray-500 group-hover:text-indigo-500 dark:group-hover:text-indigo-400"></i>
          </a>
          <a href="{{ route('supervisor.feedback.center') }}"
            class="flex items-center gap-3 p-3.5 rounded-xl border border-slate-200 dark:border-gray-700 hover:shadow-sm hover:border-indigo-200 dark:hover:border-indigo-500/50 bg-white dark:bg-gray-900 group">
            <div
              class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center shrink-0">
              <i class="fas fa-comments"></i></div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-slate-900 dark:text-gray-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Feedback Center</p>
              <p class="text-xs text-slate-500 dark:text-gray-400 truncate">Review feedback</p>
            </div><i class="fas fa-chevron-right text-xs text-slate-300 dark:text-gray-500 group-hover:text-indigo-500 dark:group-hover:text-indigo-400"></i>
          </a>
        </div>
        <div class="mt-4 p-3 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-100 dark:border-indigo-500/30">
          <p class="text-xs font-semibold text-indigo-700 dark:text-indigo-300 flex items-center gap-1.5"><i
              class="fas fa-lightbulb text-amber-500"></i> Pro tip</p>
          <p class="text-xs text-indigo-700/80 dark:text-indigo-300/80 mt-1 leading-relaxed">Use the PPST template for Master Teachers —
            Post-Conference is auto-managed after rating.</p>
        </div>
      </div>
    </div>

    {{-- Continue Where You Left Off --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-slate-200 dark:border-gray-800 p-6 section-card">
      <div class="flex items-center justify-between gap-3 mb-5">
        <h2 class="text-sm font-bold tracking-widest uppercase text-slate-700 dark:text-gray-200 flex items-center gap-2"><span
            class="w-1.5 h-5 rounded-full bg-amber-500"></span> Continue Where You Left Off</h2>
        @if($todoObservations->count() > 0)<a href="{{ route('supervisor.observations.index') }}"
          class="text-xs font-semibold px-3 py-1.5 rounded-full bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700 text-slate-700 dark:text-gray-200 hover:bg-white dark:hover:bg-gray-900">View
        all →</a>@endif
      </div>
      @if($todoObservations->count() > 0)
        <div class="grid md:grid-cols-2 gap-3">
          @foreach($todoObservations as $observation)
            @php $route = match ($observation->stage) { 'pre_observation_planning' => 'supervisor.observations.preObservationPlanning', 'pre_conference' => 'supervisor.observations.preConference', 'observation' => 'supervisor.observations.observation', 'post_conference' => 'supervisor.observations.postConference', default => null};
              $stageLabel = match ($observation->stage) { 'pre_observation_planning' => 'Planning', 'pre_conference' => 'Pre-Conference', 'observation' => 'Observation', 'post_conference' => 'Post-Conference', default => ucwords(str_replace('_', ' ', $observation->stage))};
              $pct = match ($observation->stage) { 'pre_observation_planning' => 25, 'pre_conference' => 50, 'observation' => 75, 'post_conference' => 90, default => 10};
            $stageTone = match ($observation->stage) { 'pre_observation_planning' => 'bg-slate-100 text-slate-700 border-slate-200', 'pre_conference' => 'bg-indigo-50 text-indigo-700 border-indigo-200', 'observation' => 'bg-amber-50 text-amber-700 border-amber-200', 'post_conference' => 'bg-emerald-50 text-emerald-700 border-emerald-200', default => 'bg-slate-50 text-slate-600 border-slate-200'}; @endphp
            <div class="rounded-xl border border-slate-200 dark:border-gray-700 p-4 flex items-center gap-4 hover:shadow-sm bg-white dark:bg-gray-900">
              <div class="w-11 h-11 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold shrink-0">
                {{ strtoupper(substr($observation->observee->user->name ?? '?', 0, 1)) }}</div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold truncate text-slate-900 dark:text-gray-100">{{ $observation->observee->user->name ?? 'Unknown' }}
                </p>
                <p class="text-xs text-slate-500 dark:text-gray-400 flex items-center gap-1.5 flex-wrap"><span
                    class="inline-flex px-2 py-0.5 rounded-full border text-[11px] font-semibold {{ $stageTone }}">{{ $stageLabel }}</span>
                  · {{ $observation->observation_date?->format('M d, Y') ?? 'No date' }}</p>
                <div class="mt-2 h-1.5 bg-slate-100 dark:bg-gray-800 rounded-full overflow-hidden">
                  <div class="h-1.5 bg-indigo-600 rounded-full" style="width: {{ $pct }}%"></div>
                </div>
              </div>
              @if($route)<a href="{{ route($route, $observation) }}"
                class="shrink-0 px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-semibold hover:bg-indigo-700 shadow-sm">Continue
              <i class="fas fa-arrow-right ml-1 text-xs"></i></a>@endif
            </div>
          @endforeach
        </div>
      @else<div
        class="flex flex-col items-center py-10 text-center border-2 border-dashed border-slate-200 dark:border-gray-700 rounded-xl bg-slate-50/50 dark:bg-gray-800">
        <div class="w-10 h-10 rounded-full bg-emerald-50 border border-emerald-200 flex items-center justify-center mb-2">
          <i class="fas fa-check text-emerald-600"></i></div>
        <p class="text-sm font-semibold text-slate-700 dark:text-gray-200">All caught up!</p>
        <p class="text-xs text-slate-500 dark:text-gray-400 mt-1">No pending evaluations.</p><a
          href="{{ route('supervisor.observations.create') }}"
          class="mt-3 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-400">Create observation →</a>
      </div>@endif
    </div>

    {{-- Recent Activity --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-slate-200 dark:border-gray-800 p-6 section-card">
      <div class="flex items-center justify-between gap-3 mb-5">
        <h2 class="text-sm font-bold tracking-widest uppercase text-slate-700 dark:text-gray-200 flex items-center gap-2"><span
            class="w-1.5 h-5 rounded-full bg-slate-400"></span> Recent Activity</h2>@if($recentObservations->count() > 0)<a
              href="{{ route('supervisor.observations.index') }}"
            class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-400">View all →</a>@endif
      </div>
      @if($recentObservations->count() > 0)
        <div class="divide-y divide-slate-100 dark:divide-gray-800">
          @foreach($recentObservations as $observation)
            <div class="flex items-center justify-between py-3.5 gap-3">
              <div class="flex items-center gap-3 min-w-0">
                <div
                  class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center text-xs font-bold shrink-0">
                  {{ strtoupper(substr($observation->observee->user->name ?? '?', 0, 1)) }}</div>
                <div class="min-w-0">
                  <p class="text-sm font-medium text-slate-900 dark:text-gray-100 truncate">{{ $observation->observee->user->name ?? 'Unknown' }}
                  </p>
                  <p class="text-xs text-slate-500 dark:text-gray-400 truncate">{{ $observation->observation_date->format('M d, Y') }} ·
                    {{ $observation->subject ?? '—' }}</p>
                </div>
              </div>
              <div class="flex items-center gap-2 shrink-0">
                <span
                  class="hidden sm:inline-flex px-2.5 py-1 rounded-full text-xs font-semibold border {{ $observation->status === 'completed' ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/30' : ($observation->status === 'scheduled' ? 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 border-indigo-200 dark:border-indigo-500/30' : ($observation->status === 'cancelled' ? 'bg-slate-50 dark:bg-gray-800 text-slate-600 dark:text-gray-300 border-slate-200 dark:border-gray-700' : 'bg-amber-50 dark:bg-yellow-500/10 text-amber-700 dark:text-yellow-400 border-amber-200 dark:border-yellow-500/30')) }}">{{ ucwords(str_replace('_', ' ', $observation->status)) }}</span>
                <a href="{{ route('supervisor.observations.show', $observation) }}"
                  class="text-xs font-semibold px-3 py-1.5 rounded-full bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-700 text-slate-700 dark:text-gray-200 hover:bg-slate-50 dark:hover:bg-gray-800">View</a>
              </div>
            </div>
          @endforeach
        </div>
      @else<div
        class="flex flex-col items-center py-10 text-center border-2 border-dashed border-slate-200 dark:border-gray-700 rounded-xl bg-slate-50/50 dark:bg-gray-800">
        <div class="w-10 h-10 rounded-full bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-700 flex items-center justify-center mb-2"><i
            class="fas fa-inbox text-slate-400 dark:text-gray-500"></i></div>
        <p class="text-sm text-slate-600 dark:text-gray-300">No recent activity.</p><a href="{{ route('supervisor.observations.create') }}"
          class="mt-2 text-sm font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-400">Create observation →</a>
      </div>@endif
    </div>
  </div>
  @if(count($cotScores) > 0)
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>(function () { const ctx = document.getElementById('cotChart').getContext('2d'); new Chart(ctx, { type: 'line', data: { labels: {!! json_encode($cotLabels) !!}, datasets: [{ label: 'Observation Score', data: {!! json_encode($cotScores) !!}, borderColor: '#4f46e5', backgroundColor: 'rgba(79,70,229,0.08)', borderWidth: 2.2, tension: .38, fill: true, pointRadius: 4, pointHoverRadius: 6, pointBackgroundColor: '#4f46e5' }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { backgroundColor: '#1e1b4b', titleColor: '#fff', bodyColor: '#e0e7ff', padding: 10, cornerRadius: 10 } }, scales: { y: { min: 1, max: 7, ticks: { stepSize: 1, color: '#64748b', font: { size: 11 } }, grid: { color: 'rgba(148,163,184,.18)' } }, x: { ticks: { color: '#64748b', font: { size: 11 } }, grid: { display: false } } } } }); })();</script>
  @endif
@endsection