<!-- Admin Sidebar -->
<aside class="sidebar-glass h-screen fixed left-0 top-0 z-40 transition-all duration-300 ease-sidebar flex flex-col overflow-hidden" x-data="{ registrationOpen: $persist(false), managementOpen: $persist(true), otherOpen: $persist(true) }" :class="$store.sidebar.collapsed ? 'w-16' : 'w-56'">

    <!-- Logo - fixed at top -->
    <div class="h-16 flex items-center px-5 border-b border-gray-100 shrink-0">
        <a href="{{ route('admin.dashboard') }}" :class="$store.sidebar.collapsed ? 'mx-auto' : ''" class="flex items-center space-x-2.5">
            <span class="text-lg font-bold text-indigo-600">ASPIRE Admin</span>
        </a>
    </div>

    <!-- Navigation - scrollable -->
    <nav class="flex-1 overflow-y-auto sidebar-scroll mt-5 pb-4" :class="$store.sidebar.collapsed ? 'px-2' : 'px-3'">
        <ul class="space-y-1">
            <!-- Main Navigation Section -->
            <li class="mb-4">
                <div x-show="!$store.sidebar.collapsed" class="sidebar-section-header px-3 py-1.5">
                    <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Main</span>
                </div>
                <ul class="space-y-0.5 mt-1.5">
                    <!-- Dashboard -->
                    <li>
                        <a href="{{ route('admin.dashboard') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 {{ request()->routeIs('admin.dashboard') ? 'sidebar-link-active icon-dashboard' : '' }}"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-dashboard" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.collapsed" class="font-medium">Dashboard</span>
                        </a>
                    </li>
                    <!-- Profile -->
                    <li>
                        <a href="{{ route('admin.profile.edit') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 {{ request()->routeIs('admin.profile.*') ? 'sidebar-link-active icon-profile' : '' }}"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-profile" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.collapsed" class="font-medium">Profile</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Management Section -->
            <li :class="$store.sidebar.collapsed ? 'mb-1' : 'mb-3'">
                <button x-show="!$store.sidebar.collapsed" @click="managementOpen = !managementOpen" class="sidebar-section-header w-full px-3 py-1.5 text-xs font-semibold text-gray-400 uppercase tracking-wider hover:text-gray-600 transition-colors">
                    <span>Management</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': managementOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul x-show="!$store.sidebar.collapsed ? managementOpen : true" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="space-y-0.5 mt-1">
                    <li>
                        <a href="{{ route('admin.users.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 {{ request()->routeIs('admin.users.*') ? 'sidebar-link-active icon-users' : '' }}"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-users" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.collapsed" class="font-medium">Users</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('admin.schools.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 {{ request()->routeIs('admin.schools.*') ? 'sidebar-link-active icon-schools' : '' }}"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-schools" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.collapsed" class="font-medium">Schools</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('admin.teachers.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 {{ request()->routeIs('teachers.*') ? 'sidebar-link-active icon-profile' : '' }}"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-profile" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.collapsed" class="font-medium">Teachers</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('admin.supervisors.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 {{ request()->routeIs('admin.supervisors.*') ? 'sidebar-link-active icon-coaching' : '' }}"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-coaching" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.collapsed" class="font-medium">Supervisors</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Registration Section -->
            <li :class="$store.sidebar.collapsed ? 'mb-1' : 'mb-3'">
                <button x-show="!$store.sidebar.collapsed" @click="registrationOpen = !registrationOpen" class="sidebar-section-header w-full px-3 py-1.5 text-xs font-semibold text-gray-400 uppercase tracking-wider hover:text-gray-600 transition-colors">
                    <span>Registration</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': registrationOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul x-show="!$store.sidebar.collapsed ? registrationOpen : true" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="space-y-0.5 mt-1">
                    <li>
                        <a href="{{ route('admin.users.create') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 {{ request()->routeIs('admin.users.create') ? 'sidebar-link-active icon-register' : '' }}"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-register" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.collapsed" class="font-medium">Register Account</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Other Section -->
            <li :class="$store.sidebar.collapsed ? 'mb-1' : ''">
                <button x-show="!$store.sidebar.collapsed" @click="otherOpen = !otherOpen" class="sidebar-section-header w-full px-3 py-1.5 text-xs font-semibold text-gray-400 uppercase tracking-wider hover:text-gray-600 transition-colors">
                    <span>Other</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': otherOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul x-show="!$store.sidebar.collapsed ? otherOpen : true" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="space-y-0.5 mt-1">
                    <li>
                        <a href="{{ route('admin.observations.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 {{ request()->routeIs('admin.observations.*') ? 'sidebar-link-active icon-observations' : '' }}"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-observations" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.collapsed" class="font-medium">Observations</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('admin.announcements.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 {{ request()->routeIs('admin.announcements.*') ? 'sidebar-link-active icon-announcements' : '' }}"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-announcements" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.collapsed" class="font-medium">Announcements</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('admin.audit-logs.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 {{ request()->routeIs('admin.audit-logs.*') ? 'sidebar-link-active icon-audit' : '' }}"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-audit" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.collapsed" class="font-medium">Audit Logs</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('admin.reports.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 {{ request()->routeIs('admin.reports.*') ? 'sidebar-link-active icon-reports' : '' }}"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-reports" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V8a2 2 0 00-2-2h-1.586a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 0012.586 3H8a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.collapsed" class="font-medium">Reports</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('admin.ai.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 {{ request()->routeIs('admin.ai.*') ? 'sidebar-link-active icon-ai' : '' }}"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-ai" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.collapsed" class="font-medium">AI Settings</span>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>

    <!-- User Profile Section - fixed at bottom -->
    <div class="mx-3 mb-3 shrink-0 px-3 py-3 rounded-xl border border-gray-100" :class="$store.sidebar.collapsed ? 'flex justify-center p-2' : ''">
        <div class="flex items-center" :class="$store.sidebar.collapsed ? '' : 'space-x-3'">
            <div class="w-9 h-9 rounded-full flex items-center justify-center sidebar-avatar-ring bg-indigo-600 flex-shrink-0">
                <span class="text-white text-sm font-semibold">{{ substr(Auth::user()->name, 0, 1) }}</span>
            </div>
            <div x-show="!$store.sidebar.collapsed" class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-800 truncate leading-tight">{{ Auth::user()->name }}</p>
                <p class="text-xs text-gray-400 truncate">{{ Auth::user()->email }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}" x-show="!$store.sidebar.collapsed">
                @csrf
                <button type="submit" class="p-1.5 text-gray-300 hover:text-red-400 hover:bg-red-50 rounded-lg transition-all duration-200" title="Logout">
                    <svg class="w-4 h-4 icon-logout" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>
