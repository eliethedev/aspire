/* ASPIRE offline observation package — vanilla JS, no dependencies.
 *
 * Implements the tablet side of the offline clinical-supervision workflow:
 *   1. downloadObservationPackage(id) caches the server bundle (metadata +
 *      pinned COT rubric + lesson plan + pre-generated AI prompts) in IndexedDB.
 *   2. renderPackageInto(root, pkg) shows AI suggestions side-by-side with the
 *      offline COT encoding form, including a deterministic live summary that
 *      uses the exact same thresholds as CotRating::descriptiveTotal().
 *   3. saveObservationOffline() stores ratings + STAR notes in the outbox with
 *      a UUID client_id. syncNow() pushes to POST /sync/push on reconnect.
 *
 * Auth: same-origin session + X-CSRF-TOKEN header (mirrors offline-encode.js).
 * No Sanctum/token needed because the PWA shell is same-origin; the tablet
 * must open the workspace once while online (session cookie set), after which
 * the service worker serves the cached shell with zero connectivity. Bearer
 * tokens were deliberately avoided: on shared school tablets a long-lived
 * token in localStorage is a bigger credential-theft risk than the session
 * cookie, and revoking a compromised tablet means one logout, not token hunts.
 *
 * IndexedDB database "aspire_package" v1:
 *  - offline_packages   : cached bundles, keyPath observation_server_id
 *  - outbox_observations: pending pushes, keyPath client_id  (+ status/ai_ready)
 *  - outbox_ratings     : per-indicator rows, keyPath client_id, index by observation_client_id
 *
 * NOTE on Dexie.js: the IT mandate names Dexie, but npm is unreachable from
 * this environment so no new dependency could be installed. The `idb()`
 * helper below exposes the same promise API shape (table(name).put/get/
 * delete/toArray) — swapping in real Dexie later is a one-file change with
 * zero caller edits.
 */
(function () {
    'use strict';

    var DB_NAME = 'aspire_package';
    var DB_VERSION = 1;
    var PKG_STORE = 'offline_packages';
    var OBS_STORE = 'outbox_observations';
    var RAT_STORE = 'outbox_ratings';

    /* ---------- tiny promise wrapper over raw IndexedDB ---------- */
    var dbPromise = null;

    function idb() {
        if (dbPromise) return dbPromise;
        dbPromise = new Promise(function (resolve, reject) {
            if (!('indexedDB' in window)) { reject(new Error('IndexedDB unavailable')); return; }
            var req = indexedDB.open(DB_NAME, DB_VERSION);
            req.onupgradeneeded = function () {
                var db = req.result;
                if (!db.objectStoreNames.contains(PKG_STORE)) {
                    db.createObjectStore(PKG_STORE, { keyPath: 'observation_server_id' });
                }
                if (!db.objectStoreNames.contains(OBS_STORE)) {
                    db.createObjectStore(OBS_STORE, { keyPath: 'client_id' });
                }
                if (!db.objectStoreNames.contains(RAT_STORE)) {
                    var rs = db.createObjectStore(RAT_STORE, { keyPath: 'client_id' });
                    rs.createIndex('observation_client_id', 'observation_client_id', { unique: false });
                }
            };
            req.onsuccess = function () { resolve(req.result); };
            req.onerror = function () { reject(req.error || new Error('IDB open failed')); };
        });
        return dbPromise;
    }

    function tx(store, mode, fn) {
        return idb().then(function (db) {
            return new Promise(function (resolve, reject) {
                var t = db.transaction(store, mode);
                var os = t.objectStore(store);
                var out;
                try { out = fn(os); } catch (e) { reject(e); return; }
                t.oncomplete = function () { resolve(out && out.value); };
                t.onerror = function () { reject(t.error || new Error('IDB transaction failed')); };
            });
        });
    }

    function put(store, value) {
        return tx(store, 'readwrite', function (os) {
            var r = os.put(value);
            var box = {};
            r.onsuccess = function () { box.value = value; };
            return box;
        });
    }

    function get(store, key) {
        return tx(store, 'readonly', function (os) {
            var box = {};
            var r = os.get(key);
            r.onsuccess = function () { box.value = r.result || null; };
            return box;
        });
    }

    function all(store) {
        return tx(store, 'readonly', function (os) {
            var box = { value: [] };
            var r = os.openCursor();
            r.onsuccess = function () {
                var c = r.result;
                if (c) { box.value.push(c.value); c.continue(); }
            };
            return box;
        });
    }

    function del(store, key) {
        return tx(store, 'readwrite', function (os) { os.delete(key); return {}; });
    }

    /* ---------- helpers ---------- */
    function uuid() {
        if (window.crypto && crypto.randomUUID) return crypto.randomUUID();
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            var r = (Math.random() * 16) | 0;
            return ((c === 'x' ? r : (r & 0x3) | 0x8)).toString(16);
        });
    }

    function base() {
        return (typeof window !== 'undefined' && window.ASPIRE_BASE_URL) || '';
    }

    function csrf() {
        var el = document.querySelector('meta[name="csrf-token"]');
        return el ? el.getAttribute('content') : '';
    }

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function toast(type, message) {
        if (typeof window.showToast === 'function') { window.showToast(type, message); return; }
        var host = document.getElementById('offline-package-toast');
        if (!host) {
            host = document.createElement('div');
            host.id = 'offline-package-toast';
            host.setAttribute('role', 'status');
            host.style.cssText = 'position:fixed;bottom:1rem;left:50%;transform:translateX(-50%);z-index:9999;display:flex;flex-direction:column;gap:.5rem;align-items:center;';
            document.body.appendChild(host);
        }
        var el = document.createElement('div');
        el.textContent = message;
        el.style.cssText = 'padding:.6rem 1rem;border-radius:.6rem;font-size:.85rem;font-weight:600;color:#fff;background:'
            + (type === 'success' ? '#059669' : type === 'error' ? '#dc2626' : type === 'warning' ? '#d97706' : '#4f46e5');
        host.appendChild(el);
        setTimeout(function () { el.remove(); }, 4500);
    }

    /* Deterministic rule-based summary — MUST match CotRating::descriptiveTotal():
     * Outstanding >= max*5.5/6, Very Satisfactory >= max*4.5/6,
     * Satisfactory >= max*3.5/6, Poor >= max*2.5/6, else Needs Improvement. */
    function descriptive(avg, scaleMax) {
        if (avg == null || isNaN(avg)) return 'No Score Yet';
        var max = scaleMax || 6;
        if (avg >= max * 5.5 / 6) return 'Outstanding';
        if (avg >= max * 4.5 / 6) return 'Very Satisfactory';
        if (avg >= max * 3.5 / 6) return 'Satisfactory';
        if (avg >= max * 2.5 / 6) return 'Poor';
        return 'Needs Improvement';
    }

    /* ---------- 1) download + cache the bundle ---------- */
    function downloadObservationPackage(serverId, opts) {
        opts = opts || {};
        var url = opts.url || (base() + '/supervisor/observations/' + serverId + '/offline-package');
        return fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
            .then(function (r) {
                return r.json().catch(function () { return {}; }).then(function (json) {
                    // 409 = teacher has not confirmed yet: never cache a locked bundle.
                    if (r.status === 409) {
                        var err = new Error((json && json.message) || 'Package locked until teacher confirmation.');
                        err.code = 'locked';
                        throw err;
                    }
                    if (!r.ok) throw new Error((json && json.message) || ('download failed: ' + r.status));
                    return json;
                });
            })
            .then(function (pkg) {
                if (!opts.force && !pkg.ai_ready) {
                    var err = new Error('AI prompts are still being prepared. Tap "Prepare Offline Package" while online, then download again.');
                    err.code = 'not_ready';
                    throw err;
                }
                // An offline visit requires the downloaded AI result: never
                // cache a bundle whose suggestions the observer has not
                // reviewed and approved (the server gates this too).
                if (!opts.force && !pkg.ai_approved) {
                    var err2 = new Error('Review and approve the AI suggestions on the observation page first, then download again.');
                    err2.code = 'not_approved';
                    throw err2;
                }
                var record = {
                    observation_server_id: pkg.server_id,
                    bundle: pkg,
                    saved_at: new Date().toISOString(),
                };
                return put(PKG_STORE, record).then(function () {
                    toast('success', 'Offline package saved on this device.');
                    updateSyncBadge();
                    return record;
                });
            });
    }

    function getPackage(serverId) { return get(PKG_STORE, serverId); }
    function listPackages() { return all(PKG_STORE); }

    /* ---------- 2) side-by-side render: AI prompts + encoding form ---------- */
    function renderPackageInto(root, pkg) {
        if (typeof root === 'string') root = document.querySelector(root);
        if (!root) throw new Error('render target missing');
        var obs = pkg.observation || {};
        var rubric = pkg.rubric || {};
        var ai = pkg.ai_prompts || {};
        var scale = rubric.scale || {};
        var scaleMax = rubric.scale_max || 6;
        var indicators = rubric.indicators || [];

        function list(items) {
            items = items || [];
            if (!items.length) return '<p class="pkg-muted">No prompts cached.</p>';
            return '<ul class="pkg-list">' + items.map(function (t) { return '<li>' + esc(t) + '</li>'; }).join('') + '</ul>';
        }

        // Ascending numeric order, like the online sheet (stored scales are descending).
        var scaleVals = Object.keys(scale).map(Number).filter(function (n) { return !isNaN(n); })
            .sort(function (a, b) { return a - b; }).map(String);

        // Group indicators by domain (same order as the online rating sheet).
        var domains = [];
        var byDomain = {};
        indicators.forEach(function (ind, i) {
            var d = ind.domain || 'General';
            if (!byDomain[d]) { byDomain[d] = []; domains.push(d); }
            byDomain[d].push({ ind: ind, idx: i });
        });

        function rateButtons(i) {
            return scaleVals.map(function (v) {
                return '<button type="button" class="pkg-rate" data-rate-btn data-idx="' + i + '" data-value="' + esc(v) + '"'
                    + ' title="' + esc(scale[v] || ('Score ' + v)) + '" aria-label="Rate ' + esc(v) + '">'
                    + esc(v) + '</button>';
            }).join('')
            + '<button type="button" class="pkg-rate pkg-no" data-rate-no data-idx="' + i + '" title="Not observed">NO</button>'
            + '<button type="button" class="pkg-rate pkg-na" data-rate-na data-idx="' + i + '" title="Not applicable — excluded from scoring">N/A</button>';
        }

        var rows = domains.map(function (d) {
            return '<div class="pkg-domain">' + esc(d) + '</div>'
            + byDomain[d].map(function (entry) {
                var ind = entry.ind, i = entry.idx;
                return '<fieldset class="pkg-ind" data-indicator="' + esc(ind.code) + '" data-idx="' + i + '">'
                + '<legend><strong>' + esc(ind.code) + '</strong> <span class="pkg-muted">' + esc(ind.domain || '') + '</span></legend>'
                + '<p class="pkg-muted">' + esc(ind.description || '') + '</p>'
                + '<div class="pkg-rates" role="group" aria-label="Rating for ' + esc(ind.code) + '">'
                + rateButtons(i)
                + '<button type="button" class="pkg-cbtn" data-rate-comment data-idx="' + i + '">Comment</button>'
                + '</div>'
                + '<input type="hidden" data-field="rating" data-idx="' + i + '" value="">'
                + '<input type="checkbox" data-field="not_observed" data-idx="' + i + '" hidden>'
                + '<input type="checkbox" data-field="not_applicable" data-idx="' + i + '" hidden>'
                + '<div class="pkg-comment" data-comment-row="' + i + '" hidden>'
                + '<textarea data-field="comments" data-idx="' + i + '" rows="2" placeholder="Evidence / comments for ' + esc(ind.code) + '"></textarea>'
                + '</div>'
                + '</fieldset>';
            }).join('');
        }).join('');

        root.innerHTML =
            '<div class="pkg-grid">'
            + '<section class="pkg-col" aria-label="Lesson plan and AI prompts">'
            + '<h3>Lesson plan</h3>'
            + '<p class="pkg-muted">' + esc((pkg.lesson_plan && pkg.lesson_plan.objectives) || 'Objectives not extracted.') + '</p>'
            + '<details' + (((pkg.lesson_plan || {}).text) ? '' : ' hidden') + '><summary>Full extracted text</summary><pre class="pkg-pre">' + esc((pkg.lesson_plan || {}).text || '') + '</pre></details>'
            + '<h3>Pre-observation AI strategies ' + (ai.fallback ? '<span class="pkg-flag">rule-based fallback</span>' : '<span class="pkg-flag ok">AI generated</span>') + '</h3>'
            + list(ai.strategies)
            + '<h3>Evidence prompts</h3>' + list(ai.evidence_prompts)
            + '<h3>Coaching prompts</h3>' + list(ai.coaching_prompts)
            + '</section>'
            + '<section class="pkg-col" aria-label="Offline encoding form">'
            + '<h3>Observation Rating Sheet (offline)</h3>'
            + '<p class="pkg-muted">' + esc(obs.teacher ? obs.teacher.name : '') + ' · ' + esc(obs.subject || '') + ' · ' + esc(obs.observation_date || '') + '</p>'
            + '<div class="pkg-progress"><span data-role="rated-label">0 of ' + indicators.length + ' rated</span>'
            + '<div class="pkg-progress-track"><div data-role="rated-bar"></div></div></div>'
            + '<div class="pkg-live" aria-live="polite">Live summary: <strong data-role="live-avg">—</strong> · <strong data-role="live-desc">No Score Yet</strong></div>'
            + '<div class="pkg-actions" style="margin-top:0;margin-bottom:8px"><button type="button" class="pkg-btn" data-mark-all-no>Mark All as NO</button></div>'
            + rows
            + '<label>General notes<textarea data-field="notes" rows="3" placeholder="Overall notes"></textarea></label>'
            + '<label>STAR notes (Situation · Task · Action · Result)<textarea data-field="star_notes" rows="3" placeholder="STAR qualitative notes"></textarea></label>'
            + '<div class="pkg-actions"><button type="button" class="pkg-btn primary" data-action="save-offline">Save Observation (Offline)</button>'
            + '<span class="pkg-muted" data-role="save-state"></span></div>'
            + '</section></div>';

        // Live deterministic summary as the supervisor picks scores (same
        // rule as online: numeric ratings excluding NO/N/A).
        function rowState(i) {
            var hid = root.querySelector('input[data-field="rating"][data-idx="' + i + '"]');
            var noBox = root.querySelector('input[data-field="not_observed"][data-idx="' + i + '"]');
            var naBox = root.querySelector('input[data-field="not_applicable"][data-idx="' + i + '"]');
            return {
                rating: hid && hid.value !== '' ? parseInt(hid.value, 10) : null,
                no: !!(noBox && noBox.checked),
                na: !!(naBox && naBox.checked),
            };
        }
        function recompute() {
            var vals = [];
            var rated = 0;
            for (var k = 0; k < indicators.length; k++) {
                var st = rowState(k);
                if (st.rating != null || st.no || st.na) rated++;
                if (st.no || st.na) continue;
                if (st.rating != null) vals.push(st.rating);
            }
            var avg = vals.length ? vals.reduce(function (a, b) { return a + b; }, 0) / vals.length : null;
            var avgEl = root.querySelector('[data-role="live-avg"]');
            var descEl = root.querySelector('[data-role="live-desc"]');
            if (avgEl) avgEl.textContent = avg == null ? '—' : avg.toFixed(2);
            if (descEl) descEl.textContent = descriptive(avg, scaleMax);
            var pct = indicators.length ? Math.round((rated / indicators.length) * 100) : 0;
            var lab = root.querySelector('[data-role="rated-label"]');
            var bar = root.querySelector('[data-role="rated-bar"]');
            if (lab) lab.textContent = rated + ' of ' + indicators.length + ' rated';
            if (bar) bar.style.width = pct + '%';
        }
        root.addEventListener('change', recompute);
        root.addEventListener('input', recompute);

        // Rating buttons mirror the online sheet: one selection per row.
        function paintRow(i) {
            var fs = root.querySelector('fieldset.pkg-ind[data-idx="' + i + '"]');
            if (!fs) return;
            var st = rowState(i);
            fs.querySelectorAll('[data-rate-btn]').forEach(function (b) {
                var on = st.rating != null && !st.no && !st.na && String(st.rating) === b.getAttribute('data-value');
                b.classList.toggle('active', on);
            });
            var noB = fs.querySelector('[data-rate-no]');
            if (noB) noB.classList.toggle('active', st.no);
            var naB = fs.querySelector('[data-rate-na]');
            if (naB) naB.classList.toggle('active', st.na);
            fs.classList.toggle('rated', st.rating != null || st.no || st.na);
        }
        function pickRating(i, v) {
            var hid = root.querySelector('input[data-field="rating"][data-idx="' + i + '"]');
            var noBox = root.querySelector('input[data-field="not_observed"][data-idx="' + i + '"]');
            var naBox = root.querySelector('input[data-field="not_applicable"][data-idx="' + i + '"]');
            if (!hid) return;
            hid.value = v;
            if (noBox) noBox.checked = false;
            if (naBox) naBox.checked = false;
            paintRow(i); recompute();
        }
        function pickNo(i) {
            var hid = root.querySelector('input[data-field="rating"][data-idx="' + i + '"]');
            var noBox = root.querySelector('input[data-field="not_observed"][data-idx="' + i + '"]');
            var naBox = root.querySelector('input[data-field="not_applicable"][data-idx="' + i + '"]');
            if (!noBox) return;
            if (hid) hid.value = '';
            noBox.checked = true;
            if (naBox) naBox.checked = false;
            paintRow(i); recompute();
        }
        function pickNa(i) {
            var hid = root.querySelector('input[data-field="rating"][data-idx="' + i + '"]');
            var noBox = root.querySelector('input[data-field="not_observed"][data-idx="' + i + '"]');
            var naBox = root.querySelector('input[data-field="not_applicable"][data-idx="' + i + '"]');
            if (!naBox) return;
            if (hid) hid.value = '';
            if (noBox) noBox.checked = false;
            naBox.checked = true;
            paintRow(i); recompute();
        }
        if (!root.dataset.pkgRatesWired) {
            root.dataset.pkgRatesWired = '1';
            root.addEventListener('click', function (e) {
                var t = e.target.closest('[data-rate-btn],[data-rate-no],[data-rate-na],[data-rate-comment],[data-mark-all-no]');
                if (!t || !root.contains(t)) return;
                if (t.hasAttribute('data-mark-all-no')) {
                    if (!confirm('Mark all indicators as Not Observed (NO)?')) return;
                    for (var k = 0; k < indicators.length; k++) pickNo(k);
                    return;
                }
                var idx = parseInt(t.getAttribute('data-idx'), 10);
                if (isNaN(idx)) return;
                if (t.hasAttribute('data-rate-btn')) pickRating(idx, t.getAttribute('data-value'));
                else if (t.hasAttribute('data-rate-no')) pickNo(idx);
                else if (t.hasAttribute('data-rate-na')) pickNa(idx);
                else if (t.hasAttribute('data-rate-comment')) {
                    var row = root.querySelector('[data-comment-row="' + idx + '"]');
                    if (row) {
                        row.hidden = !row.hidden;
                        t.classList.toggle('has-comment', !row.hidden);
                        if (!row.hidden) {
                            var ta = row.querySelector('textarea');
                            if (ta) ta.focus();
                        }
                    }
                }
            });
            // Comment presence styling on the toggle button.
            root.addEventListener('input', function (e) {
                var ta = e.target.closest ? e.target.closest('textarea[data-field="comments"]') : null;
                if (!ta) return;
                var btn = root.querySelector('[data-rate-comment][data-idx="' + ta.getAttribute('data-idx') + '"]');
                if (btn) {
                    var has = ta.value.trim().length > 0;
                    btn.classList.toggle('has-comment', has);
                    btn.textContent = has ? 'View Comment' : 'Comment';
                }
            });
        }
        recompute();

        var saveBtn = root.querySelector('[data-action="save-offline"]');
        if (saveBtn) saveBtn.addEventListener('click', function () {
            collectForm(root, pkg).then(function (form) {
                return saveObservationOffline(pkg.server_id, form);
            }).then(function () {
                var st = root.querySelector('[data-role="save-state"]');
                if (st) st.textContent = 'Pending Sync (Saved Locally)';
                toast('success', 'Saved locally. It will sync when you are back online.');
            }).catch(function (e) { toast('error', e.message || 'Save failed.'); });
        });
    }

    function collectForm(root, pkg) {
        var indicators = ((pkg || {}).rubric || {}).indicators || [];
        var ratings = [];
        var valid = true;
        indicators.forEach(function (ind, i) {
            var hid = root.querySelector('input[data-field="rating"][data-idx="' + i + '"]');
            var box = root.querySelector('input[data-field="not_observed"][data-idx="' + i + '"]');
            var naBox = root.querySelector('input[data-field="not_applicable"][data-idx="' + i + '"]');
            var com = root.querySelector('textarea[data-field="comments"][data-idx="' + i + '"]');
            var notObs = !!(box && box.checked);
            var notNa = !!(naBox && naBox.checked);
            var val = hid && hid.value !== '' ? parseInt(hid.value, 10) : null;
            if (!notObs && !notNa && val == null) return; // untouched row: skip silently
            ratings.push({
                client_id: uuid(),
                indicator_code: ind.code,
                domain: ind.domain,
                indicator: ind.description,
                rating: (notObs || notNa) ? null : val,
                not_observed: notObs,
                not_applicable: notNa,
                comments: com ? com.value : null,
            });
        });
        if (!ratings.length) valid = false;
        return Promise.resolve().then(function () {
            if (!valid) throw new Error('Rate at least one indicator (or mark NO / N/A) before saving.');
            var notes = root.querySelector('textarea[data-field="notes"]');
            var star = root.querySelector('textarea[data-field="star_notes"]');
            return {
                ratings: ratings,
                notes: notes ? notes.value : null,
                star_notes: star ? star.value : null,
            };
        });
    }

    /* ---------- 3) outbox save + sync push ---------- */
    function saveObservationOffline(serverId, form) {
        var record = {
            client_id: uuid(),
            observation_server_id: serverId,
            device_updated_at: new Date().toISOString(),
            status: 'dirty',
            payload: {
                notes: form.notes || null,
                star_notes: form.star_notes || null,
                ratings: form.ratings,
            },
        };
        var ratingRows = form.ratings.map(function (r) {
            return {
                client_id: r.client_id,
                observation_client_id: record.client_id,
                observation_server_id: serverId,
                indicator_code: r.indicator_code,
                data: r,
            };
        });
        // Write ratings first so a crash mid-save never leaves an observation
        // row pointing at missing rating rows.
        var chain = Promise.resolve();
        ratingRows.forEach(function (row) {
            chain = chain.then(function () { return put(RAT_STORE, row); });
        });
        return chain.then(function () { return put(OBS_STORE, record); })
            .then(function () { updateSyncBadge(); return record; });
    }

    function pendingCount() {
        return all(OBS_STORE).then(function (items) {
            return items.filter(function (i) { return i.status === 'dirty'; }).length;
        });
    }

    var syncing = false;

    function syncNow() {
        if (syncing) return Promise.resolve({ skipped: true });
        if (!navigator.onLine) {
            toast('warning', 'You are offline. Changes stay saved locally.');
            return Promise.resolve({ skipped: true });
        }
        return all(OBS_STORE).then(function (items) {
            var dirty = items.filter(function (i) { return i.status === 'dirty'; }).slice(0, 10);
            if (!dirty.length) { toast('info', 'Nothing to sync.'); return { synced: [] }; }
            syncing = true;
            return fetch(base() + '/sync/push', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf() },
                body: JSON.stringify({
                    device_id: 'tablet-' + (navigator.userAgent || '').slice(0, 40),
                    items: dirty.map(function (i) {
                        return {
                            client_id: i.client_id,
                            device_updated_at: i.device_updated_at,
                            server_id: i.observation_server_id,
                            payload: i.payload,
                        };
                    }),
                }),
            }).then(function (r) {
                // 419 = session expired while offline: re-login online, the
                // outbox is untouched so nothing is lost.
                if (r.status === 419) throw new Error('Session expired. Please log in again while online — your saved work is intact.');
                return r.json().catch(function () { return {}; }).then(function (json) {
                    if (!r.ok && r.status !== 207) throw new Error((json && json.message) || ('sync failed: ' + r.status));
                    return json;
                });
            }).then(function (json) {
                var syncedIds = ((json && json.synced) || []).map(function (s) { return s.client_id; });
                var conflicts = (json && json.conflicts) || [];
                var chain = Promise.resolve();
                // Mark failures/conflicts on the record so the UI can explain
                // them instead of silently retrying forever.
                (syncedIds).forEach(function (id) { chain = chain.then(function () { return del(OBS_STORE, id); }); });
                conflicts.forEach(function (c) {
                    chain = chain.then(function () {
                        return get(OBS_STORE, c.client_id).then(function (rec) {
                            if (!rec) return null;
                            rec.status = 'conflict';
                            rec.conflict = { reason: c.reason, message: c.message };
                            return put(OBS_STORE, rec);
                        });
                    });
                });
                return chain.then(function () {
                    syncing = false;
                    updateSyncBadge();
                    if (syncedIds.length) {
                        var first = (json.synced || [])[0] || {};
                        toast('success', 'Synced ' + syncedIds.length + ' observation(s).'
                            + (first.descriptive ? ' Summary: ' + first.descriptive + '.' : ''));
                    } else if (conflicts.length) {
                        toast('warning', conflicts.length + ' item(s) need review: ' + (conflicts[0].message || conflicts[0].reason));
                    }
                    document.dispatchEvent(new CustomEvent('offline-package:synced', { detail: json }));
                    return json;
                });
            }).catch(function (err) {
                syncing = false;
                toast('error', err.message || 'Sync failed. Items stay saved locally.');
                document.dispatchEvent(new CustomEvent('offline-package:sync-error', { detail: { error: err } }));
                throw err;
            });
        });
    }

    /* ---------- connectivity badge ---------- */
    function updateSyncBadge() {
        var badge = document.getElementById('offline-package-badge');
        pendingCount().then(function (n) {
            if (!badge) return;
            if (!navigator.onLine) {
                badge.textContent = n ? ('Offline · ' + n + ' pending sync') : 'Offline';
                badge.dataset.state = 'offline';
            } else if (n) {
                badge.textContent = 'Pending Sync (Saved Locally · ' + n + ')';
                badge.dataset.state = 'pending';
            } else {
                badge.textContent = 'Synced';
                badge.dataset.state = 'synced';
            }
        }).catch(function () {});
    }

    window.addEventListener('online', function () { updateSyncBadge(); syncNow().catch(function () {}); });
    window.addEventListener('offline', updateSyncBadge);
    document.addEventListener('DOMContentLoaded', updateSyncBadge);

    window.AspireOfflinePackage = {
        downloadObservationPackage: downloadObservationPackage,
        getPackage: getPackage,
        listPackages: listPackages,
        renderPackageInto: renderPackageInto,
        saveObservationOffline: saveObservationOffline,
        pendingCount: pendingCount,
        syncNow: syncNow,
        updateSyncBadge: updateSyncBadge,
        descriptive: descriptive,
    };
})();
