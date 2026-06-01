<!-- Teacher Sidebar -->
<aside class="bg-white shadow-sm border-r border-gray-200 h-screen fixed left-0 top-0 z-40 transition-all duration-300 overflow-y-auto" x-data="{ observationsOpen: true, feedbackOpen: true, analyticsOpen: true }" :class="$store.sidebar.collapsed ? 'w-16' : 'w-56'">
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
        <button @click="$store.sidebar.toggle(); if($store.sidebar.collapsed) { observationsOpen = false; feedbackOpen = false; analyticsOpen = false; }" class="p-2 focus:outline-none hover:bg-gray-100 rounded-lg transition-colors" :class="$store.sidebar.collapsed ? 'absolute right-2' : ''" title="Toggle sidebar">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
        </button>
        <a href="{{ route('teacher.dashboard') }}" class="flex items-center space-x-2">
            <span x-show="!$store.sidebar.collapsed" class="font-bold text-gray-900">ASPIRE Teacher</span>
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
                        <a href="{{ route('teacher.dashboard') }}"
                           class="flex items-center px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors {{ request()->routeIs('teacher.dashboard') ? 'bg-indigo-50 text-indigo-700' : '' }}"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <svg class="w-5 h-5" :class="$store.sidebar.collapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            <span x-show="!$store.sidebar.collapsed">Dashboard</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Observations Section -->
            <li class="mb-4" x-show="!$store.sidebar.collapsed">
                <button @click="observationsOpen = !observationsOpen" class="w-full flex items-center justify-between px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider hover:text-gray-700 transition-colors">
                    <span>Observations</span>
                    <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': observationsOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Observations Links (Collapsible) -->
                <ul x-show="observationsOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="space-y-1 mt-1">
                    <!-- My Observations -->
                    <li>
                        <a href="#"
                           class="flex items-center px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <svg class="w-5 h-5" :class="$store.sidebar.collapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <span x-show="!$store.sidebar.collapsed">My Observations</span>
                        </a>
                    </li>

                    <!-- Upcoming Observations -->
                    <li>
                        <a href="#"
                           class="flex items-center px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <svg class="w-5 h-5" :class="$store.sidebar.collapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span x-show="!$store.sidebar.collapsed">Upcoming</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Feedback Section -->
            <li class="mb-4" x-show="!$store.sidebar.collapsed">
                <button @click="feedbackOpen = !feedbackOpen" class="w-full flex items-center justify-between px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider hover:text-gray-700 transition-colors">
                    <span>Feedback</span>
                    <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': feedbackOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Feedback Links (Collapsible) -->
                <ul x-show="feedbackOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="space-y-1 mt-1">
                    <!-- Feedback & Coaching -->
                    <li>
                        <a href="#"
                           class="flex items-center px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <svg class="w-5 h-5" :class="$store.sidebar.collapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            <span x-show="!$store.sidebar.collapsed">Feedback & Coaching</span>
                        </a>
                    </li>

                    <!-- Improvement Plan -->
                    <li>
                        <a href="#"
                           class="flex items-center px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <svg class="w-5 h-5" :class="$store.sidebar.collapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span x-show="!$store.sidebar.collapsed">Improvement Plan</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Analytics Section -->
            <li x-show="!$store.sidebar.collapsed">
                <button @click="analyticsOpen = !analyticsOpen" class="w-full flex items-center justify-between px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider hover:text-gray-700 transition-colors">
                    <span>Analytics</span>
                    <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': analyticsOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Analytics Links (Collapsible) -->
                <ul x-show="analyticsOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="space-y-1 mt-1">
                    <!-- Performance Analytics -->
                    <li>
                        <a href="#"
                           class="flex items-center px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <svg class="w-5 h-5" :class="$store.sidebar.collapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V8a2 2 0 00-2-2h-1.586a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 0012.586 3H8a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span x-show="!$store.sidebar.collapsed">Performance Analytics</span>
                        </a>
                    </li>

                    <!-- Reports -->
                    <li>
                        <a href="#"
                           class="flex items-center px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors"
                           :class="$store.sidebar.collapsed ? 'justify-center px-2' : ''">
                            <svg class="w-5 h-5" :class="$store.sidebar.collapsed ? '' : 'mr-3'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span x-show="!$store.sidebar.collapsed">Reports</span>
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
