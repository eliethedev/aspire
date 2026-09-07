<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'ASPIRE') }} - {{ $heading ?? 'Error' }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        (function(){ if(localStorage.getItem('theme')==='dark') document.documentElement.classList.add('dark'); })();
    </script>

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
        .dark .light-bg { background-color: #030712; }
        .dark .glass-card {
            background: #111827;
            border-color: rgba(255, 255, 255, 0.08);
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.4);
        }
        .dark .btn-secondary {
            background-color: #1f2937;
            color: #e5e7eb;
            border-color: #374151;
        }
        .dark .btn-secondary:hover { background-color: #374151; border-color: #4b5563; }
    </style>
</head>
<body class="antialiased font-sans light-bg min-h-screen flex flex-col">

    <nav class="w-full px-4 sm:px-6 py-3 sm:py-4 flex items-center justify-between">
        <a href="{{ route('home') }}" class="flex items-center gap-2 text-blue-600 dark:text-blue-400 font-bold text-lg sm:text-xl tracking-tight">
            ASPIRE
        </a>
        <span class="text-xs sm:text-sm text-gray-400 dark:text-gray-500 font-medium">
            {{ $heading ?? 'Error' }}
        </span>
    </nav>

    <main class="flex-1 flex items-center justify-center p-4">
        <div class="w-full max-w-md glass-card rounded-2xl p-8 sm:p-10 text-center">

            <div class="float-icon inline-flex items-center justify-center w-20 h-20 rounded-2xl {{ $iconBg ?? 'bg-blue-50 dark:bg-blue-500/10 border border-blue-100 dark:border-blue-500/20' }} mb-6">
                {!! $icon ?? '' !!}
            </div>

            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-gray-100 mb-3 tracking-tight">
                {{ $title ?? 'Error' }}
            </h1>

            <p class="text-gray-500 dark:text-gray-400 text-sm sm:text-base leading-relaxed mb-8">
                {{ $message ?? 'Something went wrong.' }}
            </p>

            @yield('actions')

        </div>
    </main>

    <footer class="text-center py-6 text-xs text-gray-400 dark:text-gray-500">
        {{ config('app.name', 'ASPIRE') }} &copy; {{ date('Y') }}
    </footer>
</body>
</html>
