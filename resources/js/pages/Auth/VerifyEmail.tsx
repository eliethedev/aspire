import React, { useState } from 'react';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import GuestLayout from '../../layouts/GuestLayout';

interface VerifyEmailProps {
    status?: string;
}

export default function VerifyEmail({ status }: VerifyEmailProps) {
    const { auth } = usePage().props;
    const user = auth?.user;

    const { post, processing } = useForm({});

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('verification.send'));
    };

    return (
        <GuestLayout title="Verify Email - ASPIRE">
            <Head title="Verify Email - ASPIRE" />

            <div className="text-center">
                <div className="mb-6">
                    <div className="inline-flex items-center justify-center w-16 h-16 rounded-full bg-yellow-100 mb-4">
                        <svg className="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h2 className="text-2xl font-bold text-white mb-2">
                        Verify Your Email Address
                    </h2>
                    <p className="text-white/60 text-sm max-w-md mx-auto">
                        Before continuing, could you verify your email address by clicking on the link we just emailed to you? 
                        If you didn't receive the email, we will gladly send you another.
                    </p>
                </div>

                {user?.email && (
                    <div className="mb-6 p-4 bg-white/10 rounded-lg">
                        <p className="text-white/80 text-sm">
                            Verification sent to: <span className="font-semibold">{user.email}</span>
                        </p>
                    </div>
                )}

                {status === 'verification-link-sent' && (
                    <div className="mb-4 p-3 bg-green-100 text-green-700 rounded-lg text-sm">
                        A new verification link has been sent to your email address.
                    </div>
                )}

                <form onSubmit={submit} className="space-y-4">
                    <button
                        type="submit"
                        disabled={processing}
                        className="bg-gradient-to-r from-indigo-500 to-rose-500 text-white px-6 py-3 rounded-xl font-semibold hover:from-indigo-600 hover:to-rose-600 transition-all duration-200 shadow-lg shadow-indigo-500/30 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {processing ? 'Sending...' : 'Resend Verification Email'}
                    </button>
                </form>

                <div className="mt-6 space-y-3">
                    {user ? (
                        <Link
                            href={route('logout')}
                            method="post"
                            as="button"
                            className="block text-indigo-400 hover:text-indigo-300 text-sm font-semibold"
                        >
                            Log Out
                        </Link>
                    ) : (
                        <Link
                            href={route('login')}
                            className="block text-indigo-400 hover:text-indigo-300 text-sm font-semibold"
                        >
                            Back to Login
                        </Link>
                    )}
                </div>

                <div className="mt-8 p-4 bg-white/5 rounded-lg">
                    <h3 className="text-white/80 font-semibold mb-2">Need Help?</h3>
                    <p className="text-white/60 text-sm">
                        If you're having trouble verifying your email, please contact your school administrator 
                        or the IT support team for assistance.
                    </p>
                </div>
            </div>
        </GuestLayout>
    );
}
