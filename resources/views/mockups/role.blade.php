{{-- Standalone mockup preview for every role (no auth, static sample data).
     The real app dashboards live in admin/supervisor/school-head/teacher
     views and keep the current sidebar logo (<x-application-logo />).
     This file is preview-only and never touches the real sidebars. --}}
@php
  $role = $role ?? 'supervisor';
  $configs = [
    'admin' => [
      'console' => 'Admin Console', 'crumb' => 'Admin', 'user' => 'A. Reyes', 'userSub' => 'Admin · System',
      'greet' => 'Good morning, Admin', 'sub' => '128 users · 12 schools · 3 open tickets',
      'kpis' => [['Total users','128','▲ 6 this month',1],['Active schools','12','All operational',0],['Observations','342','▲ 18 this month',1],['Completion','76%','▲ 4 pts',1]],
      'panel' => 'System pipeline · user onboarding', 'panelHint' => 'Invitations · schools · sync',
      'rows' => [['R. Dela Cruz','Teacher III · Science','Sep 24','5.4','Rating'],['M. Santos','Teacher II · English','Sep 22','5.8','Finalized'],['J. Ramos','Teacher I · Math','Sep 19','4.1','Finalized'],['New invitation','Teacher · Sta. Maria HS','Sep 17','—','Scheduled']],
    ],
    'supervisor' => [
      'console' => 'Supervisor Console', 'crumb' => 'Supervisor', 'user' => 'M. Santiago', 'userSub' => 'Supervisor · District 4',
      'greet' => 'Good morning, Supervisor', 'sub' => '4 observations due this week · 2 AI analyses pending review',
      'kpis' => [['Scheduled','12','▲ 3 vs last week',1],['Completed','28 / 40','70% cycle progress',0],['Avg. COT score','5.2 / 6.0','▲ 0.4 this quarter',1],['Pending sync','3','will clear on reconnect',0]],
      'panel' => 'Active observation · R. Dela Cruz', 'panelHint' => 'COT-RPMS · Teacher III · Science 7',
      'rows' => [['R. Dela Cruz','Teacher III · Science','Sep 24','5.4','Rating'],['M. Santos','Teacher II · English','Sep 22','5.8','Finalized'],['J. Ramos','Teacher I · Math','Sep 19','4.1','Finalized'],['A. Villanueva','School Head','Sep 17','—','Scheduled']],
    ],
    'school-head' => [
      'console' => 'School Head Console', 'crumb' => 'School Head', 'user' => 'J. Aquino', 'userSub' => 'School Head · San Isidro ES',
      'greet' => 'Good morning, Ma’am Aquino', 'sub' => '18 teachers · 6 lesson plans for checking · 2 confirmations',
      'kpis' => [['Teachers','18','2 need support',0],['Observations','24','▲ 5 this term',1],['Avg COT','5.0 / 7.0','▲ 0.3 vs last term',1],['For checking','6','lesson plans waiting',0]],
      'panel' => 'Attention queue · lesson plans & confirmations', 'panelHint' => 'DLL review · scheduled observations',
      'rows' => [['R. Dela Cruz','Teacher III · Science','Sep 24','5.4','Rating'],['M. Santos','Teacher II · English','Sep 22','5.8','Finalized'],['J. Ramos','Teacher I · Math','Sep 19','4.1','Finalized'],['L. Torres','Teacher I · Filipino','Sep 17','—','Scheduled']],
    ],
    'teacher' => [
      'console' => 'Teacher Portfolio', 'crumb' => 'Teacher', 'user' => 'R. Dela Cruz', 'userSub' => 'Teacher III · Science 7',
      'greet' => 'Good morning, R. Dela Cruz — keep growing', 'sub' => '3 completed · 1 scheduled · avg 5.4',
      'kpis' => [['Total cycles','4','3 done · 1 active',0],['Avg COT','5.4 / 7.0','▲ 0.4 growth',1],['Completed','3 / 4','75% portfolio',0],['Scheduled','1','1 needs confirmation',0]],
      'panel' => 'My cycle · Sep 26 observation', 'panelHint' => 'Science 7 · observer M. Santiago',
      'rows' => [['My COT 04','COT-RPMS · Science','Sep 24','5.4','Rating'],['My COT 03','COT-RPMS · Science','Aug 28','5.1','Finalized'],['My COT 02','COT-RPMS · Science','Jun 12','4.9','Finalized'],['My COT 01','COT-RPMS · Science','Mar 03','4.6','Finalized']],
    ],
  ];
  $cfg = $configs[$role] ?? $configs['supervisor'];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>ASPIRE · {{ ucfirst(str_replace('-', ' ', $role)) }} Dashboard (Design Mockup)</title>
<style>
    :root{--bg:#101318;--panel:#151a21;--panel-2:#181e26;--panel-3:#1c232d;--line:#242c37;--line-soft:#1e2530;--text:#e6e9ee;--muted:#8b949e;--faint:#5c6672;--accent:#2f81f7;--accent-soft:rgba(47,129,247,.12);--accent-line:rgba(47,129,247,.45);--green:#3fb950;--amber:#d29922;--rose:#f85149;--radius:10px;--font:"Inter",-apple-system,"Segoe UI",Roboto,Arial,sans-serif;--mono:ui-monospace,"Cascadia Mono",Consolas,monospace}
    *{box-sizing:border-box;margin:0;padding:0}html,body{height:100%}
    body{background:var(--bg);color:var(--text);font-family:var(--font);font-size:13px;line-height:1.5}
    .app{display:grid;grid-template-columns:248px minmax(0,1fr) 304px;height:100vh;overflow:hidden}
    .side{background:var(--panel);border-right:1px solid var(--line);display:flex;flex-direction:column;min-height:0}
    .brand{display:flex;align-items:center;gap:10px;padding:16px;border-bottom:1px solid var(--line-soft)}
    .brand-mark{width:30px;height:30px;border-radius:8px;background:linear-gradient(135deg,#1f6feb,#388bfd);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;color:#fff}
    .brand-name{font-weight:650;font-size:14px}.brand-sub{font-size:11px;color:var(--muted)}
    .nav{flex:1;overflow-y:auto;padding:10px 8px;min-height:0}
    .nav-label{font-size:10px;font-weight:650;letter-spacing:.12em;text-transform:uppercase;color:var(--faint);padding:12px 10px 6px}
    .nav-item{display:flex;align-items:center;gap:10px;padding:7px 10px;border-radius:7px;color:var(--muted);font-size:13px;font-weight:500;border:1px solid transparent}
    .nav-item.active{background:var(--accent-soft);border-color:var(--accent-line);color:var(--text)}
    .nav-item .count{margin-left:auto;font-family:var(--mono);font-size:10.5px;background:var(--panel-3);border:1px solid var(--line);border-radius:20px;padding:1px 7px}
    .user-card{display:flex;align-items:center;gap:10px;margin:10px;padding:10px 12px;border:1px solid var(--line);border-radius:var(--radius);background:var(--panel-2)}
    .avatar{width:30px;height:30px;border-radius:50%;background:linear-gradient(135deg,#8957e5,#2f81f7);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff}
    .main{display:flex;flex-direction:column;min-width:0;min-height:0}
    .topbar{display:flex;align-items:center;gap:12px;padding:14px 22px;border-bottom:1px solid var(--line);background:var(--panel);flex-wrap:wrap}
    .crumbs{font-size:12.5px;color:var(--faint)}.crumbs b{color:var(--text)}
    .pill{display:inline-flex;align-items:center;gap:6px;font-size:11px;font-weight:600;border:1px solid var(--line);border-radius:20px;padding:3px 10px;color:var(--muted)}
    .pill .pulse{width:7px;height:7px;border-radius:50%;background:var(--green)}
    .top-actions{margin-left:auto;display:flex;gap:8px}
    .btn{display:inline-flex;align-items:center;gap:7px;font-size:12.5px;font-weight:600;border:1px solid var(--line);border-radius:8px;padding:7px 13px;color:var(--text);background:var(--panel-3)}
    .btn.primary{background:#1f6feb;border-color:#1f6feb;color:#fff}
    .canvas{flex:1;overflow-y:auto;padding:20px 22px 28px}
    .page-title h1{font-size:19px;font-weight:650}.page-title p{font-size:12.5px;color:var(--muted)}
    .kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin:16px 0}
    .kpi{background:var(--panel);border:1px solid var(--line);border-radius:var(--radius);padding:13px 15px;position:relative}
    .kpi::before{content:"";position:absolute;left:0;top:0;bottom:0;width:2px;background:var(--line)}
    .kpi.hot::before{background:var(--accent)}
    .kpi label{font-size:10px;font-weight:650;letter-spacing:.1em;text-transform:uppercase;color:var(--faint)}
    .kpi .val{font-size:22px;font-weight:700;margin-top:3px}
    .kpi .delta{font-size:11px;margin-top:2px;color:var(--muted)}
    .panel{background:var(--panel);border:1px solid var(--line);border-radius:var(--radius);margin-bottom:16px;overflow:hidden}
    .panel-head{display:flex;align-items:center;gap:10px;padding:12px 16px;border-bottom:1px solid var(--line-soft)}
    .panel-head h2{font-size:13px;font-weight:650}.panel-head .hint{font-size:11.5px;color:var(--faint)}.panel-head .link{margin-left:auto;font-size:12px;color:var(--accent)}
    table.grid{width:100%;border-collapse:collapse;font-size:12.5px}
    table.grid th{text-align:left;font-size:10px;letter-spacing:.1em;text-transform:uppercase;color:var(--faint);padding:9px 16px;border-bottom:1px solid var(--line)}
    table.grid td{padding:10px 16px;border-bottom:1px solid var(--line-soft)}
    .mono{font-family:var(--mono);font-size:11.5px;color:var(--muted)}
    .score{display:inline-flex;min-width:38px;height:24px;padding:0 8px;border-radius:6px;font-weight:700;font-size:12px;align-items:center;justify-content:center;background:rgba(63,185,80,.13);color:var(--green);border:1px solid rgba(63,185,80,.4)}
    .status{font-size:10.5px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;padding:3px 9px;border-radius:20px;border:1px solid var(--accent-line);color:#9ec5ff;background:var(--accent-soft)}
    .rail{background:var(--panel);border-left:1px solid var(--line);overflow-y:auto;padding:16px 14px;display:flex;flex-direction:column;gap:14px}
    .mod{background:var(--panel-2);border:1px solid var(--line);border-radius:var(--radius)}
    .mod-head{padding:10px 13px;border-bottom:1px solid var(--line-soft);font-size:11px;font-weight:700;letter-spacing:.09em;text-transform:uppercase;color:var(--muted)}
    .mod-body{padding:12px 13px;font-size:12px;color:var(--muted)}
    .role-switch{display:flex;gap:6px;flex-wrap:wrap;margin-top:8px}
    .role-switch a{font-size:11px;font-weight:600;color:#9ec5ff;background:var(--accent-soft);border:1px solid var(--accent-line);border-radius:20px;padding:2px 9px;text-decoration:none}
    .role-switch a.on{background:#1f6feb;color:#fff;border-color:#1f6feb}
    @media(max-width:1180px){.app{grid-template-columns:220px minmax(0,1fr)}.rail{display:none}.kpis{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(max-width:860px){.app{grid-template-columns:minmax(0,1fr)}.canvas{padding:14px 12px 22px}.top-actions{margin-left:0;width:100%}.top-actions .btn{flex:1;justify-content:center}}
</style>
</head>
<body>
<div class="app">
    @include('mockups._sidebar')
    <main class="main">
        <div class="topbar"><button class="mk-hamb" id="mkHamb" type="button" aria-label="Open side menu" aria-controls="mkSideNav" title="Open menu"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg></button><div class="crumbs">{{ $cfg['crumb'] }} / <b>Dashboard</b></div><span class="pill"><span class="pulse"></span>Online</span><div class="top-actions"><span class="btn">＋ New</span><span class="btn primary">Primary action</span></div></div>
        <div class="canvas">
            <div class="page-title"><h1>{{ $cfg['greet'] }}</h1><p>{{ $cfg['sub'] }}</p></div>
            <div class="kpis">
                @foreach($cfg['kpis'] as $i => $k)
                    <div class="kpi {{ $i === 0 ? 'hot' : '' }}"><label>{{ $k[0] }}</label><div class="val">{{ $k[1] }}</div><div class="delta">{{ $k[2] }}</div></div>
                @endforeach
            </div>
            <section class="panel"><div class="panel-head"><h2>{{ $cfg['panel'] }}</h2><span class="hint">{{ $cfg['panelHint'] }}</span><span class="link">Open →</span></div>
                <table class="grid"><thead><tr><th>Name</th><th>Detail</th><th>Date</th><th>Score</th><th>Status</th></tr></thead><tbody>
                @foreach($cfg['rows'] as $row)
                    <tr><td><b>{{ $row[0] }}</b><br><span class="mono">{{ $row[1] }}</span></td><td class="mono">COT-RPMS</td><td class="mono">{{ $row[2] }}</td><td><span class="score">{{ $row[3] }}</span></td><td><span class="status">{{ $row[4] }}</span></td></tr>
                @endforeach
                </tbody></table>
            </section>
        </div>
    </main>
    <aside class="rail">
        <div class="mod"><div class="mod-head">System insights</div><div class="mod-body">Mockup preview only — live data appears in the real {{ $cfg['crumb'] }} dashboard. Real sidebar logo (&lt;x-application-logo /&gt;) is preserved in the app.</div></div>
        <div class="mod"><div class="mod-head">Quick actions</div><div class="mod-body">Primary · secondary · export actions mirror the live dashboard rail.</div></div>
    </aside>
</div>
</body>
</html>
