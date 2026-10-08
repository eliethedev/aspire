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
            <x-application-logo class="h-10 w-auto" />
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
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2 tracking-tight">Check Your Email</h1>
            <p class="text-gray-500 dark:text-gray-400 text-xs sm:text-sm">
                We sent a 6-digit verification code to
                @if(auth()->user() && auth()->user()->email)
                    <span class="font-semibold text-gray-800 dark:text-gray-100">{{ auth()->user()->email }}</span>.
                @else
                    your email address.
                @endif
                Enter it below to verify your account. The code expires in 30 minutes.
            </p>
        </div>

        @if(session('status') == 'verification-code-sent')
            <div class="mb-4 sm:mb-6 p-3 sm:p-4 bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-800 rounded-xl">
                <p class="text-green-700 dark:text-green-400 text-xs sm:text-sm text-center">
                    A new verification code has been sent to your email address.
                </p>
            </div>
        @endif

        @if(session('status') == 'verification-code-cooldown')
            <div class="mb-4 sm:mb-6 p-3 sm:p-4 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-800 rounded-xl">
                <p class="text-amber-700 dark:text-amber-400 text-xs sm:text-sm text-center">
                    Please wait a few seconds before requesting another code.
                </p>
            </div>
        @endif

        @if($errors->has('code'))
            <div class="mb-4 sm:mb-6 p-3 sm:p-4 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-800 rounded-xl">
                <p class="text-red-700 dark:text-red-400 text-xs sm:text-sm text-center">
                    {{ $errors->first('code') }}
                </p>
            </div>
        @endif

        <!-- Code Entry Form -->
        <form method="POST" action="{{ route('verification.verify') }}" id="verify-code-form" class="space-y-4">
            @csrf

            @if(!auth()->check())
                <div>
                    <label for="email" class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email', request('email')) }}" required
                           class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                           placeholder="you@example.com" autocomplete="email">
                    @error('email')
                        <p class="mt-1 text-xs sm:text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            <div>
                <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 text-center">6-Digit Verification Code</label>
                <div id="otp-boxes" class="flex items-center justify-center gap-2 sm:gap-3" dir="ltr">
                    @for($i = 0; $i < 6; $i++)
                        <input type="text" inputmode="numeric" autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}" maxlength="1"
                               class="otp-box w-11 h-12 sm:w-12 sm:h-14 text-center text-xl sm:text-2xl font-bold border {{ $errors->has('code') ? 'border-red-400' : 'border-gray-300 dark:border-gray-600' }} bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                               aria-label="Digit {{ $i + 1 }} of 6">
                    @endfor
                </div>
                <input type="hidden" name="code" id="otp-combined" value="{{ old('code') }}">
                @error('code')
                    <p class="mt-2 text-xs sm:text-sm text-red-600 dark:text-red-400 text-center">{{ $message }}</p>
                @enderror
            </div>

            <button
                type="submit"
                class="btn-primary w-full py-3 px-4 rounded-xl font-semibold text-sm flex items-center justify-center gap-2"
            >
                Verify Email
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </button>
        </form>

        <!-- Resend Code Form -->
        <form method="POST" action="{{ route('verification.send') }}" id="resend-code-form" class="mt-3">
            @csrf
            @if(!auth()->check())
                <input type="hidden" name="email" id="resend-email" value="{{ old('email', request('email')) }}">
            @endif
            <button
                type="submit"
                id="resend-code-btn"
                class="w-full py-3 px-4 rounded-xl font-semibold text-sm flex items-center justify-center gap-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-transparent dark:disabled:hover:bg-transparent"
            >
                <span id="resend-code-label">Resend Code</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </button>
            <p id="resend-code-hint" class="mt-2 text-[11px] sm:text-xs text-gray-400 dark:text-gray-500 text-center">Didn't get the code? Check spam, then resend.</p>
        </form>

        <script>
        (function () {
            var boxes = Array.prototype.slice.call(document.querySelectorAll('.otp-box'));
            var combined = document.getElementById('otp-combined');
            var resendEmail = document.getElementById('resend-email');
            var emailInput = document.getElementById('email');
            if (!boxes.length || !combined) return;

            function sync() {
                combined.value = boxes.map(function (b) { return b.value; }).join('');
                if (resendEmail && emailInput) resendEmail.value = emailInput.value;
            }

            // Restore a previously submitted code (e.g. after a validation error)
            if (combined.value && combined.value.length === 6) {
                boxes.forEach(function (b, i) { b.value = combined.value[i] || ''; });
            }

            boxes.forEach(function (box, i) {
                box.addEventListener('input', function () {
                    box.value = box.value.replace(/\D/g, '').slice(0, 1);
                    if (box.value && i < boxes.length - 1) boxes[i + 1].focus();
                    sync();
                });
                box.addEventListener('keydown', function (e) {
                    if (e.key === 'Backspace' && !box.value && i > 0) {
                        boxes[i - 1].focus();
                        boxes[i - 1].value = '';
                        sync();
                    }
                });
                box.addEventListener('paste', function (e) {
                    e.preventDefault();
                    var text = (e.clipboardData.getData('text') || '').replace(/\D/g, '').slice(0, 6);
                    text.split('').forEach(function (ch, j) {
                        if (boxes[i + j]) boxes[i + j].value = ch;
                    });
                    var next = Math.min(i + text.length, boxes.length - 1);
                    boxes[next].focus();
                    sync();
                });
            });

            if (emailInput) emailInput.addEventListener('input', sync);

            document.getElementById('verify-code-form').addEventListener('submit', sync);

            /* ---------- 25-second resend cooldown ---------- */
            var RESEND_COOLDOWN = 25;
            var RESEND_KEY = 'aspire_resend_at';
            var resendForm = document.getElementById('resend-code-form');
            var resendBtn = document.getElementById('resend-code-btn');
            var resendLabel = document.getElementById('resend-code-label');
            var resendHint = document.getElementById('resend-code-hint');
            var cooldownTimer = null;

            function setResendEnabled(enabled, remaining) {
                if (!resendBtn) return;
                resendBtn.disabled = !enabled;
                if (enabled) {
                    resendLabel.textContent = 'Resend Code';
                    if (resendHint) resendHint.textContent = "Didn't get the code? Check spam, then resend.";
                } else {
                    resendLabel.textContent = 'Resend in ' + remaining + 's';
                    if (resendHint) resendHint.textContent = 'Please wait ' + remaining + 's before requesting a new code.';
                }
            }

            function startCooldown(remaining) {
                if (!resendBtn) return;
                if (cooldownTimer) clearInterval(cooldownTimer);
                setResendEnabled(false, remaining);
                cooldownTimer = setInterval(function () {
                    remaining -= 1;
                    if (remaining <= 0) {
                        clearInterval(cooldownTimer);
                        cooldownTimer = null;
                        try { localStorage.removeItem(RESEND_KEY); } catch (e) {}
                        setResendEnabled(true);
                    } else {
                        setResendEnabled(false, remaining);
                    }
                }, 1000);
            }

            // Resume a cooldown that started before a reload/redirect.
            try {
                var lastSent = parseInt(localStorage.getItem(RESEND_KEY) || '0', 10);
                var elapsed = Math.floor((Date.now() - lastSent) / 1000);
                if (lastSent && elapsed < RESEND_COOLDOWN) {
                    startCooldown(RESEND_COOLDOWN - elapsed);
                }
            } catch (e) {}

            if (resendForm) {
                resendForm.addEventListener('submit', function () {
                    try { localStorage.setItem(RESEND_KEY, String(Date.now())); } catch (e) {}
                });
            }
        })();
        </script>

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
