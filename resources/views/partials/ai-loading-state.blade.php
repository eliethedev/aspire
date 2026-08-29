{{--
    Shared "AI is generating" loading panel.
    Usage (inside a @push('scripts') block):
        @include('partials.ai-loading-state')
    API:
        AiLoading.start(slotOrId, message, tagline)  // insert loading panel into container
        AiLoading.stop()                             // remove panel + stop timers
    The panel shows an inline spinner, a rotating set of friendly hints,
    an elapsed-seconds counter and an indeterminate progress bar so the user
    knows the request is still working while AI composes the response.
--}}
<script>
window.AiLoading = (function () {
    var timer = null;
    var startedAt = 0;
    var hints = [
        'Reviewing the submitted lesson plan',
        'Reading the teacher\u2019s previous observations',
        'Cross-referencing PPST indicators',
        'Drafting focus points and key things to watch',
        'Polishing the insights \u2014 almost done'
    ];

    function ensureStyles() {
        if (document.getElementById('ai-loading-styles')) return;
        var s = document.createElement('style');
        s.id = 'ai-loading-styles';
        s.textContent = '@keyframes ai-loading-slide { 0% { transform: translateX(-40%); } 100% { transform: translateX(260%); } }';
        document.head.appendChild(s);
    }

    function markup(message, tagline) {
        return '<div id="ai-loading-panel" role="status" aria-live="polite" class="bg-gradient-to-br from-purple-50 to-indigo-50 dark:from-purple-900/10 dark:to-indigo-900/10 rounded-xl p-5 border border-purple-200 dark:border-purple-900/40">' +
            '<div class="flex items-center gap-3">' +
                '<svg class="w-6 h-6 text-purple-500 animate-spin shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>' +
                '<div class="min-w-0">' +
                    '<p class="text-sm font-semibold text-purple-800 dark:text-purple-200">' + message + '</p>' +
                    '<p class="text-xs text-purple-600/80 dark:text-purple-300/80 mt-0.5" id="ai-loading-hint">' + tagline + '</p>' +
                '</div>' +
            '</div>' +
            '<div class="mt-4 flex items-center gap-2">' +
                '<span class="text-[11px] font-medium text-gray-500 dark:text-gray-400 tabular-nums" id="ai-loading-elapsed">0s elapsed</span>' +
                '<span class="flex gap-1 ml-auto">' +
                    '<span class="w-1.5 h-1.5 rounded-full bg-purple-400 animate-bounce"></span>' +
                    '<span class="w-1.5 h-1.5 rounded-full bg-purple-500 animate-bounce" style="animation-delay:.15s"></span>' +
                    '<span class="w-1.5 h-1.5 rounded-full bg-purple-600 animate-bounce" style="animation-delay:.3s"></span>' +
                '</span>' +
            '</div>' +
            '<div class="w-full h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full mt-2 overflow-hidden">' +
                '<div class="h-full w-2/5 rounded-full bg-gradient-to-r from-purple-500 to-indigo-500" style="animation: ai-loading-slide 1.4s ease-in-out infinite"></div>' +
            '</div>' +
        '</div>';
    }

    function elapsed() {
        return Math.max(0, Math.round((Date.now() - startedAt) / 1000));
    }

    function tick() {
        var el = document.getElementById('ai-loading-elapsed');
        if (el) el.textContent = elapsed() + 's elapsed';
        var hint = document.getElementById('ai-loading-hint');
        if (hint && startedAt) {
            var idx = Math.min(Math.floor(elapsed() / 12), hints.length - 1);
            hint.textContent = hints[idx];
        }
    }

    function resolveSlot(slot) {
        return typeof slot === 'string' ? document.getElementById(slot) : slot;
    }

    return {
        start: function (slot, message, tagline) {
            ensureStyles();
            var container = resolveSlot(slot);
            if (!container) return;
            this.stop();
            var wrap = document.createElement('div');
            wrap.innerHTML = markup(message || 'Generating AI insights', tagline || 'This usually takes less than a minute.').trim();
            container.appendChild(wrap.firstChild);
            startedAt = Date.now();
            timer = setInterval(tick, 1000);
            tick();
        },
        stop: function () {
            if (timer) clearInterval(timer);
            timer = null;
            startedAt = 0;
            var panel = document.getElementById('ai-loading-panel');
            if (panel) panel.remove();
        }
    };
})();
</script>