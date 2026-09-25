<!-- Admin Sidebar — mockup folder-tree design. Brand logo
     (<x-application-logo />) is preserved. Collapse + mobile behavior still
     runs on the shared $store.sidebar Alpine store used by the layout. -->
@include('partials.mks-sidebar-styles')
<aside class="sidebar-glass h-screen fixed left-0 top-0 z-[70] md:z-40 transition-all duration-300 ease-sidebar flex flex-col overflow-hidden" x-data="{ registrationOpen: $persist(false), managementOpen: $persist(true), standardsOpen: $persist(true), otherOpen: $persist(true), foldersOpen: $persist(true) }" :class="[$store.sidebar.isCollapsed() ? 'w-16' : 'w-56', $store.sidebar.mobileOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0']">

    <!-- Logo - fixed at top (friendly brand lockup, same h-16 size) -->
    <div class="h-16 flex items-center justify-between gap-2 px-4 border-b border-gray-200/80 dark:border-gray-800/80 shrink-0 bg-white dark:bg-transparent">
        <a href="{{ route('admin.dashboard') }}" :class="$store.sidebar.isCollapsed() ? 'hidden' : ''"
           class="sidebar-brand flex flex-1 items-center justify-center min-w-0 rounded-lg focus:outline-none"
           title="ASPIRE — Go to Home (Dashboard)"
           aria-label="ASPIRE — Go to Home Dashboard">
            <x-application-logo class="h-12 w-auto max-w-full" />
        </a>
        {{-- Desktop collapse/expand toggle: plain-language labels for older users. Same p-2 / w-5 h-5 sizes. --}}
        <button @click="$store.sidebar.toggle()"
                :class="$store.sidebar.isCollapsed() ? 'mx-auto' : ''"
                class="sidebar-toggle-btn hidden md:flex items-center justify-center p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/60 text-slate-600 dark:text-gray-300 hover:bg-indigo-50 hover:text-indigo-700 hover:border-indigo-200 dark:hover:bg-gray-800 dark:hover:text-indigo-300 dark:hover:border-indigo-800 transition-colors focus:outline-none"
                :title="$store.sidebar.isCollapsed() ? 'Show side menu' : 'Hide side menu'"
                :aria-label="$store.sidebar.isCollapsed() ? 'Show side menu' : 'Hide side menu'"
                :aria-expanded="!$store.sidebar.isCollapsed()"
                aria-controls="sidebar-nav">
            <span class="sr-only" x-text="$store.sidebar.isCollapsed() ? 'Show side menu' : 'Hide side menu'"></span>
            <svg x-show="!$store.sidebar.isCollapsed()" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h16M18 10l-2 2 2 2"/>
            </svg>
            <svg x-show="$store.sidebar.isCollapsed()" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h16M16 10l2 2-2 2"/>
            </svg>
        </button>
        <button @click="$store.sidebar.closeMobile()"
                class="md:hidden p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/60 text-slate-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors focus:outline-none"
                title="Close side menu" aria-label="Close side menu">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <!-- Mockup quick-find filter -->
    <div class="px-2 pt-2 shrink-0 mks-hide-collapsed">
        <label class="mks-search" title="Filter menu">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
            <input id="mks-filter-admin" type="text" placeholder="Quick find…" aria-label="Filter side menu" autocomplete="off">
            <kbd class="mks-kbd">⌘K</kbd>
        </label>
    </div>

    <!-- Navigation - scrollable -->
    <nav id="sidebar-nav-admin" data-mks-nav aria-label="Main menu" class="flex-1 overflow-y-auto sidebar-scroll mt-2 pb-4" :class="$store.sidebar.isCollapsed() ? 'px-2' : 'px-2'">
        <ul class="space-y-0.5">
            <!-- ═══ Workspace — flat quick access, mirrors the mockup ═══ -->
            <li class="mb-1">
                <div x-show="!$store.sidebar.isCollapsed()" class="sidebar-section-header px-3 py-1">
                    <span class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Workspace</span>
                </div>
                <ul class="space-y-0.5 mt-0.5">
                    <!-- Dashboard -->
                    <li>
                        <a data-mks="dashboard home" href="{{ route('admin.dashboard') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('admin.dashboard') ? 'sidebar-link-active icon-dashboard' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-dashboard" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Dashboard</span>
                        </a>
                    </li>
                    @php
                        $mksWsObsCount = \App\Models\Observation::count();
                    @endphp
                    <!-- Observations -->
                    <li>
                        <a data-mks="observations cot monitoring" href="{{ route('admin.observations.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('admin.observations.index') ? 'sidebar-link-active icon-observations' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-observations" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Observations</span>
                            @if($mksWsObsCount > 0)<span x-show="!$store.sidebar.isCollapsed()" class="mks-count-pill ml-auto">{{ $mksWsObsCount }}</span>@endif
                        </a>
                    </li>
                    <!-- Calendar -->
                    <li>
                        <a data-mks="calendar schedule dates" href="{{ route('admin.calendar.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('admin.calendar.index') ? 'sidebar-link-active icon-calendar' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-calendar" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Calendar</span>
                        </a>
                    </li>
                    <!-- Announcements -->
                    <li>
                        <a data-mks="announcements news broadcast" href="{{ route('admin.announcements.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('admin.announcements.index') ? 'sidebar-link-active icon-announcements' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-announcements" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Announcements</span>
                        </a>
                    </li>
                    <!-- Profile -->
                    <li>
                        <a data-mks="profile account" href="{{ route('admin.profile.edit') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('admin.profile.*') ? 'sidebar-link-active icon-profile' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-profile" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Profile</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Standards & Instruments folder -->
            <li class="mb-1" data-mks-group="standards cot templates ppst instruments">
                <button x-show="!$store.sidebar.isCollapsed()" @click="standardsOpen = !standardsOpen" class="sidebar-section-header w-full px-3 py-1.5 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider hover:text-gray-600 transition-colors mks-fbtn" :aria-expanded="standardsOpen.toString()">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="#58a6ff" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/></svg>
                    <span>Standards</span>
                    <svg class="w-3.5 h-3.5 mks-caret transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': standardsOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul x-show="!$store.sidebar.isCollapsed() ? standardsOpen : true" class="space-y-0.5 mt-0.5">
                    <li>
                        <a data-mks="cot templates indicators rating" href="{{ route('admin.cot-indicators.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('admin.cot-indicators.*') ? 'sidebar-link-active icon-reports' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-reports ml-3" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">COT Templates</span>
                        </a>
                    </li>

                    <li>
                        <a data-mks="ppst standards domains" href="{{ route('admin.ppst-standards.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('admin.ppst-standards.*') ? 'sidebar-link-active icon-reports' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-reports ml-3" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">PPST Standards</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Management folder -->
            <li class="mb-1" data-mks-group="management users schools teachers supervisors">
                <button x-show="!$store.sidebar.isCollapsed()" @click="managementOpen = !managementOpen" class="sidebar-section-header w-full px-3 py-1.5 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider hover:text-gray-600 transition-colors mks-fbtn" :aria-expanded="managementOpen.toString()">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="#58a6ff" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/></svg>
                    <span>Management</span>
                    <svg class="w-3.5 h-3.5 mks-caret transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': managementOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul x-show="!$store.sidebar.isCollapsed() ? managementOpen : true" class="space-y-0.5 mt-0.5">
                    <li>
                        <a data-mks="users accounts roles" href="{{ route('admin.users.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('admin.users.*') && !request()->routeIs('admin.users.create') ? 'sidebar-link-active icon-users' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-users ml-3" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Users</span>
                        </a>
                    </li>

                    <li>
                        <a data-mks="schools campuses" href="{{ route('admin.schools.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('admin.schools.*') ? 'sidebar-link-active icon-schools' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-schools ml-3" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Schools</span>
                        </a>
                    </li>

                    <li>
                        <a data-mks="teachers faculty" href="{{ route('admin.teachers.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('teachers.*') ? 'sidebar-link-active icon-profile' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-profile ml-3" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Teachers</span>
                        </a>
                    </li>

                    <li>
                        <a data-mks="supervisors observers" href="{{ route('admin.supervisors.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('admin.supervisors.*') ? 'sidebar-link-active icon-coaching' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-coaching ml-3" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Supervisors</span>
                        </a>
                    </li>

                </ul>
            </li>

            @php
                $mksSchools = \App\Models\School::where('is_active', true)->orderBy('name')->take(6)->get();
                $mksSchObs = \App\Models\Observation::selectRaw('school_id, COUNT(*) as c')->whereIn('school_id', $mksSchools->pluck('id'))->groupBy('school_id')->pluck('c', 'school_id');
            @endphp
            <!-- Schools / Folders tree (live data) -->
            <li class="mb-1 mks-hide-collapsed" data-mks-group="schools folders campuses files">
                <button x-show="!$store.sidebar.isCollapsed()" @click="foldersOpen = !foldersOpen" class="sidebar-section-header w-full px-3 py-1.5 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider hover:text-gray-600 transition-colors" :aria-expanded="foldersOpen.toString()">
                    <span>Schools / Folders</span>
                    <svg class="w-3.5 h-3.5 mks-caret transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': foldersOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="!$store.sidebar.isCollapsed() ? foldersOpen : true" class="mks-tree space-y-0.5 mt-0.5">
                    @forelse($mksSchools as $sch)
                    <a data-mks="school {{ strtolower($sch->name) }}" href="{{ route('admin.schools.show', $sch) }}"
                       class="sidebar-link-hover flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs text-gray-600 dark:text-gray-400 {{ request()->routeIs('admin.schools.show') && (int) request()->route('school') === $sch->id ? 'sidebar-link-active icon-schools' : '' }}">
                        <span class="mks-file-dot" aria-hidden="true"></span>
                        <span class="font-medium truncate">{{ $sch->name }}</span>
                        @if(($mksSchObs[$sch->id] ?? 0) > 0)<span class="mks-count-pill ml-auto shrink-0">{{ $mksSchObs[$sch->id] }}</span>@endif
                    </a>
                    @empty
                    <p class="px-2.5 py-1.5 text-xs text-gray-400 dark:text-gray-500">No active schools yet.</p>
                    @endforelse
                    <a data-mks="view all schools" href="{{ route('admin.schools.index') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                        View all →
                    </a>
                </div>
            </li>

            <!-- Registration folder -->
            <li class="mb-1" data-mks-group="registration register account invite">
                <button x-show="!$store.sidebar.isCollapsed()" @click="registrationOpen = !registrationOpen" class="sidebar-section-header w-full px-3 py-1.5 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider hover:text-gray-600 transition-colors mks-fbtn" :aria-expanded="registrationOpen.toString()">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="#58a6ff" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/></svg>
                    <span>Registration</span>
                    <svg class="w-3.5 h-3.5 mks-caret transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': registrationOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul x-show="!$store.sidebar.isCollapsed() ? registrationOpen : true" class="space-y-0.5 mt-0.5">
                    <li>
                        <a data-mks="register account new user" href="{{ route('admin.users.create') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('admin.users.create') ? 'sidebar-link-active icon-register' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-register ml-3" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Register Account</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Other folder -->
            <li data-mks-group="other observations announcements support audit ai">
                <button x-show="!$store.sidebar.isCollapsed()" @click="otherOpen = !otherOpen" class="sidebar-section-header w-full px-3 py-1.5 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider hover:text-gray-600 transition-colors mks-fbtn" :aria-expanded="otherOpen.toString()">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="#58a6ff" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/></svg>
                    <span>Other</span>
                    <svg class="w-3.5 h-3.5 mks-caret transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': otherOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul x-show="!$store.sidebar.isCollapsed() ? otherOpen : true" class="space-y-0.5 mt-0.5">
                    <li>
                        <a data-mks="observations cot monitoring" href="{{ route('admin.observations.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('admin.observations.*') && !request()->routeIs('admin.observations.index') ? 'sidebar-link-active icon-observations' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-observations ml-3" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Observations</span>
                        </a>
                    </li>

                    <li>
                        <a data-mks="announcements news broadcast" href="{{ route('admin.announcements.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('admin.announcements.*') && !request()->routeIs('admin.announcements.index') ? 'sidebar-link-active icon-announcements' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-announcements ml-3" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Announcements</span>
                        </a>
                    </li>

                    @php($openSupportCount = \App\Models\SupportMessage::query()->open()->count())
                    <li>
                        <a data-mks="support messages inbox tickets help" href="{{ route('admin.support-messages.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('admin.support-messages.*') ? 'sidebar-link-active icon-support' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-support ml-3" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Support Messages</span>
                            @if($openSupportCount > 0)
                                <span x-show="!$store.sidebar.isCollapsed()"
                                      class="ml-auto inline-flex items-center justify-center min-w-5 h-5 px-1.5 rounded-full bg-red-500 text-white text-xs font-semibold">
                                    {{ $openSupportCount }}
                                </span>
                            @endif
                        </a>
                    </li>

                    <li>
                        <a data-mks="audit logs activity history" href="{{ route('admin.audit-logs.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('admin.audit-logs.*') ? 'sidebar-link-active icon-audit' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-audit ml-3" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Audit Logs</span>
                        </a>
                    </li>

                    <li>
                        <a data-mks="ai settings artificial intelligence configuration" href="{{ route('admin.ai.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('admin.ai.*') ? 'sidebar-link-active icon-ai' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-ai ml-3" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">AI Settings</span>
                        </a>
                    </li>

                    <li>
                        <a data-mks="ai usage cost billing tokens" href="{{ route('admin.ai-usage.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('admin.ai-usage.*') ? 'sidebar-link-active icon-ai-usage' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-ai-usage ml-3" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">AI Usage &amp; Cost</span>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>

    <!-- User Profile Section - fixed at bottom -->
    <div class="mks-user mx-3 mb-2 shrink-0 px-3 py-2 rounded-xl border border-gray-100 dark:border-gray-800" :class="$store.sidebar.isCollapsed() ? 'flex justify-center p-2' : ''">
        <div class="flex items-center" :class="$store.sidebar.isCollapsed() ? '' : 'space-x-3'">
            <div class="mks-avatar w-9 h-9 rounded-full flex items-center justify-center sidebar-avatar-ring bg-indigo-600 flex-shrink-0">
                <span class="text-white text-sm font-semibold">{{ substr(Auth::user()->name, 0, 1) }}</span>
            </div>
            <div x-show="!$store.sidebar.isCollapsed()" class="flex-1 min-w-0">
                <p class="mks-uname text-sm font-semibold text-gray-800 dark:text-gray-100 truncate leading-tight">{{ Auth::user()->name }}</p>
                <p class="mks-umail text-xs text-gray-400 dark:text-gray-500 truncate">{{ Auth::user()->email }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}" x-show="!$store.sidebar.isCollapsed()">
                @csrf
                <button type="submit" class="p-1.5 text-gray-300 dark:text-gray-500 hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-lg transition-all duration-200" title="Logout">
                    <svg class="w-4 h-4 icon-logout" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>
<script>
(function () {
    var input = document.getElementById('mks-filter-admin');
    var nav = document.getElementById('sidebar-nav-admin');
    if (!input || !nav) return;
    function apply(q) {
        q = (q || '').trim().toLowerCase();
        nav.querySelectorAll('[data-mks-group]').forEach(function (group) {
            var links = group.querySelectorAll('a[data-mks]');
            var any = false;
            links.forEach(function (a) {
                var hit = !q || (a.getAttribute('data-mks') || '').toLowerCase().indexOf(q) !== -1;
                a.style.display = hit ? '' : 'none';
                if (hit) any = true;
            });
            var headerHit = !q || (group.getAttribute('data-mks-group') || '').toLowerCase().indexOf(q) !== -1;
            group.style.display = (any || headerHit) ? '' : 'none';
        });
    }
    input.addEventListener('input', function () { apply(input.value); });
    document.addEventListener('keydown', function (e) {
        if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') { e.preventDefault(); input.focus(); }
    });
})();
</script>
