<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitation Error - ASPIRE</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        (function() {
            if (localStorage.getItem('theme') === 'dark') {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
        }
        .glass-card {
            background: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.08);
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
        }
        .dark .glass-card {
            background-color: #111827;
            border-color: rgba(255, 255, 255, 0.08);
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.4);
        }
    </style>
</head>
<body class="min-h-screen bg-[#f0f5ff] dark:bg-gray-950 flex flex-col font-sans">

    <!-- Top Nav -->
    <nav class="w-full px-4 sm:px-6 py-3 sm:py-4 flex items-center justify-between">
        <a href="{{ route('home') }}" class="flex items-center gap-2 text-blue-600 dark:text-blue-400 font-bold text-lg sm:text-xl tracking-tight">
            <x-application-logo class="h-10 w-auto" />
        </a>
        <a href="{{ route('home') }}" class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors font-medium">
            Homepage
        </a>
    </nav>

    <!-- Main Content -->
    <main class="flex-1 flex items-center justify-center p-3 sm:p-4">
    <div class="w-full max-w-md">

        <!-- Error Card -->
        <div class="glass-card rounded-2xl shadow-2xl p-6 sm:p-8">
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-12 h-12 sm:w-16 sm:h-16 bg-red-100 dark:bg-red-500/10 rounded-full mb-3 sm:mb-4">
                    <i class="fas fa-exclamation-triangle text-2xl sm:text-3xl text-red-600 dark:text-red-400"></i>
                </div>
                <h2 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-gray-100 mb-2">Invitation Invalid</h2>
                <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400 mb-4 sm:mb-6">{{ $error }}</p>
                
                <div class="bg-yellow-50 dark:bg-yellow-500/10 border border-yellow-200 dark:border-yellow-500/30 rounded-lg p-3 sm:p-4 mb-4 sm:mb-6">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-yellow-600 dark:text-yellow-400 mt-0.5 mr-2 sm:mr-3"></i>
                        <div class="text-left">
                            <p class="text-xs sm:text-sm text-yellow-800 dark:text-yellow-200 font-medium">What can you do?</p>
                            <ul class="text-xs sm:text-sm text-yellow-700 dark:text-yellow-300 mt-2 space-y-1">
                                <li>• Contact your system administrator for a new invitation</li>
                                <li>• Check if the invitation link has expired (7 days)</li>
                                <li>• Ensure you clicked the complete link from your email</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <a href="{{ route('login') }}" 
                   class="inline-flex items-center px-5 sm:px-6 py-2.5 sm:py-3 bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 focus:ring-4 focus:ring-blue-200 transition-colors text-sm sm:text-base">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Go to Login
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-6 sm:mt-8 text-center">
            <p class="text-gray-400 dark:text-gray-500 text-sm">
                Need help? Contact your system administrator.
            </p>
        </div>
    </div>
    </main>
</body>
</html>
