import React from 'react';
import { Head, Link } from '@inertiajs/react';
import GuestLayout from '../../layouts/GuestLayout';

interface InvitationErrorProps {
    error: string;
}

export default function InvitationError({ error }: InvitationErrorProps) {
    return (
        <GuestLayout title="Invitation Error - ASPIRE">
            <Head title="Invitation Error - ASPIRE" />

            <div className="max-w-md mx-auto">
                {/* Error Icon */}
                <div className="text-center mb-8">
                    <div className="inline-flex items-center justify-center w-20 h-20 rounded-full bg-red-100 mb-4">
                        <svg className="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h1 className="text-3xl font-bold text-white mb-2">
                        Invitation Error
                    </h1>
                    <p className="text-white/70">
                        We couldn't process your invitation
                    </p>
                </div>

                {/* Error Message Card */}
                <div className="bg-white/10 backdrop-blur-lg rounded-xl p-6 mb-6 border border-white/20">
                    <div className="flex items-start">
                        <div className="flex-shrink-0">
                            <svg className="h-6 w-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div className="ml-3">
                            <h3 className="text-lg font-medium text-white mb-2">
                                {error}
                            </h3>
                            <p className="text-sm text-white/70">
                                This could happen if:
                            </p>
                            <ul className="mt-2 text-sm text-white/70 space-y-1 list-disc list-inside">
                                <li>The invitation link has expired</li>
                                <li>The invitation has already been used</li>
                                <li>The invitation was cancelled by an administrator</li>
                                <li>The invitation link is invalid or corrupted</li>
                            </ul>
                        </div>
                    </div>
                </div>

                {/* Action Buttons */}
                <div className="space-y-4">
                    <Link
                        href={route('login')}
                        className="w-full bg-gradient-to-r from-indigo-500 to-rose-500 text-white py-3 rounded-xl font-semibold hover:from-indigo-600 hover:to-rose-600 transition-all duration-200 shadow-lg shadow-indigo-500/30 text-center block"
                    >
                        Go to Login
                    </Link>

                    <Link
                        href="/"
                        className="w-full bg-white/10 text-white py-3 rounded-xl font-semibold hover:bg-white/20 transition-all duration-200 border border-white/20 text-center block"
                    >
                        Return to Home
                    </Link>
                </div>

                {/* Help Section */}
                <div className="mt-8 text-center">
                    <p className="text-white/60 text-sm">
                        Need help? Contact your school administrator or system administrator for assistance.
                    </p>
                </div>
            </div>
        </GuestLayout>
    );
}
