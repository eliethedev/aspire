@extends('layouts.supervisor')

@section('title', 'Offline Capture')

@section('content')
<div class="max-w-3xl mx-auto px-3 py-4">
    <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Offline observation capture</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">No signal in the school? No problem — follow the 3 steps below. Your work saves on this device and syncs later.</p>
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
    <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
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

    {{-- Last sync result --}}
    <div id="of-result" class="hidden mt-3 rounded-lg border p-3 text-sm"></div>

    <div class="mt-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
        <h2 class="font-semibold text-gray-900 dark:text-gray-100">Already on the server — don't re-capture</h2>
        <p class="text-xs text-gray-400 mt-0.5">These observations exist on the server. Capturing the same person + date offline will be rejected as a duplicate on sync.</p>
        <ul id="of-scheduled" class="mt-2 space-y-1.5 text-sm text-gray-700 dark:text-gray-300"></ul>
    </div>

    <form id="offline-form" class="mt-4 space-y-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
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
        <p id="of-epoc-note" class="hidden text-xs text-gray-500 dark:text-gray-400 rounded-md bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 p-2">School-head observations use the EPOC instrument — you'll complete it on the server after sync. Notes above are saved with the observation.</p>
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

@push('scripts')
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
        ratingsWrap.classList.toggle('hidden', isHead);
        epocNote.classList.toggle('hidden', !isHead);
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

    function renderBundle(json) {
        bundle = json;
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
        ratingsBox.innerHTML = '';
        var tpl = (json.cot_templates || [])[0];
        if (tpl) {
            var h = document.createElement('p');
            h.className = 'text-sm font-medium text-gray-700 dark:text-gray-300';
            h.textContent = 'COT ratings · ' + tpl.label + ' — fill what you observed, leave the rest blank';
            ratingsBox.appendChild(h);
            (tpl.indicators || []).forEach(function (ind) {
                var row = document.createElement('div');
                row.className = 'flex flex-col sm:flex-row sm:items-center gap-1.5 rounded-md border border-gray-100 dark:border-gray-700 p-2';
                var label = document.createElement('span');
                label.className = 'flex-1 text-sm text-gray-700 dark:text-gray-300';
                var num = document.createElement('input');
                num.type = 'number'; num.min = '2'; num.max = '8'; num.placeholder = '2–8';
                num.className = 'w-full sm:w-20 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100';
                num.setAttribute('data-code', ind.code);
                num.setAttribute('data-domain', ind.domain || 'General');
                num.setAttribute('data-desc', ind.description || ind.code);
                var comment = document.createElement('input');
                comment.type = 'text'; comment.placeholder = 'Comment (optional)';
                comment.className = 'flex-1 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100';
                comment.setAttribute('data-comment', '');
                comment.setAttribute('data-code', ind.code);
                row.appendChild(label);
                row.appendChild(num);
                row.appendChild(comment);
                label.textContent = ind.code + ' — ' + (ind.description || '').slice(0, 100);
                ratingsBox.appendChild(row);
            });
        } else {
            var p = document.createElement('p');
            p.className = 'text-sm text-gray-400';
            p.textContent = 'No COT template cached yet — ratings will be added after sync.';
            ratingsBox.appendChild(p);
        }
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
                sub.textContent = kind + ' · ' + (i.payload.subject || 'No subject') + (isHead ? '' : ' · ' + rated + ' rating(s)') + fileNote + ' · saved ' + AspireOffline.timeAgo(i.device_updated_at);
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
        if (!isHead) {
            ratingsBox.querySelectorAll('[data-code][type="number"]').forEach(function (input) {
                var code = input.getAttribute('data-code');
                var comment = ratingsBox.querySelector('[data-comment][data-code="' + code + '"]');
                if (input.value) {
                    ratings.push({ indicator_code: code, domain: input.getAttribute('data-domain') || 'General', indicator: input.getAttribute('data-desc') || code, rating: parseInt(input.value, 10), comments: comment ? comment.value : null, client_id: AspireOffline.uuid() });
                }
            });
        }
        var payload = {
            observation_type: observeeType,
            observee_id: parseInt(teacherSel.value, 10),
            observation_date: document.getElementById('of-date').value,
            subject: document.getElementById('of-subject').value || null,
            grade_level: isHead ? null : (document.getElementById('of-grade').value || null),
            notes: document.getElementById('of-notes').value || null,
            ratings: ratings,
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
            var afterSave = function (fileNote) {
                notify('success', navigator.onLine
                    ? 'Saved on this device' + fileNote + '. Tap Sync now (step 3) to send it.'
                    : 'Saved on this device ✓' + fileNote + '. It will sync when you have signal' + (isHead ? '.' : ' (' + ratings.length + ' rating(s)).'));
                document.getElementById('of-subject').value = '';
                document.getElementById('of-grade').value = '';
                document.getElementById('of-notes').value = '';
                fileInput.value = '';
                filePreview.innerHTML = '';
                ratingsBox.querySelectorAll('input').forEach(function (i) { i.value = ''; });
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
<script src="{{ request()->getBaseUrl() }}/js/offline-encode.js"></script>
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
        var ratings = [];
        if (!isHead) {
            var box = document.getElementById('of-ratings');
            if (box) box.querySelectorAll('[data-code][type="number"]').forEach(function (input) {
                if (!input.value) return;
                var code = input.getAttribute('data-code');
                var comment = box.querySelector('[data-comment][data-code="' + code + '"]');
                ratings.push({
                    indicator_code: code,
                    domain: input.getAttribute('data-domain') || 'General',
                    rating: parseInt(input.value, 10),
                    comments: comment ? comment.value : null,
                });
            });
        }
        var sel = document.getElementById('of-teacher');
        return {
            observation_type: isHead ? 'school_head_observation' : 'teacher_observation',
            observee_id: sel && sel.value ? parseInt(sel.value, 10) : null,
            observation_date: (document.getElementById('of-date') || {}).value || null,
            subject: (document.getElementById('of-subject') || {}).value || null,
            grade_level: isHead ? null : ((document.getElementById('of-grade') || {}).value || null),
            notes: (document.getElementById('of-notes') || {}).value || null,
            ratings: ratings,
        };
    }

    var timer = null;
    form.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(function () {
            var data = snapshot();
            if (!data.observee_id && !data.notes && !(data.ratings || []).length) return;
            OfflineEncode.saveDraft({ client_id: draftId, payload: data }).then(refreshBadge).catch(function () {});
        }, 500);
    });
    form.addEventListener('change', function () {
        clearTimeout(timer);
        timer = setTimeout(function () {
            var data = snapshot();
            if (!data.observee_id && !data.notes && !(data.ratings || []).length) return;
            OfflineEncode.saveDraft({ client_id: draftId, payload: data }).then(refreshBadge).catch(function () {});
        }, 500);
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
