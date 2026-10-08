@extends($offlineLayout ?? 'layouts.supervisor')

@section('title', 'Offline Capture')
@include('partials.dashboard.mock-styles')

@push('styles')
<style>
    /* Hub quick-capture rating sheet — self-contained (no build dependency)
       so the offline page works even if compiled CSS is stale. */
    .hub-dom{margin:10px 0 2px;font-size:11px;font-weight:700;color:#4f46e5;letter-spacing:.02em}
    .dark .hub-dom{color:#a5b4fc}
    .hub-row{border:1px solid #e5e7eb;border-radius:8px;padding:8px 10px;margin-bottom:8px;background:#fff}
    .dark .hub-row{background:#1f2937;border-color:#374151}
    .hub-row.rated{border-color:#10b981}
    .hub-code{display:inline-block;font-size:11px;font-weight:700;font-family:ui-monospace,monospace;color:#4f46e5;background:#eef2ff;border-radius:4px;padding:1px 6px;margin-right:6px}
    .dark .hub-code{color:#a5b4fc;background:rgba(99,102,241,.15)}
    .hub-desc{font-size:13px;color:#374151}
    .dark .hub-desc{color:#d1d5db}
    .hub-rates{display:grid;grid-template-columns:repeat(auto-fill,40px);gap:6px;margin-top:8px;align-items:center;justify-content:start}
    .hub-rate{width:40px;height:40px;padding:0;display:inline-flex;align-items:center;justify-content:center;border-radius:999px;border:2px solid #d1d5db;background:#fff;color:#4b5563;font-weight:700;font-size:11px;cursor:pointer}
    .dark .hub-rate{background:#111827;color:#9ca3af;border-color:#4b5563}
    .hub-rate:hover{border-color:#10b981}
    .hub-rate.active{background:#16a34a;border-color:#16a34a;color:#fff}
    .hub-rate.hub-no{border-radius:8px}
    .hub-rate.hub-no.active{background:#6b7280;border-color:#6b7280;color:#fff}
    .hub-rate.hub-na{border-radius:8px}
    .hub-rate.hub-na.active{background:#d97706;border-color:#d97706;color:#fff}
    .hub-cbtn{grid-column:1/-1;justify-self:start;margin-left:0;font-size:11px;font-weight:600;color:#6b7280;background:transparent;border:1px solid #d1d5db;border-radius:8px;padding:6px 10px;cursor:pointer}
    .dark .hub-cbtn{color:#9ca3af;border-color:#4b5563}
    .hub-cbtn.has-comment{color:#4f46e5;border-color:#4f46e5}
    .dark .hub-cbtn.has-comment{color:#a5b4fc;border-color:#6366f1}
    .hub-comment{margin-top:6px}
    .hub-comment textarea{width:100%;font-size:12px;padding:6px 8px;border:1px solid #c7d2fe;border-radius:8px;resize:vertical;min-height:44px}
    .dark .hub-comment textarea{background:#111827;border-color:#4b5563;color:#e5e7eb}
</style>
@endpush

@section('content')
<div class="mock-wrap max-w-3xl mx-auto px-1 py-1">
    <div class="mock-topbar">
        <div class="mock-crumbs">{{ $offlineCrumbs ?? 'Supervisor' }} <span>/</span> <b>Offline Capture</b></div>
        <div class="mock-actions">
            <a class="mock-btn" href="{{ route($offlineBackRoute ?? 'supervisor.observations.index') }}">← Back to Evaluations</a>
        </div>
    </div>

    <div class="mock-title">
        <div>
            <h1>Offline observation visits</h1>
            <p>Confirmed observations → downloaded packages → zero-signal encoding → sync. 1 Confirm (teacher) · 2 Prepare + download · 3 Encode offline · 4 Sync.</p>
        </div>
        <time>{{ now()->format('l, F j, Y') }}</time>
    </div>
    <div class="mt-2 flex flex-wrap items-center gap-2">
        <span id="offline-mode-badge" class="hidden items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200 dark:bg-amber-900/30 dark:text-amber-200 dark:border-amber-800" role="status">
            <span class="h-2 w-2 rounded-full bg-amber-500"></span>
            <span data-badge-label>Offline Mode (Saved Locally)</span>
        </span>
    </div>
    <p id="of-diag" class="mt-1 text-xs text-gray-400 dark:text-gray-500">Starting offline tools…</p>
    @if(empty($bundle['teachers']) && empty($bundle['school_heads']))
        <div class="mt-3 rounded-lg border border-rose-200 dark:border-rose-800 bg-rose-50 dark:bg-rose-900/20 p-3 text-sm text-rose-900 dark:text-rose-100">
            <strong>No teachers or school heads found for your account{{ isset($schoolName) && $schoolName ? ' (' . $schoolName . ')' : '' }}.</strong>
            Caching cannot fix this — the server itself has nobody assigned for you to observe. Ask your admin to assign teachers to your school.
        </div>
    @endif
    {{-- Server-rendered bundle: lists work on first paint; JS persists it for true offline reloads. --}}
    <script>window.ASPIRE_BOOTSTRAP = @json($bundle ?? null);</script>

    {{-- Step cards --}}
    <section class="mock-panel" aria-label="Offline steps">
        <div class="mock-panel-head"><h2>How offline capture works</h2><span class="hint">3 steps</span></div>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3" style="padding:14px 16px">
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-3">
            <p class="flex items-center gap-2 text-sm font-bold text-gray-900 dark:text-gray-100">
                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-indigo-600 text-white text-xs">1</span>
                Cache data
            </p>
            <p id="of-cache-status" class="mt-1 text-xs text-gray-500 dark:text-gray-400">Checking…</p>
            <button type="button" class="aspire-cache-offline mt-2 w-full px-3 py-2 rounded-md bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition-colors">Cache data</button>
        </div>
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-3">
            <p class="flex items-center gap-2 text-sm font-bold text-gray-900 dark:text-gray-100">
                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-indigo-600 text-white text-xs">2</span>
                Capture offline
            </p>
            <p id="of-capture-status" class="mt-1 text-xs text-gray-500 dark:text-gray-400">Fill in the form below — it works with zero connectivity.</p>
            <a href="#offline-form" class="mt-2 block w-full text-center px-3 py-2 rounded-md bg-white dark:bg-transparent text-indigo-700 dark:text-indigo-300 text-sm font-semibold border border-indigo-300 dark:border-indigo-700 hover:bg-indigo-50 dark:hover:bg-indigo-900/40 transition-colors">Go to form</a>
        </div>
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-3">
            <p class="flex items-center gap-2 text-sm font-bold text-gray-900 dark:text-gray-100">
                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-indigo-600 text-white text-xs">3</span>
                Sync &amp; get AI
            </p>
            <p id="of-sync-status" class="mt-1 text-xs text-gray-500 dark:text-gray-400">Nothing waiting yet.</p>
            <button type="button" data-sync-now class="aspire-sync-now mt-2 w-full px-3 py-2 rounded-md bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700 transition-colors">Sync Now</button>
        </div>
    </div>
    </section>

    {{-- Offline visit packages: the mandated flow hub. Observations the teacher
         confirmed appear here after you tap "Download for Offline Use" on each
         observation page. Open a workspace with zero connectivity, encode, sync. --}}
    <section class="mock-panel" style="margin-top:12px" aria-label="Ready packages">
        <div class="mock-panel-head">
            <h2>Ready for offline visit</h2>
            <span class="hint" id="pkg-ready-count"></span>
            <span class="link" id="pkg-outbox-note"></span>
        </div>
        <div style="padding:14px 16px">
            <p class="text-xs text-gray-500 dark:text-gray-400">Downloaded packages live on this device (lesson plan, pinned rubric, AI prompts). Open one at the school with no signal, encode scores, then sync when you're back online.</p>
            <ul id="pkg-ready-list" class="mt-2 space-y-1.5"></ul>
            <div class="mt-3 flex flex-wrap items-center gap-2">
                <button type="button" id="pkg-sync-now" class="mock-btn primary">Sync package outbox</button>
                <span class="text-xs text-gray-400" id="pkg-sync-note">Pushes saved package observations to the server.</span>
            </div>
        </div>
    </section>

    {{-- Last sync result --}}
    <div id="of-result" class="hidden mt-3 rounded-lg border p-3 text-sm"></div>

    <section class="mock-panel" style="margin-top:12px" aria-label="Scheduled observations">
        <div class="mock-panel-head"><h2>Already on the server — don't re-capture</h2></div>
        <div style="padding:14px 16px">
        <p class="text-xs text-gray-400 mt-0.5">These observations exist on the server. Capturing the same person + date offline will be rejected as a duplicate on sync.</p>
        <ul id="of-scheduled" class="mt-2 space-y-1.5 text-sm text-gray-700 dark:text-gray-300"></ul>
        </div>
    </section>

    {{-- Ad-hoc capture (legacy quick form for unscheduled observations). Kept
         working as-is; the mandated flow above covers scheduled visits. --}}
    <details class="mock-panel" style="margin-top:16px">
        <summary class="mock-panel-head" style="cursor:pointer;list-style:none"><h2>Ad-hoc capture (no scheduled observation)</h2><span class="hint">legacy quick-capture form</span></summary>
        <div style="padding:0 16px 16px">
    <form id="offline-form" class="space-y-3" style="margin-top:4px">
        <div>
            <span class="block text-sm font-medium text-gray-700 dark:text-gray-300">Who are you observing?</span>
            <div class="mt-1 grid grid-cols-2 gap-2" role="radiogroup" aria-label="Observee type">
                <button type="button" id="of-type-teacher" class="rounded-md border-2 border-indigo-600 bg-indigo-50 dark:bg-indigo-900/30 px-3 py-2 text-sm font-semibold text-indigo-700 dark:text-indigo-200" aria-pressed="true">Teacher</button>
                <button type="button" id="of-type-head" class="rounded-md border-2 border-gray-200 dark:border-gray-600 px-3 py-2 text-sm font-semibold text-gray-500 dark:text-gray-400" aria-pressed="false">School Head</button>
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="of-teacher-search"><span id="of-observee-label">Teacher</span></label>
            <input id="of-teacher-search" type="text" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" placeholder="Type to search…" autocomplete="off">
            <select id="of-teacher" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required></select>
            <p class="text-xs text-gray-400 mt-1">List comes from your cached data — do step 1 while online first.</p>
            <p id="of-no-cache" class="hidden mt-1 text-xs font-semibold text-amber-700 dark:text-amber-300 rounded-md bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 px-2 py-1.5"></p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="of-date">Observation date</label>
                <input id="of-date" type="date" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="of-subject">Subject / focus</label>
                <input id="of-subject" type="text" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" placeholder="e.g. Mathematics">
            </div>
        </div>
        <div id="of-grade-wrap">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="of-grade">Grade level</label>
            <input id="of-grade" type="text" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" placeholder="e.g. Grade 7">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="of-notes">Notes / evidence</label>
            <textarea id="of-notes" rows="4" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" placeholder="Classroom evidence, quotes, timestamps…"></textarea>
        </div>
        <div id="of-ratings-wrap">
            <div id="of-ratings" class="space-y-2"></div>
        </div>
        <p id="of-epoc-note" class="hidden text-xs text-gray-500 dark:text-gray-400 rounded-md bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 p-2">EPOC instrument not cached yet — tap “Cache data” while online, then pick the school head again.</p>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="of-files">Evidence files <span class="font-normal text-gray-400">(optional — photos, video, PDF, Word · max 5 files, 5 MB each)</span></label>
            <input id="of-files" type="file" multiple accept="image/*,video/*,.pdf,.doc,.docx" class="mt-1 w-full text-sm text-gray-600 dark:text-gray-300 file:mr-3 file:px-3 file:py-2 file:rounded-md file:border-0 file:bg-indigo-50 dark:file:bg-indigo-900/40 file:text-indigo-700 dark:file:text-indigo-200 file:text-sm file:font-semibold hover:file:bg-indigo-100">
            <p class="text-xs text-gray-400 mt-1">Files stay on this device and upload automatically on sync.</p>
            <ul id="of-file-preview" class="mt-1 space-y-0.5 text-xs text-gray-500 dark:text-gray-400"></ul>
        </div>
        <button type="submit" id="of-save" class="w-full px-4 py-2.5 rounded-md bg-emerald-600 text-white font-semibold hover:bg-emerald-700 transition-colors">Save Observation</button>
    </form>

    <div class="mt-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
        <h2 class="font-semibold text-gray-900 dark:text-gray-100">Waiting on this device <span id="offline-pending" class="ml-1 text-sm font-normal text-gray-500"></span></h2>
        <p class="text-xs text-gray-400 mt-0.5">These leave your device only when you sync. AI suggestions generate on the server afterwards.</p>
        <ul id="offline-list" class="mt-2 space-y-2 text-sm text-gray-700 dark:text-gray-300"></ul>
    </div>

    <div class="mt-4 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
        <h2 class="font-semibold text-gray-900 dark:text-gray-100">Review past observations <span id="of-history-count" class="ml-1 text-sm font-normal text-gray-500"></span></h2>
        <p class="text-xs text-gray-400 mt-0.5">Read-only reference from your last cache — review ratings before observing again. Tap an entry for details.</p>
        <input id="of-history-search" type="text" class="mt-2 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm" placeholder="Search by name or subject…" autocomplete="off">
        <ul id="of-history-list" class="mt-2 space-y-2 text-sm text-gray-700 dark:text-gray-300"></ul>
    </div>
        </div>
    </details>
</div>

@push('scripts')
{{-- Engine libraries FIRST: the page script below checks window.AspireOffline
    at parse time, and classic scripts execute in document order. The core
    engine is inlined (no separate download that can 404 or go stale). --}}
<script>window.ASPIRE_BASE_URL = window.ASPIRE_BASE_URL || @json(request()->getBaseUrl());</script>
@php($offlineEngine = @file_get_contents(public_path('js/aspire-offline.js')))
@if($offlineEngine)
<script>/* ASPIRE offline engine (inlined for zero-dependency offline boot) */{!! $offlineEngine !!}</script>
@else
<script src="{{ request()->getBaseUrl() }}/js/aspire-offline.js"></script>
@endif
<script src="{{ request()->getBaseUrl() }}/js/offline-encode.js"></script>
<script src="{{ request()->getBaseUrl() }}/js/aspire-offline-package.js"></script>
<script>
(function () {
    var teacherSel = document.getElementById('of-teacher');
    var teacherSearch = document.getElementById('of-teacher-search');
    var observeeLabel = document.getElementById('of-observee-label');
    var typeTeacherBtn = document.getElementById('of-type-teacher');
    var typeHeadBtn = document.getElementById('of-type-head');
    var ratingsBox = document.getElementById('of-ratings');
    var ratingsWrap = document.getElementById('of-ratings-wrap');
    var epocNote = document.getElementById('of-epoc-note');
    var gradeWrap = document.getElementById('of-grade-wrap');
    var list = document.getElementById('offline-list');
    var pending = document.getElementById('offline-pending');
    var cacheStatus = document.getElementById('of-cache-status');
    var captureStatus = document.getElementById('of-capture-status');
    var syncStatus = document.getElementById('of-sync-status');
    var resultBox = document.getElementById('of-result');
    var saveBtn = document.getElementById('of-save');
    var bundle = null;
    var observeeType = 'teacher_observation';
    var teachersById = {};
    var headsById = {};
    var scheduledList = [];
    var historyList = [];
    var historySearch = document.getElementById('of-history-search');
    var historyBox = document.getElementById('of-history-list');
    var historyCount = document.getElementById('of-history-count');

    function notify(type, message) {
        if (typeof window.showToast === 'function') { window.showToast(type, message); }
        else { alert(message); }
    }

    function updateDiag(msg) {
        var el = document.getElementById('of-diag');
        if (el) el.textContent = 'Diagnostics: ' + msg;
    }

    // Type toggle works even when the offline library failed to load, so the
    // buttons never feel "dead" — the list simply explains nothing is cached.
    // (setObserveeType is hoisted, so calling it here is safe.)
    typeTeacherBtn.addEventListener('click', function () { setObserveeType('teacher_observation'); });
    typeHeadBtn.addEventListener('click', function () { setObserveeType('school_head_observation'); });
    setObserveeType('teacher_observation');

    // The offline engine (inlined in the page) did not start. The marker tells
    // us why: undefined = the engine script never ran (stale page, script
    // blocker, proxy); false = it ran but produced nothing (server issue).
    if (!window.AspireOffline) {
        var blocked = window.__aspireLibInlineOk === undefined;
        var why = blocked
            ? 'Your browser blocked the page engine. Hard-refresh (Ctrl+Shift+R), try another browser, or turn off ad/script blockers for this site, then reload while online.'
            : 'The page engine failed to start (server issue). Tell your admin.';
        cacheStatus.textContent = why;
        captureStatus.textContent = 'Unavailable until the page loads cleanly.';
        updateDiag(blocked ? 'library BLOCKED (extension/stale page?)' : 'library BROKEN (server) — tell admin');
        saveBtn.disabled = true;
        saveBtn.classList.add('opacity-60');
        notify('error', 'Offline tools failed to load. ' + why);
        return;
    }

    function opt(v, t) { var o = document.createElement('option'); o.value = v; o.textContent = t; return o; }

    function cacheErrorText(err) {
        var status = err && err.status;
        if (status === 401 || status === 419) return 'Session expired — reload this page (you will be asked to log in), then cache again.';
        if (!navigator.onLine) return 'You are offline — connect, then tap Cache data.';
        return 'Server error while caching (' + (status || 'no response') + '). Try again, or tell your admin if it persists.';
    }

    function fillObserveeSelect() {
        teacherSel.innerHTML = '';
        teacherSearch.value = '';
        var source = observeeType === 'school_head_observation'
            ? Object.keys(headsById).map(function (id) { return headsById[id]; })
            : Object.keys(teachersById).map(function (id) { return teachersById[id]; });
        if (!source.length) {
            teacherSel.appendChild(opt('', observeeType === 'school_head_observation' ? 'No school heads cached — cache data first' : 'No teachers cached — cache data first'));
        }
        source.forEach(function (t) { teacherSel.appendChild(opt(t.id, t.label)); });
        filterTeachers();
        updateNoCacheBanner(source.length);
    }

    // Unmistakable empty-cache guidance: tells the supervisor exactly why the
    // dropdown is empty and what to do. Fires on every toggle + refresh.
    function updateNoCacheBanner(count) {
        var banner = document.getElementById('of-no-cache');
        if (!banner) return;
        if (count > 0) { banner.classList.add('hidden'); return; }
        var isHead = observeeType === 'school_head_observation';
        var who = isHead ? 'school heads' : 'teachers';
        var msg = !bundle
            ? 'No cached data on this device — while online, tap “Cache data” above, then pick ' + who + ' here.'
            : 'No ' + who + ' in your cached data — tap “Cache data” above while online to refresh the list.';
        if (!navigator.onLine && !bundle) {
            msg = 'You are offline and nothing is cached yet — reconnect, tap “Cache data”, then reload this page.';
        }
        banner.textContent = msg;
        banner.classList.remove('hidden');
    }

    function setObserveeType(type) {
        observeeType = type;
        var isHead = type === 'school_head_observation';
        typeTeacherBtn.setAttribute('aria-pressed', String(!isHead));
        typeHeadBtn.setAttribute('aria-pressed', String(isHead));
        typeTeacherBtn.className = 'rounded-md border-2 px-3 py-2 text-sm font-semibold ' + (!isHead
            ? 'border-indigo-600 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-200'
            : 'border-gray-200 dark:border-gray-600 text-gray-500 dark:text-gray-400');
        typeHeadBtn.className = 'rounded-md border-2 px-3 py-2 text-sm font-semibold ' + (isHead
            ? 'border-indigo-600 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-200'
            : 'border-gray-200 dark:border-gray-600 text-gray-500 dark:text-gray-400');
        observeeLabel.textContent = isHead ? 'School head' : 'Teacher';
        // Both rating sheets live side by side; only the matching instrument
        // is shown (COT for teachers, EPOC 1–5 for school heads).
        applySheetVisibility();
        gradeWrap.classList.toggle('hidden', isHead);
        fillObserveeSelect();
    }
    function renderScheduled() {
        var box = document.getElementById('of-scheduled');
        box.innerHTML = '';
        if (!scheduledList.length) {
            var empty = document.createElement('li');
            empty.className = 'text-gray-400 text-sm';
            empty.textContent = bundle
                ? 'None — nothing scheduled on the server. Everything you capture here is new.'
                : 'Unknown yet — cache data (step 1) to see what is already scheduled.';
            box.appendChild(empty);
            return;
        }
        scheduledList.forEach(function (s) {
            var li = document.createElement('li');
            li.className = 'flex items-center gap-2 rounded-md border border-gray-100 dark:border-gray-700 px-2 py-1.5';
            var dot = document.createElement('span');
            dot.className = 'h-2 w-2 shrink-0 rounded-full bg-sky-500';
            var body = document.createElement('div');
            body.className = 'flex-1 min-w-0';
            var title = document.createElement('p');
            title.className = 'font-medium truncate';
            title.textContent = s.observee_name + ' · ' + (s.observation_date || 'no date');
            var sub = document.createElement('p');
            sub.className = 'text-xs text-gray-500 dark:text-gray-400 truncate';
            var kind = s.observation_type === 'school_head_observation' ? 'School head' : 'Teacher';
            sub.textContent = kind + ' · ' + (s.subject || 'No subject') + ' · ' + String(s.status || s.stage || '').replace(/_/g, ' ');
            body.appendChild(title);
            body.appendChild(sub);
            li.appendChild(dot);
            li.appendChild(body);
            box.appendChild(li);
        });
    }

    function ratingText(r) {
        if (r.not_applicable) return 'N/A';
        if (r.not_observed) return 'NO';
        return (r.rating === null || r.rating === undefined) ? '—' : String(r.rating);
    }

    function renderHistory() {
        var q = (historySearch.value || '').toLowerCase();
        var shown = historyList.filter(function (h) {
            return q === '' || (h.observee_name || '').toLowerCase().indexOf(q) !== -1
                || (h.subject || '').toLowerCase().indexOf(q) !== -1;
        });
        historyCount.textContent = historyList.length ? '(' + historyList.length + ')' : '';
        historyBox.innerHTML = '';
        if (!bundle) {
            var wait = document.createElement('li');
            wait.className = 'text-gray-400 text-sm';
            wait.textContent = 'Unknown yet — cache data (step 1) to load past observations.';
            historyBox.appendChild(wait);
            return;
        }
        if (!shown.length) {
            var empty = document.createElement('li');
            empty.className = 'text-gray-400 text-sm';
            empty.textContent = historyList.length ? 'No past observations match your search.' : 'No finished observations yet. Completed work will appear here after caching.';
            historyBox.appendChild(empty);
            return;
        }
        shown.forEach(function (h, idx) {
            var li = document.createElement('li');
            li.className = 'rounded-md border border-gray-100 dark:border-gray-700 overflow-hidden';
            var head = document.createElement('button');
            head.type = 'button';
            head.className = 'w-full flex items-center gap-2 p-2 text-left hover:bg-gray-50 dark:hover:bg-gray-700/40';
            var dot = document.createElement('span');
            dot.className = 'h-2 w-2 shrink-0 rounded-full ' + (h.status === 'completed' ? 'bg-emerald-500' : 'bg-gray-400');
            var body = document.createElement('div');
            body.className = 'flex-1 min-w-0';
            var title = document.createElement('p');
            title.className = 'font-medium truncate';
            title.textContent = (h.observee_name || '?') + ' · ' + (h.observation_date || 'no date');
            var sub = document.createElement('p');
            sub.className = 'text-xs text-gray-500 dark:text-gray-400 truncate';
            var kind = h.observation_type === 'school_head_observation' ? 'School head' : 'Teacher';
            sub.textContent = kind + ' · ' + (h.subject || 'No subject')
                + (h.overall_score !== null && h.overall_score !== undefined ? ' · score ' + h.overall_score : '')
                + ' · ' + (h.ratings || []).length + ' rating(s)';
            body.appendChild(title);
            body.appendChild(sub);
            var chev = document.createElement('span');
            chev.className = 'text-gray-400 text-xs shrink-0';
            chev.textContent = '▸';
            head.appendChild(dot);
            head.appendChild(body);
            head.appendChild(chev);
            var detail = document.createElement('div');
            detail.className = 'hidden border-t border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-700/30 p-2 space-y-1.5';
            if (h.notes) {
                var notes = document.createElement('p');
                notes.className = 'text-xs text-gray-600 dark:text-gray-300 italic';
                notes.textContent = '“' + h.notes + '”';
                detail.appendChild(notes);
            }
            (h.ratings || []).forEach(function (r) {
                var row = document.createElement('div');
                row.className = 'flex items-start gap-2 text-xs';
                var code = document.createElement('span');
                code.className = 'shrink-0 font-bold text-indigo-600 dark:text-indigo-300';
                code.textContent = (r.indicator_code || '?') + ': ' + ratingText(r);
                var desc = document.createElement('span');
                desc.className = 'flex-1 text-gray-600 dark:text-gray-300';
                desc.textContent = (r.domain ? r.domain + ' — ' : '') + (r.comments || '');
                row.appendChild(code);
                row.appendChild(desc);
                detail.appendChild(row);
            });
            if (!(h.ratings || []).length) {
                var none = document.createElement('p');
                none.className = 'text-xs text-gray-400';
                none.textContent = 'No ratings recorded.';
                detail.appendChild(none);
            }
            head.addEventListener('click', function () {
                var open = detail.classList.toggle('hidden');
                chev.textContent = open ? '▸' : '▾';
            });
            li.appendChild(head);
            li.appendChild(detail);
            historyBox.appendChild(li);
        });
    }
    historySearch.addEventListener('input', renderHistory);

    /* Hub quick-capture rating sheet — mirrors the online observation sheet:
       one selection per indicator (score / NO / N/A), comment toggle,
       progress header. State lives in hidden inputs so a refresh-safe
       re-render never invents ratings. */
    // withFlags=false renders a plain 1–5 sheet (EPOC has no NO / N/A).
    // Hidden flag inputs are always rendered so hubRowState/hubSet/hubPaint
    // keep working unchanged.
    function hubRatingRow(ind, scaleKeys, scaleLabels, withFlags) {
        var row = document.createElement('div');
        row.className = 'hub-row';
        row.setAttribute('data-hub-row', '');
        row.setAttribute('data-code', ind.code);
        row.setAttribute('data-domain', ind.domain || 'General');
        row.setAttribute('data-desc', ind.description || ind.code);
        var head = document.createElement('div');
        var code = document.createElement('span');
        code.className = 'hub-code';
        code.textContent = ind.code;
        var desc = document.createElement('span');
        desc.className = 'hub-desc';
        desc.textContent = ind.description || ind.code;
        head.appendChild(code);
        head.appendChild(desc);
        row.appendChild(head);
        var rates = document.createElement('div');
        rates.className = 'hub-rates';
        rates.setAttribute('role', 'group');
        rates.setAttribute('aria-label', 'Rating for ' + ind.code);
        scaleKeys.forEach(function (v) {
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'hub-rate';
            b.setAttribute('data-hub-btn', '');
            b.setAttribute('data-v', String(v));
            b.title = (scaleLabels && scaleLabels[v]) ? (v + ' — ' + scaleLabels[v]) : ('Score ' + v);
            b.setAttribute('aria-label', 'Rate ' + v + ((scaleLabels && scaleLabels[v]) ? ' (' + scaleLabels[v] + ')' : ''));
            b.textContent = v;
            rates.appendChild(b);
        });
        if (withFlags === undefined) withFlags = true;
        if (withFlags) {
            var noB = document.createElement('button');
            noB.type = 'button'; noB.className = 'hub-rate hub-no';
            noB.setAttribute('data-hub-no', ''); noB.title = 'Not observed'; noB.textContent = 'NO';
            var naB = document.createElement('button');
            naB.type = 'button'; naB.className = 'hub-rate hub-na';
            naB.setAttribute('data-hub-na', ''); naB.title = 'Not applicable — excluded from scoring'; naB.textContent = 'N/A';
            rates.appendChild(noB); rates.appendChild(naB);
        }
        var cB = document.createElement('button');
        cB.type = 'button'; cB.className = 'hub-cbtn';
        cB.setAttribute('data-hub-cbtn', ''); cB.textContent = 'Comment';
        rates.appendChild(cB);
        row.appendChild(rates);
        var hid = document.createElement('input');
        hid.type = 'hidden'; hid.setAttribute('data-hub-rating', '');
        var noH = document.createElement('input');
        noH.type = 'checkbox'; noH.hidden = true; noH.setAttribute('data-hub-noflag', '');
        var naH = document.createElement('input');
        naH.type = 'checkbox'; naH.hidden = true; naH.setAttribute('data-hub-naflag', '');
        row.appendChild(hid); row.appendChild(noH); row.appendChild(naH);
        var cWrap = document.createElement('div');
        cWrap.className = 'hub-comment'; cWrap.hidden = true;
        cWrap.setAttribute('data-hub-cwrap', '');
        var cTa = document.createElement('textarea');
        cTa.rows = 2; cTa.placeholder = 'Evidence / comments for ' + ind.code;
        cTa.setAttribute('data-hub-comment', '');
        cWrap.appendChild(cTa);
        row.appendChild(cWrap);
        return row;
    }

    function hubRowState(row) {
        var hid = row.querySelector('[data-hub-rating]');
        var noH = row.querySelector('[data-hub-noflag]');
        var naH = row.querySelector('[data-hub-naflag]');
        return {
            rating: hid && hid.value !== '' ? parseInt(hid.value, 10) : null,
            no: !!(noH && noH.checked),
            na: !!(naH && naH.checked),
        };
    }

    function hubPaint(row) {
        var st = hubRowState(row);
        row.querySelectorAll('[data-hub-btn]').forEach(function (b) {
            b.classList.toggle('active', st.rating != null && !st.no && !st.na && String(st.rating) === b.getAttribute('data-v'));
        });
        var noB = row.querySelector('[data-hub-no]');
        if (noB) noB.classList.toggle('active', st.no);
        var naB = row.querySelector('[data-hub-na]');
        if (naB) naB.classList.toggle('active', st.na);
        row.classList.toggle('rated', st.rating != null || st.no || st.na);
    }

    function hubSet(row, kind, value) {
        var hid = row.querySelector('[data-hub-rating]');
        var noH = row.querySelector('[data-hub-noflag]');
        var naH = row.querySelector('[data-hub-naflag]');
        if (kind === 'rating') { hid.value = value; noH.checked = false; naH.checked = false; }
        if (kind === 'no') { hid.value = ''; noH.checked = true; naH.checked = false; }
        if (kind === 'na') { hid.value = ''; noH.checked = false; naH.checked = true; }
        hubPaint(row);
        hubRecompute();
    }

    // Progress is tracked per sheet (COT + EPOC live side by side; only one
    // is visible at a time) so toggling observee type never loses counts.
    function hubRecompute() {
        ratingsBox.querySelectorAll('[data-hub-sheet]').forEach(function (sheet) {
            var rows = sheet.querySelectorAll('[data-hub-row]');
            var rated = 0;
            rows.forEach(function (row) {
                var st = hubRowState(row);
                if (st.rating != null || st.no || st.na) rated++;
            });
            var lab = sheet.querySelector('[data-hub-label]');
            var bar = sheet.querySelector('[data-hub-bar]');
            if (lab) lab.textContent = rated + ' of ' + rows.length + ' rated';
            if (bar) bar.style.width = (rows.length ? Math.round((rated / rows.length) * 100) : 0) + '%';
        });
    }

    function hubResetRatings() {
        ratingsBox.querySelectorAll('[data-hub-row]').forEach(function (row) {
            row.querySelector('[data-hub-rating]').value = '';
            row.querySelector('[data-hub-noflag]').checked = false;
            row.querySelector('[data-hub-naflag]').checked = false;
            var ta = row.querySelector('[data-hub-comment]');
            if (ta) ta.value = '';
            var cw = row.querySelector('[data-hub-cwrap]');
            if (cw) cw.hidden = true;
            var cb = row.querySelector('[data-hub-cbtn]');
            if (cb) { cb.classList.remove('has-comment'); cb.textContent = 'Comment'; }
            hubPaint(row);
        });
        hubRecompute();
    }

    if (!ratingsBox.dataset.hubWired) {
        ratingsBox.dataset.hubWired = '1';
        ratingsBox.addEventListener('click', function (e) {
            var b = e.target.closest ? e.target.closest('[data-hub-btn],[data-hub-no],[data-hub-na],[data-hub-cbtn]') : null;
            if (!b || !ratingsBox.contains(b)) return;
            var row = b.closest('[data-hub-row]');
            if (!row) return;
            if (b.hasAttribute('data-hub-btn')) hubSet(row, 'rating', b.getAttribute('data-v'));
            else if (b.hasAttribute('data-hub-no')) hubSet(row, 'no');
            else if (b.hasAttribute('data-hub-na')) hubSet(row, 'na');
            else if (b.hasAttribute('data-hub-cbtn')) {
                var cw = row.querySelector('[data-hub-cwrap]');
                if (cw) {
                    cw.hidden = !cw.hidden;
                    b.classList.toggle('has-comment', !cw.hidden);
                    if (!cw.hidden) { var ta = cw.querySelector('textarea'); if (ta) ta.focus(); }
                }
            }
        });
        ratingsBox.addEventListener('input', function (e) {
            var ta = e.target.closest ? e.target.closest('[data-hub-comment]') : null;
            if (!ta) return;
            var row = ta.closest('[data-hub-row]');
            var btn = row ? row.querySelector('[data-hub-cbtn]') : null;
            if (btn) {
                var has = ta.value.trim().length > 0;
                btn.classList.toggle('has-comment', has);
                btn.textContent = has ? 'View Comment' : 'Comment';
            }
        });
    }

    function renderBundle(json) {        bundle = json;
        teachersById = {};
        headsById = {};
        scheduledList = json.scheduled || [];
        historyList = json.history || [];
        renderScheduled();
        renderHistory();
        (json.teachers || []).forEach(function (t) {
            teachersById[t.id] = { id: t.id, label: t.name + ' (' + ((t.subjects || []).join(', ') || 'No subject set') + ')' };
        });
        (json.school_heads || []).forEach(function (h) {
            headsById[h.id] = { id: h.id, label: h.name + ' — ' + (h.school_name || '') + ' (' + (h.position || 'School Head') + ')' };
        });
        // Rebuild the visible dropdown for the currently selected type —
        // without this the list keeps showing the "nothing cached" placeholder.
        fillObserveeSelect();
        // Preserve anything already picked: background refreshes re-render the
        // sheets, and toggling observee type must not wipe entered ratings.
        var kept = captureSheetState();
        ratingsBox.innerHTML = '';
        buildCotSheet(json, kept);
        buildEpocSheet(json, kept);
        applySheetVisibility();
        hubRecompute();
    }

    function hubSheetHead(title, sub) {
        var h = document.createElement('div');
        h.innerHTML = '<p class="text-sm font-medium text-gray-700 dark:text-gray-300">' + title + '</p>'
            + '<p class="text-xs text-gray-400 mt-0.5">' + sub + '</p>'
            + '<div class="mt-1.5 mb-1 flex items-center justify-between gap-2 text-xs">'
            + '<span data-hub-label class="font-medium text-gray-600 dark:text-gray-300">0 rated</span>'
            + '<span class="flex-1 h-1.5 rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden"><span data-hub-bar class="block h-full bg-indigo-600 rounded-full transition-all" style="width:0%"></span></span></div>';
        return h;
    }

    function captureSheetState() {
        var rows = {};
        ratingsBox.querySelectorAll('[data-hub-row]').forEach(function (row) {
            var st = hubRowState(row);
            var com = row.querySelector('[data-hub-comment]');
            rows[row.getAttribute('data-code')] = {
                rating: st.rating, no: st.no, na: st.na,
                comment: com ? com.value : '',
            };
        });
        function val(id) { var el = document.getElementById(id); return el ? el.value : ''; }
        return { rows: rows, star: val('of-star'), narrative: val('of-epoc-narrative'), agreement: val('of-epoc-agreement') };
    }

    function restoreSheetState(sheet, kept) {
        if (!kept) return;
        sheet.querySelectorAll('[data-hub-row]').forEach(function (row) {
            var s = kept.rows[row.getAttribute('data-code')];
            if (!s) return;
            if (s.rating != null) hubSet(row, 'rating', String(s.rating));
            else if (s.no) hubSet(row, 'no');
            else if (s.na) hubSet(row, 'na');
            if (s.comment) {
                var ta = row.querySelector('[data-hub-comment]');
                var cw = row.querySelector('[data-hub-cwrap]');
                var cb = row.querySelector('[data-hub-cbtn]');
                if (ta) ta.value = s.comment;
                if (cw) cw.hidden = false;
                if (cb) { cb.classList.add('has-comment'); cb.textContent = 'View Comment'; }
            }
        });
    }

    function buildCotSheet(json, kept) {
        var tpl = (json.cot_templates || [])[0];
        var sheet = document.createElement('div');
        sheet.setAttribute('data-hub-sheet', 'cot');
        if (!tpl) {
            var p = document.createElement('p');
            p.className = 'text-sm text-gray-400';
            p.textContent = 'No COT template cached yet — ratings will be added after sync.';
            sheet.appendChild(p);
            ratingsBox.appendChild(sheet);
            return;
        }
        // Ascending numeric order, like the online sheet (stored scales are descending).
        var scaleKeys = (tpl.rating_scale ? Object.keys(tpl.rating_scale) : ['2', '3', '4', '5', '6'])
            .map(Number).filter(function (n) { return !isNaN(n); }).sort(function (a, b) { return a - b; })
            .map(String);
        sheet.appendChild(hubSheetHead('COT ratings · ' + tpl.label, 'Fill what you observed, leave the rest blank.'));
        var markAll = document.createElement('button');
        markAll.type = 'button';
        markAll.className = 'mb-2 px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 rounded-lg transition-colors inline-flex items-center gap-1.5';
        markAll.textContent = 'Mark All as NO';
        markAll.addEventListener('click', function () {
            if (!confirm('Mark all indicators as Not Observed (NO)?')) return;
            sheet.querySelectorAll('[data-hub-row]').forEach(function (row) { hubSet(row, 'no'); });
            hubRecompute();
        });
        sheet.appendChild(markAll);
        // Group indicators by domain, same order as the online sheet.
        var hubDomains = [];
        var hubByDomain = {};
        (tpl.indicators || []).forEach(function (ind) {
            var d = ind.domain || 'General';
            if (!hubByDomain[d]) { hubByDomain[d] = []; hubDomains.push(d); }
            hubByDomain[d].push(ind);
        });
        hubDomains.forEach(function (d) {
            var dh = document.createElement('p');
            dh.className = 'mt-2 text-xs font-semibold text-indigo-700 dark:text-indigo-300';
            dh.textContent = d;
            sheet.appendChild(dh);
            hubByDomain[d].forEach(function (ind) {
                sheet.appendChild(hubRatingRow(ind, scaleKeys, tpl.rating_scale || {}, true));
            });
        });
        var starWrap = document.createElement('div');
        starWrap.className = 'mt-2';
        starWrap.innerHTML = '<label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="of-star">STAR notes <span class="font-normal text-gray-400">(Situation · Task · Action · Result)</span></label>'
            + '<textarea id="of-star" rows="3" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" placeholder="What went well and why…"></textarea>';
        sheet.appendChild(starWrap);
        if (kept && kept.star) { var starEl = starWrap.querySelector('#of-star'); if (starEl) starEl.value = kept.star; }
        restoreSheetState(sheet, kept);
        ratingsBox.appendChild(sheet);
    }

    // EPOC sheet for school-head observations: plain 1–5 scale (same labels
    // as the online EPOC form), no NO / N/A flags, plus the narrative and
    // agreement fields the EPOC evaluation stores.
    var EPOC_LABELS = { 1: 'Never', 2: 'Seldom', 3: 'Sometimes', 4: 'Often', 5: 'Always' };

    function buildEpocSheet(json, kept) {
        var sheet = document.createElement('div');
        sheet.setAttribute('data-hub-sheet', 'epoc');
        var indicators = ((json.epoc_template || {}).indicators) || [];
        if (!indicators.length) {
            epocNote.classList.remove('hidden');
            ratingsBox.appendChild(sheet);
            return;
        }
        epocNote.classList.add('hidden');
        var tplName = (json.epoc_template && json.epoc_template.name) || 'EPOC';
        sheet.appendChild(hubSheetHead('EPOC ratings · ' + tplName, '1 = Never · 2 = Seldom · 3 = Sometimes · 4 = Often · 5 = Always. Fill what you observed, leave the rest blank.'));
        var clearAll = document.createElement('button');
        clearAll.type = 'button';
        clearAll.className = 'mb-2 px-3 py-1.5 text-xs font-medium text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 rounded-lg transition-colors inline-flex items-center gap-1.5';
        clearAll.textContent = 'Clear EPOC ratings';
        clearAll.addEventListener('click', function () {
            if (!confirm('Clear all EPOC ratings and comments?')) return;
            sheet.querySelectorAll('[data-hub-row]').forEach(function (row) {
                row.querySelector('[data-hub-rating]').value = '';
                row.querySelector('[data-hub-noflag]').checked = false;
                row.querySelector('[data-hub-naflag]').checked = false;
                var ta = row.querySelector('[data-hub-comment]');
                if (ta) ta.value = '';
                var cw = row.querySelector('[data-hub-cwrap]');
                if (cw) cw.hidden = true;
                var cb = row.querySelector('[data-hub-cbtn]');
                if (cb) { cb.classList.remove('has-comment'); cb.textContent = 'Comment'; }
                hubPaint(row);
            });
            hubRecompute();
        });
        sheet.appendChild(clearAll);
        var domains = [];
        var byDomain = {};
        indicators.forEach(function (ind) {
            var d = ind.domain || 'General';
            if (!byDomain[d]) { byDomain[d] = []; domains.push(d); }
            byDomain[d].push(ind);
        });
        var n = 0;
        domains.forEach(function (d) {
            var dh = document.createElement('p');
            dh.className = 'mt-2 text-xs font-semibold text-indigo-700 dark:text-indigo-300';
            dh.textContent = d;
            sheet.appendChild(dh);
            byDomain[d].forEach(function (ind) {
                n++;
                sheet.appendChild(hubRatingRow(
                    { code: 'E' + n, domain: ind.domain || 'General', description: ind.indicator },
                    ['1', '2', '3', '4', '5'], EPOC_LABELS, false
                ));
            });
        });
        var extra = document.createElement('div');
        extra.className = 'mt-2 grid gap-2';
        extra.innerHTML = '<div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="of-epoc-narrative">Narrative observation <span class="font-normal text-gray-400">(optional)</span></label>'
            + '<textarea id="of-epoc-narrative" rows="3" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" placeholder="Overall flow and key moments of the post-observation conference…"></textarea></div>'
            + '<div><label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="of-epoc-agreement">Agreement / next steps <span class="font-normal text-gray-400">(optional)</span></label>'
            + '<textarea id="of-epoc-agreement" rows="2" class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100" placeholder="Agreements and next steps decided upon…"></textarea></div>';
        sheet.appendChild(extra);
        if (kept) {
            if (kept.narrative) { var nar = extra.querySelector('#of-epoc-narrative'); if (nar) nar.value = kept.narrative; }
            if (kept.agreement) { var agr = extra.querySelector('#of-epoc-agreement'); if (agr) agr.value = kept.agreement; }
        }
        restoreSheetState(sheet, kept);
        ratingsBox.appendChild(sheet);
    }

    function applySheetVisibility() {
        var isHead = observeeType === 'school_head_observation';
        var cot = ratingsBox.querySelector('[data-hub-sheet="cot"]');
        var epoc = ratingsBox.querySelector('[data-hub-sheet="epoc"]');
        if (cot) cot.classList.toggle('hidden', isHead);
        if (epoc) epoc.classList.toggle('hidden', !isHead);
    }

    function filterTeachers() {
        var q = (teacherSearch.value || '').toLowerCase();
        Array.prototype.forEach.call(teacherSel.options, function (o) {
            o.hidden = q !== '' && o.text.toLowerCase().indexOf(q) === -1;
        });
    }
    teacherSearch.addEventListener('input', filterTeachers);

    var fileInput = document.getElementById('of-files');
    var filePreview = document.getElementById('of-file-preview');

    function fmtBytes(n) {
        if (!n) return '0 KB';
        if (n < 1024 * 1024) return Math.round(n / 1024) + ' KB';
        return (n / (1024 * 1024)).toFixed(1) + ' MB';
    }

    fileInput.addEventListener('change', function () {
        filePreview.innerHTML = '';
        Array.prototype.forEach.call(fileInput.files, function (f) {
            var li = document.createElement('li');
            li.textContent = '• ' + f.name + ' (' + fmtBytes(f.size) + ')';
            filePreview.appendChild(li);
        });
    });

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function reasonText(item) {
        var map = {
            teacher_not_in_school: 'Teacher is not in your school — kept off the server.',
            school_head_not_in_school: 'School head is not in your school — kept off the server.',
            observee_not_found: 'That person no longer exists on the server.',
            possible_duplicate: 'Looks like a duplicate of an existing observation.',
            server_error: 'Server hiccup — kept on this device to retry.',
            validation_failed: 'Missing or invalid fields.'
        };
        var base = map[item.reason] || item.reason || 'Not synced.';
        if (item.messages) {
            var details = [];
            Object.keys(item.messages).forEach(function (k) { details.push(item.messages[k].join(' ')); });
            if (details.length) base += ' ' + details.join(' ');
        }
        if (item.message && item.reason === 'server_error') base += ' (' + item.message + ')';
        return base;
    }

    function renderResult(res) {
        if (!res || res.skipped) return;
        var synced = res.synced || [], conflicts = res.conflicts || [], errors = res.errors || [];
        var filesUp = res.filesUploaded || [];
        var filesBad = (res.filesFailed || []).concat(res.filesErrored || []);
        if (!synced.length && !conflicts.length && !errors.length && !filesUp.length && !filesBad.length) return;
        resultBox.classList.remove('hidden');
        if (conflicts.length === 0 && errors.length === 0 && filesBad.length === 0) {
            resultBox.className = 'mt-3 rounded-lg border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/20 p-3 text-sm text-emerald-900 dark:text-emerald-100';
            resultBox.innerHTML = '<strong>✓ Synced ' + synced.length + ' observation(s)' + (filesUp.length ? ' and ' + filesUp.length + ' file(s)' : '') + '.</strong> AI suggestions will generate on the server — check back later.';
        } else {
            resultBox.className = 'mt-3 rounded-lg border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 p-3 text-sm text-amber-900 dark:text-amber-100';
            var html = '<strong>Sync finished: ' + synced.length + ' sent, ' + (conflicts.length + errors.length + filesBad.length) + ' need review' + (filesUp.length ? ', ' + filesUp.length + ' file(s) uploaded' : '') + '.</strong><ul class="mt-1 list-disc pl-5 space-y-0.5">';
            conflicts.concat(errors).forEach(function (it) {
                html += '<li>' + esc((it.client_id || '').slice(0, 8)) + '… — ' + esc(reasonText(it)) + '</li>';
            });
            filesBad.forEach(function (f) {
                html += '<li>File ' + esc(f.name || (f.file_id || '').slice(0, 8)) + ' — ' + esc(f.reason || 'upload failed, kept on this device') + '</li>';
            });
            resultBox.innerHTML = html + '</ul>';
        }
    }

    var filesByObservation = {};

    function refreshAll() {
        Promise.all([AspireOffline.listPending(), AspireOffline.getCacheInfo(), AspireOffline.listPendingFiles()]).then(function (res) {
            var items = res[0] || [];
            var cache = res[1];
            var allFiles = res[2] || [];
            var ct = cache ? ((cache.value.teachers) || []).length : 0;
            var ch = cache ? ((cache.value.school_heads) || []).length : 0;
            var hh = cache ? ((cache.value.history) || []).length : 0;
            var queuedFiles = allFiles.filter(function (f) { return f.status !== 'done'; }).length;
            updateDiag('library OK · ' + (navigator.onLine ? 'online' : 'OFFLINE') + ' · bundle: ' + (cache ? ct + ' teachers, ' + ch + ' heads, ' + hh + ' past' : 'none yet — do step 1'));
            renderScheduled();
            renderHistory();

            filesByObservation = {};
            allFiles.forEach(function (f) {
                if (f.status === 'done') return;
                (filesByObservation[f.observation_client_id] = filesByObservation[f.observation_client_id] || []).push(f);
            });

            // Card 1: cache freshness
            if (cache) {
                var t = ((cache.value.teachers) || []).length;
                var sh = ((cache.value.school_heads) || []).length;
                var c = ((cache.value.cot_templates) || []).length;
                var hp = ((cache.value.history) || []).length;
                var ago = AspireOffline.timeAgo(cache.saved_at);
                var days = (Date.now() - new Date(cache.saved_at).getTime()) / 86400000;
                cacheStatus.textContent = t + ' teacher(s) · ' + sh + ' school head(s) · ' + c + ' template(s) · ' + hp + ' past · cached ' + ago + (days > 7 ? ' — refresh before your visit!' : ' ✓');
            } else {
                cacheStatus.textContent = navigator.onLine ? 'Not cached yet — tap Cache data below.' : 'Not cached and you are offline — cache next time you have signal.';
            }

            // Card 2 + form gating
            var ready = !!cache;
            captureStatus.textContent = ready
                ? (navigator.onLine ? 'Ready — works online or offline.' : 'Ready — you are offline, saves stay on this device.')
                : 'Cache data first (step 1), then capture.';
            saveBtn.disabled = !ready;
            saveBtn.classList.toggle('opacity-60', !ready);

            // Card 3
            syncStatus.textContent = items.length === 0
                ? (queuedFiles > 0 ? queuedFiles + ' file(s) waiting.' : 'Nothing waiting yet.')
                : items.length + ' waiting' + (queuedFiles > 0 ? ' · ' + queuedFiles + ' file(s) attached' : '') + ' — sync when you have signal.';

            // Pending list
            pending.textContent = items.length ? '(' + items.length + ')' : '';
            list.innerHTML = '';
            if (!items.length) {
                var empty = document.createElement('li');
                empty.className = 'text-gray-400 text-sm';
                empty.textContent = 'Nothing here. Saved observations appear here until synced.';
                list.appendChild(empty);
            }
            items.forEach(function (i) {
                var isHead = i.payload.observation_type === 'school_head_observation';
                var known = isHead ? headsById[i.payload.observee_id] : teachersById[i.payload.observee_id];
                var rated = (i.payload.ratings || []).filter(function (r) { return r.rating; }).length;
                var eRated = (i.payload.epoc_ratings || []).filter(function (r) { return r.rating; }).length;
                var li = document.createElement('li');
                li.className = 'flex items-start gap-2 rounded-md border border-gray-100 dark:border-gray-700 p-2';
                var body = document.createElement('div');
                body.className = 'flex-1 min-w-0';
                var title = document.createElement('p');
                title.className = 'font-medium truncate';
                title.textContent = (known ? known.label.split(' (')[0].split(' — ')[0] : (isHead ? 'School head #' : 'Teacher #') + i.payload.observee_id) + ' · ' + (i.payload.observation_date || 'no date');
                var sub = document.createElement('p');
                sub.className = 'text-xs text-gray-500 dark:text-gray-400 truncate';
                var kind = isHead ? 'School head' : 'Teacher';
                var itemFiles = filesByObservation[i.client_id] || [];
                var errFiles = itemFiles.filter(function (f) { return f.status === 'error'; }).length;
                var fileNote = itemFiles.length === 0 ? '' : ' · ' + itemFiles.length + ' file(s)' + (errFiles > 0 ? ' (' + errFiles + ' rejected — tap Sync to review)' : '');
                sub.textContent = kind + ' · ' + (i.payload.subject || 'No subject') + (isHead ? ' · ' + eRated + ' EPOC rating(s)' : ' · ' + rated + ' rating(s)') + fileNote + ' · saved ' + AspireOffline.timeAgo(i.device_updated_at);
                body.appendChild(title);
                body.appendChild(sub);
                var del = document.createElement('button');
                del.type = 'button';
                del.className = 'shrink-0 text-xs font-semibold text-rose-600 hover:text-rose-800 px-2 py-1';
                del.textContent = 'Discard';
                del.setAttribute('data-client-id', i.client_id);
                li.appendChild(body);
                li.appendChild(del);
                list.appendChild(li);
            });
        });
    }

    list.addEventListener('click', function (e) {
        var btn = e.target.closest ? e.target.closest('[data-client-id]') : null;
        if (!btn) return;
        if (!confirm('Discard this queued observation and its attached files? This cannot be undone.')) return;
        var cid = btn.getAttribute('data-client-id');
        AspireOffline.deleteFilesForObservation(cid).then(function () {
            return AspireOffline.deletePending(cid);
        }).then(function () {
            notify('info', 'Queued observation (and its files) discarded.');
            refreshAll();
        });
    });

    document.getElementById('offline-form').addEventListener('submit', function (e) {
        e.preventDefault();
        if (!bundle) { notify('warning', 'Cache data first (step 1) so the teacher list is available.'); return; }
        var isHead = observeeType === 'school_head_observation';
        var ratings = [];
        var epocRatings = [];
        var sheet = ratingsBox.querySelector(isHead ? '[data-hub-sheet="epoc"]' : '[data-hub-sheet="cot"]');
        if (sheet) {
            sheet.querySelectorAll('[data-hub-row]').forEach(function (row) {
                var st = hubRowState(row);
                if (st.rating == null && !st.no && !st.na) return; // untouched: skip
                var com = row.querySelector('[data-hub-comment]');
                if (isHead) {
                    epocRatings.push({ domain: row.getAttribute('data-domain') || 'General', indicator: row.getAttribute('data-desc') || row.getAttribute('data-code'), rating: st.rating, comments: com ? (com.value || null) : null });
                } else {
                    ratings.push({ indicator_code: row.getAttribute('data-code'), domain: row.getAttribute('data-domain') || 'General', indicator: row.getAttribute('data-desc') || row.getAttribute('data-code'), rating: (st.no || st.na) ? null : st.rating, not_observed: st.no, not_applicable: st.na, comments: com ? (com.value || null) : null, client_id: AspireOffline.uuid() });
                }
            });
        }
        var starEl = document.getElementById('of-star');
        var narEl = document.getElementById('of-epoc-narrative');
        var agrEl = document.getElementById('of-epoc-agreement');
        var epocTpl = bundle && bundle.epoc_template ? bundle.epoc_template : null;
        var payload = {
            observation_type: observeeType,
            observee_id: parseInt(teacherSel.value, 10),
            observation_date: document.getElementById('of-date').value,
            subject: document.getElementById('of-subject').value || null,
            grade_level: isHead ? null : (document.getElementById('of-grade').value || null),
            notes: document.getElementById('of-notes').value || null,
            star_notes: (!isHead && starEl && starEl.value.trim()) ? starEl.value.trim() : null,
            ratings: ratings,
            epoc_ratings: epocRatings,
            epoc_template_id: (isHead && epocTpl && epocTpl.id) ? epocTpl.id : null,
            epoc_narrative_observation: (isHead && narEl && narEl.value.trim()) ? narEl.value.trim() : null,
            epoc_agreement: (isHead && agrEl && agrEl.value.trim()) ? agrEl.value.trim() : null,
        };
        if (!payload.observee_id || !payload.observation_date) { notify('warning', isHead ? 'Pick a school head and an observation date first.' : 'Pick a teacher and an observation date first.'); return; }
        var dup = scheduledList.find(function (s) {
            return s.observation_type === observeeType
                && String(s.observee_id) === String(payload.observee_id)
                && (s.observation_date || '') === (payload.observation_date || '');
        });
        if (dup && !confirm('This looks like "' + dup.observee_name + ' on ' + dup.observation_date + '", which is already on the server. Saving it offline will likely be rejected as a duplicate when you sync. Save anyway?')) return;
        var check = AspireOffline.validateFiles(fileInput.files, 0);
        if (check.rejected.length) {
            notify('warning', check.rejected.map(function (r) { return r.name + ': ' + r.reason; }).join(' | '));
            return;
        }
        AspireOffline.saveObservation(payload).then(function (item) {
            // The change-autosave draft (lightweight workflow) is superseded by this explicit save.
            if (window.OfflineEncode && window.__encodeDraftId) {
                OfflineEncode.clearOutbox([window.__encodeDraftId]).catch(function () {});
            }
            var savedCount = isHead ? epocRatings.length : ratings.length;
            var savedUnit = isHead ? ' EPOC rating(s)' : ' rating(s)';
            var afterSave = function (fileNote) {
                notify('success', navigator.onLine
                    ? 'Saved on this device' + fileNote + '. Tap Sync now (step 3) to send it.'
                    : 'Saved on this device ✓' + fileNote + '. It will sync when you have signal (' + savedCount + savedUnit + ').');
                document.getElementById('of-subject').value = '';
                document.getElementById('of-grade').value = '';
                document.getElementById('of-notes').value = '';
                var starAfter = document.getElementById('of-star');
                if (starAfter) starAfter.value = '';
                var narAfter = document.getElementById('of-epoc-narrative');
                if (narAfter) narAfter.value = '';
                var agrAfter = document.getElementById('of-epoc-agreement');
                if (agrAfter) agrAfter.value = '';
                fileInput.value = '';
                filePreview.innerHTML = '';
                hubResetRatings();
                refreshAll();
            };
            if (!check.valid.length) { afterSave(''); return; }
            AspireOffline.saveFiles(item.client_id, check.valid).then(function (meta) {
                afterSave(' with ' + meta.length + ' file(s)');
            }).catch(function () {
                notify('warning', 'Observation saved, but files could not be queued (browser storage full?). Proceed without files, or capture again.');
                refreshAll();
            });
        }).catch(function () { notify('error', 'Could not save. Your browser may be blocking site data.'); });
    });

    function loadBundle() {
        // 1) Server-rendered bundle: instant and always trustworthy. Persist it
        // so true offline reloads keep working.
        if (window.ASPIRE_BOOTSTRAP) {
            renderBundle(window.ASPIRE_BOOTSTRAP);
            AspireOffline.saveBootstrap(window.ASPIRE_BOOTSTRAP).then(refreshAll, refreshAll);
        } else {
            AspireOffline.getCachedBootstrap().then(function (cached) {
                if (cached) { renderBundle(cached); refreshAll(); }
            });
        }
        // 2) Background refresh when online. Only complains when there is
        // nothing usable to show — otherwise the old bundle stays in place.
        if (navigator.onLine) {
            AspireOffline.cacheBootstrap().then(function (json) {
                renderBundle(json);
                refreshAll();
            }).catch(function (err) {
                if (!bundle) {
                    cacheStatus.textContent = cacheErrorText(err);
                    notify('warning', 'Could not load offline data: ' + cacheErrorText(err));
                }
                refreshAll();
            });
        }
    }

    document.addEventListener('aspire:sync', function (e) { renderResult(e && e.detail); refreshAll(); });
    document.addEventListener('aspire:sync-error', refreshAll);

    document.getElementById('of-date').value = new Date().toISOString().slice(0, 10);
    try {
        loadBundle();
        refreshAll();
    } catch (err) {
        updateDiag('startup error: ' + ((err && err.message) || err));
        notify('error', 'Offline page hit a startup error. Reload while online; if it persists, tell your admin.');
    }
})();
</script>
@endpush
@push('scripts')
<script>
/* Offline visit packages hub: lists IndexedDB-cached observation bundles and
 * drives the package outbox. Read-only when the engine is missing. */
(function () {
    var P = window.AspireOfflinePackage;
    var list = document.getElementById('pkg-ready-list');
    var count = document.getElementById('pkg-ready-count');
    var note = document.getElementById('pkg-outbox-note');
    var syncBtn = document.getElementById('pkg-sync-now');
    var syncNote = document.getElementById('pkg-sync-note');
    if (!list) return;

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function workspaceUrl(serverId) {
        return (window.ASPIRE_BASE_URL || '') + '/' + @json($offlineUrlPrefix ?? 'supervisor') + '/observations/' + serverId + '/offline-workspace';
    }

    function refresh() {
        if (!P) {
            list.innerHTML = '<li class="text-xs text-gray-400">Package engine failed to load — hard-refresh while online.</li>';
            return;
        }
        P.listPackages().then(function (pkgs) {
            count.textContent = pkgs.length ? '(' + pkgs.length + ')' : '';
            list.innerHTML = '';
            if (!pkgs.length) {
                var empty = document.createElement('li');
                empty.className = 'text-xs text-gray-400';
                empty.textContent = 'No packages yet. While online, open a confirmed observation and tap “Download for Offline Use”.';
                list.appendChild(empty);
            }
            pkgs.forEach(function (rec) {
                var b = rec.bundle || {};
                var o = b.observation || {};
                var li = document.createElement('li');
                li.className = 'flex items-center gap-2 rounded-md border border-gray-100 dark:border-gray-700 px-2 py-1.5';
                var dot = document.createElement('span');
                dot.className = 'h-2 w-2 shrink-0 rounded-full bg-emerald-500';
                var body = document.createElement('div');
                body.className = 'flex-1 min-w-0';
                var title = document.createElement('p');
                title.className = 'font-medium truncate text-sm';
                title.textContent = (o.teacher ? o.teacher.name : 'Observation #' + rec.observation_server_id)
                    + ' · ' + (o.subject || 'No subject') + ' · ' + (o.observation_date || 'no date');
                var sub = document.createElement('p');
                sub.className = 'text-xs text-gray-500 dark:text-gray-400 truncate';
                sub.textContent = 'Saved ' + (rec.saved_at ? new Date(rec.saved_at).toLocaleString() : 'on this device')
                    + (b.ai_ready ? ' · AI prompts included' : '');
                body.appendChild(title);
                body.appendChild(sub);
                var open = document.createElement('a');
                open.href = workspaceUrl(rec.observation_server_id);
                open.className = 'mock-btn shrink-0';
                open.textContent = 'Open workspace';
                li.appendChild(dot);
                li.appendChild(body);
                li.appendChild(open);
                list.appendChild(li);
            });
        }).catch(function () {});
        P.pendingCount().then(function (n) {
            note.textContent = n ? n + ' waiting to sync' : '';
            if (syncNote) syncNote.textContent = n
                ? n + ' package observation(s) will push when you sync.'
                : 'Pushes saved package observations to the server.';
        }).catch(function () {});
    }

    if (syncBtn) syncBtn.addEventListener('click', function () {
        if (!P) return;
        syncBtn.disabled = true;
        P.syncNow().then(refresh).catch(refresh).then(function () { syncBtn.disabled = false; });
    });
    document.addEventListener('offline-package:synced', refresh);
    document.addEventListener('offline-package:sync-error', refresh);
    refresh();
})();
</script>
<script>
/* Lightweight offline encoding bridge (vanilla JS):
 * - loads the cached observation from IndexedDB store `offline_observations` when the
 *   main engine has nothing cached (e.g. preparation happened on the show page);
 * - saves ratings/notes/comments to the `outbox` store on every form change;
 * - drives the "Offline Mode (Saved Locally)" badge. */
(function () {
    if (!window.OfflineEncode) return;
    var form = document.getElementById('offline-form');
    var badge = document.getElementById('offline-mode-badge');
    if (!form) return;

    var draftId = (window.crypto && crypto.randomUUID)
        ? crypto.randomUUID()
        : 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            var r = (Math.random() * 16) | 0, v = c === 'x' ? r : (r & 0x3) | 0x8;
            return v.toString(16);
        });
    window.__encodeDraftId = draftId;

    // Seed the main engine's cache from `offline_observations` so lists render
    // with zero connectivity even if caching happened on another page.
    if (window.AspireOffline) {
        AspireOffline.getCacheInfo().then(function (info) {
            if (info) return;
            OfflineEncode.loadCachedObservation('bootstrap').then(function (bundle) {
                if (bundle) AspireOffline.saveBootstrap(bundle).then(function () { location.reload(); });
            }).catch(function () {});
        }).catch(function () {});
    }

    function snapshot() {
        var typeBtn = document.getElementById('of-type-head');
        var isHead = typeBtn && typeBtn.getAttribute('aria-pressed') === 'true';
        // NOTE: hubRowState lives in the main page script scope, not here —
        // read the hidden inputs directly so snapshots never throw.
        function readRow(row) {
            var hid = row.querySelector('[data-hub-rating]');
            var noH = row.querySelector('[data-hub-noflag]');
            var naH = row.querySelector('[data-hub-naflag]');
            return {
                rating: (hid && hid.value !== '') ? parseInt(hid.value, 10) : null,
                no: !!(noH && noH.checked),
                na: !!(naH && naH.checked),
            };
        }
        var ratings = [];
        var epocRatings = [];
        var box = document.getElementById('of-ratings');
        var activeSheet = box ? box.querySelector(isHead ? '[data-hub-sheet="epoc"]' : '[data-hub-sheet="cot"]') : null;
        if (activeSheet) activeSheet.querySelectorAll('[data-hub-row]').forEach(function (row) {
            var st = readRow(row);
            if (st.rating == null && !st.no && !st.na) return;
            var com = row.querySelector('[data-hub-comment]');
            if (isHead) {
                epocRatings.push({
                    domain: row.getAttribute('data-domain') || 'General',
                    indicator: row.getAttribute('data-desc') || row.getAttribute('data-code'),
                    rating: st.rating,
                    comments: com ? (com.value || null) : null,
                });
            } else {
                ratings.push({
                    indicator_code: row.getAttribute('data-code'),
                    domain: row.getAttribute('data-domain') || 'General',
                    rating: (st.no || st.na) ? null : st.rating,
                    not_observed: st.no,
                    not_applicable: st.na,
                    comments: com ? (com.value || null) : null,
                });
            }
        });
        function tval(id) { var el = document.getElementById(id); return (el && el.value.trim()) ? el.value.trim() : null; }
        var sel = document.getElementById('of-teacher');
        return {
            observation_type: isHead ? 'school_head_observation' : 'teacher_observation',
            observee_id: sel && sel.value ? parseInt(sel.value, 10) : null,
            observation_date: (document.getElementById('of-date') || {}).value || null,
            subject: (document.getElementById('of-subject') || {}).value || null,
            grade_level: isHead ? null : ((document.getElementById('of-grade') || {}).value || null),
            notes: (document.getElementById('of-notes') || {}).value || null,
            star_notes: !isHead ? tval('of-star') : null,
            ratings: ratings,
            epoc_ratings: epocRatings,
            epoc_narrative_observation: isHead ? tval('of-epoc-narrative') : null,
            epoc_agreement: isHead ? tval('of-epoc-agreement') : null,
        };
    }

    var timer = null;
    function queueDraftSnapshot() {
        clearTimeout(timer);
        timer = setTimeout(function () {
            var data = snapshot();
            if (!data.observee_id && !data.notes && !(data.ratings || []).length && !(data.epoc_ratings || []).length) return;
            OfflineEncode.saveDraft({ client_id: draftId, payload: data }).then(refreshBadge).catch(function () {});
        }, 500);
    }
    form.addEventListener('input', queueDraftSnapshot);
    form.addEventListener('change', queueDraftSnapshot);
    // Rating picks are button clicks (no input/change events) — snapshot those too.
    form.addEventListener('click', function (e) {
        var b = e.target.closest ? e.target.closest('[data-hub-btn],[data-hub-no],[data-hub-na]') : null;
        if (!b) return;
        queueDraftSnapshot();
    });

    function refreshBadge() {
        if (!badge) return;
        var encodeCount = OfflineEncode.listOutbox()
            .then(function (items) { return items.filter(function (i) { return i.status === 'dirty'; }).length; })
            .catch(function () { return 0; });
        var legacyCount = window.AspireOffline
            ? AspireOffline.pendingCount().catch(function () { return 0; })
            : Promise.resolve(0);
        Promise.all([encodeCount, legacyCount]).then(function (res) {
            var pending = res[0] + res[1];
            var show = !navigator.onLine || pending > 0;
            badge.classList.toggle('hidden', !show);
            badge.classList.toggle('inline-flex', show);
            var label = badge.querySelector('[data-badge-label]');
            if (label) label.textContent = 'Offline Mode (Saved Locally)' + (pending ? ' · ' + pending + ' waiting' : '');
        });
    }

    window.addEventListener('online', refreshBadge);
    window.addEventListener('offline', refreshBadge);
    document.addEventListener('aspire:sync', refreshBadge);
    document.addEventListener('aspire:sync-error', refreshBadge);
    document.addEventListener('offline-encode:synced', refreshBadge);
    document.addEventListener('offline-encode:saved', refreshBadge);
    refreshBadge();
})();
</script>
@endpush
@endsection
