<!-- Unified Sidebar for Teacher, Supervisor, School Head — mockup folder-tree design.
     Brand logo (<x-application-logo />) is preserved. Collapse + mobile behavior
     still runs on the shared $store.sidebar Alpine store used by the layouts. -->
@include('partials.mks-sidebar-styles')
<aside class="sidebar-glass h-screen fixed left-0 top-0 z-[70] md:z-40 transition-all duration-300 ease-sidebar flex flex-col overflow-hidden"
       x-data="@if(auth()->user()->isTeacher()) { observationsOpen: $persist(true), feedbackOpen: $persist(true), analyticsOpen: $persist(true), foldersOpen: $persist(true) } @elseif(auth()->user()->isSupervisor()) { observationsOpen: $persist(false), rateesOpen: $persist(true), postObsOpen: $persist(true), reportsOpen: $persist(true), foldersOpen: $persist(true) } @else { supervisionOpen: $persist(true), approvalsOpen: $persist(true), insightsOpen: $persist(true), foldersOpen: $persist(true) } @endif"
       :class="[$store.sidebar.isCollapsed() ? 'w-16' : 'w-56', $store.sidebar.mobileOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0']">

    <!-- Logo - fixed at top (friendly brand lockup, same h-16 size) -->
    <div class="h-16 flex items-center justify-between gap-2 px-4 border-b border-gray-200/80 dark:border-gray-800/80 shrink-0 bg-white dark:bg-transparent">
        <a href="@if(auth()->user()->isTeacher()) {{ route('teacher.dashboard') }} @elseif(auth()->user()->isSupervisor()) {{ route('supervisor.dashboard') }} @else {{ route('school-head.dashboard') }} @endif"
           :class="$store.sidebar.isCollapsed() ? 'hidden' : ''"
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
            <input id="mks-filter" type="text" placeholder="Quick find…" aria-label="Filter side menu" autocomplete="off">
            <kbd class="mks-kbd">⌘K</kbd>
        </label>
    </div>

    <!-- Navigation - scrollable — minimized spacing like admin -->
    <nav id="sidebar-nav" data-mks-nav aria-label="Main menu" class="flex-1 overflow-y-auto sidebar-scroll mt-2 pb-4" :class="$store.sidebar.isCollapsed() ? 'px-2' : 'px-2'">
        <ul class="space-y-0.5">
            <!-- ═══ Workspace — flat quick access, mirrors the mockup ═══ -->
            <li class="mb-1">
                <div x-show="!$store.sidebar.isCollapsed()" class="sidebar-section-header px-3 py-1">
                    <span class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Workspace</span>
                </div>
                <ul class="space-y-0.5 mt-0.5">
                    <!-- Dashboard -->
                    <li>
                        <a data-mks="dashboard" href="@if(auth()->user()->isTeacher()) {{ route('teacher.dashboard') }} @elseif(auth()->user()->isSupervisor()) {{ route('supervisor.dashboard') }} @else {{ route('school-head.dashboard') }} @endif"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 @if(auth()->user()->isTeacher()) {{ request()->routeIs('teacher.dashboard') ? 'sidebar-link-active icon-dashboard' : '' }} @elseif(auth()->user()->isSupervisor()) {{ request()->routeIs('supervisor.dashboard') ? 'sidebar-link-active icon-dashboard' : '' }} @else {{ request()->routeIs('school-head.dashboard') ? 'sidebar-link-active icon-dashboard' : '' }} @endif"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-dashboard" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Dashboard</span>
                        </a>
                    </li>
                    @if(auth()->user()->isTeacher())
                    @php
                        $mksWsObsCount = \App\Models\Observation::where('observee_id', auth()->user()->teacher?->id)->where('observee_type', \App\Models\Teacher::class)->count();
                    @endphp
                    <!-- Observations -->
                    <li>
                        <a data-mks="observations my cycles" href="{{ route('teacher.observations.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('teacher.observations.*') && !request()->routeIs('teacher.observations.index') ? 'sidebar-link-active icon-observations' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-observations" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Observations</span>
                            @if($mksWsObsCount > 0)<span x-show="!$store.sidebar.isCollapsed()" class="mks-count-pill ml-auto">{{ $mksWsObsCount }}</span>@endif
                        </a>
                    </li>
                    <!-- Calendar -->
                    <li>
                        <a data-mks="calendar schedule dates" href="{{ route('teacher.calendar.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('teacher.calendar.index') ? 'sidebar-link-active icon-calendar' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-calendar" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Calendar</span>
                        </a>
                    </li>
                    <!-- Feedback -->
                    <li>
                        <a data-mks="feedback coaching comments" href="{{ route('teacher.feedback.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('teacher.feedback.*') && !request()->routeIs('teacher.feedback.index') ? 'sidebar-link-active icon-feedback' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-feedback" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Feedback</span>
                        </a>
                    </li>
                    @elseif(auth()->user()->isSupervisor())
                    @php
                        $mksWsObsCount = \App\Models\Observation::where('observer_id', auth()->id())->count();
                    @endphp
                    <!-- Observations -->
                    <li>
                        <a data-mks="observations all list" href="{{ route('supervisor.observations.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('supervisor.observations.*') && !request()->routeIs('supervisor.observations.index') && !request()->routeIs('supervisor.observations.create') && !request()->routeIs('supervisor.observations.offline') ? 'sidebar-link-active icon-observations' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-observations" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Observations</span>
                            @if($mksWsObsCount > 0)<span x-show="!$store.sidebar.isCollapsed()" class="mks-count-pill ml-auto">{{ $mksWsObsCount }}</span>@endif
                        </a>
                    </li>
                    <!-- Calendar -->
                    <li>
                        <a data-mks="calendar schedule dates" href="{{ route('supervisor.calendar.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('supervisor.calendar.index') ? 'sidebar-link-active icon-calendar' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-calendar" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Calendar</span>
                        </a>
                    </li>
                    <!-- Reports -->
                    <li>
                        <a data-mks="reports analytics exports" href="{{ route('supervisor.reports.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('supervisor.reports.*') && !request()->routeIs('supervisor.reports.index') ? 'sidebar-link-active icon-reports' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-reports" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V8a2 2 0 00-2-2h-1.586a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 0012.586 3H8a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Reports</span>
                        </a>
                    </li>
                    @else
                    @php
                        $mksWsObsCount = \App\Models\Observation::where('school_head_id', auth()->id())->count();
                    @endphp
                    <!-- Observations -->
                    <li>
                        <a data-mks="observations all list" href="{{ route('school-head.observations.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('school-head.observations.index') ? 'sidebar-link-active icon-observations' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-observations" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Observations</span>
                            @if($mksWsObsCount > 0)<span x-show="!$store.sidebar.isCollapsed()" class="mks-count-pill ml-auto">{{ $mksWsObsCount }}</span>@endif
                        </a>
                    </li>
                    <!-- Calendar -->
                    <li>
                        <a data-mks="calendar schedule dates" href="{{ route('school-head.calendar.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('school-head.calendar.index') ? 'sidebar-link-active icon-calendar' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-calendar" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Calendar</span>
                        </a>
                    </li>
                    @endif
                    <!-- Profile -->
                    <li>
                        <a data-mks="profile account" href="@if(auth()->user()->isTeacher()) {{ route('teacher.profile.edit') }} @elseif(auth()->user()->isSupervisor()) {{ route('supervisor.profile.edit') }} @else {{ route('school-head.profile.edit') }} @endif"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 @if(auth()->user()->isTeacher()) {{ request()->routeIs('teacher.profile.*') ? 'sidebar-link-active icon-profile' : '' }} @elseif(auth()->user()->isSupervisor()) {{ request()->routeIs('supervisor.profile.*') ? 'sidebar-link-active icon-profile' : '' }} @else {{ request()->routeIs('school-head.profile.*') ? 'sidebar-link-active icon-profile' : '' }} @endif"
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

            @if(auth()->user()->isTeacher())
            @php
                $pendingConfirmationCount = auth()->user()->teacher
                    ? \App\Models\Observation::where('observee_id', auth()->user()->teacher->id)
                        ->where('observee_type', \App\Models\Teacher::class)
                        ->where('status', 'scheduled')
                        ->count()
                    : 0;
                $mksCycles = auth()->user()->teacher
                    ? \App\Models\Observation::where('observee_id', auth()->user()->teacher->id)
                        ->where('observee_type', \App\Models\Teacher::class)
                        ->latest('observation_date')
                        ->take(5)
                        ->get()
                    : collect();
            @endphp
            <!-- Teacher: Observations folder -->
            <li class="mb-1" data-mks-group="observations my upcoming cycles">
                <button x-show="!$store.sidebar.isCollapsed()" @click="observationsOpen = !observationsOpen" class="sidebar-section-header w-full px-3 py-1.5 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider hover:text-gray-600 transition-colors mks-fbtn" :aria-expanded="observationsOpen.toString()">
                    <svg class="w-3.5 h-3.5 mr-1 text-sky-500" fill="none" stroke="#58a6ff" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/></svg>
                    <span>Observations</span>
                    <svg class="w-3.5 h-3.5 mks-caret transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': observationsOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul x-show="!$store.sidebar.isCollapsed() ? observationsOpen : true" class="space-y-0.5 mt-0.5">
                    <li>
                        <a data-mks="my observations all" href="{{ route('teacher.observations.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('teacher.observations.index') && !request('status') ? 'sidebar-link-active icon-observations' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-observations ml-5" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">My Observations</span>
                        </a>
                    </li>
                    <li>
                        <a data-mks="upcoming scheduled confirm" href="{{ route('teacher.observations.index', ['status' => 'scheduled']) }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('teacher.observations.*') && request('status') === 'scheduled' ? 'sidebar-link-active icon-calendar' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-calendar relative ml-5" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                @if($pendingConfirmationCount > 0)
                                <span class="absolute -top-1.5 -right-1.5 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white dark:border-gray-900"></span>
                                @endif
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Upcoming</span>
                            @if($pendingConfirmationCount > 0)
                            <span x-show="!$store.sidebar.isCollapsed()" class="ml-auto inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-red-500 rounded-full">{{ $pendingConfirmationCount }}</span>
                            @endif
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Teacher: Feedback folder -->
            <li class="mb-1" data-mks-group="feedback coaching improvement">
                <button x-show="!$store.sidebar.isCollapsed()" @click="feedbackOpen = !feedbackOpen" class="sidebar-section-header w-full px-3 py-1.5 text-xs font-semibold text-indigo-400 dark:text-indigo-500 uppercase tracking-wider hover:text-indigo-600 transition-colors mks-fbtn" :aria-expanded="feedbackOpen.toString()">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="#58a6ff" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/></svg>
                    <span>Feedback</span>
                    <svg class="w-3.5 h-3.5 mks-caret transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': feedbackOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul x-show="!$store.sidebar.isCollapsed() ? feedbackOpen : true" class="space-y-0.5 mt-0.5">
                    <li>
                        <a data-mks="feedback coaching comments" href="{{ route('teacher.feedback.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('teacher.feedback.index') ? 'sidebar-link-active icon-feedback' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-feedback ml-5" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Feedback & Coaching</span>
                        </a>
                    </li>
                    <li>
                        <a data-mks="improvement plan action" href="{{ route('teacher.coaching.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('teacher.coaching.*') ? 'sidebar-link-active icon-coaching' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-coaching ml-5" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Improvement Plan</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Teacher: Analytics folder -->
            <li class="mb-1" data-mks-group="analytics performance scores growth">
                <button x-show="!$store.sidebar.isCollapsed()" @click="analyticsOpen = !analyticsOpen" class="sidebar-section-header w-full px-3 py-1.5 text-xs font-semibold text-indigo-400 dark:text-indigo-500 uppercase tracking-wider hover:text-indigo-600 transition-colors mks-fbtn" :aria-expanded="analyticsOpen.toString()">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="#58a6ff" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1 2 2h-3l-4 4z" opacity="0"/><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/></svg>
                    <span>Analytics</span>
                    <svg class="w-3.5 h-3.5 mks-caret transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': analyticsOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul x-show="!$store.sidebar.isCollapsed() ? analyticsOpen : true" class="space-y-0.5 mt-0.5">
                    <li>
                        <a data-mks="performance analytics" href="{{ route('teacher.analytics') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('teacher.analytics') ? 'sidebar-link-active icon-analytics' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-analytics ml-5" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V8a2 2 0 00-2-2h-1.586a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 0012.586 3H8a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Performance Analytics</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Teacher: My Cycles folder tree -->
            <li class="mb-1 mks-hide-collapsed" data-mks-group="cycles history results">
                <button x-show="!$store.sidebar.isCollapsed()" @click="foldersOpen = !foldersOpen" class="sidebar-section-header w-full px-3 py-1.5 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider hover:text-gray-600 transition-colors" :aria-expanded="foldersOpen.toString()">
                    <span>My Cycles</span>
                    <svg class="w-3.5 h-3.5 mks-caret transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': foldersOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="!$store.sidebar.isCollapsed() ? foldersOpen : true" class="mks-tree space-y-0.5 mt-0.5">
                    @forelse($mksCycles as $cycle)
                    <a data-mks="cycle {{ strtolower($cycle->subject ?? '') }}" href="{{ route('teacher.observations.show', $cycle) }}"
                       class="sidebar-link-hover flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs text-gray-600 dark:text-gray-400 {{ request()->routeIs('teacher.observations.show') && (request()->route('observation') === $cycle->id ?? request()->route('observation')) == $cycle->id ? 'sidebar-link-active icon-observations' : '' }}">
                        <span class="mks-file-dot" aria-hidden="true"></span>
                        <span class="font-medium truncate">{{ $cycle->observation_date?->format('M d, Y') ?? 'Cycle #' . $cycle->id }} · {{ $cycle->subject ?? 'COT' }}</span>
                        @if($cycle->overall_score !== null)<span class="mks-count-pill ml-auto shrink-0">{{ number_format((float) $cycle->overall_score, 1) }}</span>@endif
                    </a>
                    @empty
                    <p class="px-2.5 py-1.5 text-xs text-gray-400 dark:text-gray-500">No cycles yet.</p>
                    @endforelse
                </div>
            </li>

            <!-- Teacher: System -->
            <li class="mb-1" data-mks-group="notifications alerts support help">
                <div x-show="!$store.sidebar.isCollapsed()" class="sidebar-section-header px-3 py-1">
                    <span class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">System</span>
                </div>
                <ul class="space-y-0.5 mt-0.5">
                    <li>
                        <a data-mks="notifications" href="{{ route('notifications.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('notifications.*') ? 'sidebar-link-active icon-announcements' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-announcements" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Notifications</span>
                        </a>
                    </li>
                    <li>
                        <a data-mks="support help bug feedback report" href="{{ route('support.create') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('support.*') ? 'sidebar-link-active icon-support' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-support" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Help & Support</span>
                        </a>
                    </li>
                </ul>
            </li>
            @endif

            @if(auth()->user()->isSupervisor())
            <!-- Supervisor: Observations folder -->
            <li class="mb-1" data-mks-group="observations all schedule completed">
                <button x-show="!$store.sidebar.isCollapsed()" @click="observationsOpen = !observationsOpen" class="sidebar-section-header w-full px-3 py-1.5 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider hover:text-gray-600 transition-colors mks-fbtn" :aria-expanded="observationsOpen.toString()">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="#58a6ff" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/></svg>
                    <span>Observations</span>
                    <svg class="w-3.5 h-3.5 mks-caret transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': observationsOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul x-show="!$store.sidebar.isCollapsed() ? observationsOpen : true" class="space-y-0.5 mt-0.5">
                    <li>
                        <a data-mks="all observations list" href="{{ route('supervisor.observations.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('supervisor.observations.index') && !request('status') && !request('stage') ? 'sidebar-link-active icon-observations' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-observations ml-5" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">All Observations</span>
                        </a>
                    </li>
                    <li>
                        <a data-mks="schedule new observation create" href="{{ route('supervisor.observations.create') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('supervisor.observations.create') ? 'sidebar-link-active icon-register' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-register  ml-5" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Schedule Observation</span>
                        </a>
                    </li>
                    <li>
                        <a data-mks="completed done finished" href="{{ route('supervisor.observations.index', ['status' => 'completed']) }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('supervisor.observations.index') && request('status') === 'completed' ? 'sidebar-link-active icon-coaching' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-coaching  ml-5" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Completed</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Supervisor: Ratees folder -->
            <li class="mb-1" data-mks-group="ratees teachers school heads faculty">
                <button x-show="!$store.sidebar.isCollapsed()" @click="rateesOpen = !rateesOpen" class="sidebar-section-header w-full px-3 py-1.5 text-xs font-semibold text-indigo-400 dark:text-indigo-500 uppercase tracking-wider hover:text-indigo-600 transition-colors mks-fbtn" :aria-expanded="rateesOpen.toString()">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="#58a6ff" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/></svg>
                    <span>Ratees</span>
                    <svg class="w-3.5 h-3.5 mks-caret transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': rateesOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul x-show="!$store.sidebar.isCollapsed() ? rateesOpen : true" class="space-y-0.5 mt-0.5">
                    <li>
                        <a data-mks="teachers faculty ratees" href="{{ route('supervisor.teachers.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('supervisor.teachers.*') ? 'sidebar-link-active icon-users' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-users ml-5" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Teachers</span>
                        </a>
                    </li>
                    <li>
                        <a data-mks="school heads principals" href="{{ route('supervisor.school-heads.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('supervisor.school-heads.*') ? 'sidebar-link-active icon-schools' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-schools ml-5" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">School Heads</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Supervisor: Post-Observation folder -->
            <li class="mb-1" data-mks-group="post-observation conferences action plans career progression monitor">
                <button x-show="!$store.sidebar.isCollapsed()" @click="postObsOpen = !postObsOpen" class="sidebar-section-header w-full px-3 py-1.5 text-xs font-semibold text-indigo-400 dark:text-indigo-500 uppercase tracking-wider hover:text-indigo-600 transition-colors mks-fbtn" :aria-expanded="postObsOpen.toString()">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="#58a6ff" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/></svg>
                    <span>Post-Observation</span>
                    <svg class="w-3.5 h-3.5 mks-caret transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': postObsOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul x-show="!$store.sidebar.isCollapsed() ? postObsOpen : true" class="space-y-0.5 mt-0.5">
                    <li>
                        <a data-mks="conferences post conference" href="{{ route('supervisor.observations.index', ['stage' => 'post_conference']) }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('supervisor.observations.index') && request('stage') === 'post_conference' ? 'sidebar-link-active icon-feedback' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-feedback ml-5" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Conferences</span>
                        </a>
                    </li>
                    <li>
                        <a data-mks="action plans coaching agreements" href="{{ route('supervisor.coaching.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('supervisor.coaching.*') ? 'sidebar-link-active icon-coaching' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-coaching ml-5" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Action Plans</span>
                        </a>
                    </li>
                    <li>
                        <a data-mks="career progression promotion" href="{{ route('supervisor.career.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('supervisor.career.*') ? 'sidebar-link-active icon-analytics' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-analytics ml-5" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Career Progression</span>
                        </a>
                    </li>
                    <li>
                        <a data-mks="career monitor tracking" href="{{ route('supervisor.career.monitor') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('supervisor.career.monitor') ? 'sidebar-link-active icon-analytics' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-activity ml-5" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Career Monitor</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Supervisor: Reports folder -->
            <li class="mb-1" data-mks-group="reports analytics teacher performance exports">
                <button x-show="!$store.sidebar.isCollapsed()" @click="reportsOpen = !reportsOpen" class="sidebar-section-header w-full px-3 py-1.5 text-xs font-semibold text-indigo-400 dark:text-indigo-500 uppercase tracking-wider hover:text-indigo-600 transition-colors mks-fbtn" :aria-expanded="reportsOpen.toString()">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="#58a6ff" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/></svg>
                    <span>Reports</span>
                    <svg class="w-3.5 h-3.5 mks-caret transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': reportsOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul x-show="!$store.sidebar.isCollapsed() ? reportsOpen : true" class="space-y-0.5 mt-0.5">
                    <li>
                        <a data-mks="observation reports" href="{{ route('supervisor.reports.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('supervisor.reports.*') && !request('view') ? 'sidebar-link-active icon-reports' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-reports ml-5" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V8a2 2 0 00-2-2h-1.586a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 0012.586 3H8a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Observation Reports</span>
                        </a>
                    </li>
                    <li>
                        <a data-mks="analytics charts trends" href="{{ route('supervisor.reports.index', ['view' => 'analytics']) }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('supervisor.reports.index') && request('view') === 'analytics' ? 'sidebar-link-active icon-analytics' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-analytics ml-5" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Analytics</span>
                        </a>
                    </li>
                    <li>
                        <a data-mks="teacher performance ratings" href="{{ route('supervisor.reports.index', ['view' => 'performance']) }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('supervisor.reports.index') && request('view') === 'performance' ? 'sidebar-link-active icon-reports' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-reports ml-5" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Teacher Performance</span>
                        </a>
                    </li>
                </ul>
            </li>

            @php
                $mksSupSchool = auth()->user()->school->name ?? 'My School';
                $mksSupTeachers = \App\Models\Teacher::with('user:id,name')
                    ->withCount('observations')
                    ->whereHas('user', fn ($q) => $q->where('school_id', auth()->user()->school_id))
                    ->orderBy('id')
                    ->take(5)
                    ->get();
            @endphp
            <!-- Supervisor: Schools / Folders tree (live data) -->
            <li class="mb-1 mks-hide-collapsed" data-mks-group="schools folders teachers files">
                <button x-show="!$store.sidebar.isCollapsed()" @click="foldersOpen = !foldersOpen" class="sidebar-section-header w-full px-3 py-1.5 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider hover:text-gray-600 transition-colors" :aria-expanded="foldersOpen.toString()">
                    <span>Schools / Folders</span>
                    <svg class="w-3.5 h-3.5 mks-caret transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': foldersOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="!$store.sidebar.isCollapsed() ? foldersOpen : true" class="mks-tree space-y-0.5 mt-0.5">
                    <div class="flex items-center gap-2 px-2.5 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-200">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#58a6ff" stroke-width="2" aria-hidden="true"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/></svg>
                        <span class="truncate">{{ $mksSupSchool }}</span>
                    </div>
                    @forelse($mksSupTeachers as $ft)
                    <a data-mks="teacher {{ strtolower($ft->user->name ?? '') }}" href="{{ route('supervisor.teachers.show', $ft) }}"
                       class="sidebar-link-hover flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs text-gray-600 dark:text-gray-400">
                        <span class="mks-file-dot" aria-hidden="true"></span>
                        <span class="font-medium truncate">{{ $ft->user->name ?? 'Unassigned' }}</span>
                        @if(($ft->observations_count ?? 0) > 0)<span class="mks-count-pill ml-auto shrink-0">{{ $ft->observations_count }}</span>@endif
                    </a>
                    @empty
                    <p class="px-2.5 py-1.5 text-xs text-gray-400 dark:text-gray-500">No teachers assigned yet.</p>
                    @endforelse
                    <a data-mks="view all teachers roster" href="{{ route('supervisor.teachers.index') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                        View all →
                    </a>
                </div>
            </li>

            <!-- Supervisor: Notifications + System -->
            <li data-mks-group="notifications alerts">
                <a data-mks="notifications" href="{{ route('notifications.index') }}"
                   class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('notifications.*') ? 'sidebar-link-active icon-announcements' : '' }}"
                   :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                    <span class="sidebar-icon-wrap icon-announcements" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </span>
                    <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Notifications</span>
                </a>
            </li>
            <li class="mb-1" data-mks-group="system offline sync support help">
                <div x-show="!$store.sidebar.isCollapsed()" class="sidebar-section-header px-3 py-1">
                    <span class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">System</span>
                </div>
                <ul class="space-y-0.5 mt-0.5">
                    <li>
                        <a data-mks="offline sync queue" href="{{ route('supervisor.observations.offline') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('supervisor.observations.offline') ? 'sidebar-link-active icon-calendar' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-calendar" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 1 1-2.6-6.4M21 4v5h-5"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Offline Sync</span>
                        </a>
                    </li>
                    <li>
                        <a data-mks="support help bug feedback report" href="{{ route('support.create') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('support.*') ? 'sidebar-link-active icon-support' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-support" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Help & Support</span>
                        </a>
                    </li>
                </ul>
            </li>
            @endif

            @if(auth()->user()->isSchoolHead())
            @php
                $schoolHeadUser = auth()->user();
                $schoolHeadProfile = \App\Models\SchoolHeadProfile::where('user_id', $schoolHeadUser->id)->first();
                $scheduledObservationCount = \App\Models\Observation::where('status', 'scheduled')
                    ->where(function ($q) use ($schoolHeadUser, $schoolHeadProfile) {
                        $q->where('observee_id', $schoolHeadProfile?->id)
                            ->where('observee_type', \App\Models\SchoolHeadProfile::class)
                            ->orWhere('observer_id', $schoolHeadUser->id)
                            ->where('observer_type', \App\Models\User::class)
                            ->orWhere('school_head_id', $schoolHeadUser->id);
                    })
                    ->count();
                $pendingAdvancementsCount = \App\Models\CareerAdvancement::where('status', \App\Models\CareerAdvancement::STATUS_PENDING_APPROVAL)
                    ->whereHas('teacher', fn ($q) => $q->where('school_id', $schoolHeadUser->school_id))
                    ->count();
                $unreadNotificationCount = $schoolHeadUser->unreadNotifications()->count();
                $mksShSchool = $schoolHeadUser->school->name ?? 'My School';
                $mksShTeachers = \App\Models\Teacher::with('user:id,name')
                    ->withCount('observations')
                    ->where('school_id', $schoolHeadUser->school_id)
                    ->orderBy('id')
                    ->take(5)
                    ->get();
            @endphp

            <!-- School Head: Supervision folder -->
            <li class="mb-1" data-mks-group="supervision classroom observations schedule co-observations">
                <button x-show="!$store.sidebar.isCollapsed()" @click="supervisionOpen = !supervisionOpen" class="sidebar-section-header w-full px-3 py-1.5 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider hover:text-gray-600 transition-colors mks-fbtn" :aria-expanded="supervisionOpen.toString()">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="#58a6ff" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/></svg>
                    <span>Supervision</span>
                    <svg class="w-3.5 h-3.5 mks-caret transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': supervisionOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul x-show="!$store.sidebar.isCollapsed() ? supervisionOpen : true" class="space-y-0.5 mt-0.5">
                    <li>
                        <a data-mks="classroom observations all" href="{{ route('school-head.observations.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('school-head.observations.show') || request()->routeIs('school-head.observations.preObservationPlanning') || request()->routeIs('school-head.observations.preConference') || request()->routeIs('school-head.observations.observation') || request()->routeIs('school-head.observations.postConference') || request()->routeIs('school-head.observations.cancel*') ? 'sidebar-link-active icon-observations' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-observations relative ml-5" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                @if($scheduledObservationCount > 0)
                                <span class="absolute -top-1.5 -right-1.5 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white dark:border-gray-900"></span>
                                @endif
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Classroom Observations</span>
                            @if($scheduledObservationCount > 0)
                            <span x-show="!$store.sidebar.isCollapsed()" class="ml-auto inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-red-500 rounded-full">{{ $scheduledObservationCount }}</span>
                            @endif
                        </a>
                    </li>
                    <li>
                        <a data-mks="schedule new observation create" href="{{ route('school-head.observations.create') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('school-head.observations.create') ? 'sidebar-link-active icon-register' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-register ml-5" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Schedule Observation</span>
                        </a>
                    </li>
                    <li>
                        <a data-mks="co-observations joint" href="{{ route('school-head.co-observations.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('school-head.co-observations.*') ? 'sidebar-link-active icon-observations' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-observations ml-5" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Co-Observations</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- School Head: Faculty & Approvals folder -->
            <li class="mb-1" data-mks-group="faculty teachers lesson plans career advancements approvals">
                <button x-show="!$store.sidebar.isCollapsed()" @click="approvalsOpen = !approvalsOpen" class="sidebar-section-header w-full px-3 py-1.5 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider hover:text-gray-600 transition-colors mks-fbtn" :aria-expanded="approvalsOpen.toString()">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="#58a6ff" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/></svg>
                    <span>Faculty & Approvals</span>
                    <svg class="w-3.5 h-3.5 mks-caret transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': approvalsOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul x-show="!$store.sidebar.isCollapsed() ? approvalsOpen : true" class="space-y-0.5 mt-0.5">
                    <li>
                        <a data-mks="teachers faculty roster" href="{{ route('school-head.teachers.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('school-head.teachers.*') ? 'sidebar-link-active icon-users' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-users ml-5" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Teachers</span>
                        </a>
                    </li>
                    <li>
                        <a data-mks="lesson plans dll dlp review" href="{{ route('school-head.lesson-plans.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('school-head.lesson-plans.*') ? 'sidebar-link-active icon-lesson' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-lesson ml-5" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Lesson Plans</span>
                        </a>
                    </li>
                    <li>
                        <a data-mks="career advancements promotion approval" href="{{ route('school-head.career.advancements.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('school-head.career.advancements.*') ? 'sidebar-link-active icon-coaching' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-coaching relative ml-5" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                                @if($pendingAdvancementsCount > 0)
                                <span class="absolute -top-1.5 -right-1.5 w-2.5 h-2.5 bg-amber-500 rounded-full border-2 border-white dark:border-gray-900"></span>
                                @endif
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Career Advancements</span>
                            @if($pendingAdvancementsCount > 0)
                            <span x-show="!$store.sidebar.isCollapsed()" class="ml-auto inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-amber-500 rounded-full">{{ $pendingAdvancementsCount }}</span>
                            @endif
                        </a>
                    </li>
                </ul>
            </li>

            <!-- School Head: School / Folders tree (live data) -->
            <li class="mb-1 mks-hide-collapsed" data-mks-group="school folders teachers files">
                <button x-show="!$store.sidebar.isCollapsed()" @click="foldersOpen = !foldersOpen" class="sidebar-section-header w-full px-3 py-1.5 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider hover:text-gray-600 transition-colors" :aria-expanded="foldersOpen.toString()">
                    <span>School / Folders</span>
                    <svg class="w-3.5 h-3.5 mks-caret transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': foldersOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="!$store.sidebar.isCollapsed() ? foldersOpen : true" class="mks-tree space-y-0.5 mt-0.5">
                    <div class="flex items-center gap-2 px-2.5 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-200">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#58a6ff" stroke-width="2" aria-hidden="true"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/></svg>
                        <span class="truncate">{{ $mksShSchool }}</span>
                    </div>
                    @forelse($mksShTeachers as $ft)
                    <a data-mks="teacher {{ strtolower($ft->user->name ?? '') }}" href="{{ route('school-head.teachers.show', $ft) }}"
                       class="sidebar-link-hover flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs text-gray-600 dark:text-gray-400">
                        <span class="mks-file-dot" aria-hidden="true"></span>
                        <span class="font-medium truncate">{{ $ft->user->name ?? 'Unassigned' }}</span>
                        @if(($ft->observations_count ?? 0) > 0)<span class="mks-count-pill ml-auto shrink-0">{{ $ft->observations_count }}</span>@endif
                    </a>
                    @empty
                    <p class="px-2.5 py-1.5 text-xs text-gray-400 dark:text-gray-500">No teachers assigned yet.</p>
                    @endforelse
                    <a data-mks="view all teachers roster" href="{{ route('school-head.teachers.index') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                        View all →
                    </a>
                </div>
            </li>

            <!-- School Head: Insights & Reports folder -->
            <li class="mb-1" data-mks-group="insights ai feedback coaching analytics reports notifications">
                <button x-show="!$store.sidebar.isCollapsed()" @click="insightsOpen = !insightsOpen" class="sidebar-section-header w-full px-3 py-1.5 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider hover:text-gray-600 transition-colors mks-fbtn" :aria-expanded="insightsOpen.toString()">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="#58a6ff" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z"/></svg>
                    <span>Insights & Reports</span>
                    <svg class="w-3.5 h-3.5 mks-caret transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': insightsOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul x-show="!$store.sidebar.isCollapsed() ? insightsOpen : true" class="space-y-0.5 mt-0.5">
                    <li>
                        <a data-mks="ai feedback coaching" href="{{ route('school-head.feedback.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('school-head.feedback.*') || request()->routeIs('school-head.coaching.*') ? 'sidebar-link-active icon-feedback' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-feedback ml-5" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">AI Feedback & Coaching</span>
                        </a>
                    </li>
                    <li>
                        <a data-mks="analytics reports charts" href="{{ route('school-head.reports.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('school-head.reports.*') ? 'sidebar-link-active icon-reports' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-reports ml-5" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V8a2 2 0 00-2-2h-1.586a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 0012.586 3H8a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Analytics & Reports</span>
                        </a>
                    </li>
                    <li>
                        <a data-mks="notifications alerts" href="{{ route('notifications.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('notifications.*') ? 'sidebar-link-active icon-announcements' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-announcements relative ml-5" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                @if($unreadNotificationCount > 0)
                                <span class="absolute -top-1.5 -right-1.5 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white dark:border-gray-900"></span>
                                @endif
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Notifications</span>
                            @if($unreadNotificationCount > 0)
                            <span x-show="!$store.sidebar.isCollapsed()" class="ml-auto inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-red-500 rounded-full">{{ $unreadNotificationCount }}</span>
                            @endif
                        </a>
                    </li>
                </ul>
            </li>

            <!-- School Head: System -->
            <li class="mb-1" data-mks-group="system support help bug">
                <div x-show="!$store.sidebar.isCollapsed()" class="sidebar-section-header px-3 py-1">
                    <span class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">System</span>
                </div>
                <ul class="space-y-0.5 mt-0.5">
                    <li>
                        <a data-mks="support help bug feedback report" href="{{ route('support.create') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('support.*') ? 'sidebar-link-active icon-support' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-support" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Help & Support</span>
                        </a>
                    </li>
                </ul>
            </li>
            @endif
        </ul>
    </nav>

    <!-- User Profile Section - fixed at bottom -->
    <div class="mks-user mx-3 mb-2 shrink-0 px-3 py-2 rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/50" :class="$store.sidebar.isCollapsed() ? 'flex justify-center p-2' : ''">
        <div class="flex items-center" :class="$store.sidebar.isCollapsed() ? '' : 'space-x-3'">
            <div class="mks-avatar w-9 h-9 rounded-full flex items-center justify-center sidebar-avatar-ring bg-indigo-600 flex-shrink-0">
                <span class="text-white text-sm font-semibold">{{ substr(Auth::user()->name, 0, 1) }}</span>
            </div>
            <div x-show="!$store.sidebar.isCollapsed()" class="flex-1 min-w-0">
                <p class="mks-uname text-sm font-semibold text-gray-800 dark:text-gray-200 truncate leading-tight">{{ Auth::user()->name }}</p>
                <p class="mks-umail text-[11px] text-gray-400 dark:text-gray-500 truncate">{{ Auth::user()->email }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}" x-show="!$store.sidebar.isCollapsed()">
                @csrf
                <button type="submit" class="p-1.5 text-gray-300 dark:text-gray-600 hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-all duration-200" title="Logout">
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
    var input = document.getElementById('mks-filter');
    var nav = document.getElementById('sidebar-nav');
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
