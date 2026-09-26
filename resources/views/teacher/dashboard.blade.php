@extends('layouts.teacher')
@section('title','Teacher Dashboard')
@include('partials.dashboard.mock-styles')
@section('content')
@php
  $user = Auth::user();
  $completion = $stats['total'] > 0 ? round(($stats['completed'] / $stats['total']) * 100) : 0;
  $hour = (int) now()->format('G');
  $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
  $firstName = explode(' ', trim($user->name ?? ''))[0] ?? 'Teacher';
  $focus = $focusObservation ?? $nextObservation ?? $recentObservation;
  $trendLabel = ($trend > 0 ? '+' : '') . number_format((float) $trend, 1);
  $stageKeys = ['pre_observation_planning', 'pre_conference', 'observation', 'post_conference'];
  $stageTitles = ['Lesson planning', 'Chat before class', 'Classroom visit', 'Chat after class'];
  $focusStageIdx = $focus ? array_search($focus->stage, $stageKeys) : false;
  $focusStepTitle = ($focusStageIdx !== false && $focusStageIdx !== null) ? $stageTitles[$focusStageIdx] : null;
  $activeObs = $observations->whereIn('status', ['scheduled', 'in_progress'])->values();
  $doneObs = $observations->where('status', 'completed')->values();
@endphp
<div class="mock-wrap max-w-7xl mx-auto px-1 py-1">

  <div class="mock-topbar">
    <div class="mock-crumbs">Teacher <span>/</span> <b>Dashboard</b></div>
    <span class="mock-pill"><span class="pulse"></span>{{ $completion }}% portfolio complete</span>
    @if($stats['pending_confirmation'] > 0)
      <span class="mock-pill amber"><span class="pulse"></span>{{ $stats['pending_confirmation'] }} awaiting confirmation</span>
    @endif
    <div class="mock-actions">
      <a class="mock-btn" href="{{ route('teacher.observations.index') }}">My observations</a>
      <a class="mock-btn primary" href="{{ route('teacher.feedback.index') }}">View feedback</a>
    </div>
  </div>

  <div class="mock-title">
    <div>
      <h1>{{ $greeting }}, {{ $firstName }} — keep growing</h1>
      <p>{{ $stats['completed'] }} completed · {{ $stats['scheduled'] }} scheduled · avg {{ number_format((float) $stats['average_cot_score'], 1) }}</p>
    </div>
    <time>{{ now()->format('l, F j, Y') }} · Teacher Portfolio</time>
  </div>

  <div class="mock-kpis">
    <div class="mock-kpi hot">
      <label>My reviews</label>
      <div class="val">{{ $stats['total'] }}</div>
      <div class="delta mock-flat">{{ $stats['completed'] }} done · {{ $stats['in_progress'] }} active</div>
    </div>
    <div class="mock-kpi">
      <label>My average score</label>
      <div class="val">{{ number_format((float) $stats['average_cot_score'], 1) }} <small>/ 7.0</small></div>
      <div class="delta {{ $trend > 0 ? 'mock-up' : ($trend < 0 ? 'mock-down' : 'mock-flat') }}">{{ $trend != 0 ? ($trend > 0 ? '▲ ' : '▼ ') . $trendLabel . ' since last time' : '● No trend yet' }}</div>
    </div>
    <div class="mock-kpi">
      <label>Completed</label>
      <div class="val">{{ $stats['completed'] }} <small>/ {{ $stats['total'] }}</small></div>
      <div class="delta mock-flat">{{ $completion }}% portfolio progress</div>
    </div>
    <div class="mock-kpi">
      <label>Scheduled</label>
      <div class="val">{{ $stats['scheduled'] }}</div>
      <div class="delta {{ $stats['pending_confirmation'] > 0 ? 'mock-down' : 'mock-flat' }}">{{ $stats['pending_confirmation'] > 0 ? $stats['pending_confirmation'] . ' need confirmation' : 'All caught up' }}</div>
    </div>
  </div>

  <div class="mock-grid">
    <div class="min-w-0">
      <section class="mock-panel" aria-label="My observation workflow">
        <div class="mock-panel-head">
          <h2>My review · {{ $focus ? ($focus->observation_date?->format('M d, Y') ?? 'No date set') : 'Nothing yet' }}</h2>
          <span class="hint">{{ $focus ? ($focusStepTitle ?? 'Getting started') . ' · ' . ($focus->observer?->name ?? '—') : 'Your growth space' }}</span>
          @if($focus)<a class="link" href="{{ route('teacher.observations.show', $focus) }}">View details →</a>@endif
        </div>
        @if($focus)
          <div class="mock-steps">
            @foreach($stageKeys as $i => $key)
              @php
                $done = $stageStatus[$key]['done'] ?? false;
                $isCurrent = !$done && ($i === 0 || ($stageStatus[$stageKeys[$i - 1]]['done'] ?? false));
                $state = $done ? 'done' : ($isCurrent ? 'now' : '');
              @endphp
              <div class="mock-step {{ $state }}">
                <div class="mock-step-num">{{ $done ? '✓' : $i + 1 }}</div>
                <div>
                  <h3>{{ $i + 1 }} · {{ $stageTitles[$i] }}</h3>
                  <p>{{ $stageStatus[$key]['label'] ?? $stageTitles[$i] }}</p>
                  <div class="meta">{{ $done ? 'completed' : ($isCurrent ? 'in progress · ' . ($focus->subject ?? '—') : 'starts after the previous step is done') }}</div>
                </div>
                <span class="mock-status {{ $done ? 'done' : ($isCurrent ? 'now' : 'todo') }}">{{ $done ? 'Done' : ($isCurrent ? 'In progress' : 'Queued') }}</span>
              </div>
            @endforeach
          </div>
        @else
          <div class="mock-empty">No review scheduled yet. Your supervisor will schedule your first review here.</div>
        @endif
      </section>

      <section class="mock-panel" aria-label="My reviews">
        <div class="mock-panel-head">
          <h2>My reviews</h2>
          <span class="hint">grouped by progress</span>
          <a class="link" href="{{ route('teacher.observations.index') }}">See all reviews →</a>
        </div>
        @if($observations->isNotEmpty())
          <div>
            <details class="mock-folder" open>
              <summary>
                <span class="caret">▸</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" aria-hidden="true"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/></svg>
                <span>Active &amp; scheduled</span>
                <span class="spacer"><span class="mock-status {{ $activeObs->isNotEmpty() ? 'now' : 'todo' }}">{{ $activeObs->count() }} reviews</span></span>
              </summary>
              <div>
                @forelse($activeObs as $ob)
                  @php $live = $ob->status === 'in_progress'; @endphp
                  <a class="mock-file {{ $live ? 'live' : '' }}" href="{{ route('teacher.observations.show', $ob) }}">
                    <span class="mock-fdot"></span>
                    <span class="mock-fmain"><b>{{ $ob->subject ?? 'Review' }} · {{ $ob->observation_date?->format('M d, Y') ?? 'No date' }}</b><span>{{ $ob->observer?->name ?? '—' }}</span></span>
                    <span class="mock-fright"><span class="mock-status now">{{ ucwords(str_replace('_', ' ', $ob->status)) }}</span></span>
                  </a>
                @empty
                  <div class="mock-empty">Nothing scheduled — new reviews appear here.</div>
                @endforelse
              </div>
            </details>
            <details class="mock-folder" open>
              <summary>
                <span class="caret">▸</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" aria-hidden="true"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/></svg>
                <span>Completed</span>
                <span class="spacer"><span class="mock-status done">{{ $doneObs->count() }} reviews</span></span>
              </summary>
              <div>
                @forelse($doneObs as $ob)
                  @php
                    $score = $ob->overall_score !== null ? (float) $ob->overall_score : null;
                    $tone = $score === null ? 'lo' : ($score >= 4.5 ? 'hi' : ($score >= 3.5 ? 'mid' : 'lo'));
                  @endphp
                  <a class="mock-file" href="{{ route('teacher.observations.show', $ob) }}">
                    <span class="mock-fdot"></span>
                    <span class="mock-fmain"><b>{{ $ob->subject ?? 'Review' }} · {{ $ob->observation_date?->format('M d, Y') ?? 'No date' }}</b><span>{{ $ob->observer?->name ?? '—' }}</span></span>
                    <span class="mock-fright"><span class="mock-score {{ $tone }}">{{ $score !== null ? number_format($score, 1) : '—' }}</span><span class="mock-status done">Finalized</span></span>
                  </a>
                @empty
                  <div class="mock-empty">No finished reviews yet.</div>
                @endforelse
              </div>
            </details>
          </div>
        @else
          <div class="mock-empty">No reviews yet. Your supervisor will schedule your first review here.</div>
        @endif
      </section>

      <section class="mock-panel" aria-label="Growth trend">
        <div class="mock-panel-head">
          <h2>Growth trend</h2>
          <span class="hint">{{ count($cotScores) }} reviews with scores</span>
          <a class="link" href="{{ route('teacher.analytics') }}">Performance analytics →</a>
        </div>
        @if(count($cotScores) > 0)
          <div style="padding:14px 16px"><div style="position:relative;height:220px"><canvas id="growthChart"></canvas></div></div>
        @else
          <div class="mock-empty">Complete more observations to see your growth over time.</div>
        @endif
      </section>

      <section class="mock-panel" aria-label="Feedback for you">
        <div class="mock-panel-head">
          <h2>Feedback for you</h2>
          <span class="hint">Latest guidance from your observer</span>
          <a class="link" href="{{ route('teacher.feedback.index') }}">All feedback →</a>
        </div>
        @if($recentFeedback)
          @if($recentFeedback->feedback)<div style="padding:12px 16px 0"><div class="mock-mod" style="background:var(--m-accent-soft)"><div class="mock-mod-body"><b style="font-size:12px">Feedback</b><p style="font-size:12.5px;color:var(--m-text)">{{ Str::limit($recentFeedback->feedback, 260) }}</p></div></div></div>@endif
          @if($recentFeedback->supervisor_notes)<div style="padding:12px 16px"><div class="mock-mod"><div class="mock-mod-body"><b style="font-size:12px">Next steps</b><p style="font-size:12.5px;color:var(--m-muted)">{{ Str::limit($recentFeedback->supervisor_notes, 260) }}</p></div></div></div>@endif
        @else
          <div class="mock-empty">No feedback yet — it appears here after your final chat.</div>
        @endif
      </section>
    </div>

    <aside class="mock-rail" aria-label="Helpful panels">
      <div class="mock-mod">
        <div class="mock-mod-head"><h3>At a glance</h3><span class="tick {{ $stats['pending_confirmation'] > 0 ? 'warn' : '' }}"></span></div>
        <div class="mock-mod-body">
          <div class="mock-insight"><div><b>Portfolio progress</b><span>{{ $stats['completed'] }} of {{ $stats['total'] }} reviews finished</span><div class="mock-bar"><i style="width:{{ $completion }}%"></i></div></div></div>
          <div class="mock-insight"><div><b>Average rating</b><span>{{ number_format((float) $stats['average_cot_score'], 1) }} / 7.0 · {{ $trendLabel }} since last time</span></div></div>
          <div class="mock-insight"><div><b>Confirmations</b><span>{{ $stats['pending_confirmation'] }} scheduled reviews need your reply</span></div></div>
        </div>
      </div>
      <div class="mock-mod">
        <div class="mock-mod-head"><h3>Quick actions</h3></div>
        <div class="mock-mod-body">
          <a class="mock-act solid" href="{{ route('teacher.observations.index') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>View observations</a>
          <a class="mock-act" href="{{ route('teacher.feedback.index') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>Feedback &amp; coaching</a>
          <a class="mock-act" href="{{ route('teacher.coaching.index') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>Improvement plan</a>
          <a class="mock-act" href="{{ route('teacher.profile.edit') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>Update profile</a>
        </div>
      </div>
      <div class="mock-mod">
        <div class="mock-mod-head"><h3>Review details</h3><span class="tick"></span></div>
        <div class="mock-mod-body">
          <dl>
            <div class="mock-kv"><dt>Review</dt><dd>{{ $focus ? 'Review #' . $focus->id : '—' }}</dd></div>
            <div class="mock-kv"><dt>Step</dt><dd>{{ $focus ? ($focusStepTitle ?? '—') : '—' }}</dd></div>
            <div class="mock-kv"><dt>Date</dt><dd>{{ $focus?->observation_date?->format('M d, Y') ?? '—' }}</dd></div>
            <div class="mock-kv"><dt>Observer</dt><dd>{{ $focus?->observer?->name ?? '—' }}</dd></div>
            <div class="mock-kv"><dt>Subject</dt><dd>{{ $focus->subject ?? '—' }}</dd></div>
          </dl>
        </div>
      </div>
      <div class="mock-mod">
        <div class="mock-mod-head"><h3>Latest review</h3></div>
        <div class="mock-mod-body">
          @if($recentObservation)
            <div class="mock-kv"><dt>Date</dt><dd>{{ $recentObservation->observation_date?->format('M d, Y') }}</dd></div>
            <div class="mock-kv"><dt>Score</dt><dd>{{ $recentObservation->overall_score !== null ? number_format((float) $recentObservation->overall_score, 1) : '—' }}</dd></div>
            <div class="mock-kv"><dt>Observer</dt><dd>{{ $recentObservation->observer?->name ?? '—' }}</dd></div>
            <a class="mock-act" style="margin-top:10px" href="{{ route('teacher.observations.show', $recentObservation) }}">Open result →</a>
          @else
            <p style="font-size:12px;color:var(--m-muted)">No scored review yet.</p>
          @endif
          @if($nextObservation)
            <div class="mock-kv"><dt>Up next</dt><dd>{{ $nextObservation->observation_date?->format('M d, Y') }}</dd></div>
          @endif
        </div>
      </div>
    </aside>
  </div>
</div>
@if(count($cotScores) > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>(function(){const el=document.getElementById('growthChart');if(!el)return;const dark=document.documentElement.classList.contains('dark');new Chart(el.getContext('2d'),{type:'line',data:{labels:{!! json_encode($cotLabels) !!},datasets:[{data:{!! json_encode($cotScores) !!},borderColor:'#2f81f7',backgroundColor:'rgba(47,129,247,.10)',fill:true,tension:.38,pointRadius:4,borderWidth:2}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{min:1,max:7,ticks:{stepSize:1,color:dark?'#8b949e':'#64748b'}},x:{grid:{display:false},ticks:{color:dark?'#8b949e':'#64748b'}}}}});})();</script>
@endif
@endsection
