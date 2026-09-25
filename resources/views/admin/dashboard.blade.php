@extends('layouts.admin')
@include('partials.dashboard.mock-styles')
@section('title', 'Admin Dashboard')
@section('content')
@php
  $user = Auth::user();
  $firstName = explode(' ', trim($user->name ?? ''))[0] ?? 'Admin';
  $hour = (int) now()->format('G');
  $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
  $scaleMax = $performance['score_scale_max'] ?? 8;
  $avgScore = (float) ($performance['average_cot_score'] ?? 0);
  $trend = $performance['teacher_growth_trend'] ?? 'stable';
  $bands = $charts['ratingBands'] ?? ['labels' => [], 'counts' => []];
  $bandTotal = array_sum($bands['counts'] ?? []);
  $reviewQueue = ($stats['pending_cots'] ?? 0) + ($stats['open_support'] ?? 0) + ($stats['pending_invitations'] ?? 0);
@endphp
<div class="mock-wrap max-w-7xl mx-auto px-1 py-1">

  <div class="mock-topbar">
    <div class="mock-crumbs">Admin <span>/</span> <b>Control Center</b></div>
    <span class="mock-pill"><span class="pulse"></span>System operational</span>
    @if(($stats['open_support'] ?? 0) > 0)
      <span class="mock-pill amber"><span class="pulse"></span>{{ $stats['open_support'] }} open tickets</span>
    @endif
    <div class="mock-actions">
      <a class="mock-btn" href="{{ route('admin.schools.create') }}">＋ School</a>
      <a class="mock-btn primary" href="{{ route('admin.users.create') }}">＋ Add user</a>
    </div>
  </div>

  <div class="mock-title">
    <div>
      <h1>{{ $greeting }}, {{ $firstName }}</h1>
      <p>{{ $stats['total_users'] }} users · {{ $stats['total_schools'] }} schools · {{ $stats['completion_rate'] }}% completion</p>
    </div>
    <time>{{ now()->format('l, F j, Y') }} · Aspire Control Center</time>
  </div>

  <div class="mock-kpis">
    <div class="mock-kpi hot">
      <label>Total users</label>
      <div class="val">{{ $stats['total_users'] }}</div>
      <div class="delta mock-flat">{{ $stats['total_teachers'] }} teachers · {{ $stats['total_supervisors'] }} supervisors</div>
    </div>
    <div class="mock-kpi">
      <label>Active schools</label>
      <div class="val">{{ $stats['total_schools'] }}</div>
      <div class="delta mock-flat">Onboarded &amp; running</div>
    </div>
    <div class="mock-kpi">
      <label>Observations</label>
      <div class="val">{{ $stats['total_observations'] }}</div>
      <div class="delta mock-flat">{{ $stats['active_observations'] }} active · {{ $stats['pending_cots'] }} pending</div>
    </div>
    <div class="mock-kpi">
      <label>Avg COT · Completion</label>
      <div class="val">{{ number_format($avgScore, 2) }} <small>/ {{ $scaleMax }}</small></div>
      <div class="delta mock-flat">{{ $stats['completion_rate'] }}% done · trend: {{ $trend }}</div>
    </div>
  </div>

  <div class="mock-grid">
    <div class="min-w-0">
      <section class="mock-panel" aria-label="System pipeline">
        <div class="mock-panel-head">
          <h2>System pipeline · onboarding to review</h2>
          <span class="hint">{{ $reviewQueue }} items waiting in the review queue</span>
          <a class="link" href="{{ route('admin.reports.index') }}">Open reports →</a>
        </div>
        <div class="mock-steps">
          <div class="mock-step done">
            <div class="mock-step-num">✓</div>
            <div><h3>1 · Registrations</h3><p>{{ $stats['total_users'] }} users on board · {{ $stats['pending_invitations'] }} invitations pending</p><div class="meta">managed via invitations &amp; user accounts</div></div>
            <span class="mock-status done">Done</span>
          </div>
          <div class="mock-step done">
            <div class="mock-step-num">✓</div>
            <div><h3>2 · Schools onboarded</h3><p>{{ $stats['total_schools'] }} active schools running observations</p><div class="meta">across all districts</div></div>
            <span class="mock-status done">Done</span>
          </div>
          <div class="mock-step {{ $stats['active_observations'] > 0 ? 'now' : '' }}">
            <div class="mock-step-num">3</div>
            <div><h3>3 · Observations running</h3><p>{{ $stats['active_observations'] }} active cycles · {{ $stats['completed_total'] }} completed</p><div class="meta">in progress across schools</div></div>
            <span class="mock-status {{ $stats['active_observations'] > 0 ? 'now' : 'todo' }}">{{ $stats['active_observations'] > 0 ? 'In progress' : 'Queued' }}</span>
          </div>
          <div class="mock-step {{ $reviewQueue > 0 ? 'now' : 'done' }}">
            <div class="mock-step-num">{{ $reviewQueue > 0 ? 4 : '✓' }}</div>
            <div><h3>4 · Review queue</h3><p>{{ $stats['pending_cots'] }} pending COTs · {{ $stats['open_support'] }} open tickets</p><div class="meta">needs admin review</div></div>
            <span class="mock-status {{ $reviewQueue > 0 ? 'now' : 'done' }}">{{ $reviewQueue > 0 ? 'In progress' : 'Clear' }}</span>
          </div>
        </div>
      </section>

      <section class="mock-panel" aria-label="Observation groups">
        <div class="mock-panel-head">
          <h2>Observation groups</h2>
          <span class="hint">schools with observation files</span>
          <a class="link" href="{{ route('admin.observations.index') }}">View all →</a>
        </div>
        @if(count($schoolFolders) > 0)
          <div>
            @foreach($schoolFolders as $i => $g)
              <details class="mock-folder" @if($i < 2) open @endif>
                <summary>
                  <span class="caret">▸</span>
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#58a6ff" stroke-width="2" aria-hidden="true"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/></svg>
                  <span>{{ $g['school']->name }}</span>
                  <span class="spacer"><span class="mock-status todo">{{ $g['observations']->count() }} files</span></span>
                </summary>
                <div>
                  @forelse($g['observations'] as $ob)
                    @php
                      $name = $ob->observee?->user?->name ?? $ob->teacher?->user?->name ?? 'Unknown';
                      $score = $ob->overall_score !== null ? (float) $ob->overall_score : null;
                      $tone = $score === null ? 'lo' : ($score >= 4.5 ? 'hi' : ($score >= 3.5 ? 'mid' : 'lo'));
                    @endphp
                    <a class="mock-file" href="{{ route('admin.observations.show', $ob) }}">
                      <span class="mock-fdot"></span>
                      <span class="mock-fmain"><b>{{ $name }}</b><span class="mock-mono">{{ $ob->observation_date?->format('M d, Y') ?? 'No date' }}</span></span>
                      <span class="mock-fright"><span class="mock-score {{ $tone }}">{{ $score !== null ? number_format($score, 1) : '—' }}</span><span class="mock-status {{ $ob->status === 'completed' ? 'done' : 'now' }}">{{ ucwords(str_replace('_', ' ', $ob->status ?? 'pending')) }}</span></span>
                    </a>
                  @empty
                    <div class="mock-empty">No observation files yet for this school.</div>
                  @endforelse
                </div>
              </details>
            @endforeach
          </div>
        @else
          <div class="mock-empty">No active schools yet.</div>
        @endif
      </section>

      <section class="mock-panel" aria-label="Analytics overview">
        <div class="mock-panel-head">
          <h2>Analytics overview</h2>
          <span class="hint">Last 6 months · {{ array_sum($charts['monthly']['counts'] ?? []) }} observations</span>
          <a class="link" href="{{ route('admin.reports.index') }}">Open reports →</a>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;padding:14px 16px">
          <div class="mock-mod"><div class="mock-mod-head"><h3>Observations / month</h3></div><div class="mock-mod-body"><div style="height:170px"><canvas id="adminMonthlyChart"></canvas></div></div></div>
          <div class="mock-mod"><div class="mock-mod-head"><h3>By status</h3></div><div class="mock-mod-body"><div style="height:170px"><canvas id="adminStatusChart"></canvas></div></div></div>
          <div class="mock-mod"><div class="mock-mod-head"><h3>Avg COT trend</h3></div><div class="mock-mod-body"><div style="height:170px"><canvas id="adminScoreChart"></canvas></div></div></div>
          <div class="mock-mod"><div class="mock-mod-head"><h3>Users by role</h3></div><div class="mock-mod-body"><div style="height:170px"><canvas id="adminRoleChart"></canvas></div></div></div>
        </div>
      </section>

      <section class="mock-panel" aria-label="Rating distribution">
        <div class="mock-panel-head">
          <h2>Rating distribution</h2>
          <span class="hint">{{ $bandTotal }} scored observations</span>
        </div>
        @if($bandTotal > 0)
          <div style="padding:12px 16px">
            @foreach($bands['labels'] as $i => $label)
              @php $count = $bands['counts'][$i] ?? 0; $pct = $bandTotal > 0 ? round(($count / $bandTotal) * 100) : 0; @endphp
              <div style="margin-bottom:8px">
                <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:3px"><span class="mock-tag">{{ $label }}</span><b style="color:var(--m-text)">{{ $count }} <span class="mock-mono">({{ $pct }}%)</span></b></div>
                <div class="mock-bar"><i style="width:{{ $pct }}%"></i></div>
              </div>
            @endforeach
          </div>
        @else
          <div class="mock-empty">No scored observations yet.</div>
        @endif
      </section>
    </div>

    <aside class="mock-rail" aria-label="Contextual utilities">
      <div class="mock-mod">
        <div class="mock-mod-head"><h3>System insights</h3><span class="tick"></span></div>
        <div class="mock-mod-body">
          <div class="mock-insight"><div><b>Completion</b><span>{{ $stats['completed_total'] }} of {{ $stats['total_observations'] }} cycles done</span><div class="mock-bar"><i style="width:{{ $stats['completion_rate'] }}%"></i></div></div></div>
          <div class="mock-insight"><div><b>AI processing</b><span>{{ $systemStatus['ai_processing'] ?? '—' }}</span></div></div>
          <div class="mock-insight"><div><b>This month</b><span>{{ $performance['completed_observations_this_month'] }} completed · {{ $trend }}</span></div></div>
        </div>
      </div>
      <div class="mock-mod">
        <div class="mock-mod-head"><h3>Quick actions</h3></div>
        <div class="mock-mod-body">
          <a class="mock-act solid" href="{{ route('admin.users.index') }}">👥 Manage users · {{ $stats['total_users'] }}</a>
          <a class="mock-act" href="{{ route('admin.schools.index') }}">🏫 Schools · {{ $stats['total_schools'] }}</a>
          <a class="mock-act" href="{{ route('admin.observations.index') }}">📋 Observations · {{ $stats['pending_cots'] }} pending</a>
          <a class="mock-act" href="{{ route('admin.announcements.index') }}">📢 Announcements · {{ $stats['total_announcements'] }}</a>
          <a class="mock-act" href="{{ route('admin.support-messages.index') }}">🛟 Support · {{ $stats['open_support'] }} open</a>
          <a class="mock-act" href="{{ route('admin.invitations.index') }}">✉️ Invitations · {{ $stats['pending_invitations'] }} pending</a>
        </div>
      </div>
      <div class="mock-mod">
        <div class="mock-mod-head"><h3>Needs attention</h3><span class="tick warn"></span></div>
        <div class="mock-mod-body">
          <div class="mock-kv"><dt>Pending COTs</dt><dd>{{ $stats['pending_cots'] }}</dd></div>
          <div class="mock-kv"><dt>Open tickets</dt><dd>{{ $stats['open_support'] }}</dd></div>
          <div class="mock-kv"><dt>Pending invites</dt><dd>{{ $stats['pending_invitations'] }}</dd></div>
        </div>
      </div>
      <div class="mock-mod">
        <div class="mock-mod-head"><h3>System status</h3><span class="tick"></span></div>
        <div class="mock-mod-body">
          @php $ss = [['Database', $systemStatus['database']], ['API', $systemStatus['api_services']], ['Email', $systemStatus['email_service']], ['Storage', $systemStatus['file_storage']], ['AI', $systemStatus['ai_processing']]]; @endphp
          @foreach($ss as [$k, $v])
            <div class="mock-kv"><dt>{{ $k }}</dt><dd>{{ $v }}</dd></div>
          @endforeach
          <div class="mock-kv"><dt>Memory</dt><dd class="mock-mono">{{ $systemStatus['server_usage']['memory_usage'] ?? '—' }}</dd></div>
        </div>
      </div>
      <div class="mock-mod">
        <div class="mock-mod-head"><h3>Recent activity</h3><span class="tick"></span></div>
        <div class="mock-mod-body">
          @forelse($recentAuditLogs->take(6) as $log)
            <div class="mock-insight"><div><b>{{ $log->user?->name ?? 'System' }}</b><span>{{ str_replace('_', ' ', $log->action) }} {{ str_replace('_', ' ', $log->module ?? '') }} · {{ $log->created_at->diffForHumans() }}</span></div></div>
          @empty
            <p style="font-size:12px;color:var(--m-muted)">No recent activity.</p>
          @endforelse
          <a class="mock-act" style="margin-top:10px" href="{{ route('admin.audit-logs.index') }}">View audit log →</a>
        </div>
      </div>
    </aside>
  </div>
</div>
@endsection
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  if (typeof Chart === 'undefined') return;
  const dark = document.documentElement.classList.contains('dark');
  const grid = dark ? 'rgba(255,255,255,.08)' : 'rgba(0,0,0,.06)';
  const tick = dark ? '#8b949e' : '#64748b';
  const tip = { backgroundColor: dark ? '#1f2937' : '#1e1b4b', padding: 10, cornerRadius: 10 };
  const base = { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: tip } };
  const m = document.getElementById('adminMonthlyChart');
  if (m) new Chart(m, { type: 'bar', data: { labels: @json($charts['monthly']['labels']), datasets: [{ data: @json($charts['monthly']['counts']), backgroundColor: 'rgba(47,129,247,.55)', borderRadius: 6 }] }, options: { ...base, scales: { y: { beginAtZero: true, ticks: { stepSize: 1, color: tick }, grid: { color: grid } }, x: { grid: { display: false }, ticks: { color: tick } } } } });
  const s = document.getElementById('adminScoreChart');
  if (s) new Chart(s, { type: 'line', data: { labels: @json($charts['monthly']['labels']), datasets: [{ data: @json($charts['scores']), spanGaps: true, borderColor: '#3fb950', backgroundColor: 'rgba(63,185,80,.10)', fill: true, tension: .38, pointRadius: 3 }] }, options: { ...base, scales: { y: { min: 0, max: @json($scaleMax), ticks: { color: tick } }, x: { grid: { display: false }, ticks: { color: tick } } } } });
  const st = document.getElementById('adminStatusChart');
  if (st) new Chart(st, { type: 'doughnut', data: { labels: @json($charts['byStatus']['labels']), datasets: [{ data: @json($charts['byStatus']['counts']), backgroundColor: ['#f59e0b','#3b82f6','#6366f1','#8b5cf6','#3fb950','#64748b'], borderWidth: 2, borderColor: dark ? '#151a21' : '#fff' }] }, options: { responsive: true, maintainAspectRatio: false, cutout: '62%', plugins: { legend: { position: 'bottom' }, tooltip: tip } } });
  const r = document.getElementById('adminRoleChart');
  if (r) new Chart(r, { type: 'doughnut', data: { labels: @json($charts['usersByRole']['labels']), datasets: [{ data: @json($charts['usersByRole']['counts']), backgroundColor: ['#8b5cf6','#6366f1','#0ea5e9','#3fb950'], borderWidth: 2, borderColor: dark ? '#151a21' : '#fff' }] }, options: { responsive: true, maintainAspectRatio: false, cutout: '62%', plugins: { legend: { position: 'bottom' }, tooltip: tip } } });
});
</script>
@endpush
