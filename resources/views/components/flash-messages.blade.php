@props(['duration' => 5000, 'maxVisible' => 4])

@php
    // Read every session flash message once, server-side, and hand them to
    // Alpine as initial toasts. Reads do not consume flash data, so any
    // existing inline flash rendering elsewhere keeps working.
    $flashMessages = collect(['success', 'error', 'warning', 'info', 'message'])
        ->mapWithKeys(fn (string $key) => [$key => session($key)])
        ->filter()
        ->map(function (mixed $value, string $key) {
            $type = $key === 'message' ? 'info' : $key;

            return ['type' => $type, 'message' => (string) $value];
        })
        ->values()
        ->all();
@endphp

<div
    x-data='flashToasts(@json($flashMessages), @json(["duration" => (int) $duration, "maxVisible" => (int) $maxVisible]))'
    class="fixed top-16 right-4 z-[60] flex w-[calc(100vw-2rem)] max-w-sm flex-col gap-3 pointer-events-none"
    role="region"
    aria-label="Messages"
    data-flash-toast-region
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-show="toast.visible"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-6"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-6"
            class="flash-alert toast-card pointer-events-auto w-full"
            :class="typeClasses(toast.type)"
            :role="toast.type === 'error' || toast.type === 'warning' ? 'alert' : 'status'"
            @mouseenter="pauseToast(toast)"
            @mouseleave="resumeToast(toast)"
        >
            <span class="mt-0.5 shrink-0" x-html="typeIcon(toast.type)"></span>

            <div class="flex-1 min-w-0 py-0.5">
                <p class="text-sm font-bold" x-text="typeTitle(toast.type)"></p>
                <p class="mt-0.5 text-sm leading-snug" x-text="toast.message"></p>
            </div>

            <button
                type="button"
                class="toast-close shrink-0"
                :aria-label="'Dismiss ' + typeTitle(toast.type) + ' message'"
                @click="dismissToast(toast)"
            >
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </template>
</div>

@once
    @push('scripts')
        <script>
            function flashToasts(initial = [], options = {}) {
                return {
                    toasts: [],
                    duration: options.duration ?? 5000,
                    maxVisible: options.maxVisible ?? 4,
                    nextId: 1,
                    _toastHandler: null,

                    init() {
                        (initial || []).forEach((item) => {
                            this.addToast(item.type, item.message, { duration: item.duration });
                        });

                        this._toastHandler = (event) => {
                            const { type, message, ...rest } = event.detail || {};
                            if (!type || !message) return;
                            this.addToast(type, message, rest);
                        };
                        document.addEventListener('aspire:toast', this._toastHandler);
                    },

                    destroy() {
                        if (this._toastHandler) {
                            document.removeEventListener('aspire:toast', this._toastHandler);
                        }
                    },

                    addToast(type, message, options = {}) {
                        const id = this.nextId++;
                        const toast = {
                            id,
                            type,
                            message,
                            visible: true,
                            timer: null,
                            startedAt: Date.now(),
                            remaining: options.duration ?? this.duration,
                        };
                        this.toasts.push(toast);
                        this.scheduleTimeout(toast);

                        while (this.toasts.length > this.maxVisible) {
                            this.dismissToast(this.toasts[0]);
                        }
                    },

                    scheduleTimeout(toast) {
                        if (toast.timer != null) return;
                        toast.startedAt = Date.now();
                        toast.timer = setTimeout(() => this.dismissToast(toast), toast.remaining);
                    },

                    pauseToast(toast) {
                        if (!toast || toast.timer == null) return;
                        clearTimeout(toast.timer);
                        toast.timer = null;
                        toast.remaining = Math.max(toast.remaining - (Date.now() - toast.startedAt), 750);
                    },

                    resumeToast(toast) {
                        if (!toast) return;
                        this.scheduleTimeout(toast);
                    },

                    dismissToast(toast) {
                        clearTimeout(toast.timer);
                        toast.timer = null;
                        toast.visible = false;
                        setTimeout(() => {
                            this.toasts = this.toasts.filter((t) => t.id !== toast.id);
                        }, 220);
                    },

                    typeTitle(type) {
                        return { success: 'Success', error: 'Error', warning: 'Warning', info: 'Info' }[type] || 'Notice';
                    },

                    typeClasses(type) {
                        return { success: 'flash-success', error: 'flash-error', warning: 'flash-warning', info: 'flash-info' }[type] || 'flash-info';
                    },

                    typeIcon(type) {
                        const icons = {
                            success: ['M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                            error: ['M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'],
                            warning: ['M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z'],
                            info: ['M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                        };
                        const paths = icons[type] || icons.info;

                        return `<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">` +
                            paths.map((d) => `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${d}"/>`).join('') +
                            `</svg>`;
                    },
                };
            }

            // Frontend-only toasts for AJAX/JSON flows. Dispatches a custom
            // event that the flash region (if mounted) listens for.
            if (!window.showToast) {
                window.showToast = (type, message, options = {}) => {
                    document.dispatchEvent(new CustomEvent('aspire:toast', {
                        detail: { type, message, ...options },
                    }));
                };
            }
        </script>
    @endpush
@endonce
