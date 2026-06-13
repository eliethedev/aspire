<header class="glass-card border-b border-gray-200 bg-white">
  <div class="max-w-8xl mx-auto px-4 py-4 sm:px-6 lg:px-8 flex items-center justify-between">
    <!-- Search (placeholder) -->
    <div class="flex-1 mx-4 hidden md:block flex items-center">
      <input type="text" placeholder="Search users, schools, lessons..." class="w-full h-10 px-4 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
    </div>

    <!-- Right side: user menu, notifications -->
    <div class="flex items-center space-x-4">
      @php
        $user = Auth::user();
        $initials = strtoupper(substr($user->first_name ?? $user->name, 0, 1) . substr($user->last_name ?? '', 0, 1));
        $colors = ['bg-indigo-500', 'bg-emerald-500', 'bg-blue-500', 'bg-violet-500', 'bg-rose-500', 'bg-amber-500', 'bg-cyan-500', 'bg-pink-500'];
        $color = $colors[crc32($user->email) % count($colors)];
      @endphp

      <!-- User dropdown -->
      <x-dropdown align="right" width="48">
        <x-slot name="trigger">
          <button class="flex items-center focus:outline-none transition ease-in-out duration-150">
            <div class="h-10 w-10 rounded-full {{ $color }} flex items-center justify-center text-white text-sm font-semibold shadow-sm ring-2 ring-white hover:ring-gray-300 transition-all">
              {{ $initials }}
            </div>
          </button>
        </x-slot>
        <x-slot name="content">
          @php
            $profileRoute = match($user->role) {
              'teacher' => 'teacher.profile.edit',
              'supervisor' => 'supervisor.profile.edit',
              'school_head' => 'school-head.profile.edit',
              default => 'admin.profile.edit',
            };
          @endphp
          <div class="px-4 py-3 border-b border-gray-100">
            <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
            <p class="text-xs text-gray-500">{{ $user->email }}</p>
          </div>
          <x-dropdown-link :href="route($profileRoute)">{{ __('Profile') }}</x-dropdown-link>
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Log Out') }}</x-dropdown-link>
          </form>
        </x-slot>
      </x-dropdown>

      <!-- Notifications -->
      <div x-data="notificationDropdown()" x-init="fetchNotifications()">
      <x-dropdown align="right" width="w-96">
        <x-slot name="trigger">
          <button type="button" class="relative h-10 w-10 flex items-center justify-center text-gray-600 hover:text-gray-800 rounded-lg hover:bg-gray-100 transition-colors">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            <span x-text="unreadCount" x-show="unreadCount > 0" class="absolute -top-1 -right-1 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-600 rounded-full">0</span>
          </button>
        </x-slot>
        <x-slot name="content">
          <div>
            <div class="p-4 border-b border-gray-200 flex items-center">
              <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
              <button x-show="unreadCount > 0" @click="markAllAsRead()" class="ms-auto text-xs font-medium text-indigo-600 hover:text-indigo-800">Mark all as read</button>
            </div>
            <div class="max-h-96 overflow-y-auto">
              <template x-if="notifications.length === 0">
                <div class="p-4 text-center text-gray-500 text-sm">No notifications</div>
              </template>
              <template x-for="notification in notifications" :key="notification.id">
                <a :href="notification.link || '#'" @click="markAsRead(notification.id)" class="block px-4 py-3 hover:bg-gray-50 border-b border-gray-100 last:border-b-0" :class="{'bg-blue-50': !notification.is_read}">
                  <div class="flex items-start">
                    <div class="flex-1 min-w-0">
                      <p class="text-sm font-medium text-gray-900 truncate" x-text="notification.title"></p>
                      <p class="text-xs text-gray-600 mt-1 line-clamp-2" x-text="notification.message"></p>
                      <p class="text-xs text-gray-400 mt-1" x-text="formatDate(notification.created_at)"></p>
                    </div>
                    <div x-show="!notification.is_read" class="w-2 h-2 bg-blue-600 rounded-full mt-2 shrink-0"></div>
                  </div>
                </a>
              </template>
            </div>
            <div class="p-3 border-t border-gray-200">
              <a href="{{ route('notifications.show', ['role' => $user->role ?? 'admin']) }}" class="block text-center text-sm font-medium text-indigo-600 hover:text-indigo-800">View all notifications →</a>
            </div>
          </div>
        </x-slot>
      </x-dropdown>
      </div>
    </div>
  </div>
</header>

@push('scripts')
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
@endpush
