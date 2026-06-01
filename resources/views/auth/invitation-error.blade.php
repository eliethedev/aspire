<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitation Error - ASPIRE</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="min-h-screen gradient-bg flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo and Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-full shadow-lg mb-4">
                <i class="fas fa-graduation-cap text-3xl text-purple-600"></i>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">ASPIRE</h1>
            <p class="text-purple-100">Department of Education Supervision Platform</p>
        </div>

        <!-- Error Card -->
        <div class="glass-card rounded-2xl shadow-2xl p-8">
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 rounded-full mb-4">
                    <i class="fas fa-exclamation-triangle text-3xl text-red-600"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Invitation Invalid</h2>
                <p class="text-gray-600 mb-6">{{ $error }}</p>
                
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-yellow-600 mt-0.5 mr-3"></i>
                        <div class="text-left">
                            <p class="text-sm text-yellow-800 font-medium">What can you do?</p>
                            <ul class="text-sm text-yellow-700 mt-2 space-y-1">
                                <li>• Contact your system administrator for a new invitation</li>
                                <li>• Check if the invitation link has expired (7 days)</li>
                                <li>• Ensure you clicked the complete link from your email</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <a href="{{ route('login') }}" 
                   class="inline-flex items-center px-6 py-3 bg-purple-600 text-white rounded-lg font-medium hover:bg-purple-700 focus:ring-4 focus:ring-purple-300 transition-colors">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Go to Login
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center">
            <p class="text-purple-100 text-sm">
                Need help? Contact your system administrator.
            </p>
        </div>
    </div>
</body>
</html>
