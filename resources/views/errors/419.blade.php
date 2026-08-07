<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'ASPIRE') }} - Session Expired</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        .light-bg {
            background-color: #f0f5ff;
        }

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

        .btn-primary:hover {
            background-color: #1d4ed8;
        }

        .btn-secondary {
            background-color: #ffffff;
            color: #374151;
            border: 1px solid #d1d5db;
            transition: all 0.2s ease;
        }

        .btn-secondary:hover {
            background-color: #f9fafb;
            border-color: #9ca3af;
        }

        @keyframes float-icon {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }

        .float-icon {
            animation: float-icon 3s ease-in-out infinite;
        }
    </style>
</head>
<body class="antialiased font-sans light-bg min-h-screen flex flex-col">

    <!-- Top Nav -->
    <nav class="w-full px-4 sm:px-6 py-3 sm:py-4 flex items-center justify-between">
        <a href="{{ route('home') }}" class="flex items-center gap-2 text-blue-600 font-bold text-lg sm:text-xl tracking-tight">
            ASPIRE
        </a>
        <span class="text-xs sm:text-sm text-gray-400 font-medium">
            Session Expired
        </span>
    </nav>

    <!-- Main Content -->
    <main class="flex-1 flex items-center justify-center p-4">
        <div class="w-full max-w-md glass-card rounded-2xl p-8 sm:p-10 text-center">

            <!-- Floating Icon -->
            <div class="float-icon inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-blue-50 border border-blue-100 mb-6">
                <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>

            <h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 mb-3 tracking-tight">
                Your Session Has Expired
            </h1>

            <p class="text-gray-500 text-sm sm:text-base leading-relaxed mb-8">
                For your security, your session ended after a period of inactivity or after a password change.
                Please sign in again to continue using ASPIRE.
            </p>

            <div class="space-y-3">
                <a href="{{ route('login') }}" class="btn-primary w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl font-semibold text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    Go to Login
                </a>

                <button type="button" onclick="window.location.reload()" class="btn-secondary w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl font-semibold text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Refresh Page
                </button>
            </div>

            <div class="mt-8">
                <a href="{{ route('home') }}" class="inline-flex items-center text-sm text-gray-400 hover:text-blue-600 transition-colors group">
                    <svg class="w-4 h-4 mr-2 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Homepage
                </a>
            </div>
        </div>
    </main>

    <footer class="text-center py-6 text-xs text-gray-400">
        {{ config('app.name', 'ASPIRE') }} &copy; {{ date('Y') }} &middot; Keep your account secure
    </footer>
</body>
</html>
