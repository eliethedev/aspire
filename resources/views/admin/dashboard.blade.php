@extends('layouts.admin')
@section('title', 'Admin Dashboard')
@push('styles')
  <style>
    .kpi-card { transition: all .2s ease; }
    .kpi-card:hover { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(15, 23, 42, .10); }
    .section-card { transition: all .18s ease; }
    .section-card:hover { box-shadow: 0 8px 24px rgba(15, 23, 42, .06); }
    .hero-glow {
      background: linear-gradient(135deg, #eef2ff 0%, #f8fafc 55%, #ffffff 100%);
    }
    .dark .hero-glow {
      background:
        radial-gradient(600px 220px at 85% -10%, rgba(129, 140, 248, .35), transparent 60%),
        radial-gradient(500px 200px at 10% 110%, rgba(52, 211, 153, .18), transparent 60%),
        linear-gradient(135deg, #0b1220 0%, #1e1b4b 55%, #0f172a 100%);
    }
  </style>
@endpush
@section('content')
  @php
    $recentAuditLogs = $recentAuditLogs ?? collect();
    $recentObservations = $recentObservations ?? collect();
    $topSchools = $topSchools ?? [];
    $user = Auth::user();
    $firstName = explode(' ', $user->name)[0] ?? $user->name;
    $greeting = now()->hour < 12 ? 'Good morning' : (now()->hour < 18 ? 'Good afternoon' : 'Good evening');
    $todayStr = now()->format('l, F j, Y');
    $scaleMax = $performance['score_scale_max'] ?? 8;
    $avgScore = (float) ($performance['average_cot_score'] ?? 0);
    $trend = $performance['teacher_growth_trend'] ?? 'stable';
    $trendTextClass = $trend === 'improving'
      ? 'text-emerald-600 dark:text-emerald-400'
      : ($trend === 'declining' ? 'text-rose-600 dark:text-rose-400' : 'text-slate-500 dark:text-slate-400');
    $bands = $charts['ratingBands'] ?? ['labels' => [], 'counts' => []];
    $bandTotal = array_sum($bands['counts'] ?? []);
    $bandStyles = [
      'Outstanding' => ['bar' => 'bg-emerald-500', 'badge' => 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/30'],
      'Very Satisfactory' => ['bar' => 'bg-sky-500', 'badge' => 'bg-sky-50 dark:bg-sky-500/10 text-sky-700 dark:text-sky-400 border-sky-200 dark:border-sky-500/30'],
      'Satisfactory' => ['bar' => 'bg-amber-400', 'badge' => 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-500/30'],
      'Unsatisfactory' => ['bar' => 'bg-orange-500', 'badge' => 'bg-orange-50 dark:bg-orange-500/10 text-orange-700 dark:text-orange-400 border-orange-200 dark:border-orange-500/30'],
      'Poor' => ['bar' => 'bg-rose-500', 'badge' => 'bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 border-rose-200 dark:border-rose-500/30'],
    ];
    $roleStrip = [
      ['label' => 'Admins', 'value' => $stats['total_admins'] ?? 0, 'icon' => 'fa-user-shield', 'tone' => 'text-violet-600', 'chip' => 'bg-violet-50 border-violet-200 dark:bg-violet-500/10 dark:border-violet-500/30'],
      ['label' => 'Supervisors', 'value' => $stats['total_supervisors'] ?? 0, 'icon' => 'fa-user-tie', 'tone' => 'text-indigo-600', 'chip' => 'bg-indigo-50 border-indigo-200 dark:bg-indigo-500/10 dark:border-indigo-500/30'],
      ['label' => 'School Heads', 'value' => $stats['total_school_heads'] ?? 0, 'icon' => 'fa-briefcase', 'tone' => 'text-sky-600', 'chip' => 'bg-sky-50 border-sky-200 dark:bg-sky-500/10 dark:border-sky-500/30'],
      ['label' => 'Teachers', 'value' => $stats['total_teachers'] ?? 0, 'icon' => 'fa-chalkboard-user', 'tone' => 'text-emerald-600', 'chip' => 'bg-emerald-50 border-emerald-200 dark:bg-emerald-500/10 dark:border-emerald-500/30'],
    ];
  @endphp
  <div class="space-y-4 sm:space-y-6 max-w-7xl mx-auto px-1 py-1">

    {{-- Hero --}}
    <div class="hero-glow rounded-2xl sm:rounded-[20px] p-5 sm:p-6 lg:p-8 shadow-sm border border-slate-200 dark:border-transparent text-slate-900 dark:text-white overflow-hidden">
      <div class="flex flex-col xl:flex-row xl:items-start justify-between gap-5">
        <div class="flex items-center gap-3 sm:gap-4 min-w-0">
          <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-indigo-600 text-white dark:bg-white/10 dark:border dark:border-white/20 dark:backdrop-blur flex items-center justify-center font-bold text-lg sm:text-xl shrink-0 shadow-sm">
            {{ strtoupper(substr($user->name, 0, 1)) }}</div>
          <div class="min-w-0">
            <p class="text-indigo-600 dark:text-indigo-300 text-[11px] sm:text-xs tracking-widest uppercase font-semibold">{{ $greeting }} · Aspire Control Center</p>
            <h1 class="text-xl sm:text-2xl font-bold leading-tight">Welcome back, {{ $firstName }}!</h1>
            <p class="text-slate-500 dark:text-slate-300 text-xs sm:text-sm mt-1">{{ $todayStr }} · System operational</p>
            <div class="mt-2.5 sm:mt-3 flex flex-wrap items-center gap-1.5 sm:gap-2 text-[11px] sm:text-xs">
              <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white border border-slate-200 dark:bg-white/10 dark:border-white/15 whitespace-nowrap"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> {{ $stats['total_users'] }} Users</span>
              <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white border border-slate-200 dark:bg-white/10 dark:border-white/15 whitespace-nowrap"><span class="w-2 h-2 rounded-full bg-sky-500"></span> {{ $stats['total_schools'] }} Schools</span>
              <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white border border-slate-200 dark:bg-white/10 dark:border-white/15 whitespace-nowrap"><span class="w-2 h-2 rounded-full bg-amber-500"></span> {{ $stats['completion_rate'] }}% Completion</span>
            </div>
          </div>
        </div>
        <div class="flex flex-col sm:flex-row xl:flex-col gap-2 xl:shrink-0 xl:w-56">
          <a href="{{ route('admin.users.create') }}"
            class="inline-flex justify-center items-center gap-2 px-4 py-2.5 min-h-[44px] rounded-xl bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 dark:bg-white dark:text-slate-900 dark:hover:bg-indigo-50 transition-colors shadow-sm"><i class="fas fa-user-plus text-xs"></i> Add User</a>
          <div class="grid grid-cols-2 gap-2">
            <a href="{{ route('admin.schools.create') }}"
              class="inline-flex justify-center items-center gap-2 px-3 py-2.5 min-h-[44px] rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 dark:bg-white/10 dark:border-white/20 dark:text-white dark:hover:bg-white/20 transition-colors"><i class="fas fa-plus text-xs"></i> School</a>
            <a href="{{ route('admin.reports.index') }}"
              class="inline-flex justify-center items-center gap-2 px-3 py-2.5 min-h-[44px] rounded-xl bg-white border border-slate-200 text-slate-700 text-sm font-semibold hover:bg-slate-50 dark:bg-white/10 dark:border-white/20 dark:text-white dark:hover:bg-white/20 transition-colors"><i class="fas fa-chart-line text-xs"></i> Reports</a>
          </div>
        </div>
      </div>
      <div class="mt-4 sm:mt-5 grid grid-cols-2 lg:grid-cols-4 gap-2">
        @foreach($roleStrip as $r)
          <div class="bg-white border border-slate-200 dark:bg-white/10 dark:border-white/15 dark:backdrop-blur rounded-xl px-3 py-2 flex items-center justify-between gap-2">
            <span class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-200 min-w-0"><i class="fas {{ $r['icon'] }} text-indigo-500 dark:text-indigo-300 shrink-0"></i><span class="truncate">{{ $r['label'] }}</span></span>
            <span class="text-base font-extrabold">{{ $r['value'] }}</span>
          </div>
        @endforeach
      </div>
    </div>

    {{-- KPI grid --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
      <div class="kpi-card bg-white dark:bg-gray-900 rounded-2xl border border-slate-200 dark:border-gray-800 p-4 sm:p-5">
        <div class="flex justify-between items-start gap-2">
          <div class="min-w-0">
            <p class="text-[11px] tracking-widest uppercase font-semibold text-slate-500 dark:text-gray-400">Total Users</p>
            <p class="text-2xl sm:text-3xl font-extrabold mt-1 text-slate-900 dark:text-gray-100">{{ $stats['total_users'] }}</p>
            <p class="text-xs text-slate-500 dark:text-gray-400 mt-1 truncate">{{ $stats['total_teachers'] }} teachers · {{ $stats['total_supervisors'] }} supervisors</p>
          </div>
          <div class="w-9 h-9 sm:w-11 sm:h-11 shrink-0 rounded-xl bg-indigo-50 border border-indigo-100 dark:bg-indigo-500/10 dark:border-indigo-500/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400"><i class="fas fa-users"></i></div>
        </div>
        <a href="{{ route('admin.users.index') }}" class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700">Manage users <i class="fas fa-arrow-right text-[10px]"></i></a>
      </div>
      <div class="kpi-card bg-white dark:bg-gray-900 rounded-2xl border border-slate-200 dark:border-gray-800 p-4 sm:p-5">
        <div class="flex justify-between items-start gap-2">
          <div class="min-w-0">
            <p class="text-[11px] tracking-widest uppercase font-semibold text-slate-500 dark:text-gray-400">Active Schools</p>
            <p class="text-2xl sm:text-3xl font-extrabold mt-1 text-slate-900 dark:text-gray-100">{{ $stats['total_schools'] }}</p>
            <p class="text-xs text-slate-500 dark:text-gray-400 mt-1">Onboarded &amp; running</p>
          </div>
          <div class="w-9 h-9 sm:w-11 sm:h-11 shrink-0 rounded-xl bg-emerald-50 border border-emerald-200 dark:bg-emerald-500/10 dark:border-emerald-500/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400"><i class="fas fa-school"></i></div>
        </div>
        <a href="{{ route('admin.schools.index') }}" class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:text-emerald-700">Manage schools <i class="fas fa-arrow-right text-[10px]"></i></a>
      </div>
      <div class="kpi-card bg-white dark:bg-gray-900 rounded-2xl border border-slate-200 dark:border-gray-800 p-4 sm:p-5">
        <div class="flex justify-between items-start gap-2">
          <div class="min-w-0">
            <p class="text-[11px] tracking-widest uppercase font-semibold text-slate-500 dark:text-gray-400">Observations</p>
            <p class="text-2xl sm:text-3xl font-extrabold mt-1 text-slate-900 dark:text-gray-100">{{ $stats['total_observations'] }}</p>
            <p class="text-xs text-slate-500 dark:text-gray-400 mt-1">{{ $stats['active_observations'] }} active · {{ $stats['pending_cots'] }} pending</p>
          </div>
          <div class="w-9 h-9 sm:w-11 sm:h-11 shrink-0 rounded-xl bg-sky-50 border border-sky-200 dark:bg-sky-500/10 dark:border-sky-500/30 flex items-center justify-center text-sky-600 dark:text-sky-400"><i class="fas fa-clipboard-list"></i></div>
        </div>
        <a href="{{ route('admin.observations.index') }}" class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-sky-600 dark:text-sky-400 hover:text-sky-700">View observations <i class="fas fa-arrow-right text-[10px]"></i></a>
      </div>
      <div class="kpi-card bg-white dark:bg-gray-900 rounded-2xl border border-slate-200 dark:border-gray-800 p-4 sm:p-5">
        <div class="flex justify-between items-start gap-2">
          <div class="min-w-0">
            <p class="text-[11px] tracking-widest uppercase font-semibold text-slate-500 dark:text-gray-400">Avg COT Score</p>
            <p class="text-2xl sm:text-3xl font-extrabold mt-1 text-slate-900 dark:text-gray-100">{{ number_format($avgScore, 2) }}</p>
            <p class="text-xs text-slate-500 dark:text-gray-400 mt-1">Across all COT scales (max {{ $scaleMax }})</p>
          </div>
          <div class="w-9 h-9 sm:w-11 sm:h-11 shrink-0 rounded-xl bg-amber-50 border border-amber-200 dark:bg-amber-500/10 dark:border-amber-500/30 flex items-center justify-center text-amber-600 dark:text-amber-400"><i class="fas fa-star"></i></div>
        </div>
        <p class="mt-3 inline-flex items-center gap-1 text-xs font-semibold capitalize {{ $trendTextClass }}"><i class="fas fa-arrow-trend-{{ $trend === 'declining' ? 'down' : 'up' }} text-[10px]"></i> {{ $trend }} vs last month</p>
      </div>
      <div class="col-span-2 lg:col-span-1 kpi-card bg-white dark:bg-gray-900 rounded-2xl border border-slate-200 dark:border-gray-800 p-4 sm:p-5">
        <div class="flex justify-between items-start gap-2">
          <div class="min-w-0">
            <p class="text-[11px] tracking-widest uppercase font-semibold text-slate-500 dark:text-gray-400">Completion</p>
            <p class="text-2xl sm:text-3xl font-extrabold mt-1 text-slate-900 dark:text-gray-100">{{ $stats['completion_rate'] }}%</p>
            <p class="text-xs text-slate-500 dark:text-gray-400 mt-1">{{ $stats['completed_total'] }}/{{ $stats['total_observations'] }} cycles done</p>
          </div>
          <div class="w-9 h-9 sm:w-11 sm:h-11 shrink-0 rounded-xl bg-violet-50 border border-violet-200 dark:bg-violet-500/10 dark:border-violet-500/30 flex items-center justify-center text-violet-600 dark:text-violet-400"><i class="fas fa-circle-check"></i></div>
        </div>
        <div class="mt-3 h-2 bg-slate-100 dark:bg-gray-800 rounded-full overflow-hidden"><div class="h-2 bg-violet-500 rounded-full" style="width: {{ $stats['completion_rate'] }}%"></div></div>
      </div>
    </div>

    {{-- Main grid --}}
    <div class="grid xl:grid-cols-3 gap-4 sm:gap-6">
      <div class="xl:col-span-2 space-y-4 sm:space-y-6 min-w-0">

        {{-- Analytics --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-slate-200 dark:border-gray-800 p-5 sm:p-6 section-card">
          <div class="flex flex-wrap items-center justify-between gap-2 mb-5">
            <h2 class="text-xs font-bold tracking-widest uppercase flex items-center gap-2 text-slate-700 dark:text-gray-200"><span class="w-1.5 h-5 bg-indigo-600 rounded-full"></span><i class="fas fa-chart-pie text-indigo-600"></i> Analytics Overview</h2>
            <span class="text-xs px-2.5 py-1 rounded-full bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-200 dark:border-indigo-500/30 text-indigo-700 dark:text-indigo-400 font-medium">Last 6 months</span>
          </div>
          <div class="grid sm:grid-cols-2 gap-4">
            <div class="rounded-2xl border border-slate-200 dark:border-gray-700 p-4 sm:p-5">
              <div class="flex items-center justify-between mb-2">
                <h3 class="text-xs font-semibold text-slate-900 dark:text-gray-100">Observations / Month</h3>
                <span class="text-[10px] text-slate-400">{{ array_sum($charts['monthly']['counts']) }} total</span>
              </div>
              <div class="h-44"><canvas id="adminMonthlyChart"></canvas></div>
            </div>
            <div class="rounded-2xl border border-slate-200 dark:border-gray-700 p-4 sm:p-5">
              <h3 class="text-xs font-semibold text-slate-900 dark:text-gray-100 mb-2">By Status</h3>
              <div class="h-44"><canvas id="adminStatusChart"></canvas></div>
            </div>
            <div class="rounded-2xl border border-slate-200 dark:border-gray-700 p-4 sm:p-5">
              <div class="flex items-center justify-between mb-2">
                <h3 class="text-xs font-semibold text-slate-900 dark:text-gray-100">Avg COT Trend</h3>
                <span class="text-[10px] text-slate-400">max {{ $scaleMax }}</span>
              </div>
              <div class="h-44"><canvas id="adminScoreChart"></canvas></div>
            </div>
            <div class="rounded-2xl border border-slate-200 dark:border-gray-700 p-4 sm:p-5">
              <h3 class="text-xs font-semibold text-slate-900 dark:text-gray-100 mb-2">Users by Role</h3>
              <div class="h-44"><canvas id="adminRoleChart"></canvas></div>
            </div>
          </div>
        </div>

        {{-- Rating bands --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-slate-200 dark:border-gray-800 p-5 sm:p-6 section-card">
          <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
            <h2 class="text-xs font-bold tracking-widest uppercase flex items-center gap-2 text-slate-700 dark:text-gray-200"><span class="w-1.5 h-5 bg-emerald-500 rounded-full"></span><i class="fas fa-award text-emerald-600"></i> Rating Distribution</h2>
            <span class="text-xs text-slate-400">{{ $bandTotal }} scored observations</span>
          </div>
          @if($bandTotal > 0)
            <div class="space-y-3">
              @foreach($bands['labels'] as $i => $label)
                @php
                  $count = $bands['counts'][$i] ?? 0;
                  $pct = $bandTotal > 0 ? round(($count / $bandTotal) * 100) : 0;
                  $style = $bandStyles[$label] ?? ['bar' => 'bg-slate-400', 'badge' => 'bg-slate-50 text-slate-600 border-slate-200'];
                @endphp
                <div>
                  <div class="flex items-center justify-between text-xs mb-1">
                    <span class="inline-flex px-2 py-0.5 rounded-full border font-semibold {{ $style['badge'] }}">{{ $label }}</span>
                    <span class="font-bold text-slate-700 dark:text-gray-200">{{ $count }} <span class="font-normal text-slate-400">({{ $pct }}%)</span></span>
                  </div>
                  <div class="h-2.5 bg-slate-100 dark:bg-gray-800 rounded-full overflow-hidden"><div class="h-2.5 {{ $style['bar'] }} rounded-full" style="width: {{ $pct }}%"></div></div>
                </div>
              @endforeach
            </div>
            <div class="mt-4 h-44"><canvas id="adminBandChart"></canvas></div>
          @else
            <p class="text-sm text-slate-400 text-center py-8 border-2 border-dashed border-slate-200 dark:border-gray-700 rounded-xl">No scored observations yet</p>
          @endif
        </div>

        {{-- Recent observations + top schools --}}
        <div class="grid lg:grid-cols-2 gap-4 sm:gap-6">
          <div class="bg-white dark:bg-gray-900 rounded-2xl border border-slate-200 dark:border-gray-800 p-5 sm:p-6 section-card min-w-0">
            <div class="flex items-center justify-between gap-2 mb-4">
              <h2 class="text-xs font-bold tracking-widest uppercase flex items-center gap-2 text-slate-700 dark:text-gray-200"><span class="w-1.5 h-5 bg-sky-500 rounded-full"></span><i class="fas fa-clock-rotate-left text-sky-600"></i> Latest Observations</h2>
              <a href="{{ route('admin.observations.index') }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">View all →</a>
            </div>
            <div class="space-y-2.5">
              @forelse($recentObservations as $obs)
                @php
                  $observeeName = $obs->observee?->user?->name ?? $obs->teacher?->user?->name ?? 'Unknown';
                  $initial = strtoupper(substr($observeeName, 0, 1));
                  $badge = match ($obs->status) {
                    'completed' => 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/30',
                    'scheduled' => 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 border-indigo-200 dark:border-indigo-500/30',
                    'cancelled' => 'bg-slate-100 dark:bg-gray-800 text-slate-500 dark:text-gray-400 border-slate-200 dark:border-gray-700',
                    default => 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-500/30',
                  };
                @endphp
                <a href="{{ route('admin.observations.show', $obs) }}" class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 dark:border-gray-700 hover:border-indigo-200 dark:hover:border-indigo-500/50 hover:bg-indigo-50/40 dark:hover:bg-indigo-900/10 transition-colors">
                  <div class="w-9 h-9 rounded-xl bg-slate-900 dark:bg-gray-800 text-white flex items-center justify-center text-xs font-bold shrink-0">{{ $initial }}</div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-900 dark:text-gray-100 truncate">{{ $observeeName }}</p>
                    <p class="text-xs text-slate-500 dark:text-gray-400 truncate">{{ $obs->observation_date?->format('M d, Y') ?? 'No date' }} · {{ $obs->school?->name ?? '—' }}</p>
                  </div>
                  <div class="shrink-0 text-right">
                    <p class="text-sm font-extrabold text-slate-900 dark:text-gray-100">{{ $obs->overall_score !== null ? number_format((float) $obs->overall_score, 1) : '—' }}</p>
                    <span class="inline-flex px-2 py-0.5 rounded-full border text-[10px] font-semibold {{ $badge }}">{{ ucwords(str_replace('_', ' ', $obs->status ?? 'pending')) }}</span>
                  </div>
                </a>
              @empty
                <p class="text-sm text-slate-400 text-center py-8 border-2 border-dashed border-slate-200 dark:border-gray-700 rounded-xl">No observations yet</p>
              @endforelse
            </div>
          </div>
          <div class="bg-white dark:bg-gray-900 rounded-2xl border border-slate-200 dark:border-gray-800 p-5 sm:p-6 section-card min-w-0">
            <div class="flex items-center justify-between gap-2 mb-4">
              <h2 class="text-xs font-bold tracking-widest uppercase flex items-center gap-2 text-slate-700 dark:text-gray-200"><span class="w-1.5 h-5 bg-violet-500 rounded-full"></span><i class="fas fa-trophy text-violet-600"></i> Top Schools</h2>
              <a href="{{ route('admin.schools.index') }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">View all →</a>
            </div>
            <div class="space-y-2.5">
              @forelse($topSchools as $idx => $school)
                <div class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 dark:border-gray-700">
                  <div class="w-8 h-8 rounded-lg {{ $idx === 0 ? 'bg-amber-100 text-amber-700 border border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/30' : 'bg-slate-100 dark:bg-gray-800 text-slate-500 dark:text-gray-400' }} flex items-center justify-center text-sm font-extrabold shrink-0">{{ $idx + 1 }}</div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-900 dark:text-gray-100 truncate">{{ $school['name'] }}</p>
                    <p class="text-xs text-slate-500 dark:text-gray-400">{{ $school['total'] }} observations</p>
                  </div>
                  <span class="shrink-0 text-sm font-extrabold px-2.5 py-1 rounded-lg bg-violet-50 dark:bg-violet-500/10 border border-violet-200 dark:border-violet-500/30 text-violet-700 dark:text-violet-400">{{ $school['avg_score'] !== null ? number_format($school['avg_score'], 1) : '—' }}</span>
                </div>
              @empty
                <p class="text-sm text-slate-400 text-center py-8 border-2 border-dashed border-slate-200 dark:border-gray-700 rounded-xl">No school activity yet</p>
              @endforelse
            </div>
            <div class="mt-4 p-3.5 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-100 dark:border-indigo-500/30">
              <p class="text-xs font-semibold text-indigo-700 dark:text-indigo-300 flex items-center gap-1.5"><i class="fas fa-lightbulb text-amber-500"></i> This month</p>
              <p class="text-xs text-indigo-700/80 dark:text-indigo-300/80 mt-1">{{ $performance['completed_observations_this_month'] }} observations completed · trend: <span class="font-semibold capitalize">{{ $trend }}</span></p>
            </div>
          </div>
        </div>
      </div>

      {{-- Sidebar --}}
      <div class="space-y-4 sm:space-y-6 min-w-0">
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-slate-200 dark:border-gray-800 p-5 sm:p-6 section-card">
          <h2 class="text-xs font-bold tracking-widest uppercase mb-4 flex items-center gap-2 text-slate-700 dark:text-gray-200"><span class="w-1.5 h-5 bg-indigo-600 rounded-full"></span><i class="fas fa-bolt text-indigo-600"></i> Quick Actions</h2>
          <div class="space-y-2">
            <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 dark:border-gray-700 hover:border-indigo-200 hover:bg-indigo-50/60 dark:hover:border-indigo-500/50 dark:hover:bg-indigo-900/10 group">
              <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0"><i class="fas fa-users text-sm"></i></div>
              <div class="flex-1 min-w-0"><p class="text-sm font-semibold text-slate-900 dark:text-gray-100 group-hover:text-indigo-600">Manage Users</p><p class="text-xs text-slate-500">{{ $stats['total_users'] }} registered</p></div>
              <i class="fas fa-chevron-right text-xs text-slate-300 group-hover:text-indigo-500"></i>
            </a>
            <a href="{{ route('admin.schools.index') }}" class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 dark:border-gray-700 hover:border-emerald-200 hover:bg-emerald-50/60 dark:hover:border-emerald-500/50 dark:hover:bg-emerald-900/10 group">
              <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0"><i class="fas fa-school text-sm"></i></div>
              <div class="flex-1 min-w-0"><p class="text-sm font-semibold text-slate-900 dark:text-gray-100 group-hover:text-emerald-600">Manage Schools</p><p class="text-xs text-slate-500">{{ $stats['total_schools'] }} active</p></div>
              <i class="fas fa-chevron-right text-xs text-slate-300 group-hover:text-emerald-500"></i>
            </a>
            <a href="{{ route('admin.observations.index') }}" class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 dark:border-gray-700 hover:border-sky-200 hover:bg-sky-50/60 dark:hover:border-sky-500/50 dark:hover:bg-sky-900/10 group">
              <div class="w-9 h-9 rounded-xl bg-sky-600 text-white flex items-center justify-center shrink-0"><i class="fas fa-clipboard-list text-sm"></i></div>
              <div class="flex-1 min-w-0"><p class="text-sm font-semibold text-slate-900 dark:text-gray-100 group-hover:text-sky-600">Observations</p><p class="text-xs text-slate-500">{{ $stats['pending_cots'] }} pending review</p></div>
              <i class="fas fa-chevron-right text-xs text-slate-300 group-hover:text-sky-500"></i>
            </a>
            <a href="{{ route('admin.announcements.index') }}" class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 dark:border-gray-700 hover:border-violet-200 hover:bg-violet-50/60 dark:hover:border-violet-500/50 dark:hover:bg-violet-900/10 group">
              <div class="w-9 h-9 rounded-xl bg-violet-600 text-white flex items-center justify-center shrink-0"><i class="fas fa-bullhorn text-sm"></i></div>
              <div class="flex-1 min-w-0"><p class="text-sm font-semibold text-slate-900 dark:text-gray-100 group-hover:text-violet-600">Announcements</p><p class="text-xs text-slate-500">{{ $stats['total_announcements'] }} published</p></div>
              <i class="fas fa-chevron-right text-xs text-slate-300 group-hover:text-violet-500"></i>
            </a>
            <a href="{{ route('admin.support-messages.index') }}" class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 dark:border-gray-700 hover:border-amber-200 hover:bg-amber-50/60 dark:hover:border-amber-500/50 dark:hover:bg-amber-900/10 group">
              <div class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0"><i class="fas fa-life-ring text-sm"></i></div>
              <div class="flex-1 min-w-0"><p class="text-sm font-semibold text-slate-900 dark:text-gray-100 group-hover:text-amber-600">Support Inbox</p><p class="text-xs text-slate-500">{{ $stats['open_support'] }} open tickets</p></div>
              @if(($stats['open_support'] ?? 0) > 0)<span class="px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 text-xs font-bold">{{ $stats['open_support'] }}</span>@endif
            </a>
            <a href="{{ route('admin.invitations.index') }}" class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 dark:border-gray-700 hover:border-rose-200 hover:bg-rose-50/60 dark:hover:border-rose-500/50 dark:hover:bg-rose-900/10 group">
              <div class="w-9 h-9 rounded-xl bg-rose-500 text-white flex items-center justify-center shrink-0"><i class="fas fa-envelope-open-text text-sm"></i></div>
              <div class="flex-1 min-w-0"><p class="text-sm font-semibold text-slate-900 dark:text-gray-100 group-hover:text-rose-600">Invitations</p><p class="text-xs text-slate-500">{{ $stats['pending_invitations'] }} pending</p></div>
              <i class="fas fa-chevron-right text-xs text-slate-300 group-hover:text-rose-500"></i>
            </a>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-slate-200 dark:border-gray-800 p-5 sm:p-6 section-card">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-xs font-bold tracking-widest uppercase flex items-center gap-2 text-slate-700 dark:text-gray-200"><span class="w-1.5 h-5 bg-amber-500 rounded-full"></span><i class="fas fa-triangle-exclamation text-amber-500"></i> Needs Attention</h2>
          </div>
          <div class="space-y-2">
            <a href="{{ route('admin.observations.index') }}" class="flex justify-between items-center p-3 rounded-xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30">
              <span class="text-sm font-medium text-amber-800 dark:text-amber-300"><i class="fas fa-clock mr-1.5"></i>Pending COTs</span>
              <span class="text-sm font-extrabold text-amber-800 dark:text-amber-300">{{ $stats['pending_cots'] }}</span>
            </a>
            <a href="{{ route('admin.support-messages.index') }}" class="flex justify-between items-center p-3 rounded-xl bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/30">
              <span class="text-sm font-medium text-rose-700 dark:text-rose-300"><i class="fas fa-life-ring mr-1.5"></i>Open support tickets</span>
              <span class="text-sm font-extrabold text-rose-700 dark:text-rose-300">{{ $stats['open_support'] }}</span>
            </a>
            <a href="{{ route('admin.invitations.index') }}" class="flex justify-between items-center p-3 rounded-xl bg-sky-50 dark:bg-sky-500/10 border border-sky-200 dark:border-sky-500/30">
              <span class="text-sm font-medium text-sky-700 dark:text-sky-300"><i class="fas fa-envelope mr-1.5"></i>Pending invitations</span>
              <span class="text-sm font-extrabold text-sky-700 dark:text-sky-300">{{ $stats['pending_invitations'] }}</span>
            </a>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-slate-200 dark:border-gray-800 p-5 sm:p-6 section-card">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-xs font-bold tracking-widest uppercase flex items-center gap-2 text-slate-700 dark:text-gray-200"><span class="w-1.5 h-5 bg-indigo-600 rounded-full"></span><i class="fas fa-clock-rotate-left text-indigo-600"></i> Recent Activity</h2>
            <a href="{{ route('admin.audit-logs.index') }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">View all →</a>
          </div>
          <div class="space-y-3 max-h-72 overflow-auto pr-1">
            @forelse($recentAuditLogs as $log)
              <div class="flex gap-3">
                <div class="w-2 h-2 rounded-full mt-2 bg-indigo-500 shrink-0"></div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm truncate"><span class="font-semibold text-slate-900 dark:text-gray-100">{{ $log->user?->name ?? 'System' }}</span>
                    <span class="text-slate-600 dark:text-gray-300">{{ str_replace('_', ' ', $log->action) }}</span>
                    <span class="text-slate-500">{{ str_replace('_', ' ', $log->module ?? '') }}</span>
                    <span class="text-slate-400">#{{ $log->record_id }}</span></p>
                  <p class="text-xs text-slate-400">{{ $log->created_at->diffForHumans() }}</p>
                </div>
              </div>
            @empty
              <p class="text-sm text-slate-400 text-center py-8 border-2 border-dashed border-slate-200 dark:border-gray-700 rounded-xl">No recent activity</p>
            @endforelse
          </div>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-slate-200 dark:border-gray-800 p-5 sm:p-6 section-card">
          <h2 class="text-xs font-bold tracking-widest uppercase mb-4 flex items-center gap-2 text-slate-700 dark:text-gray-200"><span class="w-1.5 h-5 bg-emerald-500 rounded-full"></span><i class="fas fa-gear text-emerald-600"></i> System Status
            <span id="status-last-checked" class="text-[10px] font-normal text-slate-400 ml-auto"></span></h2>
          <div id="system-status-list" class="space-y-2">
            @php $ss = [['Database', $systemStatus['database']], ['API', $systemStatus['api_services']], ['Email', $systemStatus['email_service']], ['Storage', $systemStatus['file_storage']], ['AI', $systemStatus['ai_processing']]]; @endphp
            @foreach($ss as [$k, $v])
              <div class="flex justify-between items-center p-3 rounded-xl bg-slate-50 dark:bg-gray-800 border border-slate-200 dark:border-gray-700" data-status-key="{{ strtolower($k) }}">
                <span class="text-sm text-slate-700 dark:text-gray-200"><i class="fas fa-circle text-[8px] mr-1.5 text-emerald-500"></i>{{ $k }}</span>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-white dark:bg-gray-900 border border-slate-200 dark:border-gray-700 text-slate-700 dark:text-gray-200">{{ $v }}</span>
              </div>
            @endforeach
          </div>
          <div class="flex justify-between text-xs text-slate-400 mt-3">
            <span id="status-mem">Mem {{ $systemStatus['server_usage']['memory_usage'] ?? '—' }}</span>
            <span id="status-peak">Peak {{ $systemStatus['server_usage']['memory_peak'] ?? '—' }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
@push('scripts')
  <script>
    (function () {
      const STATUS_URL = '{{ route("admin.dashboard.status") }}';
      const INTERVAL = 30000;
      const statusList = document.getElementById('system-status-list');
      const lastChecked = document.getElementById('status-last-checked');
      const memEl = document.getElementById('status-mem');
      const peakEl = document.getElementById('status-peak');

      function getStatusColor(value) {
        const v = String(value).toLowerCase();
        if (v.includes('connected') || v.includes('operational') || v.includes('online')) return 'text-emerald-700 bg-emerald-50 border-emerald-200';
        if (v.includes('disabled') || v.includes('not configured')) return 'text-amber-700 bg-amber-50 border-amber-200';
        if (v.includes('error') || v.includes('disconnected') || v.includes('unreachable') || v.includes('degraded')) return 'text-red-700 bg-red-50 border-red-200';
        return 'text-slate-700 bg-white border-slate-200';
      }

      function getDotColor(value) {
        const v = String(value).toLowerCase();
        if (v.includes('connected') || v.includes('operational') || v.includes('online')) return 'text-emerald-500';
        if (v.includes('disabled') || v.includes('not configured')) return 'text-amber-500';
        return 'text-red-500';
      }

      function updateStatus(data) {
        const keys = ['database', 'api_services', 'email_service', 'file_storage', 'ai_processing'];
        keys.forEach(key => {
          const shortKey = key === 'api_services' ? 'api' : key === 'email_service' ? 'email' : key === 'file_storage' ? 'storage' : key === 'ai_processing' ? 'ai' : key;
          const row = statusList.querySelector('[data-status-key="' + shortKey + '"]');
          if (!row) return;
          const val = data[key] ?? 'Unknown';
          const dot = row.querySelector('i.fa-circle');
          const badge = row.querySelector('span:last-child');
          if (dot) dot.className = 'fas fa-circle text-[8px] mr-1.5 ' + getDotColor(val);
          if (badge) {
            badge.textContent = val;
            badge.className = 'text-xs font-semibold px-2.5 py-1 rounded-full bg-white border ' + getStatusColor(val);
          }
        });

        if (data.server_usage) {
          memEl.textContent = 'Mem ' + (data.server_usage.memory_usage || '—');
          peakEl.textContent = 'Peak ' + (data.server_usage.memory_peak || '—');
        }

        if (data.checked_at) {
          const d = new Date(data.checked_at);
          lastChecked.textContent = 'Updated ' + d.toLocaleTimeString();
        }
      }

      function fetchStatus() {
        fetch(STATUS_URL, {
          headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
          .then(r => r.json())
          .then(updateStatus)
          .catch(() => { });
      }

      fetchStatus();
      setInterval(fetchStatus, INTERVAL);
    })();
  </script>
@endpush
@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      if (typeof Chart === 'undefined') return;
      const isDark = document.documentElement.classList.contains('dark');
      Chart.defaults.color = isDark ? '#9ca3af' : '#6b7280';
      Chart.defaults.borderColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)';
      Chart.defaults.font.family = 'Figtree, ui-sans-serif, system-ui, sans-serif';

      const tooltipStyle = {
        backgroundColor: isDark ? '#1f2937' : '#1e1b4b',
        titleColor: '#fff',
        bodyColor: isDark ? '#d1d5db' : '#e0e7ff',
        padding: 10,
        cornerRadius: 10
      };
      const legendLabels = {
        boxWidth: 10,
        boxHeight: 10,
        borderRadius: 5,
        useBorderRadius: true,
        padding: 14,
        font: { size: 11 }
      };
      const scoreMax = @json($charts['scoreMax'] ?? 8);

      const monthlyCtx = document.getElementById('adminMonthlyChart');
      if (monthlyCtx) {
        new Chart(monthlyCtx, {
          type: 'bar',
          data: {
            labels: @json($charts['monthly']['labels']),
            datasets: [{
              label: 'Observations',
              data: @json($charts['monthly']['counts']),
              backgroundColor: 'rgba(99, 102, 241, 0.55)',
              hoverBackgroundColor: 'rgba(99, 102, 241, 0.85)',
              borderColor: 'rgba(99, 102, 241, 1)',
              borderWidth: 1,
              borderRadius: 8,
              borderSkipped: false
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: tooltipStyle },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } }, x: { grid: { display: false } } }
          }
        });
      }

      const scoreCtx = document.getElementById('adminScoreChart');
      if (scoreCtx) {
        new Chart(scoreCtx, {
          type: 'line',
          data: {
            labels: @json($charts['monthly']['labels']),
            datasets: [{
              label: 'Avg COT Score',
              data: @json($charts['scores']),
              spanGaps: true,
              borderColor: '#10b981',
              backgroundColor: 'rgba(16, 185, 129, 0.10)',
              fill: true,
              tension: 0.38,
              pointRadius: 4,
              pointHoverRadius: 6,
              pointBackgroundColor: '#10b981',
              borderWidth: 2.2
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: tooltipStyle },
            scales: { y: { min: 0, max: scoreMax, ticks: { stepSize: 1 } }, x: { grid: { display: false } } }
          }
        });
      }

      const statusCtx = document.getElementById('adminStatusChart');
      if (statusCtx && @json(count($charts['byStatus']['labels'])) > 0) {
        new Chart(statusCtx, {
          type: 'doughnut',
          data: {
            labels: @json($charts['byStatus']['labels']),
            datasets: [{
              data: @json($charts['byStatus']['counts']),
              backgroundColor: ['#f59e0b', '#3b82f6', '#6366f1', '#8b5cf6', '#10b981', '#64748b'],
              borderWidth: 2,
              borderColor: isDark ? '#111827' : '#ffffff',
              hoverOffset: 6
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '62%',
            plugins: { legend: { position: 'bottom', labels: legendLabels }, tooltip: tooltipStyle }
          }
        });
      }

      const roleCtx = document.getElementById('adminRoleChart');
      if (roleCtx) {
        new Chart(roleCtx, {
          type: 'doughnut',
          data: {
            labels: @json($charts['usersByRole']['labels']),
            datasets: [{
              data: @json($charts['usersByRole']['counts']),
              backgroundColor: ['#8b5cf6', '#6366f1', '#0ea5e9', '#10b981'],
              borderWidth: 2,
              borderColor: isDark ? '#111827' : '#ffffff',
              hoverOffset: 6
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '62%',
            plugins: { legend: { position: 'bottom', labels: legendLabels }, tooltip: tooltipStyle }
          }
        });
      }

      const bandCtx = document.getElementById('adminBandChart');
      if (bandCtx && @json(count($charts['ratingBands']['labels'] ?? [])) > 0) {
        new Chart(bandCtx, {
          type: 'bar',
          data: {
            labels: @json($charts['ratingBands']['labels']),
            datasets: [{
              label: 'Observations',
              data: @json($charts['ratingBands']['counts']),
              backgroundColor: ['#10b981', '#0ea5e9', '#fbbf24', '#fb923c', '#f43f5e'],
              borderWidth: 0,
              borderRadius: 8,
              borderSkipped: false
            }]
          },
          options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: tooltipStyle },
            scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } }, y: { grid: { display: false } } }
          }
        });
      }
    });
  </script>
@endpush
