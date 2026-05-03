<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'ASPIRE') }} - Email Verification</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        /* Dark Theme Background - Matching Login Page */
        .dark-bg {
            background-color: #030303;
            background-image: 
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.15) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(244, 63, 94, 0.1) 0px, transparent 50%);
        }
        
        /* Glassmorphism Card */
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 20px 60px -15px rgba(0, 0, 0, 0.5);
        }
        
        /* Gradient Button */
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
<body class="antialiased font-sans dark-bg min-h-screen flex items-center justify-center p-4 relative overflow-hidden">
    
    <!-- Background Glow Effects -->
    <div class="glow-orb w-96 h-96 top-20 left-20 bg-indigo-500/20"></div>
    <div class="glow-orb w-96 h-96 bottom-20 right-20 bg-rose-500/15"></div>

    <div class="w-full max-w-md glass-card rounded-2xl p-8 md:p-10 relative z-10">
        
        <!-- Header Section -->
        <div class="text-center mb-8">
            <!-- Email Icon -->
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-rose-500 mb-6 shadow-lg shadow-indigo-500/30">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            
            <h1 class="text-2xl font-bold text-white mb-2 tracking-tight">Verify Your Email</h1>
            <p class="text-white/50 text-sm leading-relaxed">
                Before continuing, please check your email for a verification link.
            </p>
        </div>

        <!-- Success Message -->
        @if (session('status'))
            <div class="mb-6 p-4 bg-green-500/10 border border-green-500/20 rounded-xl">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-400 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-green-400 text-sm font-medium">{{ session('status') }}</p>
                </div>
            </div>
        @endif

        <!-- Email Display -->
        @if(Auth::check())
            <div class="mb-8 p-4 bg-white/5 rounded-xl border border-white/10">
                <p class="text-white/60 text-sm mb-1">Verification email sent to:</p>
                <p class="text-white font-semibold text-lg">{{ Auth::user()->email }}</p>
            </div>
        @else
            <div class="mb-8 p-4 bg-white/5 rounded-xl border border-white/10">
                <p class="text-white/60 text-sm mb-1">Verification email has been sent to your registered email address.</p>
                <p class="text-white font-semibold text-lg">Check your inbox to continue</p>
            </div>
        @endif

        <!-- Instructions -->
        <div class="mb-8 space-y-3">
            <div class="flex items-start text-white/60 text-sm">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-white/10 text-white/80 text-xs font-semibold mr-3 flex-shrink-0 mt-0.5">1</span>
                <span>Check your inbox for the verification email from ASPIRE</span>
            </div>
            <div class="flex items-start text-white/60 text-sm">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-white/10 text-white/80 text-xs font-semibold mr-3 flex-shrink-0 mt-0.5">2</span>
                <span>Click the verification link in the email</span>
            </div>
            <div class="flex items-start text-white/60 text-sm">
                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-white/10 text-white/80 text-xs font-semibold mr-3 flex-shrink-0 mt-0.5">3</span>
                <span>Return to login and access your dashboard</span>
            </div>
        </div>

        <!-- Resend Verification Form -->
        @if(Auth::check())
            <form method="POST" action="{{ route('verification.send') }}" class="mb-6">
                @csrf
                <button 
                    type="submit" 
                    class="btn-gradient w-full py-3 px-4 rounded-xl font-semibold text-sm flex items-center justify-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Resend Verification Email
                </button>
            </form>
        @endif

        <!-- Help Text -->
        <div class="text-center">
            <p class="text-white/40 text-xs mb-4">
                Didn't receive the email? Check your spam folder @if(Auth::check())or click above to resend.@endif.
            </p>
            
            @if(Auth::check())
                <!-- Logout Link -->
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button 
                        type="submit" 
                        class="text-sm text-white/50 hover:text-white/70 transition-colors inline-flex items-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Sign Out
                    </button>
                </form>
            @else
                <!-- Login Link -->
                <a href="{{ route('login') }}" class="text-sm text-white/50 hover:text-white/70 transition-colors inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    Back to Login
                </a>
            @endif
        </div>
    </div>
</body>
</html>
