<!-- Admin Sidebar -->
<aside class="bg-white shadow-sm border-r border-gray-200 h-screen fixed left-0 top-0 z-40 transition-all duration-300 overflow-y-auto" x-data="{ registrationOpen: false, managementOpen: true, otherOpen: true }" :class="$store.sidebar.collapsed ? 'w-16' : 'w-58'">
    <style>
        [x-cloak] { display: none !important; }
        aside::-webkit-scrollbar {
            width: 4px;
        }
        aside::-webkit-scrollbar-track {
            background: transparent;
        }
        aside::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 2px;
        }
        aside::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>

    <!-- Logo -->
    <div class="h-16 flex items-center px-6 border-b border-gray-200 relative">
        <button @click="$store.sidebar.toggle(); if($store.sidebar.collapsed) { registrationOpen = false; managementOpen = false; otherOpen = false; }" class="p-2 focus:outline-none hover:bg-gray-100 rounded-lg transition-colors" :class="$store.sidebar.collapsed ? 'absolute right-2' : ''" title="Toggle sidebar">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
        </button>
        <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-2">
            <span x-show="!$store.sidebar.collapsed" class="font-bold text-gray-900">ASPIRE Admin</span>
        </a>
    </div>

    <!-- Navigation -->
    <nav class="mt-6 px-4 pb-4" :class="$store.sidebar.collapsed ? 'px-2' : 'px-4'">
        <ul class="space-y-1">
            <!-- Main Navigation Section -->
            <li class="mb-4">
                <span x-show="!$store.sidebar.collapsed" class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Main</span>
                <ul class="space-y-1 mt-1">
                    <!-- Dashboard -->
                    <li>
                        <a href="{{ route('admin.dashboard') }}"
                           class="flex items-center px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-50 text-indigo-700' : '' }}"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <svg class="w-5 h-5" :class="$store.sidebar.collapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            <span x-show="!$store.sidebar.collapsed">Dashboard</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Management Section -->
            <li class="mb-4" x-show="!$store.sidebar.collapsed">
                <button @click="managementOpen = !managementOpen" class="w-full flex items-center justify-between px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider hover:text-gray-700 transition-colors">
                    <span>Management</span>
                    <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': managementOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Management Links (Collapsible) -->
                <ul x-show="managementOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="space-y-1 mt-1">
                    <!-- Users Management -->
                    <li>
                        <a href="{{ route('admin.users.index') }}"
                           class="flex items-center px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors {{ request()->routeIs('admin.users.*') ? 'bg-indigo-50 text-indigo-700' : '' }}"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <svg class="w-5 h-5" :class="$store.sidebar.collapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                            </svg>
                            <span x-show="!$store.sidebar.collapsed">Users</span>
                        </a>
                    </li>

                    <!-- Schools Management -->
                    <li>
                        <a href="{{ route('admin.schools.index') }}"
                           class="flex items-center px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors {{ request()->routeIs('admin.schools.*') ? 'bg-indigo-50 text-indigo-700' : '' }}"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <svg class="w-5 h-5" :class="$store.sidebar.collapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <span x-show="!$store.sidebar.collapsed">Schools</span>
                        </a>
                    </li>

                    <!-- Teachers Management -->
                    <li>
                        <a href="{{ route('admin.teachers.index') }}"
                           class="flex items-center px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors {{ request()->routeIs('teachers.*') ? 'bg-indigo-50 text-indigo-700' : '' }}"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <svg class="w-5 h-5" :class="$store.sidebar.collapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span x-show="!$store.sidebar.collapsed">Teachers</span>
                        </a>
                    </li>

                    <!-- Supervisors Management -->
                    <li>
                        <a href="{{ route('admin.supervisors.index') }}"
                           class="flex items-center px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors {{ request()->routeIs('admin.supervisors.*') ? 'bg-indigo-50 text-indigo-700' : '' }}"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <svg class="w-5 h-5" :class="$store.sidebar.collapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <span x-show="!$store.sidebar.collapsed">Supervisors</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Registration Section -->
            <li class="mb-4" x-show="!$store.sidebar.collapsed">
                <button @click="registrationOpen = !registrationOpen" class="w-full flex items-center justify-between px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider hover:text-gray-700 transition-colors">
                    <span>Registration</span>
                    <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': registrationOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Registration Links (Collapsible) -->
                <ul x-show="registrationOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="space-y-1 mt-1">
                    <!-- Register User -->
                    <li>
                        <a href="{{ route('admin.users.create') }}"
                           class="flex items-center px-4 py-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors {{ request()->routeIs('admin.users.create') ? 'bg-blue-50 text-blue-700' : '' }}"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <svg class="w-5 h-5" :class="$store.sidebar.collapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                            <span x-show="!$store.sidebar.collapsed">Register Account</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Other Section -->
            <li x-show="!$store.sidebar.collapsed">
                <button @click="otherOpen = !otherOpen" class="w-full flex items-center justify-between px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider hover:text-gray-700 transition-colors">
                    <span>Other</span>
                    <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': otherOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Other Links (Collapsible) -->
                <ul x-show="otherOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="space-y-1 mt-1">
                    <!-- Observations -->
                    <li>
                        <a href="#"
                           class="flex items-center px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <svg class="w-5 h-5" :class="$store.sidebar.collapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                            </svg>
                            <span x-show="!$store.sidebar.collapsed">Observations</span>
                        </a>
                    </li>

                    <!-- Reports -->
                    <li>
                        <a href="#"
                           class="flex items-center px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <svg class="w-5 h-5" :class="$store.sidebar.collapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V8a2 2 0 00-2-2h-1.586a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 0012.586 3H8a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span x-show="!$store.sidebar.collapsed">Reports</span>
                        </a>
                    </li>

                    <!-- Settings -->
                    <li>
                        <a href="#"
                           class="flex items-center px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <svg class="w-5 h-5" :class="$store.sidebar.collapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span x-show="!$store.sidebar.collapsed">Settings</span>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>

    <!-- User Profile Section -->
    <div class="p-4 border-t border-gray-200 bg-white mt-auto" :class="$store.sidebar.collapsed ? 'justify-center' : ''">
        <div class="flex items-center" :class="$store.sidebar.collapsed ? '' : 'space-x-3'">
            <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                <span class="text-indigo-600 font-semibold">{{ substr(Auth::user()->name, 0, 1) }}</span>
            </div>
            <div x-show="!$store.sidebar.collapsed" class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ Auth::user()->name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}" x-show="!$store.sidebar.collapsed">
                @csrf
                <button type="submit" class="p-2 text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>
