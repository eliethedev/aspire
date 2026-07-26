<!-- Unified Sidebar for Teacher, Supervisor, School Head -->
<aside class="sidebar-glass h-screen fixed left-0 top-0 z-40 transition-all duration-300 ease-sidebar flex flex-col overflow-hidden" 
       x-data="@if(auth()->user()->isTeacher()) { observationsOpen: $persist(true), feedbackOpen: $persist(true), analyticsOpen: $persist(true) } @elseif(auth()->user()->isSupervisor()) { observationsOpen: $persist(false), feedbackOpen: $persist(true), teachersOpen: $persist(true), reportsOpen: $persist(true) } @else { supervisionOpen: $persist(true), aiInsightsOpen: $persist(true), othersOpen: $persist(false) } @endif" 
       :class="$store.sidebar.collapsed ? 'w-16' : 'w-56'">

    <!-- Logo - fixed at top -->
    <div class="h-16 flex items-center px-5 border-b border-gray-100/80 dark:border-gray-800/80 shrink-0">
        <a href="@if(auth()->user()->isTeacher()) {{ route('teacher.dashboard') }} @elseif(auth()->user()->isSupervisor()) {{ route('supervisor.dashboard') }} @else {{ route('school-head.dashboard') }} @endif" 
           :class="$store.sidebar.collapsed ? 'mx-auto' : ''"
           class="flex items-center space-x-2.5">
           <span class="text-lg font-bold text-indigo-600 tracking-tight">
                ASPIRE
            </span>
        </a>
    </div>

    <!-- Navigation - scrollable -->
    <nav class="flex-1 overflow-y-auto sidebar-scroll mt-5 pb-4" :class="$store.sidebar.collapsed ? 'px-2' : 'px-3'">
        <ul class="space-y-1">
            <!-- Main Navigation Section -->
            <li class="mb-4">
                <div x-show="!$store.sidebar.collapsed" class="sidebar-section-header px-3 py-1.5">
                    <span class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Main</span>
                </div>
                <ul class="space-y-0.5 mt-1.5">
                    <!-- Dashboard -->
                    <li>
                        <a href="@if(auth()->user()->isTeacher()) {{ route('teacher.dashboard') }} @elseif(auth()->user()->isSupervisor()) {{ route('supervisor.dashboard') }} @else {{ route('school-head.dashboard') }} @endif"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 dark:text-gray-400 @if(auth()->user()->isTeacher()) {{ request()->routeIs('teacher.dashboard') ? 'sidebar-link-active icon-dashboard' : '' }} @elseif(auth()->user()->isSupervisor()) {{ request()->routeIs('supervisor.dashboard') ? 'sidebar-link-active icon-dashboard' : '' }} @else {{ request()->routeIs('school-head.dashboard') ? 'sidebar-link-active icon-dashboard' : '' }} @endif"
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
                        <a href="@if(auth()->user()->isTeacher()) {{ route('teacher.profile.edit') }} @elseif(auth()->user()->isSupervisor()) {{ route('supervisor.profile.edit') }} @else {{ route('school-head.profile.edit') }} @endif"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 @if(auth()->user()->isTeacher()) {{ request()->routeIs('teacher.profile.*') ? 'sidebar-link-active icon-profile' : '' }} @elseif(auth()->user()->isSupervisor()) {{ request()->routeIs('supervisor.profile.*') ? 'sidebar-link-active icon-profile' : '' }} @else {{ request()->routeIs('school-head.profile.*') ? 'sidebar-link-active icon-profile' : '' }} @endif"
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

            @if(auth()->user()->isTeacher())
            <!-- Teacher: Observations Section -->
            <li :class="$store.sidebar.collapsed ? 'mb-1' : 'mb-3'">
                <button x-show="!$store.sidebar.collapsed" @click="observationsOpen = !observationsOpen" class="sidebar-section-header w-full px-3 py-1.5 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider hover:text-gray-600 transition-colors">
                    <span>Observations</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': observationsOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul x-show="!$store.sidebar.collapsed ? observationsOpen : true" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="space-y-0.5 mt-1">
                    <li>
                        <a href="{{ route('teacher.observations.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('teacher.observations.*') ? 'sidebar-link-active icon-observations' : '' }}"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-observations" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.collapsed" class="font-medium">My Observations</span>
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
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('teacher.observations.*') && request('status') === 'scheduled' ? 'sidebar-link-active icon-calendar' : '' }}"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-calendar relative" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                @if($pendingConfirmationCount > 0)
                                <span class="absolute -top-1.5 -right-1.5 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white"></span>
                                @endif
                            </span>
                            <span x-show="!$store.sidebar.collapsed" class="font-medium">Upcoming</span>
                            @if($pendingConfirmationCount > 0)
                            <span x-show="!$store.sidebar.collapsed" class="ml-auto inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-red-500 rounded-full">{{ $pendingConfirmationCount }}</span>
                            @endif
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Teacher: Feedback Section -->
            <li :class="$store.sidebar.collapsed ? 'mb-1' : 'mb-3'">
                <button x-show="!$store.sidebar.collapsed" @click="feedbackOpen = !feedbackOpen" class="sidebar-section-header w-full px-3 py-1.5 text-xs font-semibold text-indigo-400 dark:text-indigo-500 uppercase tracking-wider hover:text-indigo-600 transition-colors">
                    <span>Feedback</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': feedbackOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul x-show="!$store.sidebar.collapsed ? feedbackOpen : true" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="space-y-0.5 mt-1">
                    <li>
                        <a href="{{ route('teacher.feedback.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('teacher.feedback.*') ? 'sidebar-link-active icon-feedback' : '' }}"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-feedback" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.collapsed" class="font-medium">Feedback & Coaching</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('teacher.coaching.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('teacher.coaching.*') ? 'sidebar-link-active icon-coaching' : '' }}"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-coaching" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.collapsed" class="font-medium">Improvement Plan</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Teacher: Analytics Section -->
            <li :class="$store.sidebar.collapsed ? 'mb-1' : ''">
                <button x-show="!$store.sidebar.collapsed" @click="analyticsOpen = !analyticsOpen" class="sidebar-section-header w-full px-3 py-1.5 text-xs font-semibold text-indigo-400 dark:text-indigo-500 uppercase tracking-wider hover:text-indigo-600 transition-colors">
                    <span>Analytics</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': analyticsOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul x-show="!$store.sidebar.collapsed ? analyticsOpen : true" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="space-y-0.5 mt-1">
                    <li>
                        <a href="#"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-analytics" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V8a2 2 0 00-2-2h-1.586a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 0012.586 3H8a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.collapsed" class="font-medium">Performance Analytics</span>
                        </a>
                    </li>
                    <li>
                        <a href="#"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-reports" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.collapsed" class="font-medium">Reports</span>
                        </a>
                    </li>
                </ul>
            </li>
            @endif

            @if(auth()->user()->isSupervisor())
            <!-- Supervisor: Observations Section -->
            <li :class="$store.sidebar.collapsed ? 'mb-1' : 'mb-3'">
                <button x-show="!$store.sidebar.collapsed" @click="observationsOpen = !observationsOpen" class="sidebar-section-header w-full px-3 py-1.5 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider hover:text-gray-600 transition-colors">
                    <span>Observations</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': observationsOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul x-show="!$store.sidebar.collapsed ? observationsOpen : true" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="space-y-0.5 mt-1">
                    <li>
                        <a href="{{ route('supervisor.observations.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('supervisor.observations.*') ? 'sidebar-link-active icon-observations' : '' }}"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-observations" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.collapsed" class="font-medium">My Evaluations</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('supervisor.observations.create') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('supervisor.observations.create') ? 'sidebar-link-active icon-register' : '' }}"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-register" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.collapsed" class="font-medium">New Observation</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Supervisor: Feedback Center -->
            <li :class="$store.sidebar.collapsed ? 'mb-1' : 'mb-3'">
                <button x-show="!$store.sidebar.collapsed" @click="feedbackOpen = !feedbackOpen" class="sidebar-section-header w-full px-3 py-1.5 text-xs font-semibold text-indigo-400 dark:text-indigo-500 uppercase tracking-wider hover:text-indigo-600 transition-colors">
                    <span>Feedback</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': feedbackOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul x-show="!$store.sidebar.collapsed ? feedbackOpen : true" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="space-y-0.5 mt-1">
                    <li>
                        <a href="{{ route('supervisor.feedback.center') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('supervisor.feedback.center') ? 'sidebar-link-active icon-feedback' : '' }}"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-feedback" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.collapsed" class="font-medium">Feedback Center</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('supervisor.coaching.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('supervisor.coaching.*') ? 'sidebar-link-active icon-coaching' : '' }}"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-coaching" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.collapsed" class="font-medium">Coaching Agreements</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Supervisor: People Section -->
            <li :class="$store.sidebar.collapsed ? 'mb-1' : 'mb-3'">
                <button x-show="!$store.sidebar.collapsed" @click="teachersOpen = !teachersOpen" class="sidebar-section-header w-full px-3 py-1.5 text-xs font-semibold text-indigo-400 dark:text-indigo-500 uppercase tracking-wider hover:text-indigo-600 transition-colors">
                    <span>People</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': teachersOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul x-show="!$store.sidebar.collapsed ? teachersOpen : true" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="space-y-0.5 mt-1">
                    <li>
                        <a href="{{ route('supervisor.teachers.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('supervisor.teachers.*') ? 'sidebar-link-active icon-users' : '' }}"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-users" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.collapsed" class="font-medium">Teachers List</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('supervisor.school-heads.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('supervisor.school-heads.*') ? 'sidebar-link-active icon-schools' : '' }}"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-schools" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.collapsed" class="font-medium">School Heads</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Supervisor: Reports Section -->
            <li :class="$store.sidebar.collapsed ? 'mb-1' : ''">
                <button x-show="!$store.sidebar.collapsed" @click="reportsOpen = !reportsOpen" class="sidebar-section-header w-full px-3 py-1.5 text-xs font-semibold text-indigo-400 dark:text-indigo-500 uppercase tracking-wider hover:text-indigo-600 transition-colors">
                    <span>Reports</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': reportsOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul x-show="!$store.sidebar.collapsed ? reportsOpen : true" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="space-y-0.5 mt-1">
                    <li>
                        <a href="{{ route('supervisor.reports.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('supervisor.reports.*') ? 'sidebar-link-active icon-reports' : '' }}"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-reports" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V8a2 2 0 00-2-2h-1.586a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 0012.586 3H8a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.collapsed" class="font-medium">Reports & Analytics</span>
                        </a>
                    </li>
                    <li>
                        <a href="#"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-feedback" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.collapsed" class="font-medium">Feedback Management</span>
                        </a>
                    </li>
                </ul>
            </li>
            @endif

            @if(auth()->user()->isSchoolHead())
            <!-- School Head: Core Supervision Section -->
            <li :class="$store.sidebar.collapsed ? 'mb-1' : 'mb-3'">
                <button x-show="!$store.sidebar.collapsed" @click="supervisionOpen = !supervisionOpen" class="sidebar-section-header w-full px-3 py-1.5 text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider hover:text-gray-600 transition-colors">
                    <span>Core Supervision</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': supervisionOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul x-show="!$store.sidebar.collapsed ? supervisionOpen : true" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="space-y-0.5 mt-1">
                    <li>
                        <a href="{{ route('school-head.teachers.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('school-head.teachers.*') ? 'sidebar-link-active icon-users' : '' }}"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-users" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.collapsed" class="font-medium">Teachers</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('school-head.lesson-plans.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('school-head.lesson-plans.*') ? 'sidebar-link-active icon-lesson' : '' }}"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-lesson" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.collapsed" class="font-medium">Lesson Plans</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('school-head.observations.create') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('school-head.observations.create') ? 'sidebar-link-active icon-register' : '' }}"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-register" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.collapsed" class="font-medium">Schedule Observation</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('school-head.observations.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('school-head.observations.index') || request()->routeIs('school-head.observations.show') || request()->routeIs('school-head.observations.preObservationPlanning') || request()->routeIs('school-head.observations.preConference') || request()->routeIs('school-head.observations.observation') || request()->routeIs('school-head.observations.postConference') || request()->routeIs('school-head.observations.cancel*') ? 'sidebar-link-active icon-observations' : '' }}"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-observations" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.collapsed" class="font-medium">Classroom Observations</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- School Head: AI & Insights Section -->
            <li :class="$store.sidebar.collapsed ? 'mb-1' : 'mb-3'">
                <button x-show="!$store.sidebar.collapsed" @click="aiInsightsOpen = !aiInsightsOpen" class="sidebar-section-header w-full px-3 py-1.5 text-xs font-semibold text-indigo-400 dark:text-indigo-500 uppercase tracking-wider hover:text-indigo-600 transition-colors">
                    <span>AI & Insights</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': aiInsightsOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul x-show="!$store.sidebar.collapsed ? aiInsightsOpen : true" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="space-y-0.5 mt-1">
                    <li>
                        <a href="{{ route('school-head.feedback.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('school-head.feedback.*') || request()->routeIs('school-head.coaching.*') ? 'sidebar-link-active icon-feedback' : '' }}"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-feedback" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.collapsed" class="font-medium">AI Feedback & Coaching</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('school-head.reports.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('school-head.reports.*') ? 'sidebar-link-active icon-reports' : '' }}"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-reports" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V8a2 2 0 00-2-2h-1.586a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 0012.586 3H8a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.collapsed" class="font-medium">Analytics & Reports</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- School Head: Others Section -->
            <li :class="$store.sidebar.collapsed ? 'mb-1' : 'mb-3'">
                <button x-show="!$store.sidebar.collapsed" @click="othersOpen = !othersOpen" class="sidebar-section-header w-full px-3 py-1.5 text-xs font-semibold text-indigo-400 dark:text-indigo-500 uppercase tracking-wider hover:text-indigo-600 transition-colors">
                    <span>Others</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': othersOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul x-show="!$store.sidebar.collapsed ? othersOpen : true" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="space-y-0.5 mt-1">
                    <li>
                        <a href="{{ route('notifications.show', 'school_head') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('notifications.*') ? 'sidebar-link-active icon-announcements' : '' }}"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-announcements" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.collapsed" class="font-medium">Notifications</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('school-head.profile.edit') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 dark:text-gray-400 {{ request()->routeIs('school-head.profile.*') ? 'sidebar-link-active icon-profile' : '' }}"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap icon-profile" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.collapsed" class="font-medium">Settings</span>
                        </a>
                    </li>
                </ul>
            </li>
            @endif
        </ul>
    </nav>

    <!-- User Profile Section - fixed at bottom -->
    <div class="mx-3 mb-3 shrink-0 px-3 py-3 rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/50" :class="$store.sidebar.collapsed ? 'flex justify-center p-2' : ''">
        <div class="flex items-center" :class="$store.sidebar.collapsed ? '' : 'space-x-3'">
            <div class="w-9 h-9 rounded-full flex items-center justify-center sidebar-avatar-ring bg-indigo-600 flex-shrink-0">
                <span class="text-white text-sm font-semibold">{{ substr(Auth::user()->name, 0, 1) }}</span>
            </div>
            <div x-show="!$store.sidebar.collapsed" class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-800 dark:text-gray-200 truncate leading-tight">{{ Auth::user()->name }}</p>
                <p class="text-[11px] text-gray-400 dark:text-gray-500 truncate">{{ Auth::user()->email }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}" x-show="!$store.sidebar.collapsed">
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
