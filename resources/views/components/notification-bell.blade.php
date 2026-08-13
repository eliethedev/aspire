<div
    x-data="notificationBell()"
    @click.outside="open = false"
    @keydown.escape.window="open = false"
    class="relative"
>
    <button
        type="button"
        class="relative h-10 w-10 flex items-center justify-center text-gray-500 dark:text-gray-400 hover:text-indigo-600 rounded-lg hover:bg-indigo-50 dark:hover:bg-gray-800 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500"
        :aria-expanded="open"
        aria-haspopup="true"
        aria-controls="notification-panel"
        :aria-label="unreadCount > 0 ? `Notifications (${unreadCount} unread)` : 'Notifications'"
        @click="openMenu()"
    >
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
        <span
            x-text="unreadCount"
            x-show="unreadCount > 0"
            x-cloak
            class="absolute -top-1 -right-1 inline-flex items-center justify-center min-w-[16px] h-4 px-1 text-[10px] font-bold leading-none text-white bg-red-500 rounded-full"
            aria-hidden="true"
        >0</span>
    </button>

    <div
        id="notification-panel"
        x-show="open"
        x-ref="panel"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        role="region"
        aria-label="Notifications"
        tabindex="-1"
        style="display: none;"
        class="absolute right-0 mt-2 z-50 w-[calc(100vw-2rem)] sm:w-96 max-w-sm rounded-xl bg-white dark:bg-gray-900 shadow-lg ring-1 ring-black/5 border border-gray-100 dark:border-gray-800 overflow-hidden focus:outline-none"
    >
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Notifications</h3>
            <button
                type="button"
                x-show="unreadCount > 0"
                @click="markAllAsRead()"
                class="text-xs font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded"
            >Mark all as read</button>
        </div>

        <div class="max-h-96 overflow-y-auto" x-ref="list" :aria-busy="loading">
            <template x-if="loading">
                <div class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">Loading notifications&hellip;</div>
            </template>

            <template x-if="!loading && error">
                <div class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">Unable to load notifications.</div>
            </template>

            <template x-if="!loading && !error && notifications.length === 0">
                <div class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">No notifications</div>
            </template>

            <template x-for="notification in notifications" :key="notification.id">
                <div class="border-b border-gray-100 dark:border-gray-700 last:border-b-0">
                    <template x-if="notification.link">
                        <a
                            :href="notification.link"
                            @click="markAsRead(notification.id)"
                            class="block px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-inset"
                            :class="{'bg-blue-50 dark:bg-blue-900/20': !notification.is_read}"
                        >
                            <div class="flex items-start gap-3">
                                <span class="mt-0.5 shrink-0" :class="notification.is_read ? 'text-gray-400 dark:text-gray-500' : 'text-indigo-500'" x-html="iconMarkup(notification.type)"></span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100 leading-snug" x-text="notification.title"></p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 leading-snug line-clamp-2" x-text="notification.message"></p>
                                    <div class="flex flex-wrap items-center gap-2 mt-2">
                                        <span class="text-[11px] text-gray-400 dark:text-gray-500" x-text="formatDate(notification.created_at)"></span>
                                        <span x-show="notification.priority_label" class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium" :class="priorityClass(notification.priority)" x-text="notification.priority_label"></span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </template>
                    <template x-if="!notification.link">
                        <button
                            type="button"
                            @click="markAsRead(notification.id)"
                            class="block w-full text-left px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-inset"
                            :class="{'bg-blue-50 dark:bg-blue-900/20': !notification.is_read}"
                        >
                            <div class="flex items-start gap-3">
                                <span class="mt-0.5 shrink-0" :class="notification.is_read ? 'text-gray-400 dark:text-gray-500' : 'text-indigo-500'" x-html="iconMarkup(notification.type)"></span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100 leading-snug" x-text="notification.title"></p>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 leading-snug line-clamp-2" x-text="notification.message"></p>
                                    <div class="flex flex-wrap items-center gap-2 mt-2">
                                        <span class="text-[11px] text-gray-400 dark:text-gray-500" x-text="formatDate(notification.created_at)"></span>
                                        <span x-show="notification.priority_label" class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium" :class="priorityClass(notification.priority)" x-text="notification.priority_label"></span>
                                    </div>
                                </div>
                            </div>
                        </button>
                    </template>
                </div>
            </template>
        </div>

        <div class="px-3 py-3 border-t border-gray-200 dark:border-gray-700">
            <a href="{{ route('notifications.index') }}" class="block text-center text-sm font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                View all notifications
            </a>
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script>
            function notificationBell() {
                const ICONS = {
                    observation: ['M15 12a3 3 0 11-6 0 3 3 0 016 0z', 'M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z'],
                    observation_completed: ['M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                    feedback: ['M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z'],
                    ai_suggestion: ['M5 3v4M3 5h4M6 17v4M4 19h4M13 3l-1 3-3 1 3 1 1 3 1-3 3-1-3-1-1-3zM15 15l-.5 1.5L13 17l1.5.5L15 19l.5-1.5L17 17l-1.5-.5L15 15z'],
                    lesson_plan: ['M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                    action_required: ['M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                    conference: ['M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                    reminder: ['M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                    achievement: ['M9 12l2 2 4-4', 'M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z'],
                    professional_development: ['M12 14l9-5-9-5-9 5 9 5z', 'M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z', 'm12 14 9-5-9-5-9 5 9 5z'],
                    invitation: ['M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                    security: ['M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                    system: ['M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z', 'M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
                    announcement: ['M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z']
                };
                const DEFAULT_ICON = ['M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'];

                return {
                    open: false,
                    notifications: [],
                    unreadCount: 0,
                    loading: true,
                    error: false,

                    openMenu() {
                        this.open = !this.open;
                        if (this.open) {
                            this.fetchNotifications();
                            this.$nextTick(() => this.$refs.panel && this.$refs.panel.focus());
                        }
                    },

                    async fetchNotifications() {
                        this.loading = true;
                        this.error = false;
                        try {
                            const response = await fetch('/notifications/recent', {
                                headers: { 'Accept': 'application/json' }
                            });
                            if (!response.ok) throw new Error('Request failed');
                            const data = await response.json();
                            this.notifications = data.notifications || [];
                            this.unreadCount = data.unread_count || 0;
                        } catch (error) {
                            this.error = true;
                            console.error('Error fetching notifications:', error);
                        } finally {
                            this.loading = false;
                        }
                    },

                    async markAsRead(id) {
                        const item = this.notifications.find(n => n.id === id);
                        if (item && item.is_read) return;
                        try {
                            const response = await fetch(`/notifications/${id}/mark-read`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                },
                            });
                            if (!response.ok) throw new Error('Request failed');
                            const data = await response.json();
                            if (item) item.is_read = true;
                            this.unreadCount = data.unread_count ?? this.unreadCount;
                        } catch (error) {
                            console.error('Error marking notification as read:', error);
                        }
                    },

                    async markAllAsRead() {
                        try {
                            const response = await fetch('/notifications/mark-all-read', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                },
                            });
                            if (!response.ok) throw new Error('Request failed');
                            const data = await response.json();
                            this.notifications.forEach(n => n.is_read = true);
                            this.unreadCount = data.unread_count ?? 0;
                        } catch (error) {
                            console.error('Error marking all notifications as read:', error);
                            this.fetchNotifications();
                        }
                    },

                    iconMarkup(type) {
                        const paths = ICONS[type] || DEFAULT_ICON;
                        return `<svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">` +
                            paths.map(d => `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${d}"/>`).join('') +
                            `</svg>`;
                    },

                    priorityClass(priority) {
                        const map = {
                            critical: 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
                            high: 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
                            medium: 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
                            low: 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
                        };
                        return map[priority] || map.medium;
                    },

                    formatDate(dateString) {
                        if (!dateString) return '';
                        const date = new Date(dateString);
                        const now = new Date();
                        const diff = now - date;

                        if (diff < 60000) return 'Just now';
                        if (diff < 3600000) return Math.floor(diff / 60000) + ' minutes ago';
                        if (diff < 86400000) return Math.floor(diff / 3600000) + ' hours ago';
                        if (diff < 604800000) return Math.floor(diff / 86400000) + ' days ago';

                        return date.toLocaleDateString();
                    }
                };
            }
        </script>
    @endpush
@endonce
