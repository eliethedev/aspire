<!-- Unified Sidebar for Teacher, Supervisor, School Head -->
<aside class="sidebar-glass h-screen fixed left-0 top-0 z-[70] lg:z-40 transition-all duration-300 ease-sidebar flex flex-col overflow-hidden" 
       x-data="@if(auth()->user()->isTeacher()) { observationsOpen: $persist(true), feedbackOpen: $persist(true), analyticsOpen: $persist(true) } @elseif(auth()->user()->isSupervisor()) { observationsOpen: $persist(false), rateesOpen: $persist(true), postObsOpen: $persist(true), reportsOpen: $persist(true) } @else { supervisionOpen: $persist(true), aiInsightsOpen: $persist(true), othersOpen: $persist(false) } @endif" 
       :class="[$store.sidebar.isCollapsed() ? 'w-16' : 'w-56', $store.sidebar.mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0']">

    <!-- Logo - fixed at top -->
    <div class="h-16 flex items-center justify-between px-4 border-b border-gray-100/80 dark:border-gray-800/80 shrink-0">
        <a href="@if(auth()->user()->isTeacher()) {{ route('teacher.dashboard') }} @elseif(auth()->user()->isSupervisor()) {{ route('supervisor.dashboard') }} @else {{ route('school-head.dashboard') }} @endif" 
           :class="$store.sidebar.isCollapsed() ? 'hidden' : ''"
           class="flex items-center space-x-2.5">
           <x-deped-logo class="w-16 h-auto shrink-0" />
           <span class="text-lg font-bold text-indigo-600 tracking-tight">
                ASPIRE
            </span>
        </a>
        <button @click="$store.sidebar.toggle()" 
                :class="$store.sidebar.isCollapsed() ? 'mx-auto' : ''"
                class="hidden lg:flex p-2 focus:outline-none hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors text-gray-400 hover:text-indigo-600 dark:text-gray-500 dark:hover:text-indigo-400"
                :title="$store.sidebar.isCollapsed() ? 'Expand sidebar' : 'Collapse sidebar'"
                :aria-label="$store.sidebar.isCollapsed() ? 'Expand sidebar' : 'Collapse sidebar'">
            <svg x-show="!$store.sidebar.isCollapsed()" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
            </svg>
            <svg x-show="$store.sidebar.isCollapsed()" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 19l7-7-7-7"/>
            </svg>
        </button>
        <button @click="$store.sidebar.closeMobile()"
                class="lg:hidden p-2 focus:outline-none hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors text-gray-400 hover:text-indigo-600 dark:text-gray-500 dark:hover:text-indigo-400"
                title="Close menu" aria-label="Close menu">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <!-- Navigation - scrollable — minimized spacing like admin -->
    <nav class="flex-1 overflow-y-auto sidebar-scroll mt-2 pb-4" :class="$store.sidebar.isCollapsed() ? 'px-2' : 'px-3'">
        <ul class="space-y-0.5">
            <!-- Main Navigation Section -->
            <li class="mb-1">
                <div x-show="!$store.sidebar.isCollapsed()" class="sidebar-section-header px-3 py-1">
                    <span class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Main</span>
                </div>
                <ul class="space-y-0.5 mt-0.5">
                    <!-- Dashboard -->
                    <li>
                        <a href="@if(auth()->user()->isTeacher()) {{ route('teacher.dashboard') }} @elseif(auth()->user()->isSupervisor()) {{ route('supervisor.dashboard') }} @else {{ route('school-head.dashboard') }} @endif"
                           class="sidebar-link-hover flex items-center px-3 py-1.5 rounded-xl text-sm text-gray-600 dark:text-gray-400 dark:text-gray-400 @if(auth()->user()->isTeacher()) {{ request()->routeIs('teacher.dashboard') ? 'sidebar-link-active icon-dashboard' : '' }} @elseif(auth()->user()->isSupervisor()) {{ request()->routeIs('supervisor.dashboard') ? 'sidebar-link-active icon-dashboard' : '' }} @else {{ request()->routeIs('school-head.dashboard') ? 'sidebar-link-active icon-dashboard' : '' }} @endif"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-dashboard" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Dashboard</span>
                        </a>
                    </li>
                    <!-- Profile -->
                    <li>
                        <a href="@if(auth()->user()->isTeacher()) {{ route('teacher.profile.edit') }} @elseif(auth()->user()->isSupervisor()) {{ route('supervisor.profile.edit') }} @else {{ route('school-head.profile.edit') }} @endif"
                           class="sidebar-link-hover flex items-center px-3 py-1.5 rounded-xl text-sm text-gray-600 dark:text-gray-400 @if(auth()->user()->isTeacher()) {{ request()->routeIs('teacher.profile.*') ? 'sidebar-link-active icon-profile' : '' }} @elseif(auth()->user()->isSupervisor()) {{ request()->routeIs('supervisor.profile.*') ? 'sidebar-link-active icon-profile' : '' }} @else {{ request()->routeIs('school-head.profile.*') ? 'sidebar-link-active icon-profile' : '' }} @endif"
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
            <!-- Teacher: Observations Section -->
            <li :class="$store.sidebar.isCollapsed() ? 'mb-1' : 'mb-1'">
                <button x-show="!$store.sidebar.isCollapsed()" @click="observationsOpen = !observationsOpen" class="sidebar-section-header w-full px-3 py-1 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider hover:text-gray-600 transition-colors">
                    <span>Observations</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': observationsOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul x-show="!$store.sidebar.isCollapsed() ? observationsOpen : true" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="space-y-0.5 mt-0.5">
                    <li>
                        <a href="{{ route('teacher.observations.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-1.5 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('teacher.observations.*') ? 'sidebar-link-active icon-observations' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-observations" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">My Observations</span>
                        </a>
                    </li>
                    @php
                        $pendingConfirmationCount = auth()->user()->teacher
                            ? \App\Models\Observation::where('observee_id', auth()->user()->teacher->id)
                                ->where('observee_type', \App\Models\Teacher::class)
                                ->where('confirmation_status', 'pending')
                                ->where('status', '!=', 'cancelled')
                                ->count()
                            : 0;
                    @endphp
                    <li>
                        <a href="{{ route('teacher.observations.index', ['status' => 'scheduled']) }}"
                           class="sidebar-link-hover flex items-center px-3 py-1.5 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('teacher.observations.*') && request('status') === 'scheduled' ? 'sidebar-link-active icon-calendar' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-calendar relative" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                @if($pendingConfirmationCount > 0)
                                <span class="absolute -top-1.5 -right-1.5 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white"></span>
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

            <!-- Teacher: Feedback Section -->
            <li :class="$store.sidebar.isCollapsed() ? 'mb-1' : 'mb-1'">
                <button x-show="!$store.sidebar.isCollapsed()" @click="feedbackOpen = !feedbackOpen" class="sidebar-section-header w-full px-3 py-1 text-xs font-semibold text-indigo-400 dark:text-indigo-500 uppercase tracking-wider hover:text-indigo-600 transition-colors">
                    <span>Feedback</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': feedbackOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul x-show="!$store.sidebar.isCollapsed() ? feedbackOpen : true" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="space-y-0.5 mt-0.5">
                    <li>
                        <a href="{{ route('teacher.feedback.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-1.5 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('teacher.feedback.*') ? 'sidebar-link-active icon-feedback' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-feedback" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Feedback & Coaching</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('teacher.coaching.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-1.5 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('teacher.coaching.*') ? 'sidebar-link-active icon-coaching' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-coaching" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Improvement Plan</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Teacher: Analytics Section -->
            <li :class="$store.sidebar.isCollapsed() ? 'mb-1' : ''">
                <button x-show="!$store.sidebar.isCollapsed()" @click="analyticsOpen = !analyticsOpen" class="sidebar-section-header w-full px-3 py-1 text-xs font-semibold text-indigo-400 dark:text-indigo-500 uppercase tracking-wider hover:text-indigo-600 transition-colors">
                    <span>Analytics</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': analyticsOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul x-show="!$store.sidebar.isCollapsed() ? analyticsOpen : true" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="space-y-0.5 mt-0.5">
                    <li>
                        <a href="#"
                           class="sidebar-link-hover flex items-center px-3 py-1.5 rounded-xl text-sm text-gray-600 dark:text-gray-400"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-analytics" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V8a2 2 0 00-2-2h-1.586a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 0012.586 3H8a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Performance Analytics</span>
                        </a>
                    </li>
                    <li>
                        <a href="#"
                           class="sidebar-link-hover flex items-center px-3 py-1.5 rounded-xl text-sm text-gray-600 dark:text-gray-400"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-reports" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Reports</span>
                        </a>
                    </li>
                </ul>
            </li>
            @endif

            @if(auth()->user()->isSupervisor())
            <!-- Supervisor: Observations Section -->
            <li :class="$store.sidebar.isCollapsed() ? 'mb-1' : 'mb-1'">
                <button x-show="!$store.sidebar.isCollapsed()" @click="observationsOpen = !observationsOpen" class="sidebar-section-header w-full px-3 py-1 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider hover:text-gray-600 transition-colors">
                    <span>Observations</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': observationsOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul x-show="!$store.sidebar.isCollapsed() ? observationsOpen : true" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="space-y-0.5 mt-0.5">
                    <li>
                        <a href="{{ route('supervisor.observations.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-1.5 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('supervisor.observations.index') && !request('status') && !request('stage') ? 'sidebar-link-active icon-observations' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-observations" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">All Observations</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('supervisor.observations.create') }}"
                           class="sidebar-link-hover flex items-center px-3 py-1.5 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('supervisor.observations.create') ? 'sidebar-link-active icon-register' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-register" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Schedule Observation</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('supervisor.observations.index', ['status' => 'completed']) }}"
                           class="sidebar-link-hover flex items-center px-3 py-1.5 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('supervisor.observations.index') && request('status') === 'completed' ? 'sidebar-link-active icon-coaching' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-coaching" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Completed</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Supervisor: Ratees Section -->
            <li :class="$store.sidebar.isCollapsed() ? 'mb-1' : 'mb-1'">
                <button x-show="!$store.sidebar.isCollapsed()" @click="rateesOpen = !rateesOpen" class="sidebar-section-header w-full px-3 py-1 text-xs font-semibold text-indigo-400 dark:text-indigo-500 uppercase tracking-wider hover:text-indigo-600 transition-colors">
                    <span>Ratees</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': rateesOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul x-show="!$store.sidebar.isCollapsed() ? rateesOpen : true" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="space-y-0.5 mt-0.5">
                    <li>
                        <a href="{{ route('supervisor.teachers.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-1.5 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('supervisor.teachers.*') ? 'sidebar-link-active icon-users' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-users" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Teachers</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('supervisor.school-heads.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-1.5 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('supervisor.school-heads.*') ? 'sidebar-link-active icon-schools' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-schools" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">School Heads</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Supervisor: Post-Observation Section -->
            <li :class="$store.sidebar.isCollapsed() ? 'mb-1' : 'mb-1'">
                <button x-show="!$store.sidebar.isCollapsed()" @click="postObsOpen = !postObsOpen" class="sidebar-section-header w-full px-3 py-1 text-xs font-semibold text-indigo-400 dark:text-indigo-500 uppercase tracking-wider hover:text-indigo-600 transition-colors">
                    <span>Post-Observation</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': postObsOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul x-show="!$store.sidebar.isCollapsed() ? postObsOpen : true" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="space-y-0.5 mt-0.5">
                    <li>
                        <a href="{{ route('supervisor.observations.index', ['stage' => 'post_conference']) }}"
                           class="sidebar-link-hover flex items-center px-3 py-1.5 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('supervisor.observations.index') && request('stage') === 'post_conference' ? 'sidebar-link-active icon-feedback' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-feedback" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Conferences</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('supervisor.coaching.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-1.5 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('supervisor.coaching.*') ? 'sidebar-link-active icon-coaching' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-coaching" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Action Plans</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Supervisor: Reports Section -->
            <li :class="$store.sidebar.isCollapsed() ? 'mb-1' : ''">
                <button x-show="!$store.sidebar.isCollapsed()" @click="reportsOpen = !reportsOpen" class="sidebar-section-header w-full px-3 py-1 text-xs font-semibold text-indigo-400 dark:text-indigo-500 uppercase tracking-wider hover:text-indigo-600 transition-colors">
                    <span>Reports</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': reportsOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul x-show="!$store.sidebar.isCollapsed() ? reportsOpen : true" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="space-y-0.5 mt-0.5">
                    <li>
                        <a href="{{ route('supervisor.reports.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-1.5 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('supervisor.reports.*') && !request('view') ? 'sidebar-link-active icon-reports' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-reports" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V8a2 2 0 00-2-2h-1.586a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 0012.586 3H8a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Observation Reports</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('supervisor.reports.index', ['view' => 'progress']) }}"
                           class="sidebar-link-hover flex items-center px-3 py-1.5 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('supervisor.reports.index') && request('view') === 'progress' ? 'sidebar-link-active icon-analytics' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-analytics" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 18L9 11.25l4.306 4.306a11.95 11.95 0 015.814-5.518l2.74-1.22m0 0l-5.94-2.281m5.94 2.28l-2.28 5.941"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Development Progress</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Supervisor: Notifications -->
            <li>
                <a href="{{ route('notifications.index') }}"
                   class="sidebar-link-hover flex items-center px-3 py-1.5 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('notifications.*') ? 'sidebar-link-active icon-announcements' : '' }}"
                   :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                    <span class="sidebar-icon-wrap icon-announcements" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </span>
                    <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Notifications</span>
                </a>
            </li>
            @endif

            @if(auth()->user()->isSchoolHead())
            <!-- School Head: Core Supervision Section -->
            <li :class="$store.sidebar.isCollapsed() ? 'mb-1' : 'mb-1'">
                <button x-show="!$store.sidebar.isCollapsed()" @click="supervisionOpen = !supervisionOpen" class="sidebar-section-header w-full px-3 py-1 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider hover:text-gray-600 transition-colors">
                    <span>Core Supervision</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': supervisionOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul x-show="!$store.sidebar.isCollapsed() ? supervisionOpen : true" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="space-y-0.5 mt-0.5">
                    <li>
                        <a href="{{ route('school-head.teachers.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-1.5 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('school-head.teachers.*') ? 'sidebar-link-active icon-users' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-users" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Teachers</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('school-head.lesson-plans.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-1.5 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('school-head.lesson-plans.*') ? 'sidebar-link-active icon-lesson' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-lesson" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Lesson Plans</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('school-head.observations.create') }}"
                           class="sidebar-link-hover flex items-center px-3 py-1.5 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('school-head.observations.create') ? 'sidebar-link-active icon-register' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-register" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Schedule Observation</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('school-head.observations.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-1.5 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('school-head.observations.index') || request()->routeIs('school-head.observations.show') || request()->routeIs('school-head.observations.preObservationPlanning') || request()->routeIs('school-head.observations.preConference') || request()->routeIs('school-head.observations.observation') || request()->routeIs('school-head.observations.postConference') || request()->routeIs('school-head.observations.cancel*') ? 'sidebar-link-active icon-observations' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-observations" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Classroom Observations</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- School Head: AI & Insights Section -->
            <li :class="$store.sidebar.isCollapsed() ? 'mb-1' : 'mb-1'">
                <button x-show="!$store.sidebar.isCollapsed()" @click="aiInsightsOpen = !aiInsightsOpen" class="sidebar-section-header w-full px-3 py-1 text-xs font-semibold text-indigo-400 dark:text-indigo-500 uppercase tracking-wider hover:text-indigo-600 transition-colors">
                    <span>AI & Insights</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': aiInsightsOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul x-show="!$store.sidebar.isCollapsed() ? aiInsightsOpen : true" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="space-y-0.5 mt-0.5">
                    <li>
                        <a href="{{ route('school-head.feedback.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-1.5 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('school-head.feedback.*') || request()->routeIs('school-head.coaching.*') ? 'sidebar-link-active icon-feedback' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-feedback" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">AI Feedback & Coaching</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('school-head.reports.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-1.5 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('school-head.reports.*') ? 'sidebar-link-active icon-reports' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-reports" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V8a2 2 0 00-2-2h-1.586a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 0012.586 3H8a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Analytics & Reports</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- School Head: Others Section -->
            <li :class="$store.sidebar.isCollapsed() ? 'mb-1' : 'mb-1'">
                <button x-show="!$store.sidebar.isCollapsed()" @click="othersOpen = !othersOpen" class="sidebar-section-header w-full px-3 py-1 text-xs font-semibold text-indigo-400 dark:text-indigo-500 uppercase tracking-wider hover:text-indigo-600 transition-colors">
                    <span>Others</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': othersOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul x-show="!$store.sidebar.isCollapsed() ? othersOpen : true" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="space-y-0.5 mt-0.5">
                    <li>
                        <a href="{{ route('notifications.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-1.5 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('notifications.*') ? 'sidebar-link-active icon-announcements' : '' }}"
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
                        <a href="{{ route('school-head.profile.edit') }}"
                           class="sidebar-link-hover flex items-center px-3 py-1.5 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('school-head.profile.*') ? 'sidebar-link-active icon-profile' : '' }}"
                           :class="$store.sidebar.isCollapsed() ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-profile" :class="$store.sidebar.isCollapsed() ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.isCollapsed()" class="font-medium">Settings</span>
                        </a>
                    </li>
                </ul>
            </li>
            @endif
        </ul>
    </nav>

    <!-- User Profile Section - fixed at bottom -->
    <div class="mx-3 mb-2 shrink-0 px-3 py-2 rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/50" :class="$store.sidebar.isCollapsed() ? 'flex justify-center p-2' : ''">
        <div class="flex items-center" :class="$store.sidebar.isCollapsed() ? '' : 'space-x-3'">
            <div class="w-9 h-9 rounded-full flex items-center justify-center sidebar-avatar-ring bg-indigo-600 flex-shrink-0">
                <span class="text-white text-sm font-semibold">{{ substr(Auth::user()->name, 0, 1) }}</span>
            </div>
            <div x-show="!$store.sidebar.isCollapsed()" class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-800 dark:text-gray-200 truncate leading-tight">{{ Auth::user()->name }}</p>
                <p class="text-[11px] text-gray-400 dark:text-gray-500 truncate">{{ Auth::user()->email }}</p>
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
