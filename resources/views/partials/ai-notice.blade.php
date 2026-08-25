{{--
    Shared "AI isn't available" banner helper.
    Usage (inside a @push('scripts') block):
        @include('partials.ai-notice')
    Then from page JS:
        AINotice.show('slot-element-id', data, {
            onManual: function () { ... },   // renders the "Write it myself" button
            onRetry:  fetchAgain,            // optional retry button
            manualLabel: 'Write it myself',  // optional override
        });
    data comes from the server: { error, reason, retry_after?, manual_available }
--}}
<script>
window.AINotice = (function () {
    function resolveTarget(target) {
        return typeof target === 'string' ? document.getElementById(target) : target;
    }

    function build(message, options) {
        var wrap = document.createElement('div');
        wrap.className = 'ai-unavailable-banner rounded-xl border border-amber-300 bg-amber-50 dark:bg-amber-900/20 dark:border-amber-700 p-4 flex items-start gap-3';
        wrap.setAttribute('role', 'alert');

        var icon = document.createElement('div');
        icon.className = 'w-9 h-9 rounded-full bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center shrink-0';
        icon.innerHTML = '<svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>';

        var body = document.createElement('div');
        body.className = 'flex-1 min-w-0';

        var title = document.createElement('p');
        title.className = 'text-sm font-semibold text-amber-800 dark:text-amber-200';
        title.textContent = 'AI isn\u2019t available right now';

        var text = document.createElement('p');
        text.className = 'text-xs text-amber-700 dark:text-amber-300 mt-0.5 leading-relaxed';
        text.textContent = message || 'The AI service could not be reached.';

        body.appendChild(title);
        body.appendChild(text);

        var actions = document.createElement('div');
        actions.className = 'flex flex-wrap items-center gap-2 mt-2.5';

        if (typeof options.onManual === 'function') {
            var manualBtn = document.createElement('button');
            manualBtn.type = 'button';
            manualBtn.className = 'px-3 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors inline-flex items-center gap-1.5';
            manualBtn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>';
            manualBtn.appendChild(document.createTextNode(options.manualLabel || 'Write it myself'));
            manualBtn.addEventListener('click', options.onManual);
            actions.appendChild(manualBtn);
        }

        if (typeof options.onRetry === 'function') {
            var retryBtn = document.createElement('button');
            retryBtn.type = 'button';
            retryBtn.className = 'px-3 py-1.5 text-xs font-semibold text-amber-800 dark:text-amber-200 bg-white dark:bg-gray-900 border border-amber-300 dark:border-amber-700 hover:bg-amber-100 dark:hover:bg-amber-900/30 rounded-lg transition-colors inline-flex items-center gap-1.5';
            retryBtn.innerHTML = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>';
            retryBtn.appendChild(document.createTextNode(options.retryLabel || 'Try again'));
            retryBtn.addEventListener('click', options.onRetry);
            actions.appendChild(retryBtn);
        }

        body.appendChild(actions);

        var close = document.createElement('button');
        close.type = 'button';
        close.setAttribute('aria-label', 'Dismiss');
        close.className = 'text-amber-500 hover:text-amber-700 dark:text-amber-400 transition-colors shrink-0';
        close.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
        close.addEventListener('click', function () { wrap.remove(); });

        wrap.appendChild(icon);
        wrap.appendChild(body);
        wrap.appendChild(close);

        return wrap;
    }

    return {
        /**
         * Render the banner inside the given slot.
         * target: slot element or its id
         * data: server payload (error, reason, retry_after, manual_available)
         * options: optional callbacks/settings (onManual, onRetry, manualLabel, retryLabel)
         */
        show: function (target, data, options) {
            var slot = resolveTarget(target);
            if (!slot) return null;
            slot.innerHTML = '';
            options = options || {};
            var banner = build((data && data.error) || '', options);
            slot.appendChild(banner);
            return banner;
        },
        /** Remove any banner inside the given slot. */
        hide: function (target) {
            var slot = resolveTarget(target);
            if (slot) slot.innerHTML = '';
        },
    };
})();
</script>
