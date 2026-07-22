<!-- Header -->
<header class="bg-white dark:bg-gray-900 shadow-sm border-b border-slate-200 dark:border-gray-700">
    <div class="px-4 sm:px-6">
        <div class="flex justify-between items-center h-16">
            <!-- Left: Logo + Toggle -->
            <div class="flex items-center gap-3">
                <!-- Sidebar Toggle -->
                <button @click="$store.sidebar.toggle()"
                        class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-200 transition-colors"
                        :title="$store.sidebar.collapsed ? 'Expand sidebar' : 'Collapse sidebar'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <!-- ASPIRE Logo -->
                <a href="@if(auth()->user()->isTeacher()) {{ route('teacher.dashboard') }} @elseif(auth()->user()->isSupervisor()) {{ route('supervisor.dashboard') }} @elseif(auth()->user()->isSchoolHead()) {{ route('school-head.dashboard') }} @else {{ route('admin.dashboard') }} @endif"
                   class="flex items-center gap-2.5">
                    <x-application-logo class="w-8 h-8" />
                    <span class="text-lg font-bold text-slate-900 dark:text-gray-100 tracking-tight">ASPIRE</span>
                </a>
            </div>

            <!-- Right: User Menu -->
            <div class="flex items-center space-x-4">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-medium text-slate-900 dark:text-gray-100">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-slate-500 dark:text-gray-400">{{ ucfirst(Auth::user()->role) }}</p>
                </div>
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                        <span class="text-white text-sm font-medium">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                    </div>
                </div>
                <!-- Logout Button -->
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-slate-500 dark:text-gray-400 hover:text-red-500 dark:hover:text-red-400 transition-colors" title="Logout">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
