<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Aspire') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/persist@3.13.3/dist/cdn.min.js"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
        <script>
            (function() {
                if (localStorage.getItem('theme') === 'dark') {
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
    <body class="font-sans antialiased" x-data="{ sidebarOpen: true, isHovering: false }">
        <div class="min-h-screen bg-white dark:bg-gray-950" :class="{ 'sidebar-closed': !sidebarOpen }">
            @include('layouts.navigation')
            @include('layouts.header')

            <!-- Page Heading -->
            @isset($header)
                <header class="glass-card border-b border-gray-200 dark:border-gray-800" :class="{ 'sidebar-closed': !sidebarOpen }">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="flex" :class="{ 'sidebar-closed': !sidebarOpen }">
                @yield('content')
            </main>
        </div>
        @stack('scripts')
    </body>
</html>
