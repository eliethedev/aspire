import React, { useState } from 'react';
import { Head, useForm } from '@inertiajs/react';
import GuestLayout from '../../layouts/GuestLayout';

interface SetPasswordProps {
    token: string;
    email: string;
    name: string;
    role: string;
    school?: {
        id: number;
        name: string;
    };
}

export default function SetPassword({ token, email, name, role, school }: SetPasswordProps) {
    const { data, setData, post, processing, errors, reset } = useForm({
        token,
        password: '',
        password_confirmation: '',
    });

    const [showPassword, setShowPassword] = useState(false);
    const [showConfirmPassword, setShowConfirmPassword] = useState(false);

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('auth.set-password.store'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    const getPasswordStrength = (password: string) => {
        let strength = 0;
        if (password.length >= 8) strength++;
        if (password.length >= 12) strength++;
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
        if (/\d/.test(password)) strength++;
        if (/[^a-zA-Z0-9]/.test(password)) strength++;
        return strength;
    };

    const strength = getPasswordStrength(data.password);
    const strengthLabels = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
    const strengthColors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-blue-500', 'bg-green-500'];

    return (
        <GuestLayout title="Set Password - ASPIRE">
            <Head title="Set Password - ASPIRE" />

            <div className="max-w-md mx-auto">
                {/* Welcome Header */}
                <div className="text-center mb-8">
                    <div className="inline-flex items-center justify-center w-16 h-16 rounded-full bg-indigo-100 mb-4">
                        <svg className="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <h1 className="text-3xl font-bold text-white mb-2">
                        Welcome to ASPIRE
                    </h1>
                    <p className="text-white/70">
                        Hello, {name}! Please set your password to activate your account.
                    </p>
                </div>

                {/* Account Information Card */}
                <div className="bg-white/10 backdrop-blur-lg rounded-xl p-6 mb-6 border border-white/20">
                    <h2 className="text-lg font-semibold text-white mb-4">Account Details</h2>
                    <div className="space-y-3">
                        <div className="flex justify-between">
                            <span className="text-white/70">Name:</span>
                            <span className="text-white font-medium">{name}</span>
                        </div>
                        <div className="flex justify-between">
                            <span className="text-white/70">Email:</span>
                            <span className="text-white font-medium">{email}</span>
                        </div>
                        <div className="flex justify-between">
                            <span className="text-white/70">Role:</span>
                            <span className="text-white font-medium capitalize">{role.replace('_', ' ')}</span>
                        </div>
                        {school && (
                            <div className="flex justify-between">
                                <span className="text-white/70">School:</span>
                                <span className="text-white font-medium">{school.name}</span>
                            </div>
                        )}
                    </div>
                </div>

                {/* Set Password Form */}
                <form onSubmit={submit} className="space-y-6">
                    <div>
                        <label className="block text-sm font-semibold text-white/80 mb-2">
                            Password *
                        </label>
                        <div className="relative">
                            <input
                                type={showPassword ? 'text' : 'password'}
                                name="password"
                                value={data.password}
                                className="dark-input w-full px-4 py-3 rounded-xl pr-12"
                                placeholder="••••••••"
                                autoComplete="new-password"
                                onChange={(e) => setData('password', e.target.value)}
                                required
                            />
                            <button
                                type="button"
                                onClick={() => setShowPassword(!showPassword)}
                                className="absolute right-3 top-1/2 -translate-y-1/2 text-white/60 hover:text-white"
                            >
                                {showPassword ? (
                                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                ) : (
                                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                )}
                            </button>
                        </div>
                        {errors.password && (
                            <p className="mt-2 text-sm text-rose-400">{errors.password}</p>
                        )}

                        {/* Password Strength Indicator */}
                        {data.password && (
                            <div className="mt-3">
                                <div className="flex gap-1 mb-2">
                                    {[0, 1, 2, 3, 4].map((i) => (
                                        <div
                                            key={i}
                                            className={`h-1 flex-1 rounded-full ${
                                                i < strength ? strengthColors[strength - 1] : 'bg-white/20'
                                            }`}
                                        />
                                    ))}
                                </div>
                                <p className="text-xs text-white/60">
                                    Password strength: <span className="font-medium">{strengthLabels[strength - 1] || 'Very Weak'}</span>
                                </p>
                            </div>
                        )}

                        {/* Password Requirements */}
                        <div className="mt-3 text-xs text-white/60 space-y-1">
                            <p className="font-medium mb-1">Password requirements:</p>
                            <ul className="space-y-1">
                                <li className={data.password.length >= 8 ? 'text-green-400' : ''}>
                                    ✓ At least 8 characters
                                </li>
                                <li className={/[a-z]/.test(data.password) && /[A-Z]/.test(data.password) ? 'text-green-400' : ''}>
                                    ✓ Both uppercase and lowercase letters
                                </li>
                                <li className={/\d/.test(data.password) ? 'text-green-400' : ''}>
                                    ✓ At least one number
                                </li>
                                <li className={/[^a-zA-Z0-9]/.test(data.password) ? 'text-green-400' : ''}>
                                    ✓ At least one special character
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div>
                        <label className="block text-sm font-semibold text-white/80 mb-2">
                            Confirm Password *
                        </label>
                        <div className="relative">
                            <input
                                type={showConfirmPassword ? 'text' : 'password'}
                                name="password_confirmation"
                                value={data.password_confirmation}
                                className="dark-input w-full px-4 py-3 rounded-xl pr-12"
                                placeholder="••••••••"
                                autoComplete="new-password"
                                onChange={(e) => setData('password_confirmation', e.target.value)}
                                required
                            />
                            <button
                                type="button"
                                onClick={() => setShowConfirmPassword(!showConfirmPassword)}
                                className="absolute right-3 top-1/2 -translate-y-1/2 text-white/60 hover:text-white"
                            >
                                {showConfirmPassword ? (
                                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                ) : (
                                    <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                )}
                            </button>
                        </div>
                        {errors.password_confirmation && (
                            <p className="mt-2 text-sm text-rose-400">{errors.password_confirmation}</p>
                        )}
                    </div>

                    <button
                        type="submit"
                        disabled={processing}
                        className="w-full bg-gradient-to-r from-indigo-500 to-rose-500 text-white py-3 rounded-xl font-semibold hover:from-indigo-600 hover:to-rose-600 transition-all duration-200 shadow-lg shadow-indigo-500/30 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {processing ? 'Setting up your account...' : 'Activate Account'}
                    </button>
                </form>

                {/* Security Notice */}
                <div className="mt-6 bg-blue-500/10 border border-blue-500/20 rounded-lg p-4">
                    <div className="flex">
                        <div className="flex-shrink-0">
                            <svg className="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clipRule="evenodd" />
                            </svg>
                        </div>
                        <div className="ml-3">
                            <h3 className="text-sm font-medium text-blue-300">
                                Security Notice
                            </h3>
                            <div className="mt-2 text-sm text-blue-200/80">
                                <p>This invitation link can only be used once. After setting your password, you'll be automatically logged in to your dashboard.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </GuestLayout>
    );
}
