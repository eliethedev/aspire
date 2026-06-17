<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'ASPIRE') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @inertiaHead
        
        <!-- Alpine.js for notification dropdown -->
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <script>
            function notificationDropdown() {
                return {
                    notifications: [],
                    unreadCount: 0,
                    
                    async fetchNotifications() {
                        try {
                            const response = await fetch('/notifications');
                            const data = await response.json();
                            this.notifications = data.notifications;
                            this.unreadCount = data.unread_count;
                        } catch (error) {
                            console.error('Error fetching notifications:', error);
                        }
                    },
                    
                    async markAsRead(id) {
                        try {
                            await fetch(`/notifications/${id}/mark-read`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Content-Type': 'application/json',
                                },
                            });
                            this.fetchNotifications();
                        } catch (error) {
                            console.error('Error marking notification as read:', error);
                        }
                    },
                    
                    async markAllAsRead() {
                        try {
                            await fetch('/notifications/mark-all-read', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                    'Content-Type': 'application/json',
                                },
                            });
                            this.fetchNotifications();
                        } catch (error) {
                            console.error('Error marking all notifications as read:', error);
                        }
                    },
                    
                    formatDate(dateString) {
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
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
