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
  $stageTitles = ['Pre-observation planning', 'Pre-observation conference', 'Classroom observation', 'Post-observation conference'];
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
      <label>Total cycles</label>
      <div class="val">{{ $stats['total'] }}</div>
      <div class="delta mock-flat">{{ $stats['completed'] }} done · {{ $stats['in_progress'] }} active</div>
    </div>
    <div class="mock-kpi">
      <label>Avg COT</label>
      <div class="val">{{ number_format((float) $stats['average_cot_score'], 1) }} <small>/ 7.0</small></div>
      <div class="delta {{ $trend > 0 ? 'mock-up' : ($trend < 0 ? 'mock-down' : 'mock-flat') }}">{{ $trend != 0 ? ($trend > 0 ? '▲ ' : '▼ ') . $trendLabel : '● No trend yet' }}</div>
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
          <h2>My cycle · {{ $focus ? ($focus->observation_date?->format('M d, Y') ?? 'No date set') : 'No cycle yet' }}</h2>
          <span class="hint">{{ $focus ? ucwords(str_replace('_', ' ', $focus->stage ?? '')) . ' · ' . ($focus->observer?->name ?? '—') : 'Your growth workspace' }}</span>
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
                  <div class="meta">{{ $done ? 'completed' : ($isCurrent ? 'in progress · ' . ($focus->subject ?? '—') : 'locked until previous step is done') }}</div>
                </div>
                <span class="mock-status {{ $done ? 'done' : ($isCurrent ? 'now' : 'todo') }}">{{ $done ? 'Done' : ($isCurrent ? 'In progress' : 'Queued') }}</span>
              </div>
            @endforeach
          </div>
        @else
          <div class="mock-empty">No observation scheduled yet. Your supervisor will schedule your first COT cycle here.</div>
        @endif
      </section>

      <section class="mock-panel" aria-label="Observation groups">
        <div class="mock-panel-head">
          <h2>Observation groups</h2>
          <span class="hint">folders with your observation files</span>
          <a class="link" href="{{ route('teacher.observations.index') }}">All observations →</a>
        </div>
        @if($observations->isNotEmpty())
          <div>
            <details class="mock-folder" open>
              <summary>
                <span class="caret">▸</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#58a6ff" stroke-width="2" aria-hidden="true"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/></svg>
                <span>Active &amp; scheduled</span>
                <span class="spacer"><span class="mock-status {{ $activeObs->isNotEmpty() ? 'now' : 'todo' }}">{{ $activeObs->count() }} files</span></span>
              </summary>
              <div>
                @forelse($activeObs as $ob)
                  @php $live = $ob->status === 'in_progress'; @endphp
                  <a class="mock-file {{ $live ? 'live' : '' }}" href="{{ route('teacher.observations.show', $ob) }}">
                    <span class="mock-fdot"></span>
                    <span class="mock-fmain"><b>{{ $ob->subject ?? 'Observation' }} · {{ $ob->observation_date?->format('M d, Y') ?? 'No date' }}</b><span>{{ $ob->observer?->name ?? '—' }}</span></span>
                    <span class="mock-fright"><span class="mock-status now">{{ ucwords(str_replace('_', ' ', $ob->status)) }}</span></span>
                  </a>
                @empty
                  <div class="mock-empty">Nothing scheduled — new cycles appear here.</div>
                @endforelse
              </div>
            </details>
            <details class="mock-folder" open>
              <summary>
                <span class="caret">▸</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#58a6ff" stroke-width="2" aria-hidden="true"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/></svg>
                <span>Completed</span>
                <span class="spacer"><span class="mock-status done">{{ $doneObs->count() }} files</span></span>
              </summary>
              <div>
                @forelse($doneObs as $ob)
                  @php
                    $score = $ob->overall_score !== null ? (float) $ob->overall_score : null;
                    $tone = $score === null ? 'lo' : ($score >= 4.5 ? 'hi' : ($score >= 3.5 ? 'mid' : 'lo'));
                  @endphp
                  <a class="mock-file" href="{{ route('teacher.observations.show', $ob) }}">
                    <span class="mock-fdot"></span>
                    <span class="mock-fmain"><b>{{ $ob->subject ?? 'Observation' }} · {{ $ob->observation_date?->format('M d, Y') ?? 'No date' }}</b><span>{{ $ob->observer?->name ?? '—' }}</span></span>
                    <span class="mock-fright"><span class="mock-score {{ $tone }}">{{ $score !== null ? number_format($score, 1) : '—' }}</span><span class="mock-status done">Finalized</span></span>
                  </a>
                @empty
                  <div class="mock-empty">No completed cycles yet.</div>
                @endforelse
              </div>
            </details>
          </div>
        @else
          <div class="mock-empty">No observation files yet. Your supervisor will schedule your first COT cycle here.</div>
        @endif
      </section>

      <section class="mock-panel" aria-label="Growth trend">
        <div class="mock-panel-head">
          <h2>Growth trend</h2>
          <span class="hint">{{ count($cotScores) }} scored observations</span>
          <a class="link" href="{{ route('teacher.analytics') }}">Performance analytics →</a>
        </div>
        @if(count($cotScores) > 0)
          <div style="padding:14px 16px"><div style="position:relative;height:220px"><canvas id="growthChart"></canvas></div></div>
        @else
          <div class="mock-empty">Complete more observations to see your growth over time.</div>
        @endif
      </section>

      <section class="mock-panel" aria-label="Post-conference feedback">
        <div class="mock-panel-head">
          <h2>Post-conference feedback</h2>
          <span class="hint">Latest guidance from your observer</span>
          <a class="link" href="{{ route('teacher.feedback.index') }}">All feedback →</a>
        </div>
        @if($recentFeedback)
          @if($recentFeedback->feedback)<div style="padding:12px 16px 0"><div class="mock-mod" style="background:var(--m-accent-soft)"><div class="mock-mod-body"><b style="font-size:12px">Feedback</b><p style="font-size:12.5px;color:var(--m-text)">{{ Str::limit($recentFeedback->feedback, 260) }}</p></div></div></div>@endif
          @if($recentFeedback->supervisor_notes)<div style="padding:12px 16px"><div class="mock-mod"><div class="mock-mod-body"><b style="font-size:12px">Next steps</b><p style="font-size:12.5px;color:var(--m-muted)">{{ Str::limit($recentFeedback->supervisor_notes, 260) }}</p></div></div></div>@endif
        @else
          <div class="mock-empty">No feedback yet — it appears here after your post-conference.</div>
        @endif
      </section>
    </div>

    <aside class="mock-rail" aria-label="Contextual utilities">
      <div class="mock-mod">
        <div class="mock-mod-head"><h3>System insights</h3><span class="tick {{ $stats['pending_confirmation'] > 0 ? 'warn' : '' }}"></span></div>
        <div class="mock-mod-body">
          <div class="mock-insight"><div><b>Portfolio progress</b><span>{{ $stats['completed'] }} of {{ $stats['total'] }} cycles complete</span><div class="mock-bar"><i style="width:{{ $completion }}%"></i></div></div></div>
          <div class="mock-insight"><div><b>Average rating</b><span>{{ number_format((float) $stats['average_cot_score'], 1) }} / 7.0 · {{ $trendLabel }} vs earlier</span></div></div>
          <div class="mock-insight"><div><b>Confirmations</b><span>{{ $stats['pending_confirmation'] }} scheduled observations need your confirmation</span></div></div>
        </div>
      </div>
      <div class="mock-mod">
        <div class="mock-mod-head"><h3>Quick actions</h3></div>
        <div class="mock-mod-body">
          <a class="mock-act solid" href="{{ route('teacher.observations.index') }}">👁 View observations</a>
          <a class="mock-act" href="{{ route('teacher.feedback.index') }}">⭐ Feedback &amp; coaching</a>
          <a class="mock-act" href="{{ route('teacher.coaching.index') }}">📋 Improvement plan</a>
          <a class="mock-act" href="{{ route('teacher.profile.edit') }}">👤 Update profile</a>
        </div>
      </div>
      <div class="mock-mod">
        <div class="mock-mod-head"><h3>Observation metadata</h3><span class="tick"></span></div>
        <div class="mock-mod-body">
          <dl>
            <div class="mock-kv"><dt>Focus cycle</dt><dd>{{ $focus ? '#OBS-' . $focus->id : '—' }}</dd></div>
            <div class="mock-kv"><dt>Stage</dt><dd>{{ $focus ? ucwords(str_replace('_', ' ', $focus->stage ?? '')) : '—' }}</dd></div>
            <div class="mock-kv"><dt>Date</dt><dd>{{ $focus?->observation_date?->format('M d, Y') ?? '—' }}</dd></div>
            <div class="mock-kv"><dt>Observer</dt><dd>{{ $focus?->observer?->name ?? '—' }}</dd></div>
            <div class="mock-kv"><dt>Subject</dt><dd>{{ $focus->subject ?? '—' }}</dd></div>
          </dl>
        </div>
      </div>
      <div class="mock-mod">
        <div class="mock-mod-head"><h3>Recent observation</h3></div>
        <div class="mock-mod-body">
          @if($recentObservation)
            <div class="mock-kv"><dt>Date</dt><dd>{{ $recentObservation->observation_date?->format('M d, Y') }}</dd></div>
            <div class="mock-kv"><dt>Score</dt><dd>{{ $recentObservation->overall_score !== null ? number_format((float) $recentObservation->overall_score, 1) : '—' }}</dd></div>
            <div class="mock-kv"><dt>Observer</dt><dd>{{ $recentObservation->observer?->name ?? '—' }}</dd></div>
            <a class="mock-act" style="margin-top:10px" href="{{ route('teacher.observations.show', $recentObservation) }}">Open result →</a>
          @else
            <p style="font-size:12px;color:var(--m-muted)">No scored observation yet.</p>
          @endif
          @if($nextObservation)
            <div class="mock-kv"><dt>Next</dt><dd>{{ $nextObservation->observation_date?->format('M d, Y') }}</dd></div>
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
