<header class="fixed top-0 right-0 z-50 border-b-2 border-indigo-500/20 bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm transition-all duration-300 ease-sidebar" :class="$store.sidebar.collapsed ? 'lg:left-16' : 'lg:left-56'">
  <div class="max-w-full mx-auto px-3 sm:px-6 h-16 flex items-center justify-between">
    <!-- Left side: mobile menu button + brand -->
    <div class="flex items-center gap-2 lg:hidden shrink-0">
      <button @click="$store.sidebar.openMobile()"
              class="h-10 w-10 flex items-center justify-center text-gray-500 dark:text-gray-400 hover:text-indigo-600 rounded-lg hover:bg-indigo-50 dark:hover:bg-gray-800 transition-colors"
              title="Open menu" aria-label="Open menu">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
      </button>
      <span class="text-lg font-bold text-slate-900 dark:text-gray-100 tracking-tight">ASPIRE</span>
    </div>

    <!-- Right side: user menu, notifications -->
    <div class="flex items-center space-x-3 ml-auto">
      <!-- Theme toggle -->
      <button @click="$store.theme.toggle()" 
              class="h-10 w-10 flex items-center justify-center text-gray-500 dark:text-gray-400 hover:text-indigo-600 rounded-lg hover:bg-indigo-50 dark:hover:bg-gray-800 transition-colors"
              title="Toggle dark mode" aria-label="Toggle dark mode">
          <svg x-show="!$store.theme.dark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
          </svg>
          <svg x-show="$store.theme.dark" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
          </svg>
      </button>
      @php
        $user = Auth::user();
        $initials = strtoupper(substr($user->first_name ?? $user->name, 0, 1) . substr($user->last_name ?? '', 0, 1));
        $colors = ['bg-indigo-500', 'bg-emerald-500', 'bg-blue-500', 'bg-violet-500', 'bg-rose-500', 'bg-amber-500', 'bg-cyan-500', 'bg-pink-500'];
        $color = $colors[crc32($user->email) % count($colors)];
      @endphp

      <!-- User dropdown -->
      <x-dropdown align="right" width="56">
        <x-slot name="trigger">
          <button class="flex items-center gap-2.5 focus:outline-none group">
            <div class="h-8 w-8 rounded-full {{ $color }} flex items-center justify-center text-white text-xs font-semibold ring-2 ring-white dark:ring-gray-900 group-hover:ring-indigo-100 transition-all">
              {{ $initials }}
            </div>
            <div class="hidden md:block text-left">
              <p class="text-sm font-medium text-gray-700 dark:text-gray-200 leading-tight">{{ $user->first_name ?? $user->name }}</p>
              <p class="text-[11px] text-gray-400 dark:text-gray-500 capitalize leading-tight">{{ str_replace('_', ' ', $user->role) }}</p>
            </div>
            <svg class="hidden md:block w-4 h-4 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
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
          <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
            <div class="flex items-center gap-3">
              <div class="h-10 w-10 rounded-full {{ $color }} flex items-center justify-center text-white text-sm font-semibold shrink-0">
                {{ $initials }}
              </div>
              <div class="min-w-0">
                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $user->name }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $user->email }}</p>
                <p class="text-[11px] text-gray-400 dark:text-gray-500 capitalize truncate">{{ str_replace('_', ' ', $user->role) }} @if($user->employee_id) &middot; {{ $user->employee_id }} @endif</p>
              </div>
            </div>
          </div>
          <x-dropdown-link :href="route($profileRoute)">
            <div class="flex items-center gap-2">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              {{ __('Profile') }}
            </div>
          </x-dropdown-link>
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
              <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                {{ __('Log Out') }}
              </div>
            </x-dropdown-link>
          </form>
        </x-slot>
      </x-dropdown>

      <!-- Notifications -->
      <x-notification-bell />
    </div>
  </div>
</header>
