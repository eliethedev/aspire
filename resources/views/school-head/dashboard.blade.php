@extends('layouts.teacher')
@include('partials.dashboard.mock-styles')
@section('title', 'School Head Dashboard')
@section('content')
@php
  $schoolName = $user->school?->name ?? ($schoolHead?->school?->name ?? 'Your School');
  $syLabel = $schoolYear ?? (now()->format('Y') . '-' . (now()->year + 1));
  $firstName = explode(' ', trim($user->name ?? ''))[0] ?? 'School Head';
  $cycleLabel = 'Term ' . ($quarter ?? 1) . ' · SY ' . $syLabel;
  $completion = ($quickStats['total'] ?? 0) > 0 ? round((($quickStats['completed'] ?? 0) / $quickStats['total']) * 100) : 0;
  $hour = (int) now()->format('G');
  $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
@endphp
<div class="mock-wrap max-w-7xl mx-auto px-1 py-1">

  <div class="mock-topbar">
    <div class="mock-crumbs">School Head <span>/</span> <b>Dashboard</b></div>
    <span class="mock-pill"><span class="pulse"></span>{{ $quickStats['teacher_count'] }} teachers</span>
    @if(($attention['total'] ?? 0) > 0)
      <span class="mock-pill amber"><span class="pulse"></span>{{ $attention['total'] }} need review</span>
    @endif
    <div class="mock-actions">
      <a class="mock-btn" href="{{ route('school-head.observations.create') }}">＋ Schedule a review</a>
      <a class="mock-btn primary" href="{{ route('school-head.reports.index') }}">View reports</a>
    </div>
  </div>

  <div class="mock-title">
    <div>
      <h1>{{ $greeting }}, {{ $firstName }}</h1>
      <p>{{ $schoolName }} · {{ $cycleLabel }} · {{ $completion }}% finished</p>
    </div>
    <time>{{ now()->format('l, F j, Y') }}</time>
  </div>

  <div class="mock-kpis">
    <div class="mock-kpi hot">
      <label>Teachers</label>
      <div class="val">{{ $quickStats['teacher_count'] }}</div>
      <div class="delta mock-flat">{{ $schoolName }}</div>
    </div>
    <div class="mock-kpi">
      <label>Observations</label>
      <div class="val">{{ $quickStats['total'] }}</div>
      <div class="delta mock-flat">{{ $quickStats['completed'] }} done · {{ $quickStats['in_progress'] }} active</div>
    </div>
    <div class="mock-kpi">
      <label>Average teaching score</label>
      <div class="val">{{ number_format((float) ($quickStats['avg_score'] ?? 0), 1) }} <small>/ 7.0</small></div>
      <div class="delta {{ ($quickStats['trend'] ?? 0) > 0 ? 'mock-up' : ((($quickStats['trend'] ?? 0) < 0) ? 'mock-down' : 'mock-flat') }}">{{ ($quickStats['trend'] ?? 0) > 0 ? '▲ +' : '' }}{{ number_format((float) ($quickStats['trend'] ?? 0), 1) }} since last time</div>
    </div>
    <div class="mock-kpi">
      <label>Waiting on you</label>
      <div class="val">{{ $attention['total'] ?? 0 }}</div>
      <div class="delta mock-flat">Plans to check · replies · signatures</div>
    </div>
  </div>

  <div class="mock-grid">
    <div class="min-w-0">
      @if(($attention['total'] ?? 0) > 0)
      <section class="mock-panel" aria-label="Needs your attention">
        <div class="mock-panel-head">
          <h2>Needs your attention</h2>
          <span class="hint">{{ $attention['total'] }} waiting across plans, replies and signatures</span>
        </div>
        <div class="mock-steps">
          @foreach($attention['items'] as $i => $item)
            <div class="mock-step {{ $item['count'] > 0 ? 'now' : 'done' }}">
              <div class="mock-step-num">{{ $item['count'] > 0 ? $i + 1 : '✓' }}</div>
              <div>
                <h3>{{ $item['label'] }} · {{ $item['count'] }}</h3>
                <p>{{ $item['hint'] }}</p>
              </div>
              @if(!empty($item['route']))<a class="mock-btn" href="{{ $item['route'] }}">Review →</a>@else<span class="mock-status done">Clear</span>@endif
            </div>
          @endforeach
        </div>
      </section>
      @endif

      <section class="mock-panel" aria-label="Teachers and reviews">
        <div class="mock-panel-head">
          <h2>Teachers &amp; their reviews</h2>
          <span class="hint">{{ $schoolName }} · {{ $cycleLabel }}</span>
          <a class="link" href="{{ route('school-head.observations.index') }}">All observations →</a>
        </div>
        @if($teacherFolders->isNotEmpty())
          <div>
            @foreach($teacherFolders as $i => $row)
              @php
                $latestScore = $row->observations->whereNotNull('overall_score')->sortByDesc('observation_date')->first()?->overall_score;
                $tone = $latestScore === null ? 'lo' : ($latestScore >= 4.5 ? 'hi' : ($latestScore >= 3.5 ? 'mid' : 'lo'));
              @endphp
              <details class="mock-folder" @if($i < 2) open @endif>
                <summary>
                  <span class="caret">▸</span>
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" aria-hidden="true"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/></svg>
                  <span>{{ $row->user?->name ?? 'Unassigned' }}</span>
                  <span class="spacer">
                    @if($latestScore !== null)<span class="mock-score {{ $tone }}">{{ number_format($latestScore, 1) }}</span>@endif
                    <span class="mock-status todo">{{ $row->observations->count() }} reviews</span>
                  </span>
                </summary>
                <div>
                  @forelse($row->observations as $ob)
                    @php $live = in_array($ob->status, ['in_progress', 'scheduled']); @endphp
                    <a class="mock-file {{ $live ? 'live' : '' }}" href="{{ route('school-head.observations.show', $ob) }}">
                      <span class="mock-fdot"></span>
                      <span class="mock-fmain"><b>{{ $ob->subject ?? 'Observation' }} · {{ $ob->observation_date?->format('M d, Y') ?? 'No date' }}</b><span>{{ ucwords(str_replace('_', ' ', $ob->stage ?? '')) }}</span></span>
                      <span class="mock-fright"><span class="mock-score {{ $ob->overall_score !== null ? ($ob->overall_score >= 4.5 ? 'hi' : ($ob->overall_score >= 3.5 ? 'mid' : 'lo')) : 'lo' }}">{{ $ob->overall_score !== null ? number_format((float) $ob->overall_score, 1) : '—' }}</span><span class="mock-status {{ $ob->status === 'completed' ? 'done' : ($ob->status === 'cancelled' ? 'todo' : 'now') }}">{{ ucwords(str_replace('_', ' ', $ob->status)) }}</span></span>
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
        @if(count($cotTrend) > 0)
          <div style="padding:14px 16px;border-top:1px solid var(--m-line-soft)"><div style="position:relative;height:180px"><canvas id="cotScoreChart"></canvas></div></div>
        @endif
      </section>
    </div>

    <aside class="mock-rail" aria-label="Helpful panels">
      <div class="mock-mod">
        <div class="mock-mod-head"><h3>At a glance</h3><span class="tick {{ ($attention['total'] ?? 0) > 0 ? 'warn' : '' }}"></span></div>
        <div class="mock-mod-body">
          <div class="mock-insight"><div><b>Completion</b><span>{{ $quickStats['completed'] }} of {{ $quickStats['total'] }} reviews finished</span><div class="mock-bar"><i style="width:{{ $completion }}%"></i></div></div></div>
          <div class="mock-insight"><div><b>Average score</b><span>{{ number_format((float) ($quickStats['avg_score'] ?? 0), 1) }} · {{ number_format((float) ($quickStats['trend'] ?? 0), 1) }} since last time</span></div></div>
          <div class="mock-insight"><div><b>Active coaching</b><span>{{ count($coaching['items'] ?? []) }} agreements to follow</span></div></div>
        </div>
      </div>
      <div class="mock-mod">
        <div class="mock-mod-head"><h3>Quick actions</h3></div>
        <div class="mock-mod-body">
          <a class="mock-act solid" href="{{ route('school-head.observations.create') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>Schedule a review</a>
          <a class="mock-act" href="{{ route('school-head.lesson-plans.index') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>Review lesson plans</a>
          <a class="mock-act" href="{{ route('school-head.teachers.index') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>Teachers</a>
          <a class="mock-act" href="{{ route('school-head.coaching.index') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>Coaching</a>
        </div>
      </div>
      <div class="mock-mod">
        <div class="mock-mod-head"><h3>Lesson plans</h3><span class="tick {{ $dll['for_checking'] > 0 ? 'warn' : '' }}"></span></div>
        <div class="mock-mod-body">
          <div class="mock-kv"><dt>Submitted</dt><dd>{{ $dll['submitted'] }}</dd></div>
          <div class="mock-kv"><dt>Not yet submitted</dt><dd>{{ $dll['not_submitted'] }}</dd></div>
          <div class="mock-kv"><dt>For checking</dt><dd>{{ $dll['for_checking'] }}</dd></div>
          <a class="mock-act" style="margin-top:10px" href="{{ route('school-head.lesson-plans.index') }}">Review plans →</a>
        </div>
      </div>
      <div class="mock-mod">
        <div class="mock-mod-head"><h3>Coaching</h3><span class="tick"></span></div>
        <div class="mock-mod-body">
          @forelse($coaching['items'] ?? [] as $ag)
            <div class="mock-kv"><dt>{{ $ag['teacher'] }}</dt><dd>{{ ucfirst($ag['status']) }} · {{ $ag['signature_state'] === 'signed' ? 'Signed' : 'For signing' }}</dd></div>
          @empty
            <p style="font-size:12px;color:var(--m-muted)">No coaching agreements yet.</p>
          @endforelse
          <a class="mock-act" style="margin-top:10px" href="{{ route('school-head.coaching.index') }}">All agreements →</a>
        </div>
      </div>
      <div class="mock-mod">
        <div class="mock-mod-head"><h3>School details</h3></div>
        <div class="mock-mod-body">
          <dl>
            <div class="mock-kv"><dt>School</dt><dd>{{ $schoolName }}</dd></div>
            <div class="mock-kv"><dt>Cycle</dt><dd>{{ $cycleLabel }}</dd></div>
            <div class="mock-kv"><dt>Teachers</dt><dd>{{ $quickStats['teacher_count'] }}</dd></div>
            <div class="mock-kv"><dt>Completion</dt><dd>{{ $completion }}%</dd></div>
          </dl>
        </div>
      </div>
    </aside>
  </div>
</div>
@if(count($cotTrend) > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>(function(){const el=document.getElementById('cotScoreChart');if(!el)return;const dark=document.documentElement.classList.contains('dark');new Chart(el.getContext('2d'),{type:'line',data:{labels:@json($cotLabels),datasets:[{data:@json($cotTrend),borderColor:'#2f81f7',backgroundColor:'rgba(47,129,247,.10)',borderWidth:2,tension:.35,fill:true,pointRadius:4,pointBackgroundColor:'#2f81f7'}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{min:1,max:7,ticks:{stepSize:1,color:dark?'#8b949e':'#64748b'}},x:{ticks:{color:dark?'#8b949e':'#64748b'},grid:{display:false}}}}});})();</script>
@endif
@endsection
