<header class="glass-card border-b border-gray-200 bg-white" x-data="{}">
  <div class="max-w-8xl mx-auto px-4 py-4 sm:px-6 lg:px-8 flex items-center justify-between">
    <!-- Logo and Title -->
    <div class="flex items-center space-x-4">
      <a href="{{ route('dashboard') }}" class="flex items-center">
        <x-application-logo class="h-8 w-auto fill-current text-gray-800" />
        <span class="ml-2 font-semibold text-xl text-gray-800">{{ config('app.name', 'ASPIRE') }} Admin Dashboard</span>
      </a>
    </div>

    <!-- Search (placeholder) -->
    <div class="flex-1 mx-4 hidden md:block flex items-center">
      <input type="text" placeholder="Search users, schools, lessons..." class="w-full h-10 px-4 rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
    </div>

    <!-- Right side: notifications, user menu -->
    <div class="flex items-center space-x-4">
      <!-- Notifications -->
      <button type="button" class="relative h-10 w-10 flex items-center justify-center text-gray-600 hover:text-gray-800 rounded-lg hover:bg-gray-100 transition-colors">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
        <span class="absolute -top-1 -right-1 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-600 rounded-full">3</span>
      </button>

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
          <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Log Out') }}</x-dropdown-link>
          </form>
        </x-slot>
      </x-dropdown>
    </div>
  </div>
</header>
