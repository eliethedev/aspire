// resources/js/Pages/EmailVerification.tsx
import React, { useState, useEffect } from 'react';
import { Head, router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { CheckCircle, Mail, Clock, AlertCircle, RefreshCw, LogOut } from 'lucide-react';

interface EmailVerificationProps {
    auth: {
        user: {
            name: string;
            email: string;
            email_verified_at: string | null;
        };
    };
    status?: string;
}

export default function EmailVerification({ auth, status }: EmailVerificationProps) {
    const [isResending, setIsResending] = useState(false);
    const [resendStatus, setResendStatus] = useState<'idle' | 'success' | 'error'>('idle');
    const [countdown, setCountdown] = useState(0);

    useEffect(() => {
        if (countdown > 0) {
            const timer = setTimeout(() => setCountdown(countdown - 1), 1000);
            return () => clearTimeout(timer);
        }
    }, [countdown]);

    const handleResendVerification = async (e: React.FormEvent) => {
        e.preventDefault();
        
        if (countdown > 0) return;

        setIsResending(true);
        setResendStatus('idle');

        try {
            await router.post('/email/verification-notification', {}, {
                onSuccess: () => {
                    setResendStatus('success');
                    setCountdown(60); // 60 second cooldown
                },
                onError: () => {
                    setResendStatus('error');
                },
                onFinish: () => {
                    setIsResending(false);
                },
            });
        } catch (error) {
            setResendStatus('error');
            setIsResending(false);
        }
    };

    const handleLogout = (e: React.FormEvent) => {
        e.preventDefault();
        router.post('/logout');
    };

    const formatCountdown = (seconds: number) => {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${mins}:${secs.toString().padStart(2, '0')}`;
    };

    const isVerified = auth.user.email_verified_at !== null;

    if (isVerified) {
        return (
            <>
                <Head title="Email Verified - ASPIRE" />
                <div className="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50 flex items-center justify-center p-4">
                    <div className="max-w-md w-full">
                        <div className="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 p-8 text-center">
                            <div className="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                                <CheckCircle className="w-8 h-8 text-green-600" />
                            </div>
                            
                            <h1 className="text-2xl font-bold text-gray-900 mb-2">
                                Email Verified Successfully!
                            </h1>
                            
                            <p className="text-gray-600 mb-6">
                                Your email address <span className="font-medium text-gray-900">{auth.user.email}</span> has been verified.
                            </p>

                            <div className="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                                <p className="text-sm text-green-800">
                                    You now have full access to ASPIRE. You can proceed to your dashboard.
                                </p>
                            </div>

                            <Button
                                onClick={() => router.visit('/dashboard')}
                                className="w-full bg-blue-600 hover:bg-blue-700 text-white"
                                size="lg"
                            >
                                Go to Dashboard
                            </Button>
                        </div>
                    </div>
                </div>
            </>
        );
    }

    return (
        <>
            <Head title="Verify Email - ASPIRE" />
            <div className="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50 flex items-center justify-center p-4">
                <div className="max-w-md w-full">
                    {/* Main Card */}
                    <div className="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 p-8">
                        {/* Email Icon */}
                        <div className="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <Mail className="w-8 h-8 text-blue-600" />
                        </div>

                        {/* Title */}
                        <h1 className="text-2xl font-bold text-gray-900 mb-2 text-center">
                            Verify Your Email Address
                        </h1>

                        {/* User Info */}
                        <div className="bg-gray-50 rounded-lg p-3 mb-6 text-center">
                            <p className="text-sm text-gray-600">
                                Account: <span className="font-medium text-gray-900">{auth.user.name}</span>
                            </p>
                            <p className="text-sm text-gray-600">
                                Email: <span className="font-medium text-gray-900">{auth.user.email}</span>
                            </p>
                        </div>

                        {/* Instructions */}
                        <div className="space-y-4 mb-6">
                            <div className="flex items-start space-x-3">
                                <div className="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <span className="text-xs font-bold text-blue-600">1</span>
                                </div>
                                <p className="text-sm text-gray-700">
                                    Check your email inbox for a verification message from ASPIRE
                                </p>
                            </div>
                            
                            <div className="flex items-start space-x-3">
                                <div className="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <span className="text-xs font-bold text-blue-600">2</span>
                                </div>
                                <p className="text-sm text-gray-700">
                                    Click the verification link in the email to confirm your address
                                </p>
                            </div>
                            
                            <div className="flex items-start space-x-3">
                                <div className="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <span className="text-xs font-bold text-blue-600">3</span>
                                </div>
                                <p className="text-sm text-gray-700">
                                    Return here to access your dashboard
                                </p>
                            </div>
                        </div>

                        {/* Success Message */}
                        {status === 'verification-link-sent' && (
                            <Alert variant="success" className="mb-6">
                                <CheckCircle className="w-5 h-5" />
                                <AlertDescription>
                                    A new verification link has been sent to your email address.
                                </AlertDescription>
                            </Alert>
                        )}

                        {/* Resend Status Messages */}
                        {resendStatus === 'success' && (
                            <Alert variant="success" className="mb-6">
                                <CheckCircle className="w-5 h-5" />
                                <AlertDescription>
                                    Verification email resent successfully!
                                </AlertDescription>
                            </Alert>
                        )}

                        {resendStatus === 'error' && (
                            <Alert variant="destructive" className="mb-6">
                                <AlertCircle className="w-5 h-5" />
                                <AlertDescription>
                                    Failed to resend verification email. Please try again.
                                </AlertDescription>
                            </Alert>
                        )}

                        {/* Action Buttons */}
                        <div className="space-y-3">
                            {/* Resend Button */}
                            <form onSubmit={handleResendVerification}>
                                <Button
                                    type="submit"
                                    disabled={isResending || countdown > 0}
                                    className="w-full bg-blue-600 hover:bg-blue-700 text-white"
                                    size="lg"
                                >
                                    {isResending ? (
                                        <>
                                            <RefreshCw className="w-4 h-4 mr-2 animate-spin" />
                                            Sending...
                                        </>
                                    ) : countdown > 0 ? (
                                        <>
                                            <Clock className="w-4 h-4 mr-2" />
                                            Resend in {formatCountdown(countdown)}
                                        </>
                                    ) : (
                                        <>
                                            <Mail className="w-4 h-4 mr-2" />
                                            Resend Verification Email
                                        </>
                                    )}
                                </Button>
                            </form>

                            {/* Logout Button */}
                            <form onSubmit={handleLogout}>
                                <Button
                                    type="submit"
                                    variant="outline"
                                    className="w-full"
                                    size="lg"
                                >
                                    <LogOut className="w-4 h-4 mr-2" />
                                    Log Out
                                </Button>
                            </form>
                        </div>
                    </div>

                    {/* Help Section */}
                    <div className="mt-6 bg-white/60 backdrop-blur-sm rounded-xl border border-white/20 p-6">
                        <h2 className="text-lg font-semibold text-gray-900 mb-4">
                            Didn't receive the email?
                        </h2>
                        
                        <div className="space-y-3">
                            <div className="flex items-start space-x-3">
                                <AlertCircle className="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" />
                                <div>
                                    <p className="text-sm font-medium text-gray-900">Check your spam folder</p>
                                    <p className="text-xs text-gray-600">
                                        Sometimes verification emails end up in spam or junk folders.
                                    </p>
                                </div>
                            </div>
                            
                            <div className="flex items-start space-x-3">
                                <AlertCircle className="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" />
                                <div>
                                    <p className="text-sm font-medium text-gray-900">Verify email address</p>
                                    <p className="text-xs text-gray-600">
                                        Make sure you entered the correct email address during registration.
                                    </p>
                                </div>
                            </div>
                            
                            <div className="flex items-start space-x-3">
                                <AlertCircle className="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" />
                                <div>
                                    <p className="text-sm font-medium text-gray-900">Wait a few minutes</p>
                                    <p className="text-xs text-gray-600">
                                        Email delivery can sometimes take a few minutes.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div className="mt-4 pt-4 border-t border-gray-200">
                            <p className="text-xs text-gray-500 text-center">
                                If you continue to have issues, please contact your school administrator.
                            </p>
                        </div>
                    </div>

                    {/* ASPIRE Branding */}
                    <div className="mt-6 text-center">
                        <p className="text-sm text-gray-600">
                            <span className="font-semibold">ASPIRE</span>
                            <br />
                            <span className="text-xs">Automated Supervision Platform for Instructional Reform & Excellence</span>
                        </p>
                        <p className="text-xs text-gray-500 mt-2">
                            Department of Education Philippines
                        </p>
                    </div>
                </div>
            </div>
        </>
    );
}
