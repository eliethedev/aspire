<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'ASPIRE') }} - Confirm Password</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

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

        .light-input {
            background-color: #ffffff;
            border: 1px solid #d1d5db;
            color: #1f2937;
            transition: all 0.2s ease;
        }

        .light-input:hover {
            border-color: #9ca3af;
        }

        .light-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
            outline: none;
        }

        .light-input::placeholder {
            color: rgba(0, 0, 0, 0.4);
        }

        .btn-primary {
            background-color: #2563eb;
            color: white;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background-color: #1d4ed8;
        }
    </style>
</head>
<body class="antialiased font-sans light-bg min-h-screen flex flex-col">

    <nav class="w-full px-4 sm:px-6 py-3 sm:py-4 flex items-center justify-between">
        <a href="{{ route('home') }}" class="flex items-center gap-2 text-blue-600 font-bold text-lg sm:text-xl tracking-tight">
            <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            ASPIRE
        </a>
    </nav>

    <main class="flex-1 flex items-center justify-center p-4">
        <div class="w-full max-w-md glass-card rounded-2xl p-8 sm:p-10">
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-blue-50 border border-blue-100 mb-5">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2 tracking-tight">Confirm Password</h1>
                <p class="text-gray-500 text-sm">This is a secure area of the application. Please confirm your password before continuing.</p>
            </div>

            <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                    <input
                        type="password"
                        name="password"
                        required
                        autofocus
                        autocomplete="current-password"
                        class="light-input w-full px-4 py-3 rounded-xl"
                        placeholder="Enter your password"
                    >
                    @error('password')
                        <p class="mt-2 text-sm text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn-primary w-full py-3 px-4 rounded-xl font-semibold text-sm">
                    Confirm
                </button>
            </form>
        </div>
    </main>
</body>
</html>
