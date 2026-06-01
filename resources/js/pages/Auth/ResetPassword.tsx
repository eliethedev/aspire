import React, { useState } from 'react';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import GuestLayout from '../../layouts/GuestLayout';

interface ResetPasswordProps {
    email: string;
    token: string;
}

export default function ResetPassword({ token, email }: ResetPasswordProps) {
    const { data, setData, post, processing, errors, reset } = useForm({
        token: token,
        email: email,
        password: '',
        password_confirmation: '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('password.update'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <GuestLayout title="Reset Password - ASPIRE">
            <Head title="Reset Password - ASPIRE" />

            <div className="mb-6">
                <h2 className="text-2xl font-bold text-white mb-2">
                    Reset Password
                </h2>
                <p className="text-white/60 text-sm">
                    Enter your new password below to reset your account password.
                </p>
            </div>

            <form onSubmit={submit} className="space-y-5">
                <div>
                    <label className="block text-sm font-semibold text-white/80 mb-2">
                        Email Address
                    </label>
                    <input
                        type="email"
                        name="email"
                        value={data.email}
                        className="dark-input w-full px-4 py-3 rounded-xl"
                        placeholder="name@company.com"
                        autoComplete="email"
                        onChange={(e) => setData('email', e.target.value)}
                        required
                    />
                    {errors.email && (
                        <p className="mt-2 text-sm text-rose-400">{errors.email}</p>
                    )}
                </div>

                <div>
                    <label className="block text-sm font-semibold text-white/80 mb-2">
                        New Password
                    </label>
                    <input
                        type="password"
                        name="password"
                        value={data.password}
                        className="dark-input w-full px-4 py-3 rounded-xl"
                        placeholder="••••••••"
                        autoComplete="new-password"
                        onChange={(e) => setData('password', e.target.value)}
                        required
                    />
                    {errors.password && (
                        <p className="mt-2 text-sm text-rose-400">{errors.password}</p>
                    )}
                </div>

                <div>
                    <label className="block text-sm font-semibold text-white/80 mb-2">
                        Confirm New Password
                    </label>
                    <input
                        type="password"
                        name="password_confirmation"
                        value={data.password_confirmation}
                        className="dark-input w-full px-4 py-3 rounded-xl"
                        placeholder="••••••••"
                        autoComplete="new-password"
                        onChange={(e) => setData('password_confirmation', e.target.value)}
                        required
                    />
                    {errors.password_confirmation && (
                        <p className="mt-2 text-sm text-rose-400">{errors.password_confirmation}</p>
                    )}
                </div>

                <button
                    type="submit"
                    disabled={processing}
                    className="w-full bg-gradient-to-r from-indigo-500 to-rose-500 text-white py-3 rounded-xl font-semibold hover:from-indigo-600 hover:to-rose-600 transition-all duration-200 shadow-lg shadow-indigo-500/30 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    {processing ? 'Resetting...' : 'Reset Password'}
                </button>
            </form>

            <div className="mt-6 text-center">
                <Link
                    href={route('login')}
                    className="text-indigo-400 hover:text-indigo-300 text-sm font-semibold"
                >
                    Back to login
                </Link>
            </div>
        </GuestLayout>
    );
}
