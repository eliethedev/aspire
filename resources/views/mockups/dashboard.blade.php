<!DOCTYPE html>
<html lang="en" class="mock-dark">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>ASPIRE · Supervisor Dashboard (Design Mockup)</title>
<style>
    :root{
        --bg:#101318;
        --panel:#151a21;
        --panel-2:#181e26;
        --panel-3:#1c232d;
        --line:#242c37;
        --line-soft:#1e2530;
        --text:#e6e9ee;
        --muted:#8b949e;
        --faint:#5c6672;
        --accent:#2f81f7;
        --accent-soft:rgba(47,129,247,.12);
        --accent-line:rgba(47,129,247,.45);
        --green:#3fb950;
        --amber:#d29922;
        --rose:#f85149;
        --radius:10px;
        --font:"Inter",-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;
        --mono:"SFMono-Regular",ui-monospace,"Cascadia Mono",Consolas,"Roboto Mono",monospace;
    }
    *{box-sizing:border-box;margin:0;padding:0}
    html,body{height:100%}
    body{
        background:var(--bg);
        color:var(--text);
        font-family:var(--font);
        font-size:13px;
        line-height:1.5;
        -webkit-font-smoothing:antialiased;
    }
    /* ── App grid ─────────────────────────────── */
    .app{
        display:grid;
        grid-template-columns:248px minmax(0,1fr) 304px;
        height:100vh;
        overflow:hidden;
    }
    /* ── Left sidebar ─────────────────────────── */
    .side{
        background:var(--panel);
        border-right:1px solid var(--line);
        display:flex;
        flex-direction:column;
        min-height:0;
    }
    .brand{
        display:flex;align-items:center;gap:10px;
        padding:16px 16px 14px;
        border-bottom:1px solid var(--line-soft);
    }
    .brand-mark{
        width:30px;height:30px;border-radius:8px;flex:none;
        background:linear-gradient(135deg,#1f6feb,#388bfd);
        display:flex;align-items:center;justify-content:center;
        font-weight:700;font-size:14px;color:#fff;letter-spacing:.02em;
    }
    .brand-name{font-weight:650;font-size:14px;letter-spacing:.01em}
    .brand-sub{font-size:11px;color:var(--muted)}
    .side-search{padding:12px 12px 4px}
    .search{
        display:flex;align-items:center;gap:8px;
        background:var(--panel-2);
        border:1px solid var(--line);
        border-radius:8px;
        padding:7px 10px;
        color:var(--faint);
        font-size:12px;
    }
    .search kbd{
        margin-left:auto;
        font-family:var(--mono);font-size:10px;
        border:1px solid var(--line);border-radius:4px;
        padding:1px 5px;color:var(--faint);
    }
    .nav{flex:1;overflow-y:auto;padding:10px 8px 12px;min-height:0}
    .nav-label{
        font-size:10px;font-weight:650;letter-spacing:.12em;
        text-transform:uppercase;color:var(--faint);
        padding:12px 10px 6px;
    }
    .nav-item{
        display:flex;align-items:center;gap:10px;
        padding:7px 10px;border-radius:7px;
        color:var(--muted);font-size:13px;font-weight:500;
        border:1px solid transparent;
        cursor:default;user-select:none;
    }
    .nav-item svg{flex:none;opacity:.85}
    .nav-item:hover{background:var(--panel-2);color:var(--text)}
    .nav-item.active{
        background:var(--accent-soft);
        border-color:var(--accent-line);
        color:var(--text);
    }
    .nav-item .count{
        margin-left:auto;
        font-family:var(--mono);font-size:10.5px;
        background:var(--panel-3);
        border:1px solid var(--line);
        border-radius:20px;padding:1px 7px;color:var(--muted);
    }
    .nav-item.active .count{background:rgba(47,129,247,.18);border-color:var(--accent-line);color:#9ec5ff}
    /* folder tree */
    .tree{padding:2px 4px}
    .tree-node{border-radius:7px}
    .tree-row{
        display:flex;align-items:center;gap:7px;
        padding:6px 8px;border-radius:7px;
        color:var(--muted);font-size:12.5px;cursor:default;
    }
    .tree-row:hover{background:var(--panel-2);color:var(--text)}
    .tree-row.selected{background:var(--accent-soft);color:var(--text)}
    .caret{width:12px;flex:none;color:var(--faint);font-size:9px;text-align:center}
    .tree-children{margin-left:17px;border-left:1px solid var(--line-soft);padding-left:4px}
    .file-link{
        display:flex;align-items:center;gap:8px;
        padding:5px 8px;border-radius:6px;
        color:var(--muted);font-size:12.5px;
    }
    .file-link:hover{background:var(--panel-2);color:var(--text)}
    .file-link .dot{width:5px;height:5px;border-radius:50%;background:var(--faint);flex:none}
    .file-link.live .dot{background:var(--accent);box-shadow:0 0 6px rgba(47,129,247,.9)}
    .file-link.live{color:var(--text)}
    .user-card{
        display:flex;align-items:center;gap:10px;
        margin:10px;padding:10px 12px;
        border:1px solid var(--line);border-radius:var(--radius);
        background:var(--panel-2);
    }
    .avatar{
        width:30px;height:30px;border-radius:50%;flex:none;
        background:linear-gradient(135deg,#8957e5,#2f81f7);
        display:flex;align-items:center;justify-content:center;
        font-size:11px;font-weight:700;color:#fff;
    }
    .user-meta{min-width:0}
    .user-meta b{display:block;font-size:12.5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .user-meta span{font-size:11px;color:var(--muted)}
    /* ── Center workspace ─────────────────────── */
    .main{
        display:flex;flex-direction:column;
        min-width:0;min-height:0;
        background:var(--bg);
    }
    .topbar{
        display:flex;align-items:center;gap:12px;
        padding:14px 22px;
        border-bottom:1px solid var(--line);
        background:var(--panel);
    }
    .crumbs{display:flex;align-items:center;gap:8px;font-size:12.5px;color:var(--faint);min-width:0}
    .crumbs b{color:var(--text);font-weight:600}
    .crumbs .sep{opacity:.5}
    .pill{
        display:inline-flex;align-items:center;gap:6px;
        font-size:11px;font-weight:600;
        border:1px solid var(--line);border-radius:20px;
        padding:3px 10px;color:var(--muted);white-space:nowrap;
    }
    .pill .pulse{width:7px;height:7px;border-radius:50%;background:var(--green);box-shadow:0 0 6px rgba(63,185,80,.8)}
    .pill.amber{border-color:rgba(210,153,34,.4);color:#e3b341;background:rgba(210,153,34,.08)}
    .pill.amber .pulse{background:var(--amber);box-shadow:0 0 6px rgba(210,153,34,.8)}
    .top-actions{margin-left:auto;display:flex;gap:8px}
    .btn{
        display:inline-flex;align-items:center;gap:7px;
        font-size:12.5px;font-weight:600;
        border:1px solid var(--line);border-radius:8px;
        padding:7px 13px;color:var(--text);
        background:var(--panel-3);cursor:default;white-space:nowrap;
    }
    .btn:hover{border-color:#3a4556}
    .btn.primary{background:#1f6feb;border-color:#1f6feb;color:#fff}
    .btn.primary:hover{background:#2f81f7}
    .canvas{flex:1;overflow-y:auto;padding:20px 22px 28px;min-height:0}
    .page-title{display:flex;align-items:flex-end;gap:14px;margin-bottom:16px}
    .page-title h1{font-size:19px;font-weight:650;letter-spacing:-.01em}
    .page-title p{font-size:12.5px;color:var(--muted)}
    .page-title time{margin-left:auto;font-family:var(--mono);font-size:11px;color:var(--faint)}
    /* KPI cards */
    .kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:16px}
    .kpi{
        background:var(--panel);border:1px solid var(--line);
        border-radius:var(--radius);padding:13px 15px;
        position:relative;overflow:hidden;
    }
    .kpi::before{
        content:"";position:absolute;left:0;top:0;bottom:0;width:2px;
        background:var(--line);
    }
    .kpi.hot::before{background:var(--accent)}
    .kpi label{font-size:10px;font-weight:650;letter-spacing:.1em;text-transform:uppercase;color:var(--faint)}
    .kpi .val{font-size:22px;font-weight:700;letter-spacing:-.02em;margin-top:3px;font-variant-numeric:tabular-nums}
    .kpi .val small{font-size:12px;font-weight:500;color:var(--muted)}
    .kpi .delta{font-size:11px;margin-top:2px;font-variant-numeric:tabular-nums}
    .up{color:var(--green)} .down{color:var(--rose)} .flat{color:var(--muted)}
    /* panels */
    .panel{
        background:var(--panel);border:1px solid var(--line);
        border-radius:var(--radius);margin-bottom:16px;overflow:hidden;
    }
    .panel-head{
        display:flex;align-items:center;gap:10px;
        padding:12px 16px;border-bottom:1px solid var(--line-soft);
    }
    .panel-head h2{font-size:13px;font-weight:650}
    .panel-head .hint{font-size:11.5px;color:var(--faint)}
    .panel-head .link{margin-left:auto;font-size:12px;font-weight:600;color:var(--accent);white-space:nowrap}
    /* document steps */
    .steps{padding:8px 0}
    .step{
        display:grid;grid-template-columns:34px minmax(0,1fr) auto;
        gap:12px;align-items:start;
        padding:11px 16px;
        border-bottom:1px solid var(--line-soft);
    }
    .step:last-child{border-bottom:0}
    .step:hover{background:var(--panel-2)}
    .step-num{
        width:24px;height:24px;border-radius:50%;
        display:flex;align-items:center;justify-content:center;
        font-size:11px;font-weight:700;font-variant-numeric:tabular-nums;
        border:1px solid var(--line);color:var(--muted);background:var(--panel-3);
        margin-top:1px;
    }
    .step.done .step-num{background:rgba(63,185,80,.14);border-color:rgba(63,185,80,.5);color:var(--green)}
    .step.now .step-num{background:var(--accent-soft);border-color:var(--accent);color:#9ec5ff;box-shadow:0 0 0 3px rgba(47,129,247,.12)}
    .step h3{font-size:13px;font-weight:600}
    .step p{font-size:12px;color:var(--muted);margin-top:1px}
    .step .meta{font-family:var(--mono);font-size:10.5px;color:var(--faint);margin-top:4px}
    .status{
        font-size:10.5px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;
        padding:3px 9px;border-radius:20px;white-space:nowrap;margin-top:2px;
    }
    .status.done{color:var(--green);background:rgba(63,185,80,.1);border:1px solid rgba(63,185,80,.35)}
    .status.now{color:#9ec5ff;background:var(--accent-soft);border:1px solid var(--accent-line)}
    .status.todo{color:var(--faint);background:transparent;border:1px solid var(--line)}
    /* activity table */
    table.grid{width:100%;border-collapse:collapse;font-size:12.5px}
    table.grid th{
        text-align:left;font-size:10px;font-weight:650;letter-spacing:.1em;text-transform:uppercase;
        color:var(--faint);padding:9px 16px;border-bottom:1px solid var(--line);
    }
    table.grid td{padding:10px 16px;border-bottom:1px solid var(--line-soft);vertical-align:middle}
    table.grid tr:last-child td{border-bottom:0}
    table.grid tbody tr:hover{background:var(--panel-2)}
    .mono{font-family:var(--mono);font-size:11.5px;color:var(--muted)}
    .score{
        display:inline-flex;align-items:center;justify-content:center;
        min-width:38px;height:24px;padding:0 8px;border-radius:6px;
        font-weight:700;font-size:12px;font-variant-numeric:tabular-nums;
    }
    .score.hi{background:rgba(63,185,80,.13);color:var(--green);border:1px solid rgba(63,185,80,.4)}
    .score.mid{background:rgba(210,153,34,.12);color:#e3b341;border:1px solid rgba(210,153,34,.4)}
    .score.lo{background:rgba(248,81,73,.1);color:var(--rose);border:1px solid rgba(248,81,73,.35)}
    /* ── Right utility rail ───────────────────── */
    .rail{
        background:var(--panel);
        border-left:1px solid var(--line);
        overflow-y:auto;min-height:0;
        padding:16px 14px 20px;
        display:flex;flex-direction:column;gap:14px;
    }
    .mod{
        background:var(--panel-2);border:1px solid var(--line);
        border-radius:var(--radius);overflow:hidden;flex:none;
    }
    .mod-head{
        padding:10px 13px;border-bottom:1px solid var(--line-soft);
        display:flex;align-items:center;gap:8px;
    }
    .mod-head h3{font-size:11px;font-weight:700;letter-spacing:.09em;text-transform:uppercase;color:var(--muted)}
    .mod-head .tick{margin-left:auto;width:7px;height:7px;border-radius:50%;background:var(--green)}
    .mod-head .tick.warn{background:var(--amber)}
    .mod-body{padding:12px 13px}
    .insight{display:flex;gap:10px;padding:8px 0;border-bottom:1px solid var(--line-soft)}
    .insight:last-child{border-bottom:0;padding-bottom:0}
    .insight:first-child{padding-top:0}
    .insight-ico{
        width:28px;height:28px;border-radius:7px;flex:none;
        display:flex;align-items:center;justify-content:center;
        background:var(--panel-3);border:1px solid var(--line);
    }
    .insight b{display:block;font-size:12.5px;font-weight:600}
    .insight span{font-size:11.5px;color:var(--muted)}
    .bar{height:5px;border-radius:3px;background:var(--panel-3);margin-top:7px;overflow:hidden}
    .bar i{display:block;height:100%;border-radius:3px;background:linear-gradient(90deg,#1f6feb,#58a6ff)}
    .act-btn{
        display:flex;align-items:center;gap:9px;width:100%;
        padding:9px 12px;margin-bottom:8px;
        border-radius:8px;font-size:12.5px;font-weight:600;color:var(--text);
        background:var(--panel-3);border:1px solid var(--line);cursor:default;
    }
    .act-btn:last-child{margin-bottom:0}
    .act-btn:hover{border-color:var(--accent-line);background:var(--accent-soft)}
    .act-btn.solid{background:#1f6feb;border-color:#1f6feb;color:#fff}
    .act-btn.solid:hover{background:#2f81f7}
    .kv{display:flex;justify-content:space-between;gap:10px;padding:6px 0;border-bottom:1px solid var(--line-soft);font-size:12px}
    .kv:last-child{border-bottom:0}
    .kv dt{color:var(--faint)}
    .kv dd{color:var(--text);font-weight:550;text-align:right;font-variant-numeric:tabular-nums}
    .tags{display:flex;flex-wrap:wrap;gap:6px}
    .tag{
        font-size:11px;font-weight:600;color:#9ec5ff;
        background:var(--accent-soft);border:1px solid var(--accent-line);
        border-radius:20px;padding:2px 9px;
    }
    .tag.dim{color:var(--muted);background:transparent;border-color:var(--line)}
    ::-webkit-scrollbar{width:10px;height:10px}
    ::-webkit-scrollbar-thumb{background:#2b333f;border-radius:6px;border:2px solid var(--panel)}
    ::-webkit-scrollbar-track{background:transparent}
    @media (max-width:1180px){
        .app{grid-template-columns:220px minmax(0,1fr)}
        .rail{display:none}
        .kpis{grid-template-columns:repeat(2,minmax(0,1fr))}
    }
    @media (max-width:860px){
        .app{grid-template-columns:minmax(0,1fr)}
        .canvas{padding:14px 12px 22px}
        .top-actions{margin-left:0;width:100%}
        .top-actions .btn{flex:1;justify-content:center}
    }
</style>
</head>
<body>
<div class="app">

    @php($role = 'supervisor')
    @php($cfg = ['console' => 'Supervisor Console', 'user' => 'M. Santiago', 'userSub' => 'Supervisor · District 4'])
    @include('mockups._sidebar')
    @if(false)
    <!-- ══════════ LEFT · navigation (superseded by mockups._sidebar) ══════════ -->
    <aside class="side" aria-label="Primary navigation">
        <div class="brand">
            <div class="brand-mark">A</div>
            <div>
                <div class="brand-name">ASPIRE</div>
                <div class="brand-sub">Supervisor Console</div>
            </div>
        </div>

        <div class="side-search">
            <div class="search">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                Quick find… <kbd>⌘K</kbd>
            </div>
        </div>

        <nav class="nav">
            <div class="nav-label">Workspace</div>
            <div class="nav-item active">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
                Dashboard
            </div>
            <div class="nav-item">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path d="M2.5 12C3.8 7.9 7.5 5 12 5s8.2 2.9 9.5 7c-1.3 4.1-5 7-9.5 7s-8.2-2.9-9.5-7Z"/></svg>
                Observations <span class="count">12</span>
            </div>
            <div class="nav-item">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M8 2v4M16 2v4M3 9h18"/></svg>
                Calendar
            </div>
            <div class="nav-item">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 20V10M10 20V4M16 20v-8M22 20H2"/></svg>
                Reports
            </div>

            <div class="nav-label">Schools / Folders</div>
            <div class="tree">
                <div class="tree-node">
                    <div class="tree-row">
                        <span class="caret">▾</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#58a6ff" stroke-width="2"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/></svg>
                        San Isidro ES
                    </div>
                    <div class="tree-children">
                        <div class="tree-row"><span class="caret">▾</span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#58a6ff" stroke-width="2"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/></svg>
                            Grade 7 · Science
                        </div>
                        <div class="tree-children">
                            <div class="file-link live"><span class="dot"></span>R. Dela Cruz — COT 04</div>
                            <div class="file-link"><span class="dot"></span>M. Santos — COT 02</div>
                            <div class="file-link"><span class="dot"></span>J. Ramos — COT 01</div>
                        </div>
                        <div class="tree-row"><span class="caret">▸</span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#58a6ff" stroke-width="2"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/></svg>
                            Grade 8 · Math
                        </div>
                    </div>
                </div>
                <div class="tree-node">
                    <div class="tree-row"><span class="caret">▸</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#58a6ff" stroke-width="2"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/></svg>
                        Sta. Maria HS
                    </div>
                </div>
                <div class="tree-node">
                    <div class="tree-row"><span class="caret">▸</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#58a6ff" stroke-width="2"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/></svg>
                        Archived · SY 2024–25
                    </div>
                </div>
            </div>

            <div class="nav-label">System</div>
            <div class="nav-item">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-2.6-6.4M21 4v5h-5"/></svg>
                Offline sync <span class="count">3</span>
            </div>
            <div class="nav-item">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19 12a7 7 0 0 0-.1-1.2l2-1.6-2-3.4-2.4 1a7 7 0 0 0-2-1.2L14 3h-4l-.5 2.6a7 7 0 0 0-2 1.2l-2.4-1-2 3.4 2 1.6A7 7 0 0 0 5 12c0 .4 0 .8.1 1.2l-2 1.6 2 3.4 2.4-1a7 7 0 0 0 2 1.2L10 21h4l.5-2.6a7 7 0 0 0 2-1.2l2.4 1 2-3.4-2-1.6c.1-.4.1-.8.1-1.2Z"/></svg>
                Settings
            </div>
        </nav>

        <div class="user-card">
            <div class="avatar">MS</div>
            <div class="user-meta"><b>M. Santiago</b><span>Supervisor · District 4</span></div>
        </div>
    </aside>
    @endif

    <!-- ══════════ CENTER · workspace ══════════ -->
    <main class="main">
        <div class="topbar">
            <button class="mk-hamb" id="mkHamb" type="button" aria-label="Open side menu" aria-controls="mkSideNav" title="Open menu"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg></button>
            <div class="crumbs">Supervisor <span class="sep">/</span> <b>Dashboard</b></div>
            <span class="pill"><span class="pulse"></span>Online</span>
            <span class="pill amber"><span class="pulse"></span>3 queued offline</span>
            <div class="top-actions">
                <span class="btn">＋ New observation</span>
                <span class="btn primary">Sync now</span>
            </div>
        </div>

        <div class="canvas">
            <div class="page-title">
                <div>
                    <h1>Good morning, Supervisor</h1>
                    <p>4 observations due this week · 2 AI analyses pending review</p>
                </div>
                <time>SY 2025–26 · Q2 · Fri, Sep 25</time>
            </div>

            <div class="kpis">
                <div class="kpi hot">
                    <label>Scheduled</label>
                    <div class="val">12</div>
                    <div class="delta up">▲ 3 vs last week</div>
                </div>
                <div class="kpi">
                    <label>Completed</label>
                    <div class="val">28 <small>/ 40</small></div>
                    <div class="delta flat">70% cycle progress</div>
                </div>
                <div class="kpi">
                    <label>Avg. COT score</label>
                    <div class="val">5.2 <small>/ 6.0</small></div>
                    <div class="delta up">▲ 0.4 this quarter</div>
                </div>
                <div class="kpi">
                    <label>Pending sync</label>
                    <div class="val">3</div>
                    <div class="delta down">▼ will clear on reconnect</div>
                </div>
            </div>

            <section class="panel" aria-label="Observation workflow">
                <div class="panel-head">
                    <h2>Active observation · R. Dela Cruz</h2>
                    <span class="hint">COT-RPMS · Teacher III · Science 7</span>
                    <span class="link">Open rating sheet →</span>
                </div>
                <div class="steps">
                    <div class="step done">
                        <div class="step-num">✓</div>
                        <div>
                            <h3>1 · Pre-observation planning</h3>
                            <p>Lesson plan reviewed · focus agreed: questioning techniques</p>
                            <div class="meta">completed Sep 18 · 09:41 · by M. Santiago</div>
                        </div>
                        <span class="status done">Done</span>
                    </div>
                    <div class="step done">
                        <div class="step-num">✓</div>
                        <div>
                            <h3>2 · Pre-observation conference</h3>
                            <p>Objectives, strategies and success criteria aligned</p>
                            <div class="meta">completed Sep 19 · 14:02 · 22 min</div>
                        </div>
                        <span class="status done">Done</span>
                    </div>
                    <div class="step now">
                        <div class="step-num">3</div>
                        <div>
                            <h3>3 · Classroom observation</h3>
                            <p>Encoding 5 of 8 COT indicators · draft autosaved locally</p>
                            <div class="meta">in progress · started Sep 24 · device tablet-07</div>
                        </div>
                        <span class="status now">In progress</span>
                    </div>
                    <div class="step">
                        <div class="step-num">4</div>
                        <div>
                            <h3>4 · Post-observation conference</h3>
                            <p>Feedback discussion and coaching agreement</p>
                            <div class="meta">locked until observation is submitted</div>
                        </div>
                        <span class="status todo">Queued</span>
                    </div>
                </div>
            </section>

            <section class="panel" aria-label="Recent activity">
                <div class="panel-head">
                    <h2>Recent activity</h2>
                    <span class="hint">Across your assigned schools</span>
                    <span class="link">View all →</span>
                </div>
                <table class="grid">
                    <thead><tr>
                        <th>Observee</th><th>Instrument</th><th>Date</th><th>Score</th><th>Status</th>
                    </tr></thead>
                    <tbody>
                        <tr>
                            <td><b>R. Dela Cruz</b><br><span class="mono">Teacher III · Science</span></td>
                            <td class="mono">COT-RPMS</td>
                            <td class="mono">Sep 24</td>
                            <td><span class="score hi">5.4</span></td>
                            <td><span class="status now">Rating</span></td>
                        </tr>
                        <tr>
                            <td><b>M. Santos</b><br><span class="mono">Teacher II · English</span></td>
                            <td class="mono">COT-RPMS</td>
                            <td class="mono">Sep 22</td>
                            <td><span class="score hi">5.8</span></td>
                            <td><span class="status done">Finalized</span></td>
                        </tr>
                        <tr>
                            <td><b>J. Ramos</b><br><span class="mono">Teacher I · Math</span></td>
                            <td class="mono">COT-RPMS</td>
                            <td class="mono">Sep 19</td>
                            <td><span class="score mid">4.1</span></td>
                            <td><span class="status done">Finalized</span></td>
                        </tr>
                        <tr>
                            <td><b>A. Villanueva</b><br><span class="mono">School Head</span></td>
                            <td class="mono">EPOC</td>
                            <td class="mono">Sep 17</td>
                            <td><span class="score lo">—</span></td>
                            <td><span class="status todo">Scheduled</span></td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </div>
    </main>

    <!-- ══════════ RIGHT · utility rail ══════════ -->
    <aside class="rail" aria-label="Contextual utilities">
        <div class="mod">
            <div class="mod-head"><h3>System insights</h3><span class="tick warn"></span></div>
            <div class="mod-body">
                <div class="insight">
                    <div class="insight-ico">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#58a6ff" stroke-width="2"><path d="M21 12a9 9 0 1 1-2.6-6.4M21 4v5h-5"/></svg>
                    </div>
                    <div><b>Sync health</b><span>3 records queued · last push 2h ago</span>
                        <div class="bar"><i style="width:64%"></i></div>
                    </div>
                </div>
                <div class="insight">
                    <div class="insight-ico">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#8957e5" stroke-width="2"><path d="M12 3v1m6.4 1.6-.7.7M21 12h-1M4 12H3m3.3-5.7-.7-.7m2.8 9.9a5 5 0 1 1 7 0l-.5.6c-.6.6-1 1.5-1 2.4V19a2 2 0 0 1-4 0v-.5c0-.9-.4-1.8-1-2.4l-.5-.6Z"/></svg>
                    </div>
                    <div><b>AI analysis</b><span>2 suggestions ready for review</span></div>
                </div>
                <div class="insight">
                    <div class="insight-ico">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#3fb950" stroke-width="2"><path d="M4 20V10M10 20V4M16 20v-8M22 20H2"/></svg>
                    </div>
                    <div><b>District trend</b><span>Domain 3 up +0.6 this quarter</span></div>
                </div>
            </div>
        </div>

        <div class="mod">
            <div class="mod-head"><h3>Quick actions</h3></div>
            <div class="mod-body">
                <span class="act-btn solid">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 16a4 4 0 0 1-.9-7.9A5 5 0 1 1 15.9 6 5 5 0 0 1 17 15.9M9 19l3 3 3-3M12 10v12"/></svg>
                    Prepare for offline
                </span>
                <span class="act-btn">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12a9 9 0 1 1-2.6-6.4M21 4v5h-5"/></svg>
                    Sync now · 3 pending
                </span>
                <span class="act-btn">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5Z"/></svg>
                    Draft coaching agreement
                </span>
            </div>
        </div>

        <div class="mod">
            <div class="mod-head"><h3>Observation metadata</h3><span class="tick"></span></div>
            <div class="mod-body">
                <dl>
                    <div class="kv"><dt>Observation</dt><dd>#OBS-1042</dd></div>
                    <div class="kv"><dt>Stage</dt><dd>Classroom obs.</dd></div>
                    <div class="kv"><dt>Coverage</dt><dd>5 / 8 indicators</dd></div>
                    <div class="kv"><dt>Device</dt><dd class="mono">tablet-07</dd></div>
                    <div class="kv"><dt>Saved</dt><dd>local · 4 min ago</dd></div>
                </dl>
            </div>
        </div>

        <div class="mod">
            <div class="mod-head"><h3>Focus tags</h3></div>
            <div class="mod-body">
                <div class="tags">
                    <span class="tag">Questioning</span>
                    <span class="tag">Feedback</span>
                    <span class="tag dim">Group work</span>
                    <span class="tag dim">Assessment</span>
                </div>
            </div>
        </div>
    </aside>

</div>
</body>
</html>
