@php
    // Accept values passed either as view data (second arg of response()->view)
    // or as Blade sections defined by the child error page.
    $title = $title ?? trim($__env->yieldContent('title')) ?: 'Error';
    $heading = $heading ?? trim($__env->yieldContent('heading')) ?: 'Error';
    $message = $message ?? trim($__env->yieldContent('message')) ?: 'Something went wrong.';
    $iconBg = $iconBg ?? trim($__env->yieldContent('iconBg')) ?: 'bg-blue-50 border border-blue-100';
    $icon = $icon ?? $__env->yieldContent('icon');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} - {{ $heading }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .light-bg { background-color: #f0f5ff; }
        .glass-card {
            background: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.08);
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
        }
        .btn-primary {
            background-color: #2563eb;
            color: white;
            transition: all 0.2s ease;
        }
        .btn-primary:hover { background-color: #1d4ed8; }
        .btn-secondary {
            background-color: #ffffff;
            color: #374151;
            border: 1px solid #d1d5db;
            transition: all 0.2s ease;
        }
        .btn-secondary:hover { background-color: #f9fafb; border-color: #9ca3af; }
        @keyframes float-icon {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }
        .float-icon { animation: float-icon 3s ease-in-out infinite; }
    </style>
</head>
<body class="antialiased font-sans light-bg min-h-screen flex flex-col">

    <nav class="w-full px-4 sm:px-4 py-3 sm:py-4 flex items-center justify-between">
        <a href="{{ route('home') }}" class="flex items-center gap-2 text-blue-600 font-bold text-lg sm:text-xl tracking-tight">
            ASPIRE
        </a>
        <span class="text-xs sm:text-sm text-gray-400 font-medium">
            {{ $heading }}
        </span>
    </nav>

    <main class="flex-1 flex items-center justify-center p-4">
        <div class="w-full max-w-md glass-card rounded-2xl p-8 sm:p-10 text-center">
            <div class="float-icon inline-flex items-center justify-center w-20 h-20 rounded-2xl {{ $iconBg }} mb-6">
                {!! $icon !!}
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 mb-3 tracking-tight">
                {{ $title }}
            </h1>
            <p class="text-gray-500 text-sm sm:text-base leading-relaxed mb-8">
                {{ $message }}
            </p>
            @yield('actions')
            @stack('actions')
        </div>
    </main>

    <footer class="text-center py-6 text-xs text-gray-400">
        {{ config('app.name', 'ASPIRE') }} &copy; {{ date('Y') }}
    </footer>
</body>
</html>
