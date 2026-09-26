{{-- Shared mockup-accurate sidebar styling for the REAL system sidebars.
     Mirrors mockups/_sidebar.blade.php spacing + hierarchy (Workspace,
     folder trees, System, user card) in light and dark modes.
     Brand logo (<x-application-logo />) and Alpine collapse/mobile behavior
     are untouched — this only aligns visuals with the mockup. --}}
<style>
    /* ── Mockup nav container + items (data-mks-nav) ── */
    .sidebar-glass nav[data-mks-nav]{padding:10px 8px !important}
    .sidebar-glass nav[data-mks-nav] a.sidebar-link-hover,
    .sidebar-glass nav[data-mks-nav] .mks-ws-item{display:flex;align-items:center;gap:10px;padding:7px 10px !important;border-radius:7px !important;font-size:13px;font-weight:500;border:1px solid transparent}
    .sidebar-glass nav[data-mks-nav] a.sidebar-link-hover:hover{border-color:transparent}
    /* mockup active: soft accent fill + accent border (instead of left bar) */
    .sidebar-glass nav[data-mks-nav] a.sidebar-link-active{background:rgba(47,129,247,.12) !important;border-color:rgba(47,129,247,.45) !important;color:#0f172a}
    .dark .sidebar-glass nav[data-mks-nav] a.sidebar-link-active{background:rgba(47,129,247,.12) !important;border-color:rgba(47,129,247,.45) !important;color:#e6e9ee}
    .sidebar-glass nav[data-mks-nav] a.sidebar-link-active::before{display:none}
    /* icon chips: mockup-quiet, keep app tint when active */
    .sidebar-glass nav[data-mks-nav] .sidebar-icon-wrap{width:30px;height:30px;margin-right:0 !important}
    /* ── Mockup section labels ── */
    .sidebar-glass .sidebar-section-header{padding:12px 10px 6px !important;font-size:10px !important;font-weight:650 !important;letter-spacing:.12em !important;text-transform:uppercase !important}
    .sidebar-glass .sidebar-section-header span{font-size:10px !important;font-weight:650 !important;letter-spacing:.12em !important}
    /* ── Search ── */
    .mks-search{display:flex;align-items:center;gap:8px;background:rgba(148,163,184,.12);border:1px solid rgba(148,163,184,.25);border-radius:8px;padding:1px 6px;color:#94a3b8;font-size:12px;width:100%;cursor:text}
    .dark .mks-search{background:rgba(255,255,255,.04);border-color:rgba(255,255,255,.09);color:#5c6672}
    .mks-search input{background:transparent;border:0;outline:0;flex:1;min-width:0;font-size:12px;color:inherit}
    .mks-search input::placeholder{color:inherit}
    .mks-kbd{font-family:ui-monospace,Consolas,monospace;font-size:10px;border:1px solid rgba(148,163,184,.35);border-radius:4px;padding:1px 5px;flex:none}
    /* ── Folder trees ── */
    .mks-tree{margin-left:17px;border-left:1px solid rgba(148,163,184,.3);padding-left:4px;position:relative}
    .dark .mks-tree{border-left-color:#1e293b}
    .mks-file-dot{width:5px;height:5px;border-radius:50%;background:#94a3b8;flex:none}
    .dark .mks-file-dot{background:#64748b}
    /* ── Tree-node indicator: which folder connects to the current page ── */
    .sidebar-glass nav[data-mks-nav] li[data-mks-group] > ul{margin-left:17px;border-left:1px solid rgba(148,163,184,.3);padding-left:4px}
    .dark .sidebar-glass nav[data-mks-nav] li[data-mks-group] > ul{border-left-color:#1e293b}
    .sidebar-glass nav[data-mks-nav] li[data-mks-group]:has(a.sidebar-link-active) > ul{border-left-color:rgba(59,130,246,.55)}
    .sidebar-glass nav[data-mks-nav] .mks-tree:has(a.sidebar-link-active){border-left-color:rgba(59,130,246,.55)}
    .sidebar-glass nav[data-mks-nav] li[data-mks-group] > ul a.sidebar-link-active,
    .sidebar-glass nav[data-mks-nav] .mks-tree a.sidebar-link-active{position:relative}
    .sidebar-glass nav[data-mks-nav] li[data-mks-group] > ul a.sidebar-link-active::after,
    .sidebar-glass nav[data-mks-nav] .mks-tree a.sidebar-link-active::after{content:"";position:absolute;left:-8px;top:50%;transform:translateY(-50%);width:7px;height:7px;border-radius:50%;background:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.18);pointer-events:none}
    .sidebar-glass nav[data-mks-nav] li[data-mks-group]:has(a.sidebar-link-active) > button.mks-fbtn{color:#2563eb;background:rgba(59,130,246,.12)}
    .dark .sidebar-glass nav[data-mks-nav] li[data-mks-group]:has(a.sidebar-link-active) > button.mks-fbtn{color:#93c5fd;background:rgba(59,130,246,.12)}
    .sidebar-glass nav[data-mks-nav] li[data-mks-group]:has(a.sidebar-link-active) > button.mks-fbtn::after{content:"";width:7px;height:7px;border-radius:50%;background:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.18);flex:none;margin-left:6px}
    .sidebar-glass.w-16 nav[data-mks-nav] li[data-mks-group] > ul{border-left:0;margin-left:0;padding-left:0}
    .sidebar-glass.w-16 nav[data-mks-nav] li[data-mks-group] > ul a.sidebar-link-active::after,
    .sidebar-glass.w-16 nav[data-mks-nav] .mks-tree a.sidebar-link-active::after,
    .sidebar-glass.w-16 nav[data-mks-nav] li[data-mks-group]:has(a.sidebar-link-active) > button.mks-fbtn::after{display:none}
    .mks-caret{transition:transform .2s ease}
    .mks-count-pill{font-family:ui-monospace,Consolas,monospace;font-size:10.5px;background:rgba(148,163,184,.15);border:1px solid rgba(148,163,184,.25);border-radius:20px;padding:1px 7px;color:#64748b}
    .dark .mks-count-pill{color:#8b949e}
    .mks-folder-ico{color:#3b82f6;flex:none}
    /* ── Mockup-compact icons: bare 15px glyphs, no chips ── */
    .sidebar-glass nav[data-mks-nav] .sidebar-icon-wrap{width:auto !important;height:auto !important;background:none !important;border:0 !important;border-radius:0 !important;margin-right:0 !important;flex:none}
    .sidebar-glass nav[data-mks-nav] .sidebar-icon-wrap svg{width:15px !important;height:15px !important}
    .sidebar-glass nav[data-mks-nav] a.sidebar-link-hover{font-size:13px}
    .sidebar-glass nav[data-mks-nav] a.sidebar-link-hover > span:last-child{white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    /* ── Mockup folder-row headers: caret + folder + emphasized label ── */
    .sidebar-glass nav[data-mks-nav] button.mks-fbtn{justify-content:flex-start !important;text-transform:none !important;letter-spacing:normal !important;padding:6px 8px !important;font-size:12.5px !important;font-weight:600 !important;color:#475569}
    .dark .sidebar-glass nav[data-mks-nav] button.mks-fbtn{color:#cbd5e1}
    .sidebar-glass nav[data-mks-nav] button.mks-fbtn:hover{color:#0f172a;background:rgba(148,163,184,.12)}
    .dark .sidebar-glass nav[data-mks-nav] button.mks-fbtn:hover{color:#ffffff;background:rgba(255,255,255,.05)}
    .sidebar-glass nav[data-mks-nav] button.mks-fbtn > span{font-size:12.5px !important;font-weight:600 !important;letter-spacing:normal !important;text-transform:none !important}
    .sidebar-glass nav[data-mks-nav] button.mks-fbtn > svg.mks-caret{order:-1;margin:0 !important;width:12px !important;height:12px !important;transform:rotate(-90deg)}
    .sidebar-glass nav[data-mks-nav] button.mks-fbtn[aria-expanded="true"] > svg.mks-caret{transform:rotate(0deg)}
    .sidebar-glass nav[data-mks-nav] button.mks-fbtn > svg:not(.mks-caret){margin:0 !important}
    .sidebar-glass nav[data-mks-nav] button.mks-fbtn .mks-fcount{margin-left:auto}
    .sidebar-glass nav[data-mks-nav] .mks-tree a.sidebar-link-hover{padding:5px 8px !important;font-size:12.5px !important;border-radius:6px !important}
    /* ── Mockup-compact user card ── */
    .sidebar-glass .mks-user{margin:10px !important;padding:10px 12px !important;border-radius:10px !important}
    .sidebar-glass .mks-user .mks-avatar{width:30px !important;height:30px !important}
    .sidebar-glass .mks-user .mks-avatar span{font-size:11px !important}
    .sidebar-glass .mks-user .mks-uname{font-size:12.5px !important}
    .sidebar-glass .mks-user .mks-umail{font-size:11px !important}
    .sidebar-glass.w-16 nav[data-mks-nav] a.sidebar-link-hover{justify-content:center;padding:7px 0 !important}
    .sidebar-glass.w-16 .mks-hide-collapsed{display:none !important}
    .sidebar-glass.w-16 .mks-tree{border-left:0;margin-left:0;padding-left:0}
    .sidebar-glass.w-16 nav[data-mks-nav]{padding:10px 6px !important}
</style>
