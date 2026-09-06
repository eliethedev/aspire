<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} - @yield('title', 'Teacher')</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/persist@3.13.3/dist/cdn.min.js"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
        <script>
            // Apply dark mode immediately to prevent flash
            (function() {
                const theme = localStorage.getItem('theme');
                if (theme === 'dark') {
                    document.documentElement.classList.add('dark');
                }
                if (localStorage.getItem('app_text_large') === '1') {
                    document.documentElement.classList.add('text-large');
                }
            })();
        </script>
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.store('sidebar', {
                    collapsed: localStorage.getItem('sidebar_collapsed') === 'true',
                    mobileOpen: false,
                    isCollapsed() {
                        return window.innerWidth >= 1024 ? this.collapsed : false;
                    },
                    toggle() {
                        this.collapsed = !this.collapsed;
                        localStorage.setItem('sidebar_collapsed', this.collapsed);
                    },
                    openMobile() {
                        this.mobileOpen = true;
                    },
                    closeMobile() {
                        this.mobileOpen = false;
                    }
                });
                Alpine.store('theme', {
                    dark: document.documentElement.classList.contains('dark'),
                    toggle() {
                        this.dark = !this.dark;
                        document.documentElement.classList.toggle('dark', this.dark);
                        localStorage.setItem('theme', this.dark ? 'dark' : 'light');
                    }
                });
                Alpine.store('accessibility', {
                    large: localStorage.getItem('app_text_large') === '1',
                    toggle() {
                        this.large = !this.large;
                        document.documentElement.classList.toggle('text-large', this.large);
                        localStorage.setItem('app_text_large', this.large ? '1' : '0');
                    },
                    setLarge(v) {
                        this.large = v;
                        document.documentElement.classList.toggle('text-large', v);
                        localStorage.setItem('app_text_large', v ? '1' : '0');
                    }
                });
            });
        </script>
        @stack('styles')
    </head>
    <body class="font-sans antialiased bg-gray-50 dark:bg-gray-950 transition-colors" x-data>
        <x-flash-messages />
        <a href="#main-content" class="skip-link">Skip to main content</a>
        <div class="flex min-h-screen">
            <!-- Sidebar -->
            @include('partials.sidebar')

            <!-- Mobile backdrop -->
            <div x-show="$store.sidebar.mobileOpen" x-cloak @click="$store.sidebar.closeMobile()" class="fixed inset-0 z-[60] bg-slate-900/50 backdrop-blur-sm lg:hidden" aria-hidden="true"></div>

            <!-- Main Content -->
            <div class="flex-1 min-w-0 pt-16 transition-all duration-300 ease-sidebar" :class="$store.sidebar.collapsed ? 'lg:ml-16' : 'lg:ml-56'">
                <!-- Header -->
                @include('layouts.header')
                
                <!-- Page Heading -->
                @isset($header)
                    <header class="bg-white dark:bg-gray-900 border-b border-gray-100 dark:border-gray-800">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <!-- Page Content -->
                <main id="main-content" class="p-4 sm:p-6">
                    <div class="mx-auto">
                        @yield('content')
                    </div>
                </main>
            </div>
        </div>
        @stack('scripts')
    </body>
</html>
