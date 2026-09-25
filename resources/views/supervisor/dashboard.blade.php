@extends('layouts.supervisor')
@section('title', 'Supervisor Dashboard')
@include('partials.dashboard.mock-styles')
@section('content')
@php
  $user = Auth::user();
  $completion = $stats['total_observations'] > 0 ? round(($stats['completed'] / $stats['total_observations']) * 100) : 0;
  $pendingTotal = $stats['in_progress'] + $stats['stage_pre_planning'] + $stats['stage_pre_conference'] + $stats['stage_observation'];
  $todayStr = now()->format('l, F j, Y');
  $hour = (int) now()->format('G');
  $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
  $firstName = explode(' ', trim($user->name ?? ''))[0] ?? 'Supervisor';
  $focus = $todoObservations->first() ?? $recentObservations->first();
  $focusName = $focus?->observee?->user?->name ?? 'No active observation';
  $focusStage = $focus?->stage ?? null;
  $stageOrder = ['pre_observation_planning' => 1, 'pre_conference' => 2, 'observation' => 3, 'post_conference' => 4];
  $focusStep = $focusStage ? ($stageOrder[$focusStage] ?? 0) : 0;
  $trendLabel = ($trend > 0 ? '+' : '') . number_format((float) $trend, 1);
  $steps = [
    ['key' => 'pre_observation_planning', 'n' => 1, 'title' => 'Pre-observation planning', 'desc' => 'Lesson plan reviewed · focus agreed with teacher'],
    ['key' => 'pre_conference', 'n' => 2, 'title' => 'Pre-observation conference', 'desc' => 'Objectives, strategies and success criteria aligned'],
    ['key' => 'observation', 'n' => 3, 'title' => 'Classroom observation', 'desc' => 'Encode COT indicators · draft autosaved locally'],
    ['key' => 'post_conference', 'n' => 4, 'title' => 'Post-observation conference', 'desc' => 'Feedback discussion and coaching agreement'],
  ];
@endphp
<div class="mock-wrap max-w-7xl mx-auto px-1 py-1">

  <div class="mock-topbar">
    <div class="mock-crumbs">Supervisor <span>/</span> <b>Dashboard</b></div>
    <span class="mock-pill"><span class="pulse"></span>Online</span>
    @if($pendingTotal > 0)
      <span class="mock-pill amber"><span class="pulse"></span>{{ $pendingTotal }} awaiting action</span>
    @endif
    <div class="mock-actions">
      <a class="mock-btn" href="{{ route('supervisor.observations.create') }}">＋ New observation</a>
      <a class="mock-btn primary" href="{{ route('supervisor.observations.index') }}">Open workspace</a>
    </div>
  </div>

  <div class="mock-title">
    <div>
      <h1>{{ $greeting }}, {{ $firstName }}</h1>
      <p>{{ $pendingTotal }} observations need a next step · {{ $needsAttention->count() }} teachers need attention</p>
    </div>
    <time>SY {{ now()->format('Y') }}–{{ now()->addYear()->format('y') }} · {{ $todayStr }}</time>
  </div>

  <div class="mock-kpis">
    <div class="mock-kpi hot">
      <label>Scheduled</label>
      <div class="val">{{ $stats['scheduled'] + $stats['in_progress'] }}</div>
      <div class="delta mock-flat">{{ $stats['total_teachers'] }} teachers in scope</div>
    </div>
    <div class="mock-kpi">
      <label>Completed</label>
      <div class="val">{{ $stats['completed'] }} <small>/ {{ $stats['total_observations'] }}</small></div>
      <div class="delta mock-flat">{{ $completion }}% cycle progress</div>
    </div>
    <div class="mock-kpi">
      <label>Avg. COT score</label>
      <div class="val">{{ number_format((float) $stats['average_score'], 1) }} <small>/ 7.0</small></div>
      <div class="delta {{ $trend > 0 ? 'mock-up' : ($trend < 0 ? 'mock-down' : 'mock-flat') }}">{{ $trend > 0 ? '▲' : ($trend < 0 ? '▼' : '●') }} {{ $trendLabel }} vs previous</div>
    </div>
    <div class="mock-kpi">
      <label>Pipeline</label>
      <div class="val">{{ $pendingTotal }}</div>
      <div class="delta mock-flat">{{ $stats['stage_post_conference'] }} post-conf · {{ $stats['stage_pre_planning'] }} planning</div>
    </div>
  </div>

  <div class="mock-grid">
    <div class="min-w-0">
      <section class="mock-panel" aria-label="Observation workflow">
        <div class="mock-panel-head">
          <h2>Active observation · {{ $focusName }}</h2>
          <span class="hint">{{ $focus ? 'COT cycle · ' . ucwords(str_replace('_', ' ', $focus->stage ?? '')) : 'Nothing in progress — start a new cycle' }}</span>
          @if($focus)
            <a class="link" href="{{ route('supervisor.observations.show', $focus) }}">Open rating sheet →</a>
          @endif
        </div>
        @if($focus)
          <div class="mock-steps">
            @foreach($steps as $s)
              @php
                $state = $focusStep > $s['n'] || $focus->status === 'completed' ? 'done' : ($focusStep === $s['n'] ? 'now' : '');
                $badge = $state === 'done' ? 'done' : ($state === 'now' ? 'now' : 'todo');
                $badgeLabel = $state === 'done' ? 'Done' : ($state === 'now' ? 'In progress' : 'Queued');
              @endphp
              <div class="mock-step {{ $state }}">
                <div class="mock-step-num">{{ $state === 'done' ? '✓' : $s['n'] }}</div>
                <div>
                  <h3>{{ $s['n'] }} · {{ $s['title'] }}</h3>
                  <p>{{ $s['desc'] }}</p>
                  <div class="meta">
                    @if($state === 'now') in progress · {{ $focus->observation_date?->format('M d, Y') ?? 'no date set' }} · {{ $focus->subject ?? '—' }}
                    @elseif($state === 'done') completed · {{ $focus->updated_at?->format('M d · H:i') ?? '' }}
                    @else locked until previous stage is submitted @endif
                  </div>
                </div>
                <span class="mock-status {{ $badge }}">{{ $badgeLabel }}</span>
              </div>
            @endforeach
          </div>
        @else
          <div class="mock-empty">All caught up — no pending evaluations. <a class="link" href="{{ route('supervisor.observations.create') }}">Create observation →</a></div>
        @endif
      </section>

      <section class="mock-panel" aria-label="Observation groups">
        <div class="mock-panel-head">
          <h2>Observation groups</h2>
          <span class="hint">{{ $schoolName }} · folders with observation files</span>
          <a class="link" href="{{ route('supervisor.teachers.index') }}">Review teachers →</a>
        </div>
        @if($schoolGroups->isNotEmpty())
          <div>
            @foreach($schoolGroups as $i => $t)
              @php $lvl = $needsAttention->get($t->id); @endphp
              <details class="mock-folder" @if($i < 2) open @endif>
                <summary>
                  <span class="caret">▸</span>
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#58a6ff" stroke-width="2" aria-hidden="true"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/></svg>
                  <span>{{ $t->user->name ?? 'Unassigned' }}</span>
                  <span class="hint">{{ $t->position_label ?? 'Teacher' }}</span>
                  <span class="spacer">
                    @if($lvl)<span class="mock-status {{ $lvl['level'] === 'high' ? 'now' : 'todo' }}" style="{{ $lvl['level'] === 'high' ? 'color:var(--m-rose);border-color:rgba(248,81,73,.35);background:rgba(248,81,73,.08)' : '' }}">{{ ucfirst($lvl['level']) }}</span>@endif
                    <span class="mock-status todo">{{ $t->observations->count() }} files</span>
                  </span>
                </summary>
                <div>
                  @forelse($t->observations as $ob)
                    @php
                      $score = $ob->overall_score !== null ? (float) $ob->overall_score : null;
                      $tone = $score === null ? 'lo' : ($score >= 4.5 ? 'hi' : ($score >= 3.5 ? 'mid' : 'lo'));
                      $live = in_array($ob->status, ['in_progress', 'scheduled']);
                    @endphp
                    <a class="mock-file {{ $live ? 'live' : '' }}" href="{{ route('supervisor.observations.show', $ob) }}">
                      <span class="mock-fdot"></span>
                      <span class="mock-fmain"><b>{{ $ob->subject ?? 'Observation' }} · {{ $ob->observation_date?->format('M d, Y') ?? 'No date' }}</b><span>{{ ucwords(str_replace('_', ' ', $ob->stage ?? '')) }}</span></span>
                      <span class="mock-fright"><span class="mock-score {{ $tone }}">{{ $score !== null ? number_format($score, 1) : '—' }}</span><span class="mock-status {{ $ob->status === 'completed' ? 'done' : ($ob->status === 'cancelled' ? 'todo' : 'now') }}">{{ ucwords(str_replace('_', ' ', $ob->status)) }}</span></span>
                    </a>
                  @empty
                    <div class="mock-empty">No observation files yet for this teacher.</div>
                  @endforelse
                </div>
              </details>
            @endforeach
          </div>
        @else
          <div class="mock-empty">No teachers assigned to your school yet.</div>
        @endif
      </section>

      <section class="mock-panel" aria-label="Observation score trend">
        <div class="mock-panel-head">
          <h2>Observation score trend</h2>
          <span class="hint">{{ count($cotScores) }} scored</span>
          <a class="link" href="{{ route('supervisor.reports.index') }}">Open reports →</a>
        </div>
        @if(count($cotScores) > 0)
          <div style="padding:14px 16px"><div style="position:relative;height:220px"><canvas id="cotChart"></canvas></div></div>
        @else
          <div class="mock-empty">No scored observations yet — complete an observation and rate the indicators to see the trend.</div>
        @endif
      </section>
    </div>

    <aside class="mock-rail" aria-label="Contextual utilities">
      <div class="mock-mod">
        <div class="mock-mod-head"><h3>System insights</h3><span class="tick {{ $pendingTotal > 0 ? 'warn' : '' }}"></span></div>
        <div class="mock-mod-body">
          <div class="mock-insight"><div><b>Pipeline health</b><span>{{ $pendingTotal }} records awaiting your next step · {{ $completion }}% complete</span><div class="mock-bar"><i style="width:{{ $completion }}%"></i></div></div></div>
          <div class="mock-insight"><div><b>AI analysis</b><span>{{ $stats['stage_post_conference'] }} post-conferences ready for feedback review</span></div></div>
          <div class="mock-insight"><div><b>District trend</b><span>Avg {{ number_format((float) $stats['average_score'], 1) }} · {{ $trendLabel }} vs previous</span></div></div>
        </div>
      </div>
      <div class="mock-mod">
        <div class="mock-mod-head"><h3>Quick actions</h3></div>
        <div class="mock-mod-body">
          <a class="mock-act solid" href="{{ route('supervisor.observations.create') }}">＋ New observation</a>
          <a class="mock-act" href="{{ route('supervisor.teachers.index') }}">👥 Teachers · {{ $stats['total_teachers'] }}</a>
          <a class="mock-act" href="{{ route('supervisor.career.monitor') }}">📈 Career monitor</a>
          <a class="mock-act" href="{{ route('supervisor.feedback.center') }}">💬 Feedback center</a>
        </div>
      </div>
      <div class="mock-mod">
        <div class="mock-mod-head"><h3>Observation metadata</h3><span class="tick"></span></div>
        <div class="mock-mod-body">
          <dl>
            <div class="mock-kv"><dt>Observation</dt><dd>{{ $focus ? '#OBS-' . $focus->id : '—' }}</dd></div>
            <div class="mock-kv"><dt>Stage</dt><dd>{{ $focus ? ucwords(str_replace('_', ' ', $focus->stage ?? '')) : '—' }}</dd></div>
            <div class="mock-kv"><dt>Date</dt><dd>{{ $focus?->observation_date?->format('M d, Y') ?? '—' }}</dd></div>
            <div class="mock-kv"><dt>Status</dt><dd>{{ $focus ? ucwords(str_replace('_', ' ', $focus->status)) : '—' }}</dd></div>
            <div class="mock-kv"><dt>Observee</dt><dd>{{ $focusName }}</dd></div>
          </dl>
        </div>
      </div>
      <div class="mock-mod">
        <div class="mock-mod-head"><h3>Continue queue</h3></div>
        <div class="mock-mod-body">
          <div class="mock-tags">
            @forelse($todoObservations->take(4) as $ob)
              <span class="mock-tag">{{ $ob->observee?->user?->name ?? 'Unknown' }}</span>
            @empty
              <span class="mock-tag dim">Queue clear</span>
            @endforelse
          </div>
        </div>
      </div>
    </aside>
  </div>
</div>
@if(count($cotScores) > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>(function () { const el = document.getElementById('cotChart'); if (!el) return; const dark = document.documentElement.classList.contains('dark'); new Chart(el.getContext('2d'), { type: 'line', data: { labels: {!! json_encode($cotLabels) !!}, datasets: [{ data: {!! json_encode($cotScores) !!}, borderColor: '#2f81f7', backgroundColor: 'rgba(47,129,247,.10)', borderWidth: 2.2, tension: .38, fill: true, pointRadius: 4, pointBackgroundColor: '#2f81f7' }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { min: 1, max: 7, ticks: { stepSize: 1, color: dark ? '#8b949e' : '#64748b' } }, x: { ticks: { color: dark ? '#8b949e' : '#64748b' }, grid: { display: false } } } } }); })();</script>
@endif
@endsection
