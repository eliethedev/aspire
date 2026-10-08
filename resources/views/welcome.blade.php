<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="ASPIRE — Automated Supervision Platform for Instructional Reform & Excellence. Digital COT observations, AI-assisted feedback, and growth analytics for Philippine schools.">
    <meta name="theme-color" content="#0B3D91" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#020617" media="(prefers-color-scheme: dark)">

    <title>{{ config('app.name', 'ASPIRE') }} - Automated Supervision Platform for Instructional Reform & Excellence</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/landing.tsx'])

    <script>
        (function() {
            if (localStorage.getItem('theme') === 'dark') {
                document.documentElement.classList.add('dark');
            }

        })();
    </script>
    <style>
        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            *, *::before, *::after { animation-duration: 0.01ms !important; transition-duration: 0.01ms !important; }
        }
    </style>
</head>
<body class="bg-white font-sans text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[100] focus:rounded-lg focus:bg-[#0B3D91] focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-white">Skip to main content</a>
    <div id="landing-root"></div>
    <noscript>
        <div style="max-width: 640px; margin: 96px auto; padding: 0 24px; text-align: center; font-family: Inter, system-ui, sans-serif;">
            <h1 style="font-size: 24px; font-weight: 700;">ASPIRE — Automated Supervision Platform for Instructional Reform &amp; Excellence</h1>
            <p style="margin-top: 12px; color: #475569;">This landing page needs JavaScript to display its interactive content.</p>
            <p style="margin-top: 16px;"><a href="/login" style="display:inline-block; background:#0B3D91; color:#fff; padding: 12px 24px; border-radius: 999px; text-decoration: none; font-weight: 600;">Log in to your portal</a></p>
        </div>
    </noscript>
</body>
</html>
