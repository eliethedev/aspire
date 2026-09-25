{{-- Mockup sidebar (preview-only). Functional collapse toggle, mobile
     hamburger/off-canvas menu, and role-accurate hierarchical trees that
     mirror the real app sidebars (partials/sidebar.blade.php for
     teacher/supervisor/school-head, partials/admin/sidebar.blade.php).
     Expects $role and $cfg (console, user, userSub) from the host page. --}}
@php
  $mkRole = $role ?? 'supervisor';
  $mkTrees = [
    'admin' => [
      'label' => 'System / Folders',
      'folders' => [
        ['id' => 'standards', 'label' => 'Standards', 'open' => true, 'children' => [
          ['label' => 'COT Templates', 'count' => 'v2025'],
          ['label' => 'PPST Standards', 'count' => '7 domains'],
        ]],
        ['id' => 'management', 'label' => 'Management', 'open' => true, 'children' => [
          ['label' => 'Users', 'count' => '128'],
          ['label' => 'Schools', 'count' => '12'],
          ['label' => 'Teachers', 'count' => '96'],
          ['label' => 'Supervisors', 'count' => '8'],
        ]],
        ['id' => 'registration', 'label' => 'Registration', 'open' => false, 'children' => [
          ['label' => 'Register Account'],
          ['label' => 'Invitations', 'count' => '5'],
        ]],
        ['id' => 'other', 'label' => 'Other', 'open' => false, 'children' => [
          ['label' => 'Observations', 'count' => '342'],
          ['label' => 'Announcements'],
          ['label' => 'Support Messages', 'count' => '3'],
          ['label' => 'Audit Logs'],
          ['label' => 'AI Settings'],
          ['label' => 'AI Usage & Cost'],
        ]],
      ],
      'system' => [
        ['label' => 'Support inbox', 'count' => '3'],
        ['label' => 'Settings'],
      ],
    ],
    'supervisor' => [
      'label' => 'Observation Groups',
      'folders' => [
        ['id' => 'observations', 'label' => 'Observations', 'open' => false, 'children' => [
          ['label' => 'All Observations', 'count' => '12'],
          ['label' => 'Schedule Observation'],
          ['label' => 'Completed', 'count' => '28'],
        ]],
        ['id' => 'ratees', 'label' => 'Ratees', 'open' => false, 'children' => [
          ['label' => 'Teachers', 'count' => '24'],
          ['label' => 'School Heads', 'count' => '3'],
        ]],
        ['id' => 'postobs', 'label' => 'Post-Observation', 'open' => false, 'children' => [
          ['label' => 'Conferences'],
          ['label' => 'Action Plans'],
          ['label' => 'Career Progression'],
          ['label' => 'Career Monitor'],
        ]],
        ['id' => 'reports', 'label' => 'Reports', 'open' => false, 'children' => [
          ['label' => 'Observation Reports'],
          ['label' => 'Analytics'],
          ['label' => 'Teacher Performance'],
        ]],
      ],
      'schools' => [
        ['id' => 's1', 'label' => 'San Isidro ES', 'open' => true, 'children' => [
          ['id' => 's1g7', 'label' => 'Grade 7 · Science', 'open' => true, 'children' => [
            ['label' => 'R. Dela Cruz — COT 04', 'live' => true],
            ['label' => 'M. Santos — COT 02'],
            ['label' => 'J. Ramos — COT 01'],
          ]],
          ['id' => 's1g8', 'label' => 'Grade 8 · Math', 'open' => false, 'children' => [
            ['label' => 'L. Torres — COT 02'],
          ]],
        ]],
        ['id' => 's2', 'label' => 'Sta. Maria HS', 'open' => false, 'children' => [
          ['label' => 'A. Villanueva — EPOC'],
        ]],
        ['id' => 's3', 'label' => 'Archived · SY 2024–25', 'open' => false, 'children' => []],
      ],
      'system' => [
        ['label' => 'Offline sync', 'count' => '3'],
        ['label' => 'Settings'],
      ],
    ],
    'school-head' => [
      'label' => 'School / Folders',
      'folders' => [
        ['id' => 'supervision', 'label' => 'Supervision', 'open' => true, 'children' => [
          ['label' => 'Classroom Observations', 'count' => '24'],
          ['label' => 'Schedule Observation'],
          ['label' => 'Co-Observations', 'count' => '2'],
        ]],
        ['id' => 'faculty', 'label' => 'Faculty & Approvals', 'open' => true, 'children' => [
          ['label' => 'Teachers', 'count' => '18'],
          ['label' => 'Lesson Plans', 'count' => '6'],
          ['label' => 'Career Advancements', 'count' => '2'],
        ]],
        ['id' => 'insights', 'label' => 'Insights & Reports', 'open' => false, 'children' => [
          ['label' => 'AI Feedback & Coaching'],
          ['label' => 'Analytics & Reports'],
          ['label' => 'Notifications', 'count' => '4'],
        ]],
      ],
      'schools' => [
        ['id' => 'h1', 'label' => 'San Isidro ES', 'open' => true, 'children' => [
          ['id' => 'h1t', 'label' => 'Science Dept', 'open' => true, 'children' => [
            ['label' => 'R. Dela Cruz — COT 04', 'live' => true],
            ['label' => 'M. Santos — COT 02'],
          ]],
          ['id' => 'h1m', 'label' => 'Math Dept', 'open' => false, 'children' => [
            ['label' => 'J. Ramos — COT 01'],
          ]],
        ]],
      ],
      'system' => [
        ['label' => 'Notifications', 'count' => '4'],
        ['label' => 'Settings'],
      ],
    ],
    'teacher' => [
      'label' => 'My Growth Folders',
      'folders' => [
        ['id' => 'observations', 'label' => 'Observations', 'open' => true, 'children' => [
          ['label' => 'My Observations', 'count' => '4'],
          ['label' => 'Upcoming', 'count' => '1'],
        ]],
        ['id' => 'feedback', 'label' => 'Feedback', 'open' => true, 'children' => [
          ['label' => 'Feedback & Coaching', 'count' => '3'],
          ['label' => 'Improvement Plan'],
        ]],
        ['id' => 'analytics', 'label' => 'Analytics', 'open' => false, 'children' => [
          ['label' => 'Performance Analytics'],
        ]],
      ],
      'schools' => [
        ['id' => 't1', 'label' => 'SY 2025–26 · Science 7', 'open' => true, 'children' => [
          ['label' => 'COT 04 — Sep 24', 'live' => true],
          ['label' => 'COT 03 — Aug 28'],
          ['label' => 'COT 02 — Jun 12'],
          ['label' => 'COT 01 — Mar 03'],
        ]],
        ['id' => 't2', 'label' => 'Archived · SY 2024–25', 'open' => false, 'children' => []],
      ],
      'system' => [
        ['label' => 'Notifications', 'count' => '2'],
        ['label' => 'Settings'],
      ],
    ],
  ];
  $mk = $mkTrees[$mkRole] ?? $mkTrees['supervisor'];
  $mkWorkspace = [
    'admin' => [['Dashboard', true, null], ['Profile', false, null], ['Calendar', false, null]],
    'supervisor' => [['Dashboard', true, null], ['Observations', false, '12'], ['Calendar', false, null], ['Reports', false, null]],
    'school-head' => [['Dashboard', true, null], ['Profile', false, null], ['Calendar', false, null]],
    'teacher' => [['Dashboard', true, null], ['Profile', false, null], ['Calendar', false, null]],
  ][$mkRole] ?? [['Dashboard', true, null]];
@endphp
<style>
    /* ── Mockup sidebar: collapse + mobile + trees (preview-only) ── */
    .mk-toggle{display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;flex:none;border:1px solid var(--line);border-radius:8px;background:var(--panel-3);color:var(--muted);cursor:pointer}
    .mk-toggle:hover{color:var(--text);border-color:var(--accent-line)}
    .mk-toggle svg{width:15px;height:15px}
    .mk-close{display:none}
    .mk-hamb{display:none;align-items:center;justify-content:center;width:34px;height:34px;flex:none;border:1px solid var(--line);border-radius:8px;background:var(--panel-3);color:var(--text);cursor:pointer}
    .mk-hamb svg{width:17px;height:17px}
    .mk-backdrop{display:none}
    .brand-text{min-width:0}
    /* folder tree */
    .tree{padding:2px 4px}
    .tree-node{border-radius:7px}
    .tree-row{width:100%;display:flex;align-items:center;gap:7px;padding:6px 8px;border:0;border-radius:7px;background:transparent;color:var(--muted);font-size:12.5px;font-family:inherit;cursor:pointer;text-align:left}
    .tree-row:hover{background:var(--panel-2);color:var(--text)}
    .tree-row .caret{width:12px;flex:none;color:var(--faint);font-size:9px;text-align:center;transition:transform .18s ease}
    .tree-row[aria-expanded="true"] .caret{transform:rotate(90deg)}
    .tree-children{margin-left:17px;border-left:1px solid var(--line-soft);padding-left:4px}
    .tree-children[hidden]{display:none}
    .file-link{display:flex;align-items:center;gap:8px;padding:5px 8px;border-radius:6px;color:var(--muted);font-size:12.5px;cursor:pointer;border:0;background:transparent;width:100%;font-family:inherit;text-align:left}
    .file-link:hover{background:var(--panel-2);color:var(--text)}
    .file-link .dot{width:5px;height:5px;border-radius:50%;background:var(--faint);flex:none}
    .file-link.live .dot{background:var(--accent);box-shadow:0 0 6px rgba(47,129,247,.9)}
    .file-link.live{color:var(--text)}
    .file-link .count{margin-left:auto;font-family:var(--mono);font-size:10px;color:var(--faint)}
    /* collapsed rail (desktop) */
    body.mk-collapsed .app{grid-template-columns:72px minmax(0,1fr) 304px}
    body.mk-collapsed .side{width:72px}
    body.mk-collapsed .brand{justify-content:center;padding:16px 8px}
    body.mk-collapsed .brand-text,body.mk-collapsed .side-search,body.mk-collapsed .nav-label,
    body.mk-collapsed .nav-item span.lbl,body.mk-collapsed .nav-item .count,
    body.mk-collapsed .tree,body.mk-collapsed .role-switch-wrap,body.mk-collapsed .user-meta,
    body.mk-collapsed .user-card .logout-hint{display:none}
    body.mk-collapsed .nav-item{justify-content:center;padding:8px 0}
    body.mk-collapsed .user-card{justify-content:center;margin:10px 8px;padding:8px}
    body.mk-collapsed .mk-toggle .ico-open{display:none}
    body.mk-collapsed .mk-toggle .ico-closed{display:block}
    .mk-toggle .ico-closed{display:none}
    @media(max-width:1180px){body.mk-collapsed .app{grid-template-columns:72px minmax(0,1fr)}}
    /* mobile off-canvas */
    @media(max-width:860px){
        .app{grid-template-columns:minmax(0,1fr)}
        .mk-hamb{display:inline-flex}
        .side{position:fixed;left:0;top:0;bottom:0;width:264px;z-index:60;transform:translateX(-105%);transition:transform .25s ease;box-shadow:8px 0 30px rgba(0,0,0,.45)}
        body.mk-open .side{transform:translateX(0)}
        body.mk-open .mk-backdrop{display:block;position:fixed;inset:0;z-index:55;background:rgba(2,6,12,.6)}
        .mk-close{display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;flex:none;border:1px solid var(--line);border-radius:8px;background:var(--panel-3);color:var(--muted);cursor:pointer}
        .mk-close svg{width:14px;height:14px}
        .mk-toggle{display:none}
        body.mk-collapsed .app{grid-template-columns:minmax(0,1fr)}
        body.mk-collapsed .side{width:264px}
        body.mk-collapsed .brand-text,body.mk-collapsed .side-search,body.mk-collapsed .nav-label,
        body.mk-collapsed .nav-item span.lbl,body.mk-collapsed .tree,body.mk-collapsed .user-meta{display:block}
        body.mk-collapsed .nav-item{justify-content:flex-start}
    }
    .tree-row:focus-visible,.file-link:focus-visible,.nav-item:focus-visible,.mk-toggle:focus-visible,.mk-hamb:focus-visible,.mk-close:focus-visible{outline:2px solid var(--accent);outline-offset:2px}
    @media(prefers-reduced-motion:reduce){.side,.tree-row .caret{transition:none}}
</style>
<div class="mk-backdrop" id="mkBackdrop" aria-hidden="true"></div>
<aside class="side" id="mkSide" aria-label="Primary navigation">
    <div class="brand">
        <div class="brand-mark" aria-hidden="true">A</div>
        <div class="brand-text">
            <div class="brand-name">ASPIRE</div>
            <div class="brand-sub">{{ $cfg['console'] ?? 'Console' }}</div>
        </div>
        <button class="mk-toggle" id="mkToggle" type="button" aria-label="Hide side menu" aria-expanded="true" aria-controls="mkSideNav" title="Hide side menu">
            <svg class="ico-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h10M4 18h16M18 10l-2 2 2 2"/></svg>
            <svg class="ico-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h10M4 18h16M16 10l2 2-2 2"/></svg>
        </button>
        <button class="mk-close" id="mkClose" type="button" aria-label="Close side menu" title="Close side menu">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
        </button>
    </div>

    <div class="side-search">
        <div class="search">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
            Quick find… <kbd>⌘K</kbd>
        </div>
    </div>

    <nav class="nav" id="mkSideNav" aria-label="Mockup menu">
        <div class="nav-label">Workspace</div>
        @foreach($mkWorkspace as $w)
            <div class="nav-item {{ $w[1] ? 'active' : '' }}" {{ $w[1] ? 'aria-current="page"' : '' }} tabindex="0" title="{{ $w[0] }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
                <span class="lbl">{{ $w[0] }}</span>
                @if(!empty($w[2]))<span class="count">{{ $w[2] }}</span>@endif
            </div>
        @endforeach

        <div class="nav-label">{{ $mk['label'] }}</div>
        <div class="tree" role="tree" aria-label="{{ $mk['label'] }}">
            @foreach($mk['folders'] as $f)
                <div class="tree-node" role="treeitem" aria-expanded="{{ $f['open'] ? 'true' : 'false' }}">
                    <button class="tree-row" type="button" data-folder="{{ $f['id'] }}" aria-expanded="{{ $f['open'] ? 'true' : 'false' }}">
                        <span class="caret">▸</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#58a6ff" stroke-width="2" aria-hidden="true"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/></svg>
                        <span>{{ $f['label'] }}</span>
                    </button>
                    <div class="tree-children" data-children="{{ $f['id'] }}" @if(!$f['open']) hidden @endif>
                        @foreach($f['children'] as $c)
                            <button class="file-link {{ !empty($c['live']) ? 'live' : '' }}" type="button"><span class="dot" aria-hidden="true"></span>{{ $c['label'] }}@if(!empty($c['count']))<span class="count">{{ $c['count'] }}</span>@endif</button>
                        @endforeach
                    </div>
                </div>
            @endforeach
            @if(!empty($mk['schools']))
                @foreach($mk['schools'] as $s)
                    <div class="tree-node" role="treeitem" aria-expanded="{{ $s['open'] ? 'true' : 'false' }}">
                        <button class="tree-row" type="button" data-folder="{{ $s['id'] }}" aria-expanded="{{ $s['open'] ? 'true' : 'false' }}">
                            <span class="caret">▸</span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#58a6ff" stroke-width="2" aria-hidden="true"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1 2 2H5a2 2 0 0 1-2-2V7Z"/></svg>
                            <span>{{ $s['label'] }}</span>
                        </button>
                        <div class="tree-children" data-children="{{ $s['id'] }}" @if(!$s['open']) hidden @endif>
                            @foreach($s['children'] as $ch)
                                @if(isset($ch['children']))
                                    <div class="tree-node" role="treeitem" aria-expanded="{{ $ch['open'] ? 'true' : 'false' }}">
                                        <button class="tree-row" type="button" data-folder="{{ $ch['id'] }}" aria-expanded="{{ $ch['open'] ? 'true' : 'false' }}">
                                            <span class="caret">▸</span>
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#58a6ff" stroke-width="2" aria-hidden="true"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1 2 2H5a2 2 0 0 1-2-2V7Z"/></svg>
                                            <span>{{ $ch['label'] }}</span>
                                        </button>
                                        <div class="tree-children" data-children="{{ $ch['id'] }}" @if(!$ch['open']) hidden @endif>
                                            @foreach($ch['children'] as $leaf)
                                                <button class="file-link {{ !empty($leaf['live']) ? 'live' : '' }}" type="button"><span class="dot" aria-hidden="true"></span>{{ $leaf['label'] }}</button>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <button class="file-link {{ !empty($ch['live']) ? 'live' : '' }}" type="button"><span class="dot" aria-hidden="true"></span>{{ $ch['label'] }}</button>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        <div class="nav-label">System</div>
        @foreach($mk['system'] as $s)
            <div class="nav-item" tabindex="0" title="{{ $s['label'] }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M19 12a7 7 0 0 0-.1-1.2l2-1.6-2-3.4-2.4 1a7 7 0 0 0-2-1.2L14 3h-4l-.5 2.6a7 7 0 0 0-2 1.2l-2.4-1-2 3.4 2 1.6A7 7 0 0 0 5 12c0 .4 0 .8.1 1.2l-2 1.6 2 3.4 2.4-1a7 7 0 0 0 2 1.2L10 21h4l.5-2.6a7 7 0 0 0 2-1.2l2.4 1 2-3.4-2-1.6c.1-.4.1-.8.1-1.2Z"/></svg>
                <span class="lbl">{{ $s['label'] }}</span>
                @if(!empty($s['count']))<span class="count">{{ $s['count'] }}</span>@endif
            </div>
        @endforeach

        <div class="nav-label role-switch-wrap">Preview roles</div>
        <div class="role-switch role-switch-wrap">
            @foreach(['admin','supervisor','school-head','teacher'] as $r)
                <a href="{{ route('mockups.dashboard.role', $r) }}" class="{{ $mkRole === $r ? 'on' : '' }}" {{ $mkRole === $r ? 'aria-current="page"' : '' }}>{{ $r }}</a>
            @endforeach
        </div>
    </nav>

    <div class="user-card">
        <div class="avatar" aria-hidden="true">{{ strtoupper(substr($cfg['user'] ?? 'U', 0, 1)) }}</div>
        <div class="user-meta"><b>{{ $cfg['user'] ?? '' }}</b><span>{{ $cfg['userSub'] ?? '' }}</span></div>
    </div>
</aside>
<script>
(function () {
    var role = @json($mkRole);
    var body = document.body;
    var side = document.getElementById('mkSide');
    var toggle = document.getElementById('mkToggle');
    var closeBtn = document.getElementById('mkClose');
    var backdrop = document.getElementById('mkBackdrop');
    if (!side) return;

    /* ── Desktop collapse (persisted) ── */
    var collapseKey = 'mk-side-collapsed';
    try {
        if (localStorage.getItem(collapseKey) === '1') {
            body.classList.add('mk-collapsed');
            if (toggle) { toggle.setAttribute('aria-expanded', 'false'); toggle.setAttribute('aria-label', 'Show side menu'); toggle.title = 'Show side menu'; }
        }
    } catch (e) {}
    function setCollapsed(collapsed) {
        body.classList.toggle('mk-collapsed', collapsed);
        if (toggle) {
            toggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            toggle.setAttribute('aria-label', collapsed ? 'Show side menu' : 'Hide side menu');
            toggle.title = collapsed ? 'Show side menu' : 'Hide side menu';
        }
        try { localStorage.setItem(collapseKey, collapsed ? '1' : '0'); } catch (e) {}
    }
    if (toggle) toggle.addEventListener('click', function () { setCollapsed(!body.classList.contains('mk-collapsed')); });

    /* ── Mobile off-canvas + hamburger ── */
    function openMobile() { body.classList.add('mk-open'); if (closeBtn) closeBtn.focus(); }
    function closeMobile() { body.classList.remove('mk-open'); var h = document.getElementById('mkHamb'); if (h) h.focus(); }
    document.addEventListener('click', function (ev) {
        var t = ev.target.closest('#mkHamb');
        if (t) { body.classList.contains('mk-open') ? closeMobile() : openMobile(); }
    });
    if (closeBtn) closeBtn.addEventListener('click', closeMobile);
    if (backdrop) backdrop.addEventListener('click', closeMobile);
    document.addEventListener('keydown', function (ev) {
        if (ev.key === 'Escape' && body.classList.contains('mk-open')) closeMobile();
        if ((ev.metaKey || ev.ctrlKey) && ev.key.toLowerCase() === 'k') ev.preventDefault();
    });
    side.addEventListener('click', function (ev) {
        if (window.innerWidth <= 860 && ev.target.closest('.nav-item, .file-link, .role-switch a')) closeMobile();
    });

    /* ── Hierarchical folder toggles (persisted per role) ── */
    var treeKey = 'mk-tree-' + role;
    var openSet = null;
    try { openSet = JSON.parse(localStorage.getItem(treeKey) || 'null'); } catch (e) {}
    function persist() {
        if (!openSet) return;
        try { localStorage.setItem(treeKey, JSON.stringify(Array.from(openSet))); } catch (e) {}
    }
    function applyState(btn, open) {
        var id = btn.getAttribute('data-folder');
        var kids = side.querySelector('[data-children="' + id + '"]');
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
        var node = btn.closest('.tree-node');
        if (node) node.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (kids) { if (open) kids.removeAttribute('hidden'); else kids.setAttribute('hidden', ''); }
    }
    var folders = side.querySelectorAll('.tree-row[data-folder]');
    if (Array.isArray(openSet)) {
        folders.forEach(function (btn) { applyState(btn, openSet.indexOf(btn.getAttribute('data-folder')) !== -1); });
    } else {
        openSet = new Set();
        folders.forEach(function (btn) {
            if (btn.getAttribute('aria-expanded') === 'true') openSet.add(btn.getAttribute('data-folder'));
        });
    }
    folders.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var open = btn.getAttribute('aria-expanded') !== 'true';
            applyState(btn, open);
            var id = btn.getAttribute('data-folder');
            if (open) openSet.add(id); else openSet.delete(id);
            persist();
        });
    });

    /* ── File selection is visual-only in the mockup ── */
    side.addEventListener('click', function (ev) {
        var f = ev.target.closest('.file-link');
        if (!f) return;
        side.querySelectorAll('.file-link.live').forEach(function (el) { el.classList.remove('live'); });
        f.classList.add('live');
    });
})();
</script>
