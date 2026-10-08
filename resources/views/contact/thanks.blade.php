<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'ASPIRE') }} — Inquiry Received</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        (function() {
            if (localStorage.getItem('theme') === 'dark') {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
</head>
<body class="min-h-screen bg-[#f4f5fb] dark:bg-[#09090b] text-zinc-900 dark:text-zinc-100 antialiased font-sans flex items-center justify-center px-6 py-12">
    <div class="w-full max-w-md text-center">
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200/90 dark:border-zinc-800 rounded-2xl p-8 sm:p-10 shadow-[0_1px_2px_rgba(16,24,40,0.05),0_12px_32px_-12px_rgba(16,24,40,0.15)] dark:shadow-none">
            <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-50 dark:bg-emerald-500/10 ring-1 ring-emerald-200 dark:ring-emerald-500/30">
                <svg class="h-7 w-7 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </span>
            <h1 class="mt-5 text-2xl font-bold tracking-tight">Inquiry received</h1>
            <p class="mt-2 text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">
                {{ session('success', 'Thank you! Your inquiry has been received.') }}
            </p>
            <div class="mt-7 flex flex-col sm:flex-row items-center justify-center gap-2.5">
                <a href="{{ route('home') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-indigo-700 hover:bg-indigo-800 dark:bg-indigo-600 dark:hover:bg-indigo-500 text-white font-semibold text-sm transition">
                    Back to homepage
                </a>
                <a href="{{ route('login') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-2.5 rounded-xl border border-zinc-300 dark:border-zinc-700 text-sm font-semibold text-zinc-700 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition">
                    Sign in
                </a>
            </div>
        </div>
        <p class="mt-6 text-xs text-zinc-400 dark:text-zinc-600">&copy; {{ date('Y') }} ASPIRE Platform. All rights reserved.</p>
    </div>
</body>
</html>
