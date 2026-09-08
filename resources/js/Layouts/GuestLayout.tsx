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
                            <img
                                src="/images/darklogotheme.jpg"
                                alt="ASPIRE — Learn • Grow • Serve"
                                className="mx-auto mb-4 h-16 w-auto rounded-lg object-contain"
                            />
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
