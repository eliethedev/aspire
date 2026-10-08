{{--
    Reusable debounced autosave wiring.
    Expects: $observation (the Observation model).
    Optional: $autosaveUrl (defaults to the supervisor autosave endpoint —
    pass route('school-head.observations.autosave', $observation) on
    school-head pages).
    Usage in a view: @include('partials.autosave', ['observation' => $observation])
    Then initialize from that view's script block:
        asAutoSave(document.querySelector('form[data-autosave-form]'), 'observation', document.getElementById('autosave-status'));
    To trigger a save programmatically (e.g. after JS updates the DOM):
        asAutoSaveDebounced('observation');   // debounced
        asAutoSaveNow('observation');         // immediate
    Unsaved changes are also flushed via sendBeacon on pagehide, so a
    refresh/close right after picking a score never loses it.
--}}
<script>
    (function () {
        function asAutoSaveDebounce(fn, wait) {
            var t;
            return function () {
                var args = arguments;
                var ctx = this;
                clearTimeout(t);
                t = setTimeout(function () { fn.apply(ctx, args); }, wait);
            };
        }

        window.asAutoSaveRegistry = window.asAutoSaveRegistry || {};

        window.asAutoSave = function (form, stage, statusEl, opts) {
            if (!form || !form.elements) return null;
            opts = opts || {};
            var wait = opts.wait || 1500;
            var url = '{{ $autosaveUrl ?? route("supervisor.observations.autosave", $observation) }}';
            var token = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
            // True while there are changes the server has not confirmed yet.
            // A pagehide flush sends them via beacon so a refresh/close/tab
            // crash never loses the last picked scores.
            var dirty = false;

            function showStatus(text, isError) {
                if (!statusEl) return;
                statusEl.textContent = text;
                statusEl.classList.remove('text-red-600', 'dark:text-red-400');
                if (isError) {
                    statusEl.classList.add('text-red-600', 'dark:text-red-400');
                }
            }

            function buildFd() {
                var fd = new FormData(form);
                // Never re-upload files during background autosave.
                [].forEach.call(form.elements, function (el) {
                    if (el.type === 'file' && el.name) fd.delete(el.name);
                });
                // Drop the hidden autosave status input if present so it is not persisted.
                [].forEach.call(form.elements, function (el) {
                    if (el.dataset && el.dataset.autosaveStatus && el.name) fd.delete(el.name);
                });
                fd.append('stage', stage);
                fd.append('_token', token);
                return fd;
            }

            function save() {
                var fd = buildFd();

                fetch(url, {
                    method: 'POST',
                    body: fd,
                    headers: { 'X-CSRF-TOKEN': token },
                })
                .then(function (res) {
                    if (res.status === 419) {
                        showStatus('Session expired — log in again to keep auto-saving', true);
                        return null;
                    }
                    return res.json();
                })
                .then(function (data) {
                    if (data === null) return;
                    if (data && data.ok) {
                        dirty = false;
                        showStatus('Draft saved ' + (data.saved_at || ''), false);
                    } else {
                        showStatus('Draft could not be saved', true);
                    }
                })
                .catch(function () { showStatus('Offline - draft not saved', true); });
            }

            var debounced = asAutoSaveDebounce(save, wait);

            function markDirty() { dirty = true; }

            form.addEventListener('input', function (e) {
                if (e.target.type === 'file') return;
                markDirty();
                debounced();
            });
            form.addEventListener('change', function (e) {
                if (e.target.type === 'file') return;
                markDirty();
                debounced();
            });

            window.asAutoSaveRegistry[stage] = {
                save: save,
                debounced: function () { markDirty(); debounced(); },
                statusEl: statusEl,
                url: url,
                isDirty: function () { return dirty; },
                buildFormData: buildFd,
            };

            // Flush unsaved changes when the page is hidden/closed/refreshed.
            // sendBeacon survives page unload; the _token field in the body
            // satisfies CSRF and cookies ride along same-origin.
            if (!window.__asAutoSavePagehideHook) {
                window.__asAutoSavePagehideHook = true;
                window.addEventListener('pagehide', function () {
                    Object.keys(window.asAutoSaveRegistry).forEach(function (key) {
                        var entry = window.asAutoSaveRegistry[key];
                        if (!entry || !entry.isDirty || !entry.isDirty()) return;
                        try {
                            var fd = entry.buildFormData ? entry.buildFormData() : null;
                            if (fd && navigator.sendBeacon) navigator.sendBeacon(entry.url, fd);
                        } catch (e) { /* page is going away; nothing else to do */ }
                    });
                });
            }

            return save;
        };

        window.asAutoSaveNow = function (stage) {
            var entry = window.asAutoSaveRegistry[stage];
            if (entry) entry.save();
        };

        window.asAutoSaveDebounced = function (stage) {
            var entry = window.asAutoSaveRegistry[stage];
            if (entry) entry.debounced();
        };
    })();
</script>
