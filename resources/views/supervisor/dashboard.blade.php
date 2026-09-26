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
  $focusName = $focus?->observee?->user?->name ?? 'No review in progress';
  $focusStage = $focus?->stage ?? null;
  $stageOrder = ['pre_observation_planning' => 1, 'pre_conference' => 2, 'observation' => 3, 'post_conference' => 4];
  $focusStep = $focusStage ? ($stageOrder[$focusStage] ?? 0) : 0;
  $trendLabel = ($trend > 0 ? '+' : '') . number_format((float) $trend, 1);
  $steps = [
    ['key' => 'pre_observation_planning', 'n' => 1, 'title' => 'Lesson planning'],
    ['key' => 'pre_conference', 'n' => 2, 'title' => 'Chat befosre class'],
    ['key' => 'observation', 'n' => 3, 'title' => 'Classroom visit'],
    ['key' => 'post_conference', 'n' => 4, 'title' => 'Chat after class'],
  ];
  $planRecord = $focus?->preObservationPlanning;
  $preRecord = $focus?->preConference;
  $postRecord = $focus?->postConference;
  $ratingCount = (int) ($focus?->cot_ratings_count ?? 0);
  $focusDone = ($focus?->status === 'completed');
  $currentStepTitle = collect($steps)->firstWhere('n', $focusStep)['title'] ?? null;
  $stageRoutes = ['pre_observation_planning' => 'supervisor.observations.preObservationPlanning', 'pre_conference' => 'supervisor.observations.preConference', 'observation' => 'supervisor.observations.observation', 'post_conference' => 'supervisor.observations.postConference'];
  $continueRoute = $focusDone ? 'supervisor.observations.show' : ($stageRoutes[$focusStage] ?? 'supervisor.observations.show');
  $teacherFirst = explode(' ', trim($focusName))[0] ?? 'the teacher';
  $plannedOn = $focus?->observation_date?->format('M d, Y');
  $visitBits = array_filter([$focus?->subject, $focus?->grade_level_label]);
  $visitWhat = $visitBits ? implode(' · ', $visitBits) : null;
  $focusTopics = is_array($planRecord?->suggested_focus ?? null) ? array_values(array_filter($planRecord->suggested_focus, 'is_string')) : [];
@endphp
<div class="mock-wrap max-w-7xl mx-auto px-1 py-1">

  <div class="mock-topbar">
    <div class="mock-crumbs">Supervisor <span>/</span> <b>Dashboard</b></div>
    <span class="mock-pill"><span class="pulse"></span>Online</span>
    @if($pendingTotal > 0)
      <span class="mock-pill amber"><span class="pulse"></span>{{ $pendingTotal }} awaiting action</span>
    @endif
    <div class="mock-actions">
      <a class="mock-btn" href="{{ route('supervisor.observations.create') }}">＋ Start new review</a>
      <a class="mock-btn primary" href="{{ route('supervisor.observations.index') }}">See all reviews</a>
    </div>
  </div>

  <div class="mock-title">
    <div>
      <h1>{{ $greeting }}, {{ $firstName }}</h1>
      <p>{{ $pendingTotal }} reviews need a next step · {{ $needsAttention->count() }} teachers need help</p>
    </div>
    <time>SY {{ now()->format('Y') }}–{{ now()->addYear()->format('y') }} · {{ $todayStr }}</time>
  </div>

  <div class="mock-grid">
    <div class="mock-kpis">
      <div class="mock-kpi hot">
        <label>Scheduled</label>
        <div class="val">{{ $stats['scheduled'] + $stats['in_progress'] }}</div>
        <div class="delta mock-flat">{{ $stats['total_teachers'] }} teachers assigned to you</div>
      </div>
      <div class="mock-kpi">
        <label>Completed</label>
        <div class="val">{{ $stats['completed'] }} <small>/ {{ $stats['total_observations'] }}</small></div>
        <div class="delta mock-flat">{{ $completion }}% of this round finished</div>
      </div>
      <div class="mock-kpi">
        <label>Average teaching score</label>
        <div class="val">{{ number_format((float) $stats['average_score'], 1) }} <small>/ 7.0</small></div>
        <div class="delta {{ $trend > 0 ? 'mock-up' : ($trend < 0 ? 'mock-down' : 'mock-flat') }}">{{ $trend > 0 ? '▲' : ($trend < 0 ? '▼' : '●') }} {{ $trendLabel }} since last time</div>
      </div>
      <div class="mock-kpi">
        <label>Needs your action</label>
        <div class="val">{{ $pendingTotal }}</div>
        <div class="delta mock-flat">{{ $stats['stage_post_conference'] }} ready for final chat · {{ $stats['stage_pre_planning'] }} just starting</div>
      </div>
    </div>

    <div class="min-w-0">
      <section class="mock-panel" aria-label="Review progress">
        <div class="mock-panel-head">
          <h2>Current review · {{ $focusName }}</h2>
          <span class="hint">{{ $focus ? ($focusDone ? 'Teaching review · Finished' : 'Teaching review · Now: ' . ($currentStepTitle ?? 'Getting started')) : 'Nothing in progress — start a new review' }}</span>
          @if($focus)
            <a class="link" href="{{ route($continueRoute, $focus) }}">Continue review →</a>
          @endif
        </div>
        @if($focus)
          <div class="mock-steps">
            @foreach($steps as $s)
              @php
                $state = $focusStep > $s['n'] || $focusDone ? 'done' : ($focusStep === $s['n'] ? 'now' : '');
                $badge = $state === 'done' ? 'done' : ($state === 'now' ? 'now' : 'todo');
                $badgeLabel = $state === 'done' ? 'Done' : ($state === 'now' ? 'In progress' : 'Queued');
                $meta = match ($s['key']) {
                  'pre_observation_planning' => $focus->lesson_plan_reviewed_at
                    ? 'Checked · ' . $focus->lesson_plan_reviewed_at->format('M d, Y')
                    : (($planRecord || $focus->lesson_plan_path || $focus->teacher_confirmed_at)
                      ? 'Plan submitted · waiting for your check'
                      : ($state === 'now' ? 'Waiting for ' . $teacherFirst . '’s lesson plan' : ($state === 'done' ? 'Done · ' . ($focus->updated_at?->format('M d · H:i') ?? '') : ($plannedOn ? 'Planned · ' . $plannedOn : 'Not started yet')))),
                  'pre_conference' => $preRecord?->conference_date
                    ? 'Done · ' . $preRecord->conference_date->format('M d, Y')
                    : ($preRecord ? 'Notes saved · ' . ($preRecord->updated_at?->format('M d · H:i') ?? '') : ($state === 'done' ? 'Done · ' . ($focus->updated_at?->format('M d · H:i') ?? '') : ($plannedOn ? 'Planned · ' . $plannedOn : 'Starts after lesson planning'))),
                  'observation' => $ratingCount > 0
                    ? $ratingCount . ' indicators scored' . ($focus->overall_score !== null ? ' · score ' . number_format((float) $focus->overall_score, 1) : '')
                    : ($state === 'now' ? 'In progress · ' . ($plannedOn ?? 'no date set') . ' · ' . ($focus->subject ?? '—') : ($state === 'done' ? 'Done · ' . ($plannedOn ?? $focus->updated_at?->format('M d · H:i') ?? '') : ($plannedOn ? 'Planned · ' . $plannedOn : 'Starts after the short chat'))),
                  'post_conference' => $postRecord?->conference_date
                    ? 'Done · ' . $postRecord->conference_date->format('M d, Y')
                    : ($postRecord ? 'Notes saved · ' . ($postRecord->updated_at?->format('M d · H:i') ?? '') : ($focusDone ? 'Done · ' . ($focus->finalized_at?->format('M d · H:i') ?? $focus->updated_at?->format('M d · H:i') ?? '') : ($state === 'now' ? 'Ready when the classroom visit is scored' : ($plannedOn ? 'Planned · ' . $plannedOn : 'Starts after the classroom visit')))),
                  default => '',
                };
                $desc = match ($s['key']) {
                  'pre_observation_planning' => !empty($focusTopics)
                    ? 'Focus: ' . implode(', ', array_slice($focusTopics, 0, 3))
                    : (($planRecord || $focus->lesson_plan_path || $focus->teacher_confirmed_at)
                      ? 'Lesson plan from ' . $teacherFirst
                      : 'Waiting for ' . $teacherFirst . '’s lesson plan'),
                  'pre_conference' => $preRecord?->topic
                    ? 'About: ' . $preRecord->topic
                    : (($preRecord?->finalized_focus)
                      ? 'Agreed focus: ' . $preRecord->finalized_focus
                      : ($preRecord ? 'Chat notes saved' : 'A short chat with ' . $teacherFirst . ' before the visit')),
                  'observation' => $visitWhat ?? 'Classroom visit with ' . $teacherFirst,
                  'post_conference' => $postRecord?->prioritized_next_steps
                    ? 'Next: ' . \Illuminate\Support\Str::limit($postRecord->prioritized_next_steps, 90)
                    : ($postRecord ? 'Feedback notes saved' : 'Final feedback chat with ' . $teacherFirst),
                  default => '',
                };
              @endphp
              <div class="mock-step {{ $state }}">
                <div class="mock-step-num">{{ $state === 'done' ? '✓' : $s['n'] }}</div>
                <div>
                  <h3>{{ $s['n'] }} · {{ $s['title'] }}</h3>
                  <p>{{ $desc }}</p>
                  <div class="meta">{{ $meta }}</div>
                </div>
                <span class="mock-status {{ $badge }}">{{ $badgeLabel }}</span>
              </div>
            @endforeach
          </div>
        @else
          <div class="mock-empty">All caught up — nothing waiting for you. <a class="link" href="{{ route('supervisor.observations.create') }}">Start a review →</a></div>
        @endif
      </section>

      <section class="mock-panel" aria-label="Teachers and reviews">
        <div class="mock-panel-head">
          <h2>Teachers &amp; their reviews</h2>
          <span class="hint">{{ $schoolName ?? 'Your School' }} · grouped by teacher</span>
          <a class="link" href="{{ route('supervisor.teachers.index') }}">Review teachers →</a>
        </div>
        @if(($schoolGroups ?? collect())->isNotEmpty())
          <div>
            @foreach(($schoolGroups ?? []) as $i => $t)
              @php $lvl = ($attention[$t->id] ?? null); if ($lvl && ($lvl['level'] ?? 'ok') === 'ok') $lvl = null; @endphp
              <details class="mock-folder" @if($i < 2) open @endif>
                <summary>
                  <span class="caret">▸</span>
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" aria-hidden="true"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/></svg>
                  <span>{{ $t->user->name ?? 'Unassigned' }}</span>
                  <span class="hint">{{ $t->position_label ?? 'Teacher' }}</span>
                  <span class="spacer">
                    @if($lvl)<span class="mock-status {{ $lvl['level'] === 'high' ? 'now' : 'todo' }}" style="{{ $lvl['level'] === 'high' ? 'color:var(--m-rose);border-color:rgba(248,81,73,.35);background:rgba(248,81,73,.08)' : '' }}">{{ ucfirst($lvl['level']) }}</span>@endif
                    <span class="mock-status todo">{{ $t->observations->count() }} reviews</span>
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
                    <div class="mock-empty">No reviews yet for this teacher.</div>
                  @endforelse
                </div>
              </details>
            @endforeach
          </div>
        @else
          <div class="mock-empty">No teachers assigned to your school yet.</div>
        @endif
      </section>

      <section class="mock-panel" aria-label="Scores over time">
        <div class="mock-panel-head">
          <h2>Scores over time</h2>
          <span class="hint">{{ count($cotScores) }} with scores</span>
          <a class="link" href="{{ route('supervisor.reports.index') }}">Open reports →</a>
        </div>
        @if(count($cotScores) > 0)
          <div style="padding:14px 16px"><div style="position:relative;height:220px"><canvas id="cotChart"></canvas></div></div>
        @else
          <div class="mock-empty">No scored reviews yet — finish a classroom visit and add scores to see the trend.</div>
        @endif
      </section>
    </div>

    <aside class="mock-rail" aria-label="Helpful panels">
      <div class="mock-mod">
        <div class="mock-mod-head"><h3>At a glance</h3><span class="tick {{ $pendingTotal > 0 ? 'warn' : '' }}"></span></div>
        <div class="mock-mod-body">
          <div class="mock-insight"><div><b>Your workload</b><span>{{ $pendingTotal }} reviews waiting for you · {{ $completion }}% finished</span><div class="mock-bar"><i style="width:{{ $completion }}%"></i></div></div></div>
          <div class="mock-insight"><div><b>Ready for feedback</b><span>{{ $stats['stage_post_conference'] }} final chats ready for you to review</span></div></div>
          <div class="mock-insight"><div><b>Overall average</b><span>Average {{ number_format((float) $stats['average_score'], 1) }} · {{ $trendLabel }} since last time</span></div></div>
        </div>
      </div>
      <div class="mock-mod">
        <div class="mock-mod-head"><h3>Quick actions</h3></div>
        <div class="mock-mod-body">
          <a class="mock-act solid" href="{{ route('supervisor.observations.create') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>Start new review</a>
          <a class="mock-act" href="{{ route('supervisor.teachers.index') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>Teachers · {{ $stats['total_teachers'] }}</a>
          <a class="mock-act" href="{{ route('supervisor.career.monitor') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>Teacher growth</a>
          <a class="mock-act" href="{{ route('supervisor.feedback.center') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>Feedback center</a>
        </div>
      </div>
      <div class="mock-mod">
        <div class="mock-mod-head"><h3>Review details</h3><span class="tick"></span></div>
        <div class="mock-mod-body">
          <dl>
            <div class="mock-kv"><dt>Review</dt><dd>{{ $focus ? 'Review #' . $focus->id : '—' }}</dd></div>
            <div class="mock-kv"><dt>Step</dt><dd>{{ $focus ? ucwords(str_replace('_', ' ', $focus->stage ?? '')) : '—' }}</dd></div>
            <div class="mock-kv"><dt>Date</dt><dd>{{ $focus?->observation_date?->format('M d, Y') ?? '—' }}</dd></div>
            <div class="mock-kv"><dt>Status</dt><dd>{{ $focus ? ucwords(str_replace('_', ' ', $focus->status)) : '—' }}</dd></div>
            <div class="mock-kv"><dt>Score</dt><dd>{{ $focus?->overall_score !== null ? number_format((float) $focus->overall_score, 1) . ' / 7.0' . ($ratingCount > 0 ? ' · ' . $ratingCount . ' scored' : '') : 'Not scored yet' }}</dd></div>
            <div class="mock-kv"><dt>Teacher</dt><dd>{{ $focusName }}</dd></div>
          </dl>
        </div>
      </div>
      <div class="mock-mod">
        <div class="mock-mod-head"><h3>Up next</h3></div>
        <div class="mock-mod-body">
          <div class="mock-tags">
            @forelse($todoObservations->take(4) as $ob)
              <span class="mock-tag">{{ $ob->observee?->user?->name ?? 'Unknown' }}</span>
            @empty
              <span class="mock-tag dim">All done</span>
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
