/* ASPIRE lightweight offline encoding workflow — vanilla JS, no dependencies.
 * Works alongside the full offline engine (js/aspire-offline.js).
 *
 * Stores (IndexedDB database "aspire_encode"):
 *  - offline_observations : pre-cached observation payloads from GET /sync/bootstrap
 *  - outbox               : locally-saved ratings/notes/comments waiting for POST /sync/push
 */
(function () {
    'use strict';

    var DB_NAME = 'aspire_encode';
    var DB_VERSION = 1;
    var OBS_STORE = 'offline_observations';
    var OUTBOX_STORE = 'outbox';

    function uuid() {
        if (window.crypto && crypto.randomUUID) return crypto.randomUUID();
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            var r = (Math.random() * 16) | 0;
            var v = c === 'x' ? r : (r & 0x3) | 0x8;
            return v.toString(16);
        });
    }

    function base() {
        return (typeof window !== 'undefined' && window.ASPIRE_BASE_URL) || '';
    }

    function csrf() {
        var el = document.querySelector('meta[name="csrf-token"]');
        return el ? el.getAttribute('content') : '';
    }

    function toast(type, message) {
        if (typeof window.showToast === 'function') { window.showToast(type, message); return; }
        var host = document.getElementById('offline-encode-toast');
        if (!host) {
            host = document.createElement('div');
            host.id = 'offline-encode-toast';
            host.setAttribute('role', 'status');
            host.style.cssText = 'position:fixed;bottom:1rem;left:50%;transform:translateX(-50%);z-index:9999;display:flex;flex-direction:column;gap:.5rem;align-items:center;';
            document.body.appendChild(host);
        }
        var el = document.createElement('div');
        el.textContent = message;
        el.style.cssText = 'padding:.6rem 1rem;border-radius:.6rem;font-size:.85rem;font-weight:600;color:#fff;background:'
            + (type === 'success' ? '#059669' : type === 'error' ? '#dc2626' : type === 'warning' ? '#d97706' : '#4f46e5');
        host.appendChild(el);
        setTimeout(function () { el.remove(); }, 4000);
    }

    function openDb() {
        return new Promise(function (resolve, reject) {
            if (!('indexedDB' in window)) { reject(new Error('IndexedDB unavailable')); return; }
            var req = indexedDB.open(DB_NAME, DB_VERSION);
            req.onupgradeneeded = function () {
                var db = req.result;
                if (!db.objectStoreNames.contains(OBS_STORE)) {
                    db.createObjectStore(OBS_STORE, { keyPath: 'key' });
                }
                if (!db.objectStoreNames.contains(OUTBOX_STORE)) {
                    db.createObjectStore(OUTBOX_STORE, { keyPath: 'client_id' });
                }
            };
            req.onsuccess = function () { resolve(req.result); };
            req.onerror = function () { reject(req.error); };
        });
    }

    function put(store, value) {
        return openDb().then(function (db) {
            return new Promise(function (resolve, reject) {
                var t = db.transaction(store, 'readwrite');
                t.objectStore(store).put(value);
                t.oncomplete = function () { resolve(value); };
                t.onerror = function () { reject(t.error); };
            });
        });
    }

    function get(store, key) {
        return openDb().then(function (db) {
            return new Promise(function (resolve) {
                var t = db.transaction(store, 'readonly');
                var req = t.objectStore(store).get(key);
                req.onsuccess = function () { resolve(req.result || null); };
                req.onerror = function () { resolve(null); };
            });
        });
    }

    function getAll(store) {
        return openDb().then(function (db) {
            return new Promise(function (resolve, reject) {
                var items = [];
                var t = db.transaction(store, 'readonly');
                t.objectStore(store).openCursor().onsuccess = function (e) {
                    var c = e.target.result;
                    if (c) { items.push(c.value); c.continue(); }
                    else { resolve(items); }
                };
                t.onerror = function () { reject(t.error); };
            });
        });
    }

    function del(store, key) {
        return openDb().then(function (db) {
            return new Promise(function (resolve, reject) {
                var t = db.transaction(store, 'readwrite');
                t.objectStore(store).delete(key);
                t.oncomplete = function () { resolve(true); };
                t.onerror = function () { reject(t.error); };
            });
        });
    }

    // 1) Preparation (online): fetch payload, save to offline_observations, register SW.
    function registerWorker() {
        if (!('serviceWorker' in navigator)) return Promise.resolve(false);
        var host = window.location.hostname;
        var ok = window.location.protocol === 'https:' || host === 'localhost' || host === '127.0.0.1';
        if (!ok) return Promise.resolve(false); // SW unsupported on plain LAN http — IndexedDB still works.
        return navigator.serviceWorker.register(base() + '/sw.js').then(function () { return true; }).catch(function () { return false; });
    }

    function prepareOffline(observationKey) {
        if (!navigator.onLine) {
            toast('warning', 'You are offline. Connect, then tap Prepare for Offline.');
            return Promise.reject(new Error('offline'));
        }
        return fetch(base() + '/sync/bootstrap', { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
            .then(function (r) {
                if (!r.ok) throw new Error('bootstrap failed: ' + r.status);
                return r.json();
            })
            .then(function (bundle) {
                var writes = [put(OBS_STORE, { key: 'bootstrap', value: bundle, saved_at: new Date().toISOString() })];
                // Also index each scheduled observation so the offline screen can load one directly.
                (bundle.scheduled || []).forEach(function (s) {
                    writes.push(put(OBS_STORE, { key: 'scheduled:' + s.server_id, value: s, saved_at: new Date().toISOString() }).catch(function () {}));
                });
                if (observationKey) {
                    writes.push(put(OBS_STORE, { key: String(observationKey), value: bundle, saved_at: new Date().toISOString() }).catch(function () {}));
                }
                return Promise.all(writes).then(function () { return bundle; });
            })
            .then(function (bundle) {
                return registerWorker().then(function () { return bundle; });
            })
            .then(function (bundle) {
                // Best-effort mirror into the legacy engine's cache store so the
                // capture page renders offline even when preparation happened
                // on a different page (show/evaluation). Never upgrades the DB.
                try {
                    var lr = indexedDB.open('aspire_offline');
                    lr.onsuccess = function () {
                        try {
                            var ldb = lr.result;
                            if (ldb.objectStoreNames.contains('cache')) {
                                var t = ldb.transaction('cache', 'readwrite');
                                t.objectStore('cache').put({ key: 'bootstrap', value: bundle, saved_at: new Date().toISOString() });
                            }
                            ldb.close();
                        } catch (e) { /* keep prepared bundle in our own store */ }
                    };
                } catch (e) { /* IndexedDB blocked — our own store already has it */ }
                updateBadge();
                var n = ((bundle && bundle.teachers) || []).length + ((bundle && bundle.school_heads) || []).length;
                toast('success', 'Ready for offline: ' + n + ' observee(s) cached on this device.');
                document.dispatchEvent(new CustomEvent('offline-encode:prepared', { detail: bundle }));
                return bundle;
            })
            .catch(function (err) {
                toast('error', 'Could not prepare offline data. Check your connection and try again.');
                throw err;
            });
    }

    // 2) Offline screen helpers: load cached payload, save drafts to outbox.
    function loadCachedObservation(key) {
        return get(OBS_STORE, key || 'bootstrap').then(function (rec) { return rec ? rec.value : null; });
    }

    function saveDraft(record) {
        var item = {
            client_id: record.client_id || uuid(),
            device_updated_at: new Date().toISOString(),
            status: 'dirty',
            payload: record.payload || record,
        };
        return put(OUTBOX_STORE, item).then(function () {
            updateBadge();
            document.dispatchEvent(new CustomEvent('offline-encode:saved', { detail: item }));
            return item;
        });
    }

    function listOutbox() { return getAll(OUTBOX_STORE); }

    function clearOutbox(clientIds) {
        var chain = Promise.resolve();
        (clientIds || []).forEach(function (id) {
            chain = chain.then(function () { return del(OUTBOX_STORE, id); });
        });
        return chain.then(function () { updateBadge(); });
    }

    // 3) Sync: POST outbox to /sync/push on reconnect or "Sync Now".
    var syncing = false;

    function syncNow() {
        if (syncing) return Promise.resolve({ skipped: true });
        if (!navigator.onLine) {
            toast('warning', 'You are offline. Changes stay saved locally and will sync when you reconnect.');
            return Promise.resolve({ skipped: true });
        }
        return listOutbox().then(function (items) {
            var dirty = items.filter(function (i) { return i.status === 'dirty'; }).slice(0, 10);
            if (!dirty.length) { toast('info', 'Nothing to sync — everything is up to date.'); return { synced: [] }; }
            syncing = true;
            return fetch(base() + '/sync/push', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf() },
                body: JSON.stringify({
                    device_id: 'web-' + (navigator.userAgent || '').slice(0, 40),
                    items: dirty.map(function (i) {
                        return { client_id: i.client_id, device_updated_at: i.device_updated_at, payload: i.payload };
                    }),
                }),
            }).then(function (r) {
                // Server returns 207 multi-status; also accept plain 200/201.
                return r.json().catch(function () { return {}; }).then(function (json) {
                    if (!r.ok && r.status !== 207) throw new Error((json && json.message) || ('sync failed: ' + r.status));
                    return json;
                });
            }).then(function (json) {
                var syncedIds = ((json && json.synced) || []).map(function (s) { return s.client_id; });
                // Legacy/alternate shape: { success: true, synced_ids: [...] }.
                if (!syncedIds.length && json && json.success && json.synced_ids) syncedIds = json.synced_ids;
                if (!syncedIds.length && json && json.success && dirty.length && (json.synced || json.status === 'ok')) {
                    syncedIds = dirty.map(function (i) { return i.client_id; });
                }
                return clearOutbox(syncedIds).then(function () {
                    syncing = false;
                    // Best effort: also drain the legacy engine's outbox so one
                    // "Sync Now" tap clears everything waiting on this device.
                    if (window.AspireOffline && window.AspireOffline.syncAll) {
                        window.AspireOffline.syncAll().catch(function () {});
                    }
                    if (syncedIds.length) {
                        toast('success', 'Synced ' + syncedIds.length + ' observation(s).');
                    } else {
                        toast('info', 'Server reviewed the queue — nothing was marked synced yet. See details on screen.');
                    }
                    document.dispatchEvent(new CustomEvent('offline-encode:synced', { detail: json }));
                    return json;
                });
            }).catch(function (err) {
                syncing = false;
                toast('error', 'Sync failed (connection lost?). Items stay saved locally.');
                document.dispatchEvent(new CustomEvent('offline-encode:sync-error', { detail: { error: err } }));
                throw err;
            });
        });
    }

    // Visible status badge: "Offline Mode (Saved Locally)".
    function updateBadge() {
        var badge = document.getElementById('offline-mode-badge');
        if (!badge) return;
        listOutbox().then(function (items) {
            var pending = items.filter(function (i) { return i.status === 'dirty'; }).length;
            var show = !navigator.onLine || pending > 0;
            badge.classList.toggle('hidden', !show);
            var label = badge.querySelector('[data-badge-label]');
            if (label) {
                label.textContent = !navigator.onLine
                    ? 'Offline Mode (Saved Locally)' + (pending ? ' · ' + pending + ' waiting' : '')
                    : 'Offline Mode (Saved Locally) · ' + pending + ' waiting to sync';
            }
        }).catch(function () {});
    }

    window.addEventListener('online', function () { updateBadge(); syncNow().catch(function () {}); });
    window.addEventListener('offline', updateBadge);

    // Wire standard buttons/inputs (progressive enhancement — safe with Alpine.js).
    function collectFormDraft(form) {
        var payload = { observation_type: 'teacher_observation' };
        form.querySelectorAll('input, select, textarea').forEach(function (el) {
            if (!el.name && !el.id) return;
            var k = el.name || el.id;
            if (el.type === 'checkbox' || el.type === 'radio') { if (el.checked) payload[k] = el.value; return; }
            payload[k] = el.value;
        });
        // Structured ratings/notes/comments when present.
        var ratings = [];
        form.querySelectorAll('[data-rating-code]').forEach(function (el) {
            ratings.push({ indicator_code: el.getAttribute('data-rating-code'), rating: el.value ? parseInt(el.value, 10) : null });
        });
        if (ratings.length) payload.ratings = ratings;
        var notes = form.querySelector('[data-offline-notes], textarea[name="notes"], #of-notes');
        if (notes) payload.notes = notes.value;
        return payload;
    }

    document.addEventListener('click', function (e) {
        var prep = e.target.closest ? e.target.closest('[data-prepare-offline]') : null;
        if (prep) {
            e.preventDefault();
            prep.disabled = true;
            prepareOffline(prep.getAttribute('data-observation-id') || null).finally(function () { prep.disabled = false; });
            return;
        }
        var syncBtn = e.target.closest ? e.target.closest('[data-sync-now]') : null;
        if (syncBtn) { e.preventDefault(); syncNow().catch(function () {}); }
        var saveBtn = e.target.closest ? e.target.closest('[data-save-observation]') : null;
        if (saveBtn) {
            var form = saveBtn.closest('form') || document.querySelector('[data-offline-form]');
            if (form) {
                var cid = form.getAttribute('data-client-id') || uuid();
                form.setAttribute('data-client-id', cid);
                e.preventDefault();
                saveDraft({ client_id: cid, payload: collectFormDraft(form) })
                    .then(function () { toast('success', 'Saved locally. Will sync when you are back online.'); })
                    .catch(function () { toast('error', 'Could not save locally (browser storage blocked?).'); });
            }
        }
    });

    // Save drafts locally on form change (ratings, notes, comments).
    // The client_id is pinned on the form so repeated edits overwrite one
    // outbox record instead of queueing duplicates.
    var draftTimer = null;
    document.addEventListener('change', function (e) {
        var form = e.target.closest ? e.target.closest('[data-offline-form]') : null;
        if (!form) return;
        var cid = form.getAttribute('data-client-id');
        if (!cid) { cid = uuid(); form.setAttribute('data-client-id', cid); }
        clearTimeout(draftTimer);
        draftTimer = setTimeout(function () {
            saveDraft({ client_id: cid, payload: collectFormDraft(form) }).catch(function () {});
        }, 400);
    });
    document.addEventListener('DOMContentLoaded', updateBadge);
    if (document.readyState !== 'loading') updateBadge();

    window.OfflineEncode = {
        prepareOffline: prepareOffline,
        loadCachedObservation: loadCachedObservation,
        saveDraft: saveDraft,
        listOutbox: listOutbox,
        clearOutbox: clearOutbox,
        syncNow: syncNow,
        updateBadge: updateBadge,
        stores: { observations: OBS_STORE, outbox: OUTBOX_STORE },
    };
})();
