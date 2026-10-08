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
        {{-- Vendored locally (public/js/vendor) so pages keep working offline — same pinned 3.13.3 builds as the former CDN tags. --}}
        <script defer src="{{ asset('js/vendor/alpine-persist.min.js') }}"></script>
        <script defer src="{{ asset('js/vendor/alpine.min.js') }}"></script>
        <script>
            (function() {
                if (localStorage.getItem('theme') === 'dark') {
                    document.documentElement.classList.add('dark');
                }

            })();
        </script>
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.store('sidebar', {
                    collapsed: localStorage.getItem('sidebar_collapsed') === 'true',
                    mobileOpen: false,
                    isCollapsed() {
                        return window.innerWidth >= 768 ? this.collapsed : false;
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
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-4 lg:px-4">
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
