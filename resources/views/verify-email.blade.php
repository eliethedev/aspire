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
        
        /* Gradient Button - Matching Landing Page Style */
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
<body class="antialiased font-sans light-bg min-h-screen flex items-center justify-center p-4 relative ">
    
    <!-- Background Glow Effects -->
    <div class="glow-orb w-96 h-96 top-20 left-20 bg-indigo-500/10"></div>
    <div class="glow-orb w-96 h-96 bottom-20 right-20 bg-rose-500/5"></div>

    <div class="w-full max-w-md glass-card rounded-2xl p-8 md:p-10 relative z-10">
        
        <!-- Header Section -->
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-yellow-100 mb-4">
                <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2 tracking-tight">Verify Your Email Address</h1>
            <p class="text-gray-500 text-sm">
                Before continuing, could you verify your email address by clicking on the link we just emailed to you? 
                If you didn't receive the email, we will gladly send you another.
            </p>
        </div>

        @if(session('status') == 'verification-link-sent')
            <div class="mb-6 p-4 bg-green-500/20 border border-green-500/30 rounded-lg">
                <p class="text-green-400 text-sm text-center">
                    A new verification link has been sent to your email address.
                </p>
            </div>
        @endif

        @if(auth()->user() && auth()->user()->email)
            <div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                <p class="text-gray-700 text-sm text-center">
                    Click Verification Email to: <span class="font-semibold">{{ auth()->user()->email }}</span>
                </p>
            </div>
        @endif

        <!-- Resend Email Form -->
        <form method="POST" action="{{ route('verification.send') }}" class="space-y-4">
            @csrf
            
            <button 
                type="submit" 
                class="btn-gradient w-full py-3 px-4 rounded-xl font-semibold text-sm flex items-center justify-center gap-2"
            >
                Send Verification Email
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </button>
        </form>

        <!-- Footer Links -->
        <div class="mt-8 text-center space-y-3">
            @if(auth()->check())
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-indigo-600 hover:text-indigo-700 text-sm font-semibold transition-colors">
                        Log Out
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="block text-indigo-600 hover:text-indigo-700 text-sm font-semibold transition-colors">
                    Back to Login
                </a>
            @endif
        </div>

        <!-- Help Section -->
        <div class="mt-8 p-4 bg-gray-50 rounded-lg">
            <h3 class="text-gray-700 font-semibold mb-2 text-center">Need Help?</h3>
            <p class="text-gray-600 text-sm text-center">
                If you're having trouble verifying your email, please contact your school administrator 
                or the IT support team for assistance.
            </p>
        </div>
    </div>
</body>
</html>
