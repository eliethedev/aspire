<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'ASPIRE') }} - Set Password</title>

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
<body class="antialiased font-sans light-bg min-h-screen flex items-center justify-center p-4 relative">

    <!-- Background Glow Effects -->
    <div class="glow-orb w-96 h-96 top-20 left-20 bg-indigo-500/10"></div>
    <div class="glow-orb w-96 h-96 bottom-20 right-20 bg-rose-500/5"></div>

    <div class="w-full max-w-md glass-card rounded-2xl p-8 md:p-10 relative z-10">

        <!-- Header Section -->
        <div class="text-center mb-2">
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Set Your Password</h1>
            <p class="text-gray-500 text-sm">Hello, <span class="font-semibold text-gray-700">{{ $name }}</span></p>
            <p class="text-sm text-gray-500 mt-1">
                You've been invited as a <span class="font-medium text-gray-700">{{ ucfirst($role) }}</span>
                @if($school)
                    at <span class="font-medium text-gray-700">{{ $school->name }}</span>
                @endif
            </p>
        </div>

        <form method="POST" action="{{ route('auth.set-password.store') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <!-- Password Field -->
            <div>
                <label for="password" class="block text-sm font-semibold text-gray-700 ">Password</label>
                <div class="relative">
                    <input type="password" id="password" name="password" required
                           class="light-input w-full px-4 py-3 rounded-xl pr-12"
                           placeholder="Enter your password"
                           oninput="checkPasswordStrength(this.value)">
                    <button type="button" onclick="togglePasswordVisibility('password', 'password-icon')"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors p-1">
                        <svg id="password-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>

                <!-- Password Strength Indicator -->
                <div class="mt-2">
                    <div class="flex space-x-1">
                        <div id="strength-1" class="h-1 flex-1 bg-gray-200 rounded"></div>
                        <div id="strength-2" class="h-1 flex-1 bg-gray-200 rounded"></div>
                        <div id="strength-3" class="h-1 flex-1 bg-gray-200 rounded"></div>
                        <div id="strength-4" class="h-1 flex-1 bg-gray-200 rounded"></div>
                    </div>
                    <p id="strength-text" class="text-xs text-gray-500 mt-1">Password strength</p>
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

            <!-- Confirm Password Field -->
            <div>
                <label for="password_confirmation" class="block text-sm font-semibold text-gray-700">Confirm Password</label>
                <div class="relative">
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                           class="light-input w-full px-4 py-3 rounded-xl pr-12"
                           placeholder="Confirm your password"
                           oninput="checkPasswordMatch()">
                    <button type="button" onclick="togglePasswordVisibility('password_confirmation', 'confirm-password-icon')"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors p-1">
                        <svg id="confirm-password-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
                @error('password_confirmation')
                <p class="mt-2 text-sm text-rose-400 flex items-center">
                    <svg class="w-4 h-4 mr-1.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    {{ $message }}
                </p>
                @enderror
            </div>

            <!-- Password Requirements -->
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                <p class="text-sm font-semibold text-gray-700 mb-3">Password Requirements:</p>
                <ul class="text-xs text-gray-600 space-y-2">
                    <li class="flex items-center">
                        <svg id="req-length" class="w-3 h-3 mr-2 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                            <circle cx="10" cy="10" r="3"/>
                        </svg>
                        At least 8 characters
                    </li>
                    <li class="flex items-center">
                        <svg id="req-uppercase" class="w-3 h-3 mr-2 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                            <circle cx="10" cy="10" r="3"/>
                        </svg>
                        One uppercase letter
                    </li>
                    <li class="flex items-center">
                        <svg id="req-lowercase" class="w-3 h-3 mr-2 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                            <circle cx="10" cy="10" r="3"/>
                        </svg>
                        One lowercase letter
                    </li>
                    <li class="flex items-center">
                        <svg id="req-number" class="w-3 h-3 mr-2 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                            <circle cx="10" cy="10" r="3"/>
                        </svg>
                        One number
                    </li>
                    <li class="flex items-center">
                        <svg id="req-special" class="w-3 h-3 mr-2 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                            <circle cx="10" cy="10" r="3"/>
                        </svg>
                        One special character
                    </li>
                </ul>
            </div>

            <!-- Submit Button -->
            <button type="submit" id="submit-btn"
                    class="btn-gradient w-full py-3 px-4 rounded-xl font-semibold text-sm flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                Set Password & Activate Account
            </button>
        </form>

        <!-- Security Notice -->
        <div class="mt-2 text-center">
            <p class="text-xs text-gray-400">
                <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                Your connection is secure. This is a one-time setup process.
            </p>
        </div>
    </div>

    <script>
        function togglePasswordVisibility(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);

            if (input.type === 'password') {
                input.type = 'text';
                // Change to eye-slash icon
                icon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                `;
            } else {
                input.type = 'password';
                // Change to eye icon
                icon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                `;
            }
        }

        function checkPasswordStrength(password) {
            let strength = 0;
            
            // Check length
            if (password.length >= 8) {
                document.getElementById('req-length').classList.remove('text-gray-300');
                document.getElementById('req-length').classList.add('text-green-500');
                strength++;
            } else {
                document.getElementById('req-length').classList.remove('text-green-500');
                document.getElementById('req-length').classList.add('text-gray-300');
            }
            
            // Check uppercase
            if (/[A-Z]/.test(password)) {
                document.getElementById('req-uppercase').classList.remove('text-gray-300');
                document.getElementById('req-uppercase').classList.add('text-green-500');
                strength++;
            } else {
                document.getElementById('req-uppercase').classList.remove('text-green-500');
                document.getElementById('req-uppercase').classList.add('text-gray-300');
            }
            
            // Check lowercase
            if (/[a-z]/.test(password)) {
                document.getElementById('req-lowercase').classList.remove('text-gray-300');
                document.getElementById('req-lowercase').classList.add('text-green-500');
                strength++;
            } else {
                document.getElementById('req-lowercase').classList.remove('text-green-500');
                document.getElementById('req-lowercase').classList.add('text-gray-300');
            }
            
            // Check number
            if (/[0-9]/.test(password)) {
                document.getElementById('req-number').classList.remove('text-gray-300');
                document.getElementById('req-number').classList.add('text-green-500');
                strength++;
            } else {
                document.getElementById('req-number').classList.remove('text-green-500');
                document.getElementById('req-number').classList.add('text-gray-300');
            }
            
            // Check special character
            if (/[^A-Za-z0-9]/.test(password)) {
                document.getElementById('req-special').classList.remove('text-gray-300');
                document.getElementById('req-special').classList.add('text-green-500');
                strength++;
            } else {
                document.getElementById('req-special').classList.remove('text-green-500');
                document.getElementById('req-special').classList.add('text-gray-300');
            }
            
            // Update strength indicator
            const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-green-500'];
            const texts = ['Weak', 'Fair', 'Good', 'Strong'];
            
            for (let i = 1; i <= 4; i++) {
                const bar = document.getElementById('strength-' + i);
                bar.className = 'h-1 flex-1 rounded transition-colors';
                if (i <= strength) {
                    bar.classList.add(colors[strength - 1]);
                } else {
                    bar.classList.add('bg-gray-200');
                }
            }
            
            const strengthText = document.getElementById('strength-text');
            if (password.length === 0) {
                strengthText.textContent = 'Password strength';
                strengthText.className = 'text-xs text-gray-500 mt-1';
            } else if (strength <= 1) {
                strengthText.textContent = 'Weak';
                strengthText.className = 'text-xs text-red-500 mt-1';
            } else if (strength <= 2) {
                strengthText.textContent = 'Fair';
                strengthText.className = 'text-xs text-orange-500 mt-1';
            } else if (strength <= 3) {
                strengthText.textContent = 'Good';
                strengthText.className = 'text-xs text-yellow-500 mt-1';
            } else {
                strengthText.textContent = 'Strong';
                strengthText.className = 'text-xs text-green-500 mt-1';
            }
            
            checkPasswordMatch();
        }

        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('password_confirmation').value;
            const submitBtn = document.getElementById('submit-btn');
            
            if (confirmPassword && password !== confirmPassword) {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            } else if (password.length >= 8 && confirmPassword === password) {
                submitBtn.disabled = false;
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('submit-btn').disabled = true;
            document.getElementById('submit-btn').classList.add('opacity-50', 'cursor-not-allowed');
        });
    </script>
</body>
</html>
