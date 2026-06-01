import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import GuestLayout from '../../layouts/GuestLayout';

export default function ForgotPassword() {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('password.email'));
    };

    return (
        <GuestLayout title="Forgot Password - ASPIRE">
            <Head title="Forgot Password - ASPIRE" />

            <div className="mb-6">
                <h2 className="text-2xl font-bold text-white mb-2">
                    Forgot Your Password?
                </h2>
                <p className="text-white/60 text-sm">
                    No problem. Just enter your email address below and we'll send you a password reset link.
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
                        <p className="mt-2 text-sm text-rose-400 flex items-center">
                            <svg className="w-4 h-4 mr-1.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clipRule="evenodd" />
                            </svg>
                            {errors.email}
                        </p>
                    )}
                </div>

                <button
                    type="submit"
                    disabled={processing}
                    className="w-full bg-gradient-to-r from-indigo-500 to-rose-500 text-white py-3 rounded-xl font-semibold hover:from-indigo-600 hover:to-rose-600 transition-all duration-200 shadow-lg shadow-indigo-500/30 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    {processing ? 'Sending...' : 'Send Password Reset Link'}
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
