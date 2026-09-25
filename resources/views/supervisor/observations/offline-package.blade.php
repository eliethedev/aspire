<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>ASPIRE · Offline Observation #{{ $observation->id }}</title>
<style>
    :root{--bg:#101318;--panel:#151a21;--panel-2:#181e26;--line:#242c37;--text:#e6e9ee;--muted:#8b949e;--accent:#2f81f7;--green:#3fb950;--amber:#d29922;--radius:10px;--font:"Inter",-apple-system,"Segoe UI",Roboto,Arial,sans-serif}
    *{box-sizing:border-box;margin:0;padding:0}
    body{background:var(--bg);color:var(--text);font-family:var(--font);font-size:13px;line-height:1.5;padding:16px;max-width:1180px;margin:0 auto}
    h1{font-size:18px;margin-bottom:2px} h3{font-size:13px;margin:14px 0 6px}
    .muted{color:var(--muted);font-size:12px}
    .bar{display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-bottom:14px}
    .badge{font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;border:1px solid var(--line);color:var(--muted)}
    .badge[data-state="offline"]{color:var(--amber);border-color:var(--amber)}
    .badge[data-state="pending"]{color:#9ec5ff;border-color:var(--accent)}
    .badge[data-state="synced"]{color:var(--green);border-color:var(--green)}
    .btn{display:inline-flex;align-items:center;gap:6px;font-size:12.5px;font-weight:600;border:1px solid var(--line);border-radius:8px;padding:8px 14px;color:var(--text);background:#1c232d;cursor:pointer;text-decoration:none}
    .btn.primary{background:#1f6feb;border-color:#1f6feb;color:#fff}
    .btn:disabled{opacity:.55;cursor:wait}
    .pkg-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1.2fr);gap:14px}
    @media(max-width:900px){.pkg-grid{grid-template-columns:minmax(0,1fr)}}
    .pkg-col{background:var(--panel);border:1px solid var(--line);border-radius:var(--radius);padding:14px 16px;min-width:0}
    .pkg-list{margin:4px 0 4px 16px;display:grid;gap:4px}
    .pkg-pre{white-space:pre-wrap;font-size:11.5px;color:var(--muted);background:var(--panel-2);border:1px solid var(--line);border-radius:8px;padding:10px;max-height:220px;overflow:auto}
    .pkg-flag{font-size:10px;font-weight:700;padding:2px 8px;border-radius:12px;background:rgba(210,153,34,.15);color:var(--amber);border:1px solid var(--amber)}
    .pkg-flag.ok{background:rgba(63,185,80,.12);color:var(--green);border-color:var(--green)}
    .pkg-ind{border:1px solid var(--line);border-radius:8px;padding:10px 12px;margin:10px 0;background:var(--panel-2)}
    .pkg-ind legend{padding:0 6px;font-size:12.5px}
    .pkg-row{display:flex;gap:10px;flex-wrap:wrap;margin:8px 0}
    .pkg-check{display:inline-flex;align-items:center;gap:6px;font-size:12px}
    select,textarea{width:100%;background:#0d1117;color:var(--text);border:1px solid var(--line);border-radius:8px;padding:7px 9px;font-size:12.5px;font-family:inherit}
    select{width:auto}
    textarea{margin-top:6px}
    label{font-size:12px;display:block;margin-top:8px}
    .pkg-live{background:var(--panel-2);border:1px solid var(--line);border-radius:8px;padding:8px 12px;margin:8px 0;font-size:12.5px}
    .pkg-actions{margin-top:12px;display:flex;gap:8px;align-items:center}
    details summary{cursor:pointer;color:var(--accent);font-size:12px}
    .offline-note{background:rgba(210,153,34,.1);border:1px solid var(--amber);border-radius:8px;padding:10px 12px;margin-bottom:14px;font-size:12.5px}
</style>
</head>
<body>
<h1>Offline Observation #{{ $observation->id }}</h1>
<p class="muted">{{ $bundle['observation']['teacher']['name'] ?? '' }} · {{ $bundle['observation']['subject'] ?? '' }} · {{ $bundle['observation']['observation_date'] ?? '' }}</p>
<div class="bar">
    <span class="badge" id="offline-package-badge" data-state="checking">Checking…</span>
    <button type="button" class="btn primary" id="pkg-sync">Sync Now</button>
    <span class="muted" id="pkg-saved-at"></span>
</div>
<noscript><div class="offline-note">JavaScript is disabled: the cached bundle below is read-only. Enable JavaScript to encode scores offline.</div></noscript>
<div id="offline-package-root" aria-live="polite"></div>
<script>window.ASPIRE_BASE_URL = {!! json_encode(url('/')) !!};</script>
<script src="{{ asset('js/aspire-offline-package.js') }}"></script>
<script>
(function () {
    var serverId = {{ (int) $observation->id }};
    // Server-rendered bundle for first paint (works even if IndexedDB was
    // cleared); the fresher IndexedDB copy wins when present.
    var serverBundle = {!! json_encode($bundle) !!};
    var P = window.AspireOfflinePackage;
    function stamp(ts) {
        var el = document.getElementById('pkg-saved-at');
        if (el && ts) el.textContent = 'Package saved ' + new Date(ts).toLocaleString();
    }
    function boot(bundle, savedAt) {
        P.renderPackageInto('#offline-package-root', bundle);
        stamp(savedAt);
        P.updateSyncBadge();
    }
    if (!P) { document.getElementById('offline-package-root').textContent = 'Offline engine failed to load.'; return; }
    P.getPackage(serverId).then(function (rec) {
        if (rec && rec.bundle) boot(rec.bundle, rec.saved_at);
        else boot(serverBundle, null);
    }).catch(function () { boot(serverBundle, null); });
    document.getElementById('pkg-sync').addEventListener('click', function () { P.syncNow().catch(function () {}); });
})();
</script>
</body>
</html>
