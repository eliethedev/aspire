<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'ASPIRE') }} - Verify Email</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        (function() {
            if (localStorage.getItem('theme') === 'dark') {
                document.documentElement.classList.add('dark');
            }
            if (localStorage.getItem('app_text_large') === '1') {
                document.documentElement.classList.add('text-large');
            }
        })();
        function toggleTheme() {
            var root = document.documentElement;
            root.classList.toggle('dark');
            localStorage.setItem('theme', root.classList.contains('dark') ? 'dark' : 'light');
        }
    </script>

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

        .btn-primary:active {
            background-color: #1e40af;
        }

        @media (max-width: 380px) {
            .card-padding {
                padding: 1.25rem;
            }
        }

        .dark .light-bg {
            background-color: #030712;
        }

        .dark .glass-card {
            background-color: #111827;
            border-color: rgba(255, 255, 255, 0.08);
        }
    </style>
</head>
<body class="antialiased font-sans light-bg min-h-screen flex flex-col">

    <!-- Top Nav -->
    <nav class="w-full px-4 sm:px-6 py-3 sm:py-4 flex items-center justify-between">
        <a href="{{ route('home') }}" class="flex items-center gap-2 text-blue-600 dark:text-blue-400 font-bold text-lg sm:text-xl tracking-tight">
            ASPIRE
        </a>
        <div class="flex items-center gap-4">
            <button type="button" onclick="toggleTheme()" title="Toggle dark mode" aria-label="Toggle dark mode" class="text-xs sm:text-sm text-gray-500 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors font-medium">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
            </button>
            <a href="{{ route('home') }}" class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors font-medium">
                Homepage
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-1 flex items-center justify-center p-3 sm:p-4">
    <div class="w-full max-w-md glass-card rounded-2xl p-6 sm:p-8 md:p-10 card-padding">

        <!-- Header Section -->
        <div class="text-center mb-6 sm:mb-8">
            <div class="inline-flex items-center justify-center w-12 h-12 sm:w-16 sm:h-16 rounded-full bg-blue-100 dark:bg-blue-500/10 mb-3 sm:mb-4">
                <svg class="w-6 h-6 sm:w-8 sm:h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2 tracking-tight">Verify Your Email</h1>
            <p class="text-gray-500 dark:text-gray-400 text-xs sm:text-sm">
                Before continuing, please verify your email address by clicking the link we just emailed to you.
                If you didn't receive the email, we can send you another.
            </p>
        </div>

        @if(session('status') == 'verification-link-sent')
            <div class="mb-4 sm:mb-6 p-3 sm:p-4 bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-800 rounded-xl">
                <p class="text-green-700 dark:text-green-400 text-xs sm:text-sm text-center">
                    A new verification link has been sent to your email address.
                </p>
            </div>
        @endif

        @if(auth()->user() && auth()->user()->email)
            <div class="mb-4 sm:mb-6 p-3 sm:p-4 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl">
                <p class="text-gray-600 dark:text-gray-300 text-xs sm:text-sm text-center">
                    Verification email sent to: <span class="font-semibold text-gray-800 dark:text-gray-100">{{ auth()->user()->email }}</span>
                </p>
            </div>
        @endif

        <!-- Resend Email Form -->
        <form method="POST" action="{{ route('verification.send') }}" class="space-y-4">
            @csrf

            <button
                type="submit"
                class="btn-primary w-full py-3 px-4 rounded-xl font-semibold text-sm flex items-center justify-center gap-2"
            >
                Send Verification Email
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </button>
        </form>

        <!-- Footer Links -->
        <div class="mt-6 sm:mt-8 text-center space-y-3">
            @if(auth()->check())
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 text-xs sm:text-sm font-semibold transition-colors">
                        Log Out
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="inline-flex items-center text-xs sm:text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-semibold transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Login
                </a>
            @endif
        </div>

        <!-- Help Section -->
        <div class="mt-6 sm:mt-8 p-3 sm:p-4 bg-gray-50 dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-800">
            <h3 class="text-gray-700 dark:text-gray-200 font-semibold mb-1 sm:mb-2 text-center text-sm">Need Help?</h3>
            <p class="text-gray-500 dark:text-gray-400 text-xs sm:text-sm text-center">
                If you're having trouble verifying your email, please contact your school administrator
                or the IT support team for assistance.
            </p>
        </div>
    </div>
    </main>
</body>
</html>
