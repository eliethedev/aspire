{{-- Offline status + guided sync flow (Architecture B). Included in supervisor layout.
     Ambient status lives in the header pill (#aspire-net-*); this banner shows
     contextual guidance with a 3-step flow: Cache → Capture → Sync. --}}
<div id="aspire-offline-banner" class="hidden border-b border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-900/20 px-4 py-2.5" role="status">
    <div class="flex flex-wrap items-center gap-x-3 gap-y-1.5">
        <span id="aspire-offline-dot" class="h-2.5 w-2.5 shrink-0 rounded-full bg-amber-500"></span>
        <p id="aspire-offline-text" class="text-sm font-medium text-amber-900 dark:text-amber-100"></p>
        <span id="aspire-offline-count" class="hidden items-center rounded-full bg-amber-200 dark:bg-amber-800 px-2 py-0.5 text-xs font-bold text-amber-900 dark:text-amber-100"></span>
        <span class="ml-auto flex flex-wrap gap-2">
            <button type="button" class="aspire-cache-offline inline-flex items-center gap-1 rounded-md border border-indigo-300 dark:border-indigo-700 bg-white dark:bg-transparent px-3 py-1.5 text-xs font-semibold text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-900/40 transition-colors">Cache data</button>
            <button type="button" class="aspire-sync-now inline-flex items-center gap-1 rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700 transition-colors">Sync now</button>
        </span>
    </div>
    <ol id="aspire-steps" class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs" aria-label="Offline workflow">
        <li data-step="cache" class="flex items-center gap-1.5">
            <span class="aspire-step-dot inline-flex h-5 w-5 items-center justify-center rounded-full text-[11px] font-bold"></span>
            <span class="aspire-step-label">Cache data</span>
        </li>
        <li aria-hidden="true" class="text-gray-300 dark:text-gray-600">→</li>
        <li data-step="capture" class="flex items-center gap-1.5">
            <span class="aspire-step-dot inline-flex h-5 w-5 items-center justify-center rounded-full text-[11px] font-bold"></span>
            <span class="aspire-step-label">Capture offline</span>
        </li>
        <li aria-hidden="true" class="text-gray-300 dark:text-gray-600">→</li>
        <li data-step="sync" class="flex items-center gap-1.5">
            <span class="aspire-step-dot inline-flex h-5 w-5 items-center justify-center rounded-full text-[11px] font-bold"></span>
            <span class="aspire-step-label">Sync &amp; get AI</span>
        </li>
    </ol>
</div>

@once
@push('scripts')
{{-- Host-independent base (no APP_URL host baked in) so this also loads when the
     app is opened from a tablet via the server's LAN IP instead of localhost. --}}
<script>window.ASPIRE_BASE_URL = @json(request()->getBaseUrl());</script>
{{-- The offline engine is inlined (not a separate download) so it can never
     404, go stale in caches, or break on tablets using a different host.
     public/js/aspire-offline.js remains the source of truth — keep it in sync. --}}
<script>
{!! file_get_contents(public_path('js/aspire-offline.js')) !!}
</script>
<script>window.__aspireLibInlineOk = !!window.AspireOffline;</script>
<script>
// Minimal SW: keeps the offline capture shell + cached JS available with zero connectivity.
// (Service workers only run on localhost/https, so field tablets rely on keeping the tab open.)
if ('serviceWorker' in navigator && (window.location.protocol === 'https:' || window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1')) {
    window.addEventListener('load', function () {
        navigator.serviceWorker.register((window.ASPIRE_BASE_URL || '') + '/sw.js').catch(function () {});
    });
}
</script>
<script>
(function () {
    var banner = document.getElementById('aspire-offline-banner');
    var dot = document.getElementById('aspire-offline-dot');
    var text = document.getElementById('aspire-offline-text');
    var count = document.getElementById('aspire-offline-count');
    var pill = document.getElementById('aspire-net-pill');
    var pillDot = document.getElementById('aspire-net-dot');
    var pillLabel = document.getElementById('aspire-net-label');
    var pillCount = document.getElementById('aspire-net-count');
    var headerSync = document.querySelector('#aspire-net-pill + .aspire-sync-now, header .aspire-sync-now');
    var lastSyncOk = false;

    function notify(type, message) {
        if (typeof window.showToast === 'function') { window.showToast(type, message); }
        else { alert(message); }
    }

    function setBusy(busy) {
        document.querySelectorAll('.aspire-sync-now').forEach(function (b) {
            b.disabled = busy;
            b.classList.toggle('opacity-60', busy);
            if (b.dataset.label === undefined) b.dataset.label = b.textContent;
            b.textContent = busy ? 'Syncing…' : b.dataset.label;
        });
        document.querySelectorAll('.aspire-cache-offline').forEach(function (b) {
            b.disabled = busy;
            b.classList.toggle('opacity-60', busy);
        });
    }

    var STEP_NUM = { cache: '1', capture: '2', sync: '3' };

    function paintStep(name, state) {
        var li = document.querySelector('#aspire-steps li[data-step="' + name + '"]');
        if (!li) return;
        var d = li.querySelector('.aspire-step-dot');
        var l = li.querySelector('.aspire-step-label');
        d.className = 'aspire-step-dot inline-flex h-5 w-5 items-center justify-center rounded-full text-[11px] font-bold';
        l.className = 'aspire-step-label';
        if (state === 'done') {
            d.classList.add('bg-emerald-500', 'text-white');
            d.textContent = '✓';
            l.classList.add('text-gray-600', 'dark:text-gray-300');
        } else if (state === 'active') {
            d.classList.add('bg-indigo-600', 'text-white');
            d.textContent = STEP_NUM[name];
            l.classList.add('font-semibold', 'text-indigo-700', 'dark:text-indigo-300');
        } else {
            d.classList.add('bg-gray-200', 'dark:bg-gray-700', 'text-gray-500', 'dark:text-gray-400');
            d.textContent = STEP_NUM[name];
            l.classList.add('text-gray-400', 'dark:text-gray-500');
        }
    }

    function refresh() {
        if (!window.AspireOffline) return;
        Promise.all([AspireOffline.pendingCount(), AspireOffline.getCacheInfo(), AspireOffline.pendingFilesCount()]).then(function (res) {
            var pending = res[0] || 0;
            var cache = res[1];
            var pendingFiles = res[2] || 0;
            var cached = !!cache;
            var online = navigator.onLine;
            var pushing = AspireOffline.isPushing();

            // --- Header pill (ambient, always visible) ---
            if (pill) {
                pill.classList.remove('hidden');
                pillDot.className = 'h-2 w-2 rounded-full ' + (online ? 'bg-emerald-500' : 'bg-amber-500');
                pillLabel.textContent = pushing ? 'Syncing…' : (online ? 'Online' : 'Offline');
                if (pillCount) {
                    pillCount.classList.toggle('hidden', pending === 0);
                    pillCount.classList.toggle('inline-flex', pending > 0);
                    pillCount.textContent = pending;
                    pillCount.title = pending + ' observation(s) waiting to sync';
                }
                if (headerSync) headerSync.classList.toggle('hidden', pending === 0);
            }

            // --- Banner (contextual guidance) ---
            var show = !online || pending > 0;
            banner.classList.toggle('hidden', !show);
            if (show) {
                var filesNote = pendingFiles > 0 ? ' (' + pendingFiles + ' file(s) attached)' : '';
                if (!online) {
                    dot.className = 'h-2.5 w-2.5 shrink-0 rounded-full bg-amber-500';
                    text.textContent = pending > 0
                        ? 'You are offline — new observations save safely on this device (' + pending + ' waiting to sync' + filesNote + ').'
                        : 'You are offline — new observations will save safely on this device.';
                } else {
                    dot.className = 'h-2.5 w-2.5 shrink-0 rounded-full bg-sky-500';
                    text.textContent = pushing
                        ? 'Syncing your queued observations…'
                        : 'Back online — ' + pending + ' observation(s) ready to sync' + filesNote + '. AI suggestions generate after sync.';
                }
                count.classList.toggle('hidden', pending === 0);
                count.classList.toggle('inline-flex', pending > 0);
                if (pending > 0) count.textContent = pending + ' pending';
            }

            // --- Stepper ---
            paintStep('cache', cached ? 'done' : (online ? 'active' : 'todo'));
            paintStep('capture', pending > 0 ? 'done' : (cached ? 'active' : 'todo'));
            paintStep('sync', (lastSyncOk && pending === 0 && cached) ? 'done' : ((pending > 0 && online) ? 'active' : 'todo'));

            // --- Button labels / states ---
            document.querySelectorAll('.aspire-cache-offline').forEach(function (b) {
                if (b.dataset.label === undefined || b.dataset.autoLabel) {
                    b.textContent = cached ? 'Refresh cached data' : 'Cache data for field visit';
                    b.dataset.autoLabel = '1';
                }
            });
            document.querySelectorAll('.aspire-sync-now').forEach(function (b) {
                var canSync = online && pending > 0 && !pushing;
                b.disabled = !canSync;
                b.classList.toggle('opacity-60', !canSync);
                b.title = !online ? 'Connect to the internet to sync'
                    : (pending === 0 ? 'Nothing waiting to sync' : 'Push ' + pending + ' queued observation(s)');
            });
        });
    }

    function doSync() {
        if (!window.AspireOffline || AspireOffline.isPushing()) return;
        if (!navigator.onLine) {
            notify('warning', 'You are offline. Observations stay queued on this device — sync when you have signal.');
            return;
        }
        setBusy(true);
        AspireOffline.syncAll().then(function (res) {
            if (res && res.skipped) return;
            var synced = ((res && res.synced) || []).length;
            var bad = (((res && res.conflicts) || []).length) + (((res && res.errors) || []).length);
            var filesUp = ((res && res.filesUploaded) || []).length;
            var filesBad = (((res && res.filesFailed) || []).length) + (((res && res.filesErrored) || []).length);
            var filesNote = filesUp > 0 ? ' ' + filesUp + ' file(s) uploaded.' : '';
            var filesWarn = filesBad > 0 ? ' ' + filesBad + ' file(s) need review on the capture page.' : '';
            lastSyncOk = bad === 0 && filesBad === 0;
            if (synced > 0 && bad === 0 && filesBad === 0) {
                notify('success', 'Synced ' + synced + ' observation(s).' + filesNote + ' AI suggestions will generate on the server.');
            } else if (synced > 0) {
                notify('warning', 'Synced ' + synced + ', but ' + bad + ' item(s) need review on the capture page.' + filesNote + filesWarn);
            } else if (bad > 0 || filesBad > 0) {
                notify('error', (bad + filesBad) + ' item(s) could not sync. Open the capture page to review them.' + filesNote);
            } else if (filesUp > 0) {
                notify('success', 'Uploaded ' + filesUp + ' queued file(s). Everything is up to date.');
            } else {
                notify('info', 'Nothing to sync — everything is up to date.');
            }
        }).catch(function () {
            lastSyncOk = false;
            notify('error', 'Sync failed (connection lost?). Items stay queued on this device.');
        }).finally(function () {
            setBusy(false);
            refresh();
        });
    }

    function doCache() {
        if (!window.AspireOffline) return;
        if (!navigator.onLine) {
            notify('warning', 'You need internet to cache data. Connect, then tap Cache again.');
            return;
        }
        setBusy(true);
        AspireOffline.cacheBootstrap().then(function (json) {
            var t = ((json && json.teachers) || []).length;
            var c = ((json && json.cot_templates) || []).length;
            lastSyncOk = false;
            notify('success', 'Offline data ready: ' + t + ' teacher(s), ' + c + ' COT template(s). You can now work with zero connectivity.');
        }).catch(function () {
            notify('error', 'Could not cache offline data. Check your connection and try again.');
        }).finally(function () {
            setBusy(false);
            refresh();
        });
    }

    document.addEventListener('click', function (e) {
        var syncBtn = e.target.closest ? e.target.closest('.aspire-sync-now') : null;
        var cacheBtn = e.target.closest ? e.target.closest('.aspire-cache-offline') : null;
        // Back-compat with earlier id-based buttons.
        if (!syncBtn && e.target && e.target.id === 'aspire-sync-now') syncBtn = e.target;
        if (!cacheBtn && e.target && e.target.id === 'aspire-cache-offline') cacheBtn = e.target;
        if (syncBtn) { e.preventDefault(); doSync(); }
        else if (cacheBtn) { e.preventDefault(); doCache(); }
    });

    window.addEventListener('online', refresh);
    window.addEventListener('offline', refresh);
    document.addEventListener('aspire:sync', function (e) {
        setBusy(false);
        var d = (e && e.detail) || {};
        var bad = ((d.conflicts) || []).length + ((d.errors) || []).length;
        if (bad === 0 && ((d.synced) || []).length > 0) lastSyncOk = true;
        refresh();
    });
    document.addEventListener('aspire:sync-error', function () { setBusy(false); refresh(); });
    document.addEventListener('aspire:sync-start', function () { setBusy(true); refresh(); });

    refresh();
    setInterval(refresh, 15000);
})();
</script>
@endpush
@endonce
