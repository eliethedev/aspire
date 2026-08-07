{{--
    Reusable debounced autosave wiring.
    Expects: $observation (the Observation model).
    Usage in a view: @include('partials.autosave', ['observation' => $observation])
    Then initialize from that view's script block:
        asAutoSave(document.querySelector('form[data-autosave-form]'), 'pre_conference', document.getElementById('autosave-status'));
    To trigger a save programmatically (e.g. after JS updates the DOM):
        asAutoSaveDebounced('pre_conference');   // debounced
        asAutoSaveNow('pre_conference');         // immediate
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
            var url = '{{ route("supervisor.observations.autosave", $observation) }}';
            var token = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

            function showStatus(text, isError) {
                if (!statusEl) return;
                statusEl.textContent = text;
                statusEl.classList.remove('text-red-600', 'dark:text-red-400');
                if (isError) {
                    statusEl.classList.add('text-red-600', 'dark:text-red-400');
                }
            }

            function save() {
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

                fetch(url, {
                    method: 'POST',
                    body: fd,
                    headers: { 'X-CSRF-TOKEN': token },
                })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data && data.ok) {
                        showStatus('Draft saved ' + (data.saved_at || ''), false);
                    } else {
                        showStatus('Draft could not be saved', true);
                    }
                })
                .catch(function () { showStatus('Offline - draft not saved', true); });
            }

            var debounced = asAutoSaveDebounce(save, wait);

            form.addEventListener('input', function (e) {
                if (e.target.type === 'file') return;
                debounced();
            });
            form.addEventListener('change', function (e) {
                if (e.target.type === 'file') return;
                debounced();
            });

            window.asAutoSaveRegistry[stage] = {
                save: save,
                debounced: debounced,
                statusEl: statusEl,
            };

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
