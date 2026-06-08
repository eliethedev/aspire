<!-- Unified Sidebar for Teacher, Supervisor, School Head -->
<aside class="sidebar-glass sidebar-floating sidebar-scroll h-screen fixed left-0 top-0 z-40 transition-all duration-300 ease-sidebar overflow-y-auto" 
       x-data="@if(auth()->user()->isTeacher()) { observationsOpen: $persist(true), feedbackOpen: $persist(true), analyticsOpen: $persist(true) } @elseif(auth()->user()->isSupervisor()) { observationsOpen: $persist(false), teachersOpen: $persist(true), reportsOpen: $persist(true) } @else { systemOpen: $persist(true), reportsOpen: $persist(true) } @endif" 
       :class="$store.sidebar.collapsed ? 'w-16' : 'w-56'">

    <!-- Logo -->
    <div class="h-16 flex items-center px-5 border-b border-indigo-100/50 relative">
        <button @click="$store.sidebar.toggle()" 
                class="p-1.5 focus:outline-none hover:bg-indigo-100/50 rounded-lg transition-all duration-200 hover:scale-105" 
                :class="$store.sidebar.collapsed ? 'mx-auto' : ''" 
                title="Toggle sidebar">
            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/>
            </svg>
        </button>
        <a href="@if(auth()->user()->isTeacher()) {{ route('teacher.dashboard') }} @elseif(auth()->user()->isSupervisor()) {{ route('supervisor.dashboard') }} @else # @endif" 
           x-show="!$store.sidebar.collapsed"
           class="flex items-center space-x-2.5 ml-2">
            <span class="text-lg font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                @if(auth()->user()->isTeacher()) ASPIRE @elseif(auth()->user()->isSupervisor()) ASPIRE @else ASPIRE @endif
            </span>
        </a>
    </div>

    <!-- Navigation -->
    <nav class="mt-5 pb-4" :class="$store.sidebar.collapsed ? 'px-2' : 'px-3'">
        <ul class="space-y-1">
            <!-- Main Navigation Section -->
            <li class="mb-4">
                <div x-show="!$store.sidebar.collapsed" class="sidebar-section-header px-3 py-1.5">
                    <span class="text-xs font-semibold text-indigo-400 uppercase tracking-wider">Main</span>
                </div>
                <ul class="space-y-0.5 mt-1.5">
                    <!-- Dashboard -->
                    <li>
                        <a href="@if(auth()->user()->isTeacher()) {{ route('teacher.dashboard') }} @elseif(auth()->user()->isSupervisor()) {{ route('supervisor.dashboard') }} @else {{ route('school-head.dashboard') }} @endif"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 @if(auth()->user()->isTeacher()) {{ request()->routeIs('teacher.dashboard') ? 'sidebar-link-active' : '' }} @elseif(auth()->user()->isSupervisor()) {{ request()->routeIs('supervisor.dashboard') ? 'sidebar-link-active' : '' }} @else {{ request()->routeIs('school-head.dashboard') ? 'sidebar-link-active' : '' }} @endif"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap text-gray-400" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
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
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 @if(auth()->user()->isTeacher()) {{ request()->routeIs('teacher.profile.*') ? 'sidebar-link-active' : '' }} @elseif(auth()->user()->isSupervisor()) {{ request()->routeIs('supervisor.profile.*') ? 'sidebar-link-active' : '' }} @else {{ request()->routeIs('school-head.profile.*') ? 'sidebar-link-active' : '' }} @endif"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap text-gray-400" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
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
                <button x-show="!$store.sidebar.collapsed" @click="observationsOpen = !observationsOpen" class="sidebar-section-header w-full px-3 py-1.5 text-xs font-semibold text-indigo-400 uppercase tracking-wider hover:text-indigo-600 transition-colors">
                    <span>Observations</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': observationsOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul x-show="!$store.sidebar.collapsed ? observationsOpen : true" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="space-y-0.5 mt-1">
                    <li>
                        <a href="{{ route('teacher.observations.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 {{ request()->routeIs('teacher.observations.*') ? 'sidebar-link-active' : '' }}"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap text-gray-400" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.collapsed" class="font-medium">My Observations</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('teacher.observations.index', ['status' => 'scheduled']) }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 {{ request()->routeIs('teacher.observations.*') && request('status') === 'scheduled' ? 'sidebar-link-active' : '' }}"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap text-gray-400" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.collapsed" class="font-medium">Upcoming</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Teacher: Feedback Section -->
            <li :class="$store.sidebar.collapsed ? 'mb-1' : 'mb-3'">
                <button x-show="!$store.sidebar.collapsed" @click="feedbackOpen = !feedbackOpen" class="sidebar-section-header w-full px-3 py-1.5 text-xs font-semibold text-indigo-400 uppercase tracking-wider hover:text-indigo-600 transition-colors">
                    <span>Feedback</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': feedbackOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul x-show="!$store.sidebar.collapsed ? feedbackOpen : true" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="space-y-0.5 mt-1">
                    <li>
                        <a href="#"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap text-gray-400" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.collapsed" class="font-medium">Feedback & Coaching</span>
                        </a>
                    </li>
                    <li>
                        <a href="#"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap text-gray-400" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
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
                <button x-show="!$store.sidebar.collapsed" @click="analyticsOpen = !analyticsOpen" class="sidebar-section-header w-full px-3 py-1.5 text-xs font-semibold text-indigo-400 uppercase tracking-wider hover:text-indigo-600 transition-colors">
                    <span>Analytics</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': analyticsOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul x-show="!$store.sidebar.collapsed ? analyticsOpen : true" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="space-y-0.5 mt-1">
                    <li>
                        <a href="#"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap text-gray-400" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V8a2 2 0 00-2-2h-1.586a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 0012.586 3H8a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.collapsed" class="font-medium">Performance Analytics</span>
                        </a>
                    </li>
                    <li>
                        <a href="#"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap text-gray-400" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
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
                <button x-show="!$store.sidebar.collapsed" @click="observationsOpen = !observationsOpen" class="sidebar-section-header w-full px-3 py-1.5 text-xs font-semibold text-indigo-400 uppercase tracking-wider hover:text-indigo-600 transition-colors">
                    <span>Observations</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': observationsOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul x-show="!$store.sidebar.collapsed ? observationsOpen : true" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="space-y-0.5 mt-1">
                    <li>
                        <a href="{{ route('supervisor.observations.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 {{ request()->routeIs('supervisor.observations.*') ? 'sidebar-link-active' : '' }}"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap text-gray-400" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
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
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 {{ request()->routeIs('supervisor.observations.create') ? 'sidebar-link-active' : '' }}"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap text-gray-400" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.collapsed" class="font-medium">New Observation</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Supervisor: Teachers Section -->
            <li :class="$store.sidebar.collapsed ? 'mb-1' : 'mb-3'">
                <button x-show="!$store.sidebar.collapsed" @click="teachersOpen = !teachersOpen" class="sidebar-section-header w-full px-3 py-1.5 text-xs font-semibold text-indigo-400 uppercase tracking-wider hover:text-indigo-600 transition-colors">
                    <span>Teachers</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': teachersOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul x-show="!$store.sidebar.collapsed ? teachersOpen : true" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="space-y-0.5 mt-1">
                    <li>
                        <a href="{{ route('supervisor.teachers.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 {{ request()->routeIs('supervisor.teachers.*') ? 'sidebar-link-active' : '' }}"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap text-gray-400" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.collapsed" class="font-medium">Teacher Management</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Supervisor: Reports Section -->
            <li :class="$store.sidebar.collapsed ? 'mb-1' : ''">
                <button x-show="!$store.sidebar.collapsed" @click="reportsOpen = !reportsOpen" class="sidebar-section-header w-full px-3 py-1.5 text-xs font-semibold text-indigo-400 uppercase tracking-wider hover:text-indigo-600 transition-colors">
                    <span>Reports</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': reportsOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul x-show="!$store.sidebar.collapsed ? reportsOpen : true" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="space-y-0.5 mt-1">
                    <li>
                        <a href="{{ route('supervisor.reports.index') }}"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600 {{ request()->routeIs('supervisor.reports.*') ? 'sidebar-link-active' : '' }}"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap text-gray-400" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V8a2 2 0 00-2-2h-1.586a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 0012.586 3H8a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.collapsed" class="font-medium">Reports & Analytics</span>
                        </a>
                    </li>
                    <li>
                        <a href="#"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap text-gray-400" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
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
            <!-- School Head: System Section -->
            <li :class="$store.sidebar.collapsed ? 'mb-1' : 'mb-3'">
                <button x-show="!$store.sidebar.collapsed" @click="systemOpen = !systemOpen" class="sidebar-section-header w-full px-3 py-1.5 text-xs font-semibold text-indigo-400 uppercase tracking-wider hover:text-indigo-600 transition-colors">
                    <span>System</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': systemOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul x-show="!$store.sidebar.collapsed ? systemOpen : true" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="space-y-0.5 mt-1">
                    <li>
                        <a href="#"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap text-gray-400" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.collapsed" class="font-medium">System Overview</span>
                        </a>
                    </li>
                    <li>
                        <a href="#"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap text-gray-400" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.collapsed" class="font-medium">Supervisor Management</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- School Head: Reports Section -->
            <li :class="$store.sidebar.collapsed ? 'mb-1' : ''">
                <button x-show="!$store.sidebar.collapsed" @click="reportsOpen = !reportsOpen" class="sidebar-section-header w-full px-3 py-1.5 text-xs font-semibold text-indigo-400 uppercase tracking-wider hover:text-indigo-600 transition-colors">
                    <span>Reports</span>
                    <svg class="w-3.5 h-3.5 transition-transform duration-300 ease-sidebar" :class="{ 'rotate-180': reportsOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul x-show="!$store.sidebar.collapsed ? reportsOpen : true" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1" class="space-y-0.5 mt-1">
                    <li>
                        <a href="#"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap text-gray-400" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.collapsed" class="font-medium">Institution Reports</span>
                        </a>
                    </li>
                    <li>
                        <a href="#"
                           class="sidebar-link-hover flex items-center px-3 py-2 rounded-xl text-sm text-gray-600"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <span class="sidebar-icon-wrap text-gray-400" :class="$store.sidebar.collapsed ? '' : 'mr-3'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V8a2 2 0 00-2-2h-1.586a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 0012.586 3H8a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            <span x-show="!$store.sidebar.collapsed" class="font-medium">Analytics & Insights</span>
                        </a>
                    </li>
                </ul>
            </li>
            @endif
        </ul>
    </nav>

    <!-- User Profile Section -->
    <div class="mx-3 mb-3 mt-auto px-3 py-3 rounded-2xl bg-white/60 border border-indigo-50/50" :class="$store.sidebar.collapsed ? 'flex justify-center p-2' : ''">
        <div class="flex items-center" :class="$store.sidebar.collapsed ? '' : 'space-x-3'">
            <div class="w-9 h-9 rounded-full flex items-center justify-center sidebar-avatar-ring bg-gradient-to-br from-indigo-500 to-purple-500 flex-shrink-0">
                <span class="text-white text-sm font-semibold">{{ substr(Auth::user()->name, 0, 1) }}</span>
            </div>
            <div x-show="!$store.sidebar.collapsed" class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-800 truncate leading-tight">{{ Auth::user()->name }}</p>
                <p class="text-xs text-gray-400 truncate">{{ Auth::user()->email }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}" x-show="!$store.sidebar.collapsed">
                @csrf
                <button type="submit" class="p-1.5 text-gray-300 hover:text-red-400 hover:bg-red-50 rounded-lg transition-all duration-200" title="Logout">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>
