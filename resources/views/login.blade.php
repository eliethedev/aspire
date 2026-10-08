<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Sign in to the ASPIRE portal — Automated Supervision Platform for Instructional Reform & Excellence.">

    <title>{{ config('app.name', 'ASPIRE') }} — Sign In</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        (function() {
            if (localStorage.getItem('theme') === 'dark' ||
               (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();

        function toggleTheme() {
            var root = document.documentElement;
            root.classList.toggle('dark');
            localStorage.setItem('theme', root.classList.contains('dark') ? 'dark' : 'light');
        }
    </script>

    <style>
        .brand-grid {
            background-image:
                linear-gradient(rgba(255,255,255,.07) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.07) 1px, transparent 1px);
            background-size: 32px 32px;
            mask-image: radial-gradient(ellipse 90% 80% at 50% 20%, black 40%, transparent 100%);
        }
        .brand-glow {
            background:
                radial-gradient(600px 320px at 15% 10%, rgba(129,140,248,.35), transparent 60%),
                radial-gradient(700px 380px at 90% 90%, rgba(56,189,248,.28), transparent 60%),
                radial-gradient(500px 300px at 80% 10%, rgba(244,114,182,.18), transparent 60%);
        }
        @media (prefers-reduced-motion: reduce) {
            * { animation: none !important; transition: none !important; }
        }
    </style>
</head>
<body class="min-h-screen bg-[#f4f5fb] dark:bg-[#09090b] text-zinc-900 dark:text-zinc-100 antialiased font-sans selection:bg-indigo-600 selection:text-white">

    <div class="min-h-screen flex flex-col lg:grid lg:grid-cols-[1.05fr_1fr]">

        <!-- Left: brand panel -->
        <aside class="relative hidden lg:flex flex-col justify-between overflow-hidden bg-[#0B3D91] text-white p-10 xl:p-12">
            <div class="absolute inset-0 bg-gradient-to-br from-[#0B3D91] via-[#123a9e] to-[#1e1b4b]"></div>
            <div class="absolute inset-0 brand-glow"></div>
            <div class="absolute inset-0 brand-grid"></div>
            <!-- soft orbs -->
            <div class="absolute -top-24 -left-24 h-80 w-80 rounded-full bg-indigo-400/30 blur-3xl"></div>
            <div class="absolute -bottom-28 -right-16 h-96 w-96 rounded-full bg-sky-400/25 blur-3xl"></div>

            <div class="relative">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3 rounded-2xl bg-white/10 ring-1 ring-white/20 backdrop-blur px-3 py-2 pr-4 hover:bg-white/15 transition">
                    <span class="block h-9 w-9 overflow-hidden rounded-xl bg-white ring-1 ring-white/40">
                        <img src="{{ asset('images/whitelogotheme.jpg') }}" alt="ASPIRE logo" class="h-full w-full object-cover">
                    </span>
                    <span class="leading-tight">
                        <span class="block text-sm font-bold tracking-wide">ASPIRE</span>
                        <span class="block text-[11px] font-medium text-indigo-100/90 tracking-wide">Learn • Grow • Serve</span>
                    </span>
                </a>

                <div class="mt-10 max-w-xl">
                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-[11px] font-semibold tracking-wide ring-1 ring-white/20">DepEd-aligned</span>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-[11px] font-semibold tracking-wide ring-1 ring-white/20">COT-Aligned</span>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-[11px] font-semibold tracking-wide ring-1 ring-white/20">PPST-Based</span>
                    </div>
                    <h1 class="mt-5 text-4xl xl:text-[2.75rem] font-extrabold leading-[1.08] tracking-tight">
                        Automated Supervision Platform for Instructional Reform &amp; Excellence
                    </h1>
                    <p class="mt-4 text-[15px] leading-relaxed text-indigo-100/90">
                        One shared workspace for classroom observations — from scheduling and digital COT rating to AI-assisted feedback and growth analytics.
                    </p>
                </div>

                <dl class="mt-8 grid max-w-xl grid-cols-1 gap-3">
                    <div class="flex items-start gap-3 rounded-2xl bg-white/[0.08] p-4 ring-1 ring-white/15 backdrop-blur">
                        <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white/15 ring-1 ring-white/20">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        <span>
                            <dt class="text-sm font-semibold">Digital COT, end to end</dt>
                            <dd class="mt-0.5 text-[13px] leading-relaxed text-indigo-100/85">Schedule, observe, rate with guided indicators, and confirm — no paper forms.</dd>
                        </span>
                    </div>
                    <div class="flex items-start gap-3 rounded-2xl bg-white/[0.08] p-4 ring-1 ring-white/15 backdrop-blur">
                        <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white/15 ring-1 ring-white/20">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456z"/></svg>
                        </span>
                        <span>
                            <dt class="text-sm font-semibold">Coaching-ready feedback</dt>
                            <dd class="mt-0.5 text-[13px] leading-relaxed text-indigo-100/85">Clear strengths, growth areas, and next steps every teacher can act on.</dd>
                        </span>
                    </div>
                    <div class="flex items-start gap-3 rounded-2xl bg-white/[0.08] p-4 ring-1 ring-white/15 backdrop-blur">
                        <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white/15 ring-1 ring-white/20">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 13.5l5.25 5.25L21 6"/></svg>
                        </span>
                        <span>
                            <dt class="text-sm font-semibold">Track growth over time</dt>
                            <dd class="mt-0.5 text-[13px] leading-relaxed text-indigo-100/85">Ratings history and reports turn each cycle into measurable progress.</dd>
                        </span>
                    </div>
                </dl>
            </div>

            <div class="relative mt-10 flex flex-wrap items-center justify-between gap-3 text-[12px] text-indigo-100/80">
                <p class="inline-flex items-center gap-2">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.031 9-11.623 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                    Protected system. Authorized DepEd personnel only.
                </p>
                <p>&copy; {{ date('Y') }} ASPIRE Platform</p>
            </div>
        </aside>

        <!-- Right: form column -->
        <div class="flex flex-1 flex-col">
            <!-- Top utility bar -->
            <header class="w-full max-w-xl mx-auto px-6 pt-5 flex items-center justify-between gap-3">
                <a href="{{ route('home') }}" class="lg:hidden inline-flex items-center gap-2.5">
                    <span class="block h-9 w-9 overflow-hidden rounded-xl ring-1 ring-zinc-200 dark:ring-zinc-800">
                        <img src="{{ asset('images/whitelogotheme.jpg') }}" alt="ASPIRE logo" class="h-full w-full object-cover block dark:hidden">
                        <img src="{{ asset('images/darklogotheme.jpg') }}" alt="ASPIRE logo" class="h-full w-full object-cover hidden dark:block">
                    </span>
                    <span class="leading-tight">
                        <span class="block text-sm font-bold tracking-wide">ASPIRE</span>
                        <span class="block text-[11px] font-medium text-zinc-500 dark:text-zinc-400">Learn • Grow • Serve</span>
                    </span>
                </a>
                <a href="{{ route('home') }}" class="hidden lg:inline-flex items-center gap-1.5 text-[13px] font-medium text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Back to homepage
                </a>

                <button
                    type="button"
                    onclick="toggleTheme()"
                    title="Toggle visual theme"
                    aria-label="Toggle theme"
                    class="p-2.5 rounded-xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-zinc-500 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-zinc-100 hover:border-zinc-300 dark:hover:border-zinc-700 transition shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500"
                >
                    <svg class="h-4 w-4 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="4" stroke-width="2"/>
                        <path stroke-linecap="round" stroke-width="2" d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32l1.41 1.41M2 12h2m16 0h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/>
                    </svg>
                    <svg class="h-4 w-4 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                </button>
            </header>

            <!-- Main -->
            <main class="flex-1 flex items-center justify-center px-6 py-8 sm:py-10">
                <div class="w-full max-w-[440px]">

                    <!-- Mobile brand hero -->
                    <div class="lg:hidden mb-6 overflow-hidden rounded-2xl bg-[#0B3D91] text-white p-6 relative">
                        <div class="absolute inset-0 bg-gradient-to-br from-[#0B3D91] via-[#1640a3] to-[#1e1b4b]"></div>
                        <div class="absolute inset-0 brand-glow opacity-80"></div>
                        <div class="relative">
                            <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-indigo-200">ASPIRE Portal</p>
                            <p class="mt-1.5 text-lg font-bold leading-snug">Automated Supervision Platform for Instructional Reform &amp; Excellence</p>
                            <p class="mt-1 text-[13px] text-indigo-100/85">COT-Aligned • PPST-Based • Role-based portals</p>
                        </div>
                    </div>

                    <!-- Auth card -->
                    <div class="bg-white dark:bg-zinc-900 border border-zinc-200/90 dark:border-zinc-800 rounded-2xl p-7 sm:p-8 shadow-[0_1px_2px_rgba(16,24,40,0.05),0_12px_32px_-12px_rgba(16,24,40,0.15)] dark:shadow-none">

                        <div class="mb-6">
                            <p class="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 dark:bg-indigo-500/10 px-2.5 py-1 text-[11px] font-bold uppercase tracking-[0.08em] text-indigo-700 dark:text-indigo-300 ring-1 ring-indigo-100 dark:ring-indigo-500/20">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                Secure sign in
                            </p>
                            <h1 class="mt-3 text-[22px] font-bold tracking-tight text-zinc-900 dark:text-zinc-50">
                                Welcome back
                            </h1>
                            <p class="mt-1 text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">
                                Sign in with your work email to continue to your dashboard.
                            </p>
                        </div>

                        @if (session('status'))
                            <div class="mb-4 rounded-xl border border-emerald-200 dark:border-emerald-500/30 bg-emerald-50 dark:bg-emerald-500/10 px-3.5 py-3 text-[13px] font-medium text-emerald-800 dark:text-emerald-200 flex items-start gap-2.5" role="status">
                                <svg class="h-4 w-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>{{ session('status') }}</span>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}" id="loginForm" class="space-y-4" novalidate>
                            @csrf

                            <!-- Email -->
                            <div class="space-y-1.5">
                                <label for="email" class="block text-[13px] font-semibold text-zinc-700 dark:text-zinc-200">
                                    Work email
                                </label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-zinc-400 dark:text-zinc-500">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                                    </span>
                                    <input
                                        type="email"
                                        id="email"
                                        name="email"
                                        value="{{ old('email') }}"
                                        required
                                        autofocus
                                        autocomplete="username email"
                                        placeholder="name@organization.gov.ph"
                                        aria-describedby="email-error"
                                        class="w-full pl-10 pr-3.5 py-2.5 rounded-xl text-sm bg-white dark:bg-zinc-800/60 border @error('email') border-rose-300 dark:border-rose-500/60 @else border-zinc-300 dark:border-zinc-700/80 @enderror text-zinc-900 dark:text-zinc-100 placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:outline-none focus:ring-2 focus:ring-indigo-600 dark:focus:ring-indigo-400 focus:border-transparent transition"
                                    >
                                </div>
                                @error('email')
                                    <p id="email-error" class="text-xs text-rose-600 dark:text-rose-400 font-medium pt-0.5 flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Password -->
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <label for="password" class="block text-[13px] font-semibold text-zinc-700 dark:text-zinc-200">
                                        Password
                                    </label>
                                    @if (Route::has('password.request'))
                                        <a
                                            href="{{ route('password.request') }}"
                                            class="text-[13px] font-medium text-indigo-700 hover:text-indigo-900 dark:text-indigo-300 dark:hover:text-indigo-200 transition-colors"
                                        >
                                            Forgot password?
                                        </a>
                                    @endif
                                </div>
                                <div class="relative">
                                    <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-zinc-400 dark:text-zinc-500">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                                    </span>
                                    <input
                                        type="password"
                                        name="password"
                                        id="password"
                                        required
                                        autocomplete="current-password"
                                        placeholder="Enter your password"
                                        aria-describedby="password-error caps-warning"
                                        class="w-full pl-10 pr-11 py-2.5 rounded-xl text-sm bg-white dark:bg-zinc-800/60 border @error('password') border-rose-300 dark:border-rose-500/60 @else border-zinc-300 dark:border-zinc-700/80 @enderror text-zinc-900 dark:text-zinc-100 placeholder:text-zinc-400 dark:placeholder:text-zinc-500 focus:outline-none focus:ring-2 focus:ring-indigo-600 dark:focus:ring-indigo-400 focus:border-transparent transition"
                                    >
                                    <button
                                        type="button"
                                        onclick="togglePassword()"
                                        id="passwordToggle"
                                        tabindex="-1"
                                        aria-label="Show password"
                                        aria-pressed="false"
                                        class="absolute right-2.5 top-1/2 -translate-y-1/2 p-1.5 rounded-lg text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-700/60 transition"
                                    >
                                        <svg id="eyeIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <svg id="eyeOffIcon" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                        </svg>
                                    </button>
                                </div>
                                <p id="caps-warning" class="hidden text-xs font-medium text-amber-700 dark:text-amber-300 flex items-center gap-1.5 pt-0.5">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                                    Caps Lock appears to be on.
                                </p>
                                @error('password')
                                    <p id="password-error" class="text-xs text-rose-600 dark:text-rose-400 font-medium pt-0.5 flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Remember -->
                            <div class="flex items-center justify-between pt-0.5">
                                <label class="inline-flex items-center gap-2.5 cursor-pointer group">
                                    <input
                                        type="checkbox"
                                        name="remember"
                                        class="peer sr-only"
                                    >
                                    <span class="flex h-5 w-5 items-center justify-center rounded-md border border-zinc-300 dark:border-zinc-600 bg-white dark:bg-zinc-800 text-transparent peer-checked:bg-indigo-600 peer-checked:border-indigo-600 peer-checked:text-white peer-focus-visible:ring-2 peer-focus-visible:ring-indigo-500 peer-focus-visible:ring-offset-2 dark:peer-focus-visible:ring-offset-zinc-900 transition">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    </span>
                                    <span class="text-[13px] text-zinc-600 dark:text-zinc-400 select-none group-hover:text-zinc-900 dark:group-hover:text-zinc-200 transition-colors">
                                        Remember this device for 30 days
                                    </span>
                                </label>
                            </div>

                            <!-- Submit -->
                            <div class="pt-1">
                                <button
                                    type="submit"
                                    id="submitBtn"
                                    class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl bg-indigo-700 hover:bg-indigo-800 dark:bg-indigo-600 dark:hover:bg-indigo-500 text-white font-semibold text-sm transition shadow-[0_8px_20px_-8px_rgba(67,56,202,0.7)] active:scale-[0.99] focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600 dark:focus-visible:ring-indigo-400 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-900 disabled:opacity-70 disabled:cursor-not-allowed"
                                >
                                    <span id="submitLabel">Sign in securely</span>
                                    <svg id="submitArrow" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                    <svg id="submitSpinner" class="hidden h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                </button>
                                <p class="mt-3 flex items-center justify-center gap-1.5 text-[12px] text-zinc-500 dark:text-zinc-500">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.031 9-11.623 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                                    Your session is encrypted and device-bound.
                                </p>
                            </div>
                        </form>
                    </div>

                    <!-- Help -->
                    <div class="mt-4 rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white/70 dark:bg-zinc-900/60 px-4 py-3.5 flex items-start gap-3">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm3.75 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm3.75 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/></svg>
                        </span>
                        <p class="text-[13px] leading-relaxed text-zinc-600 dark:text-zinc-400">
                            Locked out or first time signing in?
                            <a href="{{ route('password.request') }}" class="font-semibold text-indigo-700 hover:text-indigo-900 dark:text-indigo-300 dark:hover:text-indigo-200">Reset your password</a>
                            or contact your school ICT coordinator.
                        </p>
                    </div>

                    <p class="lg:hidden text-center text-xs text-zinc-400 dark:text-zinc-600 mt-6">
                        &copy; {{ date('Y') }} ASPIRE Platform. All rights reserved.
                    </p>
                </div>
            </main>

            <!-- Desktop footer -->
            <footer class="hidden lg:flex w-full max-w-xl mx-auto px-6 pb-6 items-center justify-between text-xs text-zinc-400 dark:text-zinc-600">
                <div>&copy; {{ date('Y') }} ASPIRE Platform. All rights reserved.</div>
                <div class="flex gap-4">
                    <a href="#" class="hover:text-zinc-700 dark:hover:text-zinc-300 transition-colors">Privacy</a>
                    <a href="#" class="hover:text-zinc-700 dark:hover:text-zinc-300 transition-colors">Terms</a>
                    <a href="{{ route('contact.create') }}" class="hover:text-zinc-700 dark:hover:text-zinc-300 transition-colors">Contact Us</a>
                    <a href="{{ route('home') }}" class="hover:text-zinc-700 dark:hover:text-zinc-300 transition-colors">Homepage</a>
                </div>
            </footer>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const eye = document.getElementById('eyeIcon');
            const eyeOff = document.getElementById('eyeOffIcon');
            const btn = document.getElementById('passwordToggle');
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            eye.classList.toggle('hidden', show);
            eyeOff.classList.toggle('hidden', !show);
            btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            btn.setAttribute('aria-pressed', show ? 'true' : 'false');
            input.focus({ preventScroll: true });
        }

        (function () {
            const form = document.getElementById('loginForm');
            const btn = document.getElementById('submitBtn');
            const label = document.getElementById('submitLabel');
            const arrow = document.getElementById('submitArrow');
            const spinner = document.getElementById('submitSpinner');
            form.addEventListener('submit', function () {
                btn.disabled = true;
                btn.classList.add('opacity-80');
                label.textContent = 'Signing you in…';
                arrow.classList.add('hidden');
                spinner.classList.remove('hidden');
            });

            const pwd = document.getElementById('password');
            const caps = document.getElementById('caps-warning');
            function checkCaps(e) {
                try {
                    const on = e.getModifierState && e.getModifierState('CapsLock');
                    caps.classList.toggle('hidden', !on);
                    caps.classList.toggle('flex', !!on);
                } catch (_) { /* unsupported */ }
            }
            pwd.addEventListener('keyup', checkCaps);
            pwd.addEventListener('keydown', checkCaps);
        })();
    </script>
</body>
</html>
