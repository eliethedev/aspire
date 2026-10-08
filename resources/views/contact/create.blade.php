<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Contact the ASPIRE team — for teachers, school heads, and supervisors who want account access, a demo, or partnership information.">

    <title>{{ config('app.name', 'ASPIRE') }} — Contact Us</title>

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
                radial-gradient(700px 380px at 90% 90%, rgba(56,189,248,.28), transparent 60%);
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
                    <h1 class="text-4xl xl:text-[2.75rem] font-extrabold leading-[1.08] tracking-tight">
                        Talk to the ASPIRE team
                    </h1>
                    <p class="mt-4 text-[15px] leading-relaxed text-indigo-100/90">
                        Whether you're a teacher, school head, or supervisor — ask about account access, request a walkthrough, or explore bringing ASPIRE to your school or division.
                    </p>
                </div>

                <dl class="mt-8 grid max-w-xl grid-cols-1 gap-3">
                    <div class="flex items-start gap-3 rounded-2xl bg-white/[0.08] p-4 ring-1 ring-white/15 backdrop-blur">
                        <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white/15 ring-1 ring-white/20">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                        </span>
                        <span>
                            <dt class="text-sm font-semibold">For every education role</dt>
                            <dd class="mt-0.5 text-[13px] leading-relaxed text-indigo-100/85">Tell us if you're a teacher, school head, or supervisor so we route your inquiry to the right person.</dd>
                        </span>
                    </div>
                    <div class="flex items-start gap-3 rounded-2xl bg-white/[0.08] p-4 ring-1 ring-white/15 backdrop-blur">
                        <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white/15 ring-1 ring-white/20">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.031 9-11.623 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                        </span>
                        <span>
                            <dt class="text-sm font-semibold">Reviewed by real people</dt>
                            <dd class="mt-0.5 text-[13px] leading-relaxed text-indigo-100/85">Every inquiry lands in the admin inbox and is tracked until it's answered — nothing disappears into a void.</dd>
                        </span>
                    </div>
                </dl>
            </div>

            <div class="relative mt-10 flex flex-wrap items-center justify-between gap-3 text-[12px] text-indigo-100/80">
                <p>Prefer email? support@aspire.edu.ph</p>
                <p>&copy; {{ date('Y') }} ASPIRE Platform</p>
            </div>
        </aside>

        <!-- Right: form column -->
        <div class="flex flex-1 flex-col">
            <header class="w-full max-w-xl mx-auto px-6 pt-5 flex items-center justify-between gap-3">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-[13px] font-medium text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100 transition-colors">
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

            <main class="flex-1 flex items-center justify-center px-6 py-8 sm:py-10">
                <div class="w-full max-w-[520px]">
                    <div class="bg-white dark:bg-zinc-900 border border-zinc-200/90 dark:border-zinc-800 rounded-2xl p-7 sm:p-8 shadow-[0_1px_2px_rgba(16,24,40,0.05),0_12px_32px_-12px_rgba(16,24,40,0.15)] dark:shadow-none">

                        <div class="mb-6">
                            <p class="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 dark:bg-indigo-500/10 px-2.5 py-1 text-[11px] font-bold uppercase tracking-[0.08em] text-indigo-700 dark:text-indigo-300 ring-1 ring-indigo-100 dark:ring-indigo-500/20">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                Contact us
                            </p>
                            <h1 class="mt-3 text-[22px] font-bold tracking-tight text-zinc-900 dark:text-zinc-50">
                                Send us an inquiry
                            </h1>
                            <p class="mt-1 text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">
                                No account needed. We usually reply within 2–3 school days.
                            </p>
                        </div>

                        @if ($errors->any())
                            <div class="mb-4 rounded-xl border border-rose-200 dark:border-rose-500/30 bg-rose-50 dark:bg-rose-500/10 px-3.5 py-3 text-[13px] font-medium text-rose-800 dark:text-rose-200" role="alert">
                                <p class="font-semibold">Please fix the following:</p>
                                <ul class="mt-1 list-disc pl-5 space-y-0.5">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('contact.store') }}" class="space-y-4">
                            @csrf
                            {{-- Honeypot (leave empty) --}}
                            <input type="text" name="website" value="" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">

                            <!-- I am a -->
                            <fieldset>
                                <legend class="block text-[13px] font-semibold text-zinc-700 dark:text-zinc-200 mb-2">I am a…</legend>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2" role="radiogroup" aria-label="Your role">
                                    @foreach (['teacher' => 'Teacher', 'school_head' => 'School Head', 'supervisor' => 'Supervisor', 'other' => 'Other'] as $value => $label)
                                        <label class="cursor-pointer">
                                            <input type="radio" name="role" value="{{ $value }}" class="peer sr-only" {{ old('role', request('role')) === $value ? 'checked' : '' }} required>
                                            <span class="flex items-center justify-center px-2 py-2.5 rounded-xl border text-[13px] font-semibold text-center transition border-zinc-300 dark:border-zinc-700 text-zinc-600 dark:text-zinc-400 peer-checked:border-indigo-600 peer-checked:bg-indigo-50 peer-checked:text-indigo-700 dark:peer-checked:bg-indigo-500/10 dark:peer-checked:text-indigo-300 peer-checked:ring-1 peer-checked:ring-indigo-600 hover:border-zinc-400 dark:hover:border-zinc-500 peer-focus-visible:ring-2 peer-focus-visible:ring-indigo-500">
                                                {{ $label }}
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                                @error('role')<p class="mt-1 text-xs text-rose-600 dark:text-rose-400 font-medium">{{ $message }}</p>@enderror
                            </fieldset>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <label for="name" class="block text-[13px] font-semibold text-zinc-700 dark:text-zinc-200">Full name</label>
                                    <input type="text" id="name" name="name" value="{{ old('name') }}" required autocomplete="name" placeholder="Juan Dela Cruz"
                                        class="w-full px-3.5 py-2.5 rounded-xl text-sm bg-white dark:bg-zinc-800/60 border @error('name') border-rose-300 dark:border-rose-500/60 @else border-zinc-300 dark:border-zinc-700/80 @enderror text-zinc-900 dark:text-zinc-100 placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-transparent transition">
                                    @error('name')<p class="text-xs text-rose-600 dark:text-rose-400 font-medium">{{ $message }}</p>@enderror
                                </div>
                                <div class="space-y-1.5">
                                    <label for="email" class="block text-[13px] font-semibold text-zinc-700 dark:text-zinc-200">Email address</label>
                                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="you@example.com"
                                        class="w-full px-3.5 py-2.5 rounded-xl text-sm bg-white dark:bg-zinc-800/60 border @error('email') border-rose-300 dark:border-rose-500/60 @else border-zinc-300 dark:border-zinc-700/80 @enderror text-zinc-900 dark:text-zinc-100 placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-transparent transition">
                                    @error('email')<p class="text-xs text-rose-600 dark:text-rose-400 font-medium">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <label for="school_name" class="block text-[13px] font-semibold text-zinc-700 dark:text-zinc-200">School <span class="font-normal text-zinc-400">(optional)</span></label>
                                    <input type="text" id="school_name" name="school_name" value="{{ old('school_name') }}" autocomplete="organization" placeholder="e.g. Sagay National High School"
                                        class="w-full px-3.5 py-2.5 rounded-xl text-sm bg-white dark:bg-zinc-800/60 border border-zinc-300 dark:border-zinc-700/80 text-zinc-900 dark:text-zinc-100 placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-transparent transition">
                                </div>
                                <div class="space-y-1.5">
                                    <label for="topic" class="block text-[13px] font-semibold text-zinc-700 dark:text-zinc-200">Topic</label>
                                    <select id="topic" name="topic" required
                                        class="w-full px-3.5 py-2.5 rounded-xl text-sm bg-white dark:bg-zinc-800/60 border border-zinc-300 dark:border-zinc-700/80 text-zinc-900 dark:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-transparent transition">
                                        @foreach (['account_access' => 'Account access', 'demo' => 'Request a demo', 'partnership' => 'Partnership', 'feedback' => 'Feedback', 'other' => 'Other'] as $value => $label)
                                            <option value="{{ $value }}" {{ old('topic') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('topic')<p class="text-xs text-rose-600 dark:text-rose-400 font-medium">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            <div class="space-y-1.5">
                                <label for="subject" class="block text-[13px] font-semibold text-zinc-700 dark:text-zinc-200">Subject</label>
                                <input type="text" id="subject" name="subject" value="{{ old('subject') }}" required placeholder="How can we help?"
                                    class="w-full px-3.5 py-2.5 rounded-xl text-sm bg-white dark:bg-zinc-800/60 border @error('subject') border-rose-300 dark:border-rose-500/60 @else border-zinc-300 dark:border-zinc-700/80 @enderror text-zinc-900 dark:text-zinc-100 placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-transparent transition">
                                @error('subject')<p class="text-xs text-rose-600 dark:text-rose-400 font-medium">{{ $message }}</p>@enderror
                            </div>

                            <div class="space-y-1.5">
                                <label for="message" class="block text-[13px] font-semibold text-zinc-700 dark:text-zinc-200">Message</label>
                                <textarea id="message" name="message" rows="5" required placeholder="Tell us about your school, your role, and what you need…"
                                    class="w-full px-3.5 py-2.5 rounded-xl text-sm bg-white dark:bg-zinc-800/60 border @error('message') border-rose-300 dark:border-rose-500/60 @else border-zinc-300 dark:border-zinc-700/80 @enderror text-zinc-900 dark:text-zinc-100 placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-transparent transition resize-vertical">{{ old('message') }}</textarea>
                                @error('message')<p class="text-xs text-rose-600 dark:text-rose-400 font-medium">{{ $message }}</p>@enderror
                            </div>

                            <div class="pt-1">
                                <button type="submit"
                                    class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl bg-indigo-700 hover:bg-indigo-800 dark:bg-indigo-600 dark:hover:bg-indigo-500 text-white font-semibold text-sm transition shadow-[0_8px_20px_-8px_rgba(67,56,202,0.7)] active:scale-[0.99] focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-600 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-zinc-900">
                                    Send inquiry
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                </button>
                                <p class="mt-3 text-center text-[12px] text-zinc-500 dark:text-zinc-500">
                                    Already have an account? <a href="{{ route('login') }}" class="font-semibold text-indigo-700 hover:text-indigo-900 dark:text-indigo-300 dark:hover:text-indigo-200">Sign in</a> instead.
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </main>

            <footer class="hidden lg:flex w-full max-w-xl mx-auto px-6 pb-6 items-center justify-between text-xs text-zinc-400 dark:text-zinc-600">
                <div>&copy; {{ date('Y') }} ASPIRE Platform. All rights reserved.</div>
                <div class="flex gap-4">
                    <a href="{{ route('home') }}" class="hover:text-zinc-700 dark:hover:text-zinc-300 transition-colors">Homepage</a>
                    <a href="{{ route('login') }}" class="hover:text-zinc-700 dark:hover:text-zinc-300 transition-colors">Sign in</a>
                </div>
            </footer>
        </div>
    </div>
</body>
</html>
