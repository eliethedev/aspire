<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'ASPIRE') }} - Login</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        /* Light Theme Background - DepEd White Theme */
        .light-bg {
            background-color: #ffffff;
            background-image: 
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.05) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(244, 63, 94, 0.03) 0px, transparent 50%);
        }
        
        /* Glassmorphism Card - Light Theme */
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(0, 0, 0, 0.08);
            box-shadow: 0 20px 60px -15px rgba(0, 0, 0, 0.1);
        }
        
        /* Light Input Styling */
        .light-input {
            background-color: rgba(255, 255, 255, 0.8);
            border: 2px solid rgba(0, 0, 0, 0.1);
            color: #1f2937;
            transition: all 0.2s ease;
        }
        
        .light-input:hover {
            border-color: rgba(0, 0, 0, 0.2);
            background-color: rgba(255, 255, 255, 0.9);
        }
        
        .light-input:focus {
            border-color: rgba(99, 102, 241, 0.5);
            background-color: rgba(255, 255, 255, 0.95);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            outline: none;
        }
        
        .light-input::placeholder {
            color: rgba(0, 0, 0, 0.4);
        }
        
        /* Gradient Button - DepEd Theme Style */
        .btn-gradient {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: white;
            transition: all 0.2s ease;
            box-shadow: 0 4px 15px -3px rgba(99, 102, 241, 0.3);
        }
        
        .btn-gradient:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 25px -5px rgba(99, 102, 241, 0.4);
        }
        
        .btn-gradient:active {
            transform: translateY(0);
        }
        
        /* Decorative Elements */
        .glow-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
        }
    </style>
</head>
<body class="antialiased font-sans light-bg min-h-screen flex items-center justify-center p-4 relative overflow-hidden">
    
    <!-- Background Glow Effects -->
    <div class="glow-orb w-96 h-96 top-20 left-20 bg-indigo-500/10"></div>
    <div class="glow-orb w-96 h-96 bottom-20 right-20 bg-rose-500/5"></div>

    <div class="w-full max-w-md glass-card rounded-2xl p-8 md:p-10 relative z-10">
        
        <!-- Header Section -->
        <div class="text-center mb-10">
            <!-- Logo Icon -->
            <!-- <div class="inline-flex items-center justify-center w-14 h-14 rounded-xl bg-gradient-to-br from-indigo-500 to-rose-500 mb-6 shadow-lg shadow-indigo-500/30">
              <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div> -->
            
            <h1 class="text-2xl font-bold text-gray-900 mb-2 tracking-tight">Login to Aspire</h1>
            <p class="text-gray-500 text-sm">Sign in to access your ASPIRE account</p>
        </div>

        <!-- Login Form -->
        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <!-- Email Field -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
                <input 
                    type="email" 
                    name="email" 
                    value="{{ old('email') }}"
                    required 
                    autofocus
                    class="light-input w-full px-4 py-3 rounded-xl"
                    placeholder="name@company.com"
                >
                @error('email')
                    <p class="mt-2 text-sm text-rose-400 flex items-center">
                        <svg class="w-4 h-4 mr-1.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Password Field -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                <div class="relative">
                    <input 
                        type="password" 
                        name="password" 
                        required
                        id="password"
                        class="light-input w-full px-4 py-3 pr-12 rounded-xl"
                        placeholder="••••••••"
                    >
                    <button 
                        type="button" 
                        onclick="togglePassword()"
                        class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors p-1"
                    >
                        <svg id="eyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg id="eyeOffIcon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p class="mt-2 text-sm text-rose-400 flex items-center">
                        <svg class="w-4 h-4 mr-1.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Remember & Forgot -->
            <div class="flex items-center justify-between">
                <label class="flex items-center cursor-pointer group">
                    <input 
                        type="checkbox" 
                        name="remember" 
                        class="w-4 h-4 rounded border-gray-300 bg-white text-indigo-600 focus:ring-indigo-500 focus:ring-offset-0 cursor-pointer"
                    >
                    <span class="ml-2 text-sm text-gray-600 group-hover:text-gray-800 transition-colors">Remember me</span>
                </label>
                <a 
                    href="" 
                    class="text-sm font-medium text-indigo-600 hover:text-indigo-700 transition-colors"
                >
                    Forgot password?
                </a>
            </div>

            <!-- Submit Button -->
            <button 
                type="submit" 
                class="btn-gradient w-full py-3 px-4 rounded-xl font-semibold text-sm flex items-center justify-center gap-2"
            >
                Sign In
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </button>
        </form>

        <!-- Footer Links -->
        <div class="text-center space-y-4">
            <a href="{{ route('home') }}" class="inline-flex items-center text-sm text-gray-400 hover:text-gray-600 transition-colors group">
                <svg class="w-4 h-4 mr-2 transform group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Home
            </a>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            const eyeOffIcon = document.getElementById('eyeOffIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.add('hidden');
                eyeOffIcon.classList.remove('hidden');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('hidden');
                eyeOffIcon.classList.add('hidden');
            }
        }
    </script>
</body>
</html>