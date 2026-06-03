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
        @vite(['resources/css/app.css', 'resources/js/app.tsx'])
    </head>
    <body class="font-sans antialiased" x-data="{ sidebarOpen: true, isHovering: false }">
        <div class="min-h-screen bg-white" :class="{ 'sidebar-closed': !sidebarOpen }">
            @include('layouts.navigation')
            @include('layouts.header')

            <!-- Page Heading -->
            @isset($header)
                <header class="glass-card border-b border-gray-200" :class="{ 'sidebar-closed': !sidebarOpen }">
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
    </body>
</html>
