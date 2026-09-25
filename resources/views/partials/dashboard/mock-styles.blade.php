@push('styles')
<style>
    /* ── Shared mockup dashboard design system (all roles) ──────────────
       Mirrors resources/views/mockups/dashboard.blade.php visual language,
       adapted to render inside the real app layouts (which already provide
       the sidebar + header). Sidebar logos are intentionally untouched. */
    .mock-wrap {
        --m-bg: #f1f5f9;
        --m-panel: #ffffff;
        --m-panel-2: #f8fafc;
        --m-panel-3: #eef2f7;
        --m-line: #e2e8f0;
        --m-line-soft: #eef2f7;
        --m-text: #0f172a;
        --m-muted: #64748b;
        --m-faint: #94a3b8;
        --m-accent: #2f81f7;
        --m-accent-soft: rgba(47,129,247,.10);
        --m-accent-line: rgba(47,129,247,.45);
        --m-green: #16a34a;
        --m-amber: #d97706;
        --m-rose: #e11d48;
    }
    .dark .mock-wrap {
        --m-bg: #101318;
        --m-panel: #151a21;
        --m-panel-2: #181e26;
        --m-panel-3: #1c232d;
        --m-line: #242c37;
        --m-line-soft: #1e2530;
        --m-text: #e6e9ee;
        --m-muted: #8b949e;
        --m-faint: #5c6672;
        --m-accent: #2f81f7;
        --m-accent-soft: rgba(47,129,247,.12);
        --m-accent-line: rgba(47,129,247,.45);
        --m-green: #3fb950;
        --m-amber: #d29922;
        --m-rose: #f85149;
    }
    .mock-grid { display: grid; grid-template-columns: minmax(0,1fr) 300px; gap: 16px; align-items: start; }
    @media (max-width: 1180px) { .mock-grid { grid-template-columns: minmax(0,1fr); } .mock-rail { display: grid; grid-template-columns: repeat(auto-fit,minmax(240px,1fr)); gap: 12px; } }
    .mock-topbar { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; padding: 12px 16px; border: 1px solid var(--m-line); border-radius: 12px; background: var(--m-panel); }
    .mock-crumbs { display: flex; align-items: center; gap: 8px; font-size: 12.5px; color: var(--m-faint); min-width: 0; }
    .mock-crumbs b { color: var(--m-text); font-weight: 600; }
    .mock-pill { display: inline-flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 600; border: 1px solid var(--m-line); border-radius: 20px; padding: 3px 10px; color: var(--m-muted); white-space: nowrap; background: var(--m-panel); }
    .mock-pill .pulse { width: 7px; height: 7px; border-radius: 50%; background: var(--m-green); }
    .mock-pill.amber { border-color: rgba(210,153,34,.4); color: var(--m-amber); background: rgba(210,153,34,.08); }
    .mock-pill.amber .pulse { background: var(--m-amber); }
    .mock-actions { margin-left: auto; display: flex; gap: 8px; flex-wrap: wrap; }
    .mock-btn { display: inline-flex; align-items: center; gap: 7px; font-size: 12.5px; font-weight: 600; border: 1px solid var(--m-line); border-radius: 8px; padding: 7px 13px; color: var(--m-text); background: var(--m-panel-3); white-space: nowrap; text-decoration: none; }
    .mock-btn:hover { border-color: var(--m-accent-line); }
    .mock-btn.primary { background: #1f6feb; border-color: #1f6feb; color: #fff; }
    .mock-btn.primary:hover { background: #2f81f7; }
    .mock-title { display: flex; align-items: flex-end; gap: 14px; margin: 16px 2px 14px; flex-wrap: wrap; }
    .mock-title h1 { font-size: 19px; font-weight: 650; letter-spacing: -.01em; color: var(--m-text); }
    .mock-title p { font-size: 12.5px; color: var(--m-muted); }
    .mock-title time { margin-left: auto; font-family: ui-monospace,SFMono-Regular,Consolas,monospace; font-size: 11px; color: var(--m-faint); }
    .mock-kpis { display: grid; grid-template-columns: repeat(4,minmax(0,1fr)); gap: 12px; margin-bottom: 16px; }
    @media (max-width: 900px) { .mock-kpis { grid-template-columns: repeat(2,minmax(0,1fr)); } }
    .mock-kpi { background: var(--m-panel); border: 1px solid var(--m-line); border-radius: 10px; padding: 13px 15px; position: relative; overflow: hidden; transition: transform .15s ease, box-shadow .15s ease; }
    .mock-kpi:hover { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(15,23,42,.08); }
    .mock-kpi::before { content: ""; position: absolute; left: 0; top: 0; bottom: 0; width: 2px; background: var(--m-line); }
    .mock-kpi.hot::before { background: var(--m-accent); }
    .mock-kpi label { font-size: 10px; font-weight: 650; letter-spacing: .1em; text-transform: uppercase; color: var(--m-faint); }
    .mock-kpi .val { font-size: 22px; font-weight: 700; letter-spacing: -.02em; margin-top: 3px; color: var(--m-text); font-variant-numeric: tabular-nums; }
    .mock-kpi .val small { font-size: 12px; font-weight: 500; color: var(--m-muted); }
    .mock-kpi .delta { font-size: 11px; margin-top: 2px; font-variant-numeric: tabular-nums; }
    .mock-up { color: var(--m-green); } .mock-down { color: var(--m-rose); } .mock-flat { color: var(--m-muted); }
    .mock-panel { background: var(--m-panel); border: 1px solid var(--m-line); border-radius: 10px; margin-bottom: 16px; overflow: hidden; }
    .mock-panel-head { display: flex; align-items: center; gap: 10px; padding: 12px 16px; border-bottom: 1px solid var(--m-line-soft); flex-wrap: wrap; }
    .mock-panel-head h2 { font-size: 13px; font-weight: 650; color: var(--m-text); }
    .mock-panel-head .hint { font-size: 11.5px; color: var(--m-faint); }
    .mock-panel-head .link { margin-left: auto; font-size: 12px; font-weight: 600; color: var(--m-accent); white-space: nowrap; text-decoration: none; }
    .mock-steps { padding: 8px 0; }
    .mock-step { display: grid; grid-template-columns: 34px minmax(0,1fr) auto; gap: 12px; align-items: start; padding: 11px 16px; border-bottom: 1px solid var(--m-line-soft); }
    .mock-step:last-child { border-bottom: 0; }
    .mock-step:hover { background: var(--m-panel-2); }
    .mock-step-num { width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; border: 1px solid var(--m-line); color: var(--m-muted); background: var(--m-panel-3); margin-top: 1px; }
    .mock-step.done .mock-step-num { background: rgba(63,185,80,.14); border-color: rgba(63,185,80,.5); color: var(--m-green); }
    .mock-step.now .mock-step-num { background: var(--m-accent-soft); border-color: var(--m-accent); color: var(--m-accent); box-shadow: 0 0 0 3px rgba(47,129,247,.12); }
    .mock-step h3 { font-size: 13px; font-weight: 600; color: var(--m-text); }
    .mock-step p { font-size: 12px; color: var(--m-muted); margin-top: 1px; }
    .mock-step .meta { font-family: ui-monospace,SFMono-Regular,Consolas,monospace; font-size: 10.5px; color: var(--m-faint); margin-top: 4px; }
    .mock-status { font-size: 10.5px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; padding: 3px 9px; border-radius: 20px; white-space: nowrap; margin-top: 2px; }
    .mock-status.done { color: var(--m-green); background: rgba(63,185,80,.1); border: 1px solid rgba(63,185,80,.35); }
    .mock-status.now { color: var(--m-accent); background: var(--m-accent-soft); border: 1px solid var(--m-accent-line); }
    .mock-status.todo { color: var(--m-faint); background: transparent; border: 1px solid var(--m-line); }
    table.mock-grid-table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
    table.mock-grid-table th { text-align: left; font-size: 10px; font-weight: 650; letter-spacing: .1em; text-transform: uppercase; color: var(--m-faint); padding: 9px 16px; border-bottom: 1px solid var(--m-line); }
    table.mock-grid-table td { padding: 10px 16px; border-bottom: 1px solid var(--m-line-soft); vertical-align: middle; color: var(--m-text); }
    table.mock-grid-table tr:last-child td { border-bottom: 0; }
    table.mock-grid-table tbody tr:hover { background: var(--m-panel-2); }
    .mock-mono { font-family: ui-monospace,SFMono-Regular,Consolas,monospace; font-size: 11.5px; color: var(--m-muted); }
    .mock-score { display: inline-flex; align-items: center; justify-content: center; min-width: 38px; height: 24px; padding: 0 8px; border-radius: 6px; font-weight: 700; font-size: 12px; font-variant-numeric: tabular-nums; }
    .mock-score.hi { background: rgba(63,185,80,.13); color: var(--m-green); border: 1px solid rgba(63,185,80,.4); }
    .mock-score.mid { background: rgba(210,153,34,.12); color: var(--m-amber); border: 1px solid rgba(210,153,34,.4); }
    .mock-score.lo { background: rgba(248,81,73,.1); color: var(--m-rose); border: 1px solid rgba(248,81,73,.35); }
    .mock-rail { display: flex; flex-direction: column; gap: 14px; min-width: 0; }
    .mock-mod { background: var(--m-panel-2); border: 1px solid var(--m-line); border-radius: 10px; overflow: hidden; }
    .mock-mod-head { padding: 10px 13px; border-bottom: 1px solid var(--m-line-soft); display: flex; align-items: center; gap: 8px; }
    .mock-mod-head h3 { font-size: 11px; font-weight: 700; letter-spacing: .09em; text-transform: uppercase; color: var(--m-muted); }
    .mock-mod-head .tick { margin-left: auto; width: 7px; height: 7px; border-radius: 50%; background: var(--m-green); }
    .mock-mod-head .tick.warn { background: var(--m-amber); }
    .mock-mod-body { padding: 12px 13px; }
    .mock-insight { display: flex; gap: 10px; padding: 8px 0; border-bottom: 1px solid var(--m-line-soft); }
    .mock-insight:last-child { border-bottom: 0; padding-bottom: 0; }
    .mock-insight:first-child { padding-top: 0; }
    .mock-insight b { display: block; font-size: 12.5px; font-weight: 600; color: var(--m-text); }
    .mock-insight span { font-size: 11.5px; color: var(--m-muted); }
    .mock-bar { height: 5px; border-radius: 3px; background: var(--m-panel-3); margin-top: 7px; overflow: hidden; }
    .mock-bar i { display: block; height: 100%; border-radius: 3px; background: linear-gradient(90deg,#1f6feb,#58a6ff); }
    .mock-act { display: flex; align-items: center; gap: 9px; width: 100%; padding: 9px 12px; margin-bottom: 8px; border-radius: 8px; font-size: 12.5px; font-weight: 600; color: var(--m-text); background: var(--m-panel-3); border: 1px solid var(--m-line); text-decoration: none; box-sizing: border-box; }
    .mock-act:last-child { margin-bottom: 0; }
    .mock-act:hover { border-color: var(--m-accent-line); background: var(--m-accent-soft); }
    .mock-act.solid { background: #1f6feb; border-color: #1f6feb; color: #fff; }
    .mock-act.solid:hover { background: #2f81f7; }
    .mock-kv { display: flex; justify-content: space-between; gap: 10px; padding: 6px 0; border-bottom: 1px solid var(--m-line-soft); font-size: 12px; }
    .mock-kv:last-child { border-bottom: 0; }
    .mock-kv dt { color: var(--m-faint); } .mock-kv dd { color: var(--m-text); font-weight: 550; text-align: right; font-variant-numeric: tabular-nums; }
    .mock-tags { display: flex; flex-wrap: wrap; gap: 6px; }
    .mock-tag { font-size: 11px; font-weight: 600; color: var(--m-accent); background: var(--m-accent-soft); border: 1px solid var(--m-accent-line); border-radius: 20px; padding: 2px 9px; }
    .mock-tag.dim { color: var(--m-muted); background: transparent; border-color: var(--m-line); }
    .mock-empty { padding: 28px 16px; text-align: center; color: var(--m-muted); font-size: 12.5px; border: 1px dashed var(--m-line); border-radius: 8px; margin: 12px 16px; }
    /* ── Observation-group folder trees (mockup folder-column in content) ── */
    .mock-folder{border-bottom:1px solid var(--m-line-soft)}
    .mock-folder:last-child{border-bottom:0}
    .mock-folder > summary{list-style:none;display:flex;align-items:center;gap:8px;padding:10px 16px;cursor:pointer;font-size:13px;font-weight:600;color:var(--m-text)}
    .mock-folder > summary::-webkit-details-marker{display:none}
    .mock-folder > summary::marker{content:""}
    .mock-folder > summary:hover{background:var(--m-panel-2)}
    .mock-folder > summary .caret{width:12px;flex:none;font-size:9px;color:var(--m-faint);text-align:center;transition:transform .18s ease}
    .mock-folder[open] > summary .caret{transform:rotate(90deg)}
    .mock-folder > summary .hint{font-size:11.5px;color:var(--m-faint);font-weight:400}
    .mock-folder > summary .spacer{margin-left:auto;display:flex;align-items:center;gap:8px}
    .mock-file{display:flex;align-items:center;gap:8px;padding:5px 12px 5px 12px;text-decoration:none}
    .mock-file:hover{background:var(--m-panel-2)}
    .mock-file .mock-fdot{width:5px;height:5px;border-radius:50%;background:var(--m-faint);flex:none}
    .mock-file.live .mock-fdot{background:var(--m-accent);box-shadow:0 0 6px rgba(47,129,247,.9)}
    .mock-file .mock-fmain{min-width:0}
    .mock-file .mock-fmain b{display:block;font-size:11.5px;font-weight:600;color:var(--m-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .mock-file .mock-fmain span{font-size:10.5px;color:var(--m-muted)}
    .mock-file .mock-fright{margin-left:auto;display:flex;align-items:center;gap:6px;flex:none}
    .mock-file .mock-score{min-width:32px;height:22px;font-size:11px}
    .mock-file .mock-status{font-size:9.5px;padding:2px 8px}
    .mock-folder > div{margin-left:18px;border-left:1px solid var(--m-line-soft);padding:2px 0 2px 2px}
    .mock-folder > div .mock-empty{margin:8px 12px 8px 10px;padding:14px 10px}
</style>
@endpush
