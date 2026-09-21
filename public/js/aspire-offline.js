/* ASPIRE offline outbox (Architecture B) — vanilla JS, no build step.
 * Tablet encodes with zero connectivity into IndexedDB, pushes JSON to
 * /sync/push later, then uploads queued evidence blobs to /sync/push-files.
 * Idempotency: observations dedupe by client_id, files by file_id.
 */
(function () {
    'use strict';

    var DB_NAME = 'aspire_offline';
    var DB_VERSION = 2;
    var OUTBOX = 'outbox';
    var CACHE = 'cache';
    var FILES = 'files';

    // Evidence guardrails (mirror server rules, tightened for device storage).
    var MAX_FILES_PER_OBSERVATION = 5;
    var MAX_FILE_BYTES = 5 * 1024 * 1024;
    var ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'mov', 'pdf', 'doc', 'docx'];

    function uuid() {
        if (window.crypto && crypto.randomUUID) return crypto.randomUUID();
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            var r = (Math.random() * 16) | 0;
            var v = c === 'x' ? r : (r & 0x3) | 0x8;
            return v.toString(16);
        });
    }

    function openDb() {
        return new Promise(function (resolve, reject) {
            var req = indexedDB.open(DB_NAME, DB_VERSION);
            req.onupgradeneeded = function () {
                var db = req.result;
                if (!db.objectStoreNames.contains(OUTBOX)) {
                    db.createObjectStore(OUTBOX, { keyPath: 'client_id' });
                }
                if (!db.objectStoreNames.contains(CACHE)) {
                    db.createObjectStore(CACHE, { keyPath: 'key' });
                }
                if (!db.objectStoreNames.contains(FILES)) {
                    db.createObjectStore(FILES, { keyPath: 'id' });
                }
            };
            req.onsuccess = function () { resolve(req.result); };
            req.onerror = function () { reject(req.error); };
        });
    }

    function tx(store, mode, fn) {
        return openDb().then(function (db) {
            return new Promise(function (resolve, reject) {
                var t = db.transaction(store, mode);
                var s = t.objectStore(store);
                var out;
                try { out = fn(s); } catch (e) { reject(e); return; }
                t.oncomplete = function () { resolve(out && out.result !== undefined ? out.result : out); };
                t.onerror = function () { reject(t.error); };
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

    function csrf() {
        var el = document.querySelector('meta[name="csrf-token"]');
        return el ? el.getAttribute('content') : '';
    }

    // Guards overlapping pushes (auto-push on 'online' + manual Sync button).
    var pushing = false;

    // Server-provided app base ('' on `artisan serve`, '/ASPIRE-Prototype/public'
    // on XAMPP). Never hardcode the host: tablets reach the server via LAN IP.
    function base() {
        return (typeof window !== 'undefined' && window.ASPIRE_BASE_URL) || '';
    }

    function timeAgo(iso) {
        if (!iso) return '';
        var mins = Math.max(0, Math.round((Date.now() - new Date(iso).getTime()) / 60000));
        if (mins < 1) return 'just now';
        if (mins < 60) return mins + 'm ago';
        var hours = Math.floor(mins / 60);
        if (hours < 24) return hours + 'h ago';
        return Math.floor(hours / 24) + 'd ago';
    }

    var api = {
        uuid: uuid,

        pendingCount: function () {
            return getAll(OUTBOX).then(function (items) {
                return items.filter(function (i) { return i.status === 'dirty'; }).length;
            });
        },

        listPending: function () {
            return getAll(OUTBOX).then(function (items) {
                return items.filter(function (i) { return i.status === 'dirty'; });
            });
        },

        saveObservation: function (payload) {
            var item = {
                client_id: uuid(),
                device_updated_at: new Date().toISOString(),
                status: 'dirty',
                payload: payload,
            };
            return tx(OUTBOX, 'readwrite', function (s) { return s.put(item); }).then(function () { return item; });
        },

        removeSynced: function (clientIds) {
            return openDb().then(function (db) {
                return new Promise(function (resolve, reject) {
                    var t = db.transaction(OUTBOX, 'readwrite');
                    var s = t.objectStore(OUTBOX);
                    clientIds.forEach(function (id) { s.delete(id); });
                    t.oncomplete = function () { resolve(true); };
                    t.onerror = function () { reject(t.error); };
                });
            });
        },

        cacheBootstrap: function () {
            return fetch(base() + '/sync/bootstrap', { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
                .then(function (r) {
                    if (!r.ok) {
                        var err = new Error('bootstrap failed: ' + r.status);
                        err.status = r.status;
                        throw err;
                    }
                    return r.json().catch(function () {
                        var err = new Error('bootstrap returned an unexpected response (login expired?)');
                        err.status = 401;
                        throw err;
                    });
                })
                .then(function (json) {
                    return tx(CACHE, 'readwrite', function (s) {
                        return s.put({ key: 'bootstrap', value: json, saved_at: new Date().toISOString() });
                    }).then(function () { return json; });
                });
        },

        getCachedBootstrap: function () {
            return api.getCacheInfo().then(function (info) { return info ? info.value : null; });
        },

        // Persist an already-fetched bundle (e.g. server-rendered on page load).
        saveBootstrap: function (json) {
            return tx(CACHE, 'readwrite', function (s) {
                return s.put({ key: 'bootstrap', value: json, saved_at: new Date().toISOString() });
            }).then(function () { return json; });
        },

        // Full cache record: { value, saved_at }. Used for "cached 3h ago" hints.
        getCacheInfo: function () {
            return openDb().then(function (db) {
                return new Promise(function (resolve) {
                    var t = db.transaction(CACHE, 'readonly');
                    var req = t.objectStore(CACHE).get('bootstrap');
                    req.onsuccess = function () { resolve(req.result || null); };
                    req.onerror = function () { resolve(null); };
                });
            });
        },

        // Discard one queued observation (e.g. entered by mistake). Cannot be undone.
        deletePending: function (clientId) {
            return openDb().then(function (db) {
                return new Promise(function (resolve, reject) {
                    var t = db.transaction(OUTBOX, 'readwrite');
                    t.objectStore(OUTBOX).delete(clientId);
                    t.oncomplete = function () { resolve(true); };
                    t.onerror = function () { reject(t.error); };
                });
            });
        },

        isPushing: function () { return pushing; },

        limits: function () {
            return {
                maxFiles: MAX_FILES_PER_OBSERVATION,
                maxBytes: MAX_FILE_BYTES,
                extensions: ALLOWED_EXTENSIONS.slice(),
            };
        },

        // Shared file validation (mirrors server rules). Returns
        // { valid: [File...], rejected: [{ name, reason }] }.
        validateFiles: function (fileList, alreadyAttached) {
            var valid = [];
            var rejected = [];
            var files = [];
            for (var i = 0; i < (fileList || []).length; i++) files.push(fileList[i]);
            if (files.length + (alreadyAttached || 0) > MAX_FILES_PER_OBSERVATION) {
                files.forEach(function (f) {
                    rejected.push({ name: f.name, reason: 'Too many files (max ' + MAX_FILES_PER_OBSERVATION + ' per observation)' });
                });
                return { valid: valid, rejected: rejected };
            }
            files.forEach(function (f) {
                var ext = (f.name || '').split('.').pop().toLowerCase();
                if (ALLOWED_EXTENSIONS.indexOf(ext) === -1) {
                    rejected.push({ name: f.name, reason: 'File type not allowed (photos, video, PDF, Word only)' });
                } else if (f.size > MAX_FILE_BYTES) {
                    rejected.push({ name: f.name, reason: 'Too large (max 5 MB per file)' });
                } else if (!f.size) {
                    rejected.push({ name: f.name, reason: 'Empty file' });
                } else {
                    valid.push(f);
                }
            });
            return { valid: valid, rejected: rejected };
        },

        // Queue blobs for one outbox observation. Blobs live in IndexedDB
        // until their observation has synced and each file has uploaded.
        saveFiles: function (observationClientId, files) {
            var records = files.map(function (f) {
                return {
                    id: uuid(),
                    observation_client_id: observationClientId,
                    name: f.name,
                    type: f.type || '',
                    size: f.size || 0,
                    blob: f,
                    status: 'dirty',
                    error: null,
                    created_at: new Date().toISOString(),
                };
            });
            return openDb().then(function (db) {
                return new Promise(function (resolve, reject) {
                    var t = db.transaction(FILES, 'readwrite');
                    var s = t.objectStore(FILES);
                    records.forEach(function (r) { s.put(r); });
                    t.oncomplete = function () {
                        resolve(records.map(function (r) {
                            return { file_id: r.id, name: r.name, type: r.type, size: r.size };
                        }));
                    };
                    t.onerror = function () { reject(t.error); };
                });
            });
        },

        // All queued files (dirty + permanently errored), for counts/badges.
        listPendingFiles: function () {
            return getAll(FILES);
        },

        pendingFilesCount: function () {
            return getAll(FILES).then(function (files) {
                return files.filter(function (f) { return f.status !== 'done'; }).length;
            });
        },

        deletePendingFile: function (fileId) {
            return openDb().then(function (db) {
                return new Promise(function (resolve, reject) {
                    var t = db.transaction(FILES, 'readwrite');
                    t.objectStore(FILES).delete(fileId);
                    t.oncomplete = function () { resolve(true); };
                    t.onerror = function () { reject(t.error); };
                });
            });
        },

        // Discard an outbox observation AND its queued files. Cannot be undone.
        deleteFilesForObservation: function (observationClientId) {
            return api.listPendingFiles().then(function (files) {
                var ids = files
                    .filter(function (f) { return f.observation_client_id === observationClientId; })
                    .map(function (f) { return f.id; });
                var chain = Promise.resolve();
                ids.forEach(function (id) {
                    chain = chain.then(function () { return api.deletePendingFile(id); });
                });
                return chain;
            });
        },

        // Upload dirty blobs for the given (already-synced) observations.
        // Success → blob deleted locally. Validation reject → marked 'error'
        // (kept visible for review, never retried). Anything else → stays
        // dirty for the next sync.
        uploadFilesFor: function (clientIds) {
            var wanted = {};
            (clientIds || []).forEach(function (id) { wanted[id] = true; });
            return api.listPendingFiles().then(function (files) {
                var queue = files.filter(function (f) {
                    return f.status === 'dirty' && wanted[f.observation_client_id];
                });
                var out = { uploaded: [], failed: [], errored: [] };
                var chain = Promise.resolve();
                queue.forEach(function (rec) {
                    chain = chain.then(function () {
                        var fd = new FormData();
                        fd.append('observation_client_id', rec.observation_client_id);
                        fd.append('file_id', rec.id);
                        fd.append('file', rec.blob, rec.name);
                        return fetch(base() + '/sync/push-files', {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf() },
                            body: fd,
                        }).then(function (r) {
                            return r.json().catch(function () { return {}; }).then(function (json) {
                                if (r.status === 201 || (r.ok && (json.status === 'uploaded' || json.status === 'already_uploaded'))) {
                                    return api.deletePendingFile(rec.id).then(function () {
                                        out.uploaded.push(rec.id);
                                    });
                                }
                                if (r.status === 422) {
                                    var msg = json.message || 'Rejected by server';
                                    if (json.errors) {
                                        msg = Object.keys(json.errors).map(function (k) {
                                            return json.errors[k].join(' ');
                                        }).join(' ');
                                    }
                                    return tx(FILES, 'readwrite', function (s) {
                                        return s.put({
                                            id: rec.id,
                                            observation_client_id: rec.observation_client_id,
                                            name: rec.name, type: rec.type, size: rec.size,
                                            blob: rec.blob, status: 'error', error: msg,
                                            created_at: rec.created_at,
                                        });
                                    }).then(function () {
                                        out.errored.push({ file_id: rec.id, name: rec.name, reason: msg });
                                    });
                                }
                                var reason = (r.status === 401 || r.status === 419)
                                    ? 'Session expired — reload and log in, then sync again'
                                    : ((json && json.message) || ('HTTP ' + r.status));
                                out.failed.push({ file_id: rec.id, name: rec.name, reason: reason });
                            });
                        }).catch(function () {
                            out.failed.push({ file_id: rec.id, name: rec.name, reason: 'Network error — will retry' });
                        });
                    });
                });
                return chain.then(function () { return out; });
            });
        },

        // JSON push without events/cleanup (internal core).
        pushJson: function () {
            return api.listPending().then(function (items) {
                if (!items.length) return { synced: [], conflicts: [], errors: [] };
                var batch = items.slice(0, 10);
                return fetch(base() + '/sync/push', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrf(),
                    },
                    body: JSON.stringify({
                        device_id: 'tablet-' + (navigator.userAgent || '').slice(0, 40),
                        items: batch.map(function (i) {
                            return { client_id: i.client_id, device_updated_at: i.device_updated_at, payload: i.payload };
                        }),
                    }),
                }).then(function (r) {
                    return r.json().then(function (json) {
                        if (!r.ok && r.status !== 207) throw new Error(json.message || ('push failed: ' + r.status));
                        return json;
                    });
                });
            });
        },

        // Legacy JSON-only push (kept for compatibility; prefer syncAll()).
        pushPending: function () {
            if (pushing) return Promise.resolve({ synced: [], conflicts: [], errors: [], skipped: true });
            pushing = true;
            document.dispatchEvent(new CustomEvent('aspire:sync-start'));
            return api.pushJson().then(function (json) {
                var done = (json.synced || []).map(function (s) { return s.client_id; });
                return api.removeSynced(done).then(function () { return json; });
            }).then(function (res) {
                pushing = false;
                document.dispatchEvent(new CustomEvent('aspire:sync', { detail: res }));
                return res;
            }, function (err) {
                pushing = false;
                document.dispatchEvent(new CustomEvent('aspire:sync-error', { detail: { error: err } }));
                throw err;
            });
        },

        // Full sync: JSON push → file uploads → cleanup. An outbox item is
        // removed only when the server synced it AND none of its files are
        // still queued, so partially-uploaded work stays visible (and
        // discardable) instead of silently orphaning blobs.
        syncAll: function () {
            if (pushing) {
                return Promise.resolve({
                    synced: [], conflicts: [], errors: [],
                    filesUploaded: [], filesFailed: [], filesErrored: [], skipped: true,
                });
            }
            pushing = true;
            document.dispatchEvent(new CustomEvent('aspire:sync-start'));
            var combined = null;
            return api.pushJson().then(function (json) {
                combined = json;
                combined.filesUploaded = [];
                combined.filesFailed = [];
                combined.filesErrored = [];
                var ids = (json.synced || []).map(function (s) { return s.client_id; });
                if (!ids.length) return null;
                return api.uploadFilesFor(ids).then(function (fr) {
                    combined.filesUploaded = fr.uploaded;
                    combined.filesFailed = fr.failed;
                    combined.filesErrored = fr.errored;
                });
            }).then(function () {
                return api.listPendingFiles().then(function (files) {
                    var busy = {};
                    files.forEach(function (f) { busy[f.observation_client_id] = true; });
                    var done = ((combined && combined.synced) || [])
                        .map(function (s) { return s.client_id; })
                        .filter(function (id) { return !busy[id]; });
                    return api.removeSynced(done);
                });
            }).then(function () {
                pushing = false;
                document.dispatchEvent(new CustomEvent('aspire:sync', { detail: combined }));
                return combined;
            }, function (err) {
                pushing = false;
                document.dispatchEvent(new CustomEvent('aspire:sync-error', { detail: { error: err } }));
                throw err;
            });
        },

        timeAgo: timeAgo,
    };

    window.AspireOffline = api;

    // Auto-sync when signal returns (full flow: JSON + files).
    window.addEventListener('online', function () {
        api.syncAll().catch(function () { /* stay queued */ });
    });
})();
