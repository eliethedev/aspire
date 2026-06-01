import React from 'react';
import { Head } from '@inertiajs/react';
import { Link } from '@inertiajs/react';

interface GuestLayoutProps {
    children: React.ReactNode;
    title?: string;
}

export default function GuestLayout({ children, title }: GuestLayoutProps) {
    return (
        <div className="min-h-screen dark-bg flex items-center justify-center">
            <Head title={title} />
            
            <div className="w-full max-w-md">
                <div className="glass-card rounded-2xl p-8">
                    {/* Logo and Header */}
                    <div className="text-center mb-10">
                        <Link href="/" className="inline-block">
                            <div className="inline-flex items-center justify-center w-14 h-14 rounded-xl bg-gradient-to-br from-indigo-500 to-rose-500 mb-6 shadow-lg shadow-indigo-500/30">
                                <svg className="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            <h1 className="text-2xl font-bold text-white mb-2 tracking-tight">ASPIRE</h1>
                            <p className="text-white/50 text-sm">Automated Supervision Platform for Instructional Reform & Excellence</p>
                        </Link>
                    </div>

                    {/* Main Content */}
                    {children}

                    {/* Footer Links */}
                    <div className="mt-8 text-center">
                        <p className="text-white/50 text-xs">
                            &copy; 2024 ASPIRE. Department of Education Philippines
                        </p>
                    </div>
                </div>
            </div>
        </div>
    );
}
