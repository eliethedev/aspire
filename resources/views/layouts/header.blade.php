<header class="glass-card border-b border-gray-200 bg-white">
  <div class="max-w-8xl mx-auto px-4 py-4 sm:px-6 lg:px-8 flex items-center justify-between">
    <!-- Search (placeholder) -->
    <div class="flex-1 mx-4 hidden md:block flex items-center">
      <input type="text" placeholder="Search users, schools, lessons..." class="w-full h-10 px-4 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
    </div>

    <!-- Right side: notifications, user menu -->
    <div class="flex items-center space-x-4">
      <!-- Notifications -->
      <div x-data="notificationDropdown()" x-init="fetchNotifications()">
      <x-dropdown align="right" width="80">
        <x-slot name="trigger">
          <button type="button" class="relative h-10 w-10 flex items-center justify-center text-gray-600 hover:text-gray-800 rounded-lg hover:bg-gray-100 transition-colors">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            <span x-text="unreadCount" x-show="unreadCount > 0" class="absolute -top-1 -right-1 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-600 rounded-full">0</span>
          </button>
        </x-slot>
        <x-slot name="content">
          <div>
            <div class="p-4 border-b border-gray-200 flex justify-between items-center">
              <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
              <button x-show="unreadCount > 0" @click="markAllAsRead()" class="text-xs text-indigo-600 hover:text-indigo-800">Mark all as read</button>
            </div>
            <div class="max-h-96 overflow-y-auto">
              <template x-if="notifications.length === 0">
                <div class="p-4 text-center text-gray-500 text-sm">No notifications</div>
              </template>
              <template x-for="notification in notifications" :key="notification.id">
                <a :href="notification.link || '#'" @click="markAsRead(notification.id)" class="block px-4 py-3 hover:bg-gray-50 border-b border-gray-100 last:border-b-0" :class="{'bg-blue-50': !notification.is_read}">
                  <div class="flex items-start">
                    <div class="flex-1">
                      <p class="text-sm font-medium text-gray-900" x-text="notification.title"></p>
                      <p class="text-xs text-gray-600 mt-1" x-text="notification.message"></p>
                      <p class="text-xs text-gray-400 mt-1" x-text="formatDate(notification.created_at)"></p>
                    </div>
                    <div x-show="!notification.is_read" class="w-2 h-2 bg-blue-600 rounded-full mt-2"></div>
                  </div>
                </a>
              </template>
            </div>
            <div class="p-3 border-t border-gray-200">
              <a href="{{ route('notifications.show', ['role' => auth()->user()->role ?? 'admin']) }}" class="block text-center text-sm text-indigo-600 hover:text-indigo-800">View all notifications</a>
            </div>
          </div>
        </x-slot>
      </x-dropdown>
      </div>

      <!-- User dropdown (reuse from navigation) -->
      <x-dropdown align="right" width="48">
        <x-slot name="trigger">
          <button class="inline-flex items-center h-10 px-4 border text-sm leading-4 font-medium rounded-lg focus:outline-none transition ease-in-out duration-150 border-gray-300 text-gray-700 bg-white hover:bg-gray-50 hover:text-gray-900">
            <div>{{ Auth::user()->name }}</div>
            <div class="ms-1">
              <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
            </div>
          </button>
        </x-slot>
        <x-slot name="content">
          @php
            $profileRoute = match(auth()->user()->role) {
              'teacher' => 'teacher.profile.edit',
              'supervisor' => 'supervisor.profile.edit',
              'school_head' => 'school-head.profile.edit',
              default => 'admin.profile.edit',
            };
          @endphp
          <x-dropdown-link :href="route($profileRoute)">{{ __('Profile') }}</x-dropdown-link>
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Log Out') }}</x-dropdown-link>
          </form>
        </x-slot>
      </x-dropdown>
    </div>
  </div>
</header>
