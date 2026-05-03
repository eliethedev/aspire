<!-- Header -->
<header class="bg-white shadow-sm border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-blue-600 shadow-lg shadow-blue-600/30">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-3">
                    <h1 class="text-xl font-bold text-slate-900">{{ config('app.name', 'ASPIRE') }}</h1>
                    <p class="text-xs text-slate-500">Teacher Portal</p>
                </div>
            </div>

            <!-- User Menu -->
            <div class="flex items-center space-x-4">
                <div class="text-right">
                    <p class="text-sm font-medium text-slate-900">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-slate-500">{{ ucfirst(Auth::user()->role) }}</p>
                </div>
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                        <span class="text-white text-sm font-medium">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                    </div>
                </div>
                <!-- Logout Button -->
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-slate-500 hover:text-slate-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
