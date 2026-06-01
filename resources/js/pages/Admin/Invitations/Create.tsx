import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '../../layouts/AuthenticatedLayout';

interface CreateInvitationProps {
    schools: Array<{
        id: number;
        name: string;
    }>;
}

export default function CreateInvitation({ schools }: CreateInvitationProps) {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        role: 'teacher',
        school_id: '',
        department: '',
        years_of_service: '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('admin.invitations.store'), {
            onFinish: () => reset('name', 'email', 'department', 'years_of_service'),
        });
    };

    return (
        <AuthenticatedLayout>
            <Head title="Create Invitation - ASPIRE" />

            <div className="max-w-2xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
                <div className="mb-6">
                    <Link
                        href={route('admin.invitations.index')}
                        className="text-indigo-600 hover:text-indigo-700"
                    >
                        ← Back to Invitations
                    </Link>
                </div>

                <div className="bg-white shadow-lg rounded-lg overflow-hidden">
                    <div className="px-6 py-4 border-b border-gray-200">
                        <h1 className="text-2xl font-bold text-gray-900">
                            Send User Invitation
                        </h1>
                        <p className="mt-1 text-sm text-gray-600">
                            Create a new user account and send an invitation email
                        </p>
                    </div>

                    <form onSubmit={submit} className="p-6 space-y-6">
                        {/* Personal Information */}
                        <div className="space-y-4">
                            <h3 className="text-lg font-semibold text-gray-900">
                                Personal Information
                            </h3>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Full Name *
                                </label>
                                <input
                                    type="text"
                                    name="name"
                                    value={data.name}
                                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="Juan Dela Cruz"
                                    onChange={(e) => setData('name', e.target.value)}
                                    required
                                />
                                {errors.name && (
                                    <p className="mt-1 text-sm text-red-600">{errors.name}</p>
                                )}
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Email Address *
                                </label>
                                <input
                                    type="email"
                                    name="email"
                                    value={data.email}
                                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="juan@school.edu.ph"
                                    onChange={(e) => setData('email', e.target.value)}
                                    required
                                />
                                {errors.email && (
                                    <p className="mt-1 text-sm text-red-600">{errors.email}</p>
                                )}
                            </div>
                        </div>

                        {/* Role Assignment */}
                        <div className="space-y-4">
                            <h3 className="text-lg font-semibold text-gray-900">
                                Role Assignment
                            </h3>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Role *
                                </label>
                                <select
                                    name="role"
                                    value={data.role}
                                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    onChange={(e) => setData('role', e.target.value)}
                                    required
                                >
                                    <option value="teacher">Teacher</option>
                                    <option value="supervisor">Supervisor/Observer</option>
                                    <option value="school_head">School Head/Principal</option>
                                    <option value="admin">Administrator</option>
                                </select>
                                {errors.role && (
                                    <p className="mt-1 text-sm text-red-600">{errors.role}</p>
                                )}
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    School
                                </label>
                                <select
                                    name="school_id"
                                    value={data.school_id}
                                    className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    onChange={(e) => setData('school_id', e.target.value)}
                                >
                                    <option value="">Select a school (optional)</option>
                                    {schools?.map((school) => (
                                        <option key={school.id} value={school.id}>
                                            {school.name}
                                        </option>
                                    ))}
                                </select>
                                {errors.school_id && (
                                    <p className="mt-1 text-sm text-red-600">{errors.school_id}</p>
                                )}
                            </div>
                        </div>

                        {/* Teacher-Specific Fields */}
                        {data.role === 'teacher' && (
                            <div className="space-y-4">
                                <h3 className="text-lg font-semibold text-gray-900">
                                    Teacher Information
                                </h3>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Department
                                    </label>
                                    <input
                                        type="text"
                                        name="department"
                                        value={data.department}
                                        className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                        placeholder="Mathematics"
                                        onChange={(e) => setData('department', e.target.value)}
                                    />
                                    {errors.department && (
                                        <p className="mt-1 text-sm text-red-600">{errors.department}</p>
                                    )}
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-2">
                                        Years of Service
                                    </label>
                                    <input
                                        type="number"
                                        name="years_of_service"
                                        value={data.years_of_service}
                                        className="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                        placeholder="5"
                                        min="0"
                                        max="50"
                                        onChange={(e) => setData('years_of_service', e.target.value)}
                                    />
                                    {errors.years_of_service && (
                                        <p className="mt-1 text-sm text-red-600">{errors.years_of_service}</p>
                                    )}
                                </div>
                            </div>
                        )}

                        {/* Submit Button */}
                        <div className="flex justify-end space-x-4 pt-4 border-t border-gray-200">
                            <Link
                                href={route('admin.invitations.index')}
                                className="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                disabled={processing}
                                className="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                {processing ? 'Sending...' : 'Send Invitation'}
                            </button>
                        </div>
                    </form>
                </div>

                {/* Info Box */}
                <div className="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div className="flex">
                        <div className="flex-shrink-0">
                            <svg className="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clipRule="evenodd" />
                            </svg>
                        </div>
                        <div className="ml-3">
                            <h3 className="text-sm font-medium text-blue-800">
                                Invitation Information
                            </h3>
                            <div className="mt-2 text-sm text-blue-700">
                                <ul className="list-disc list-inside space-y-1">
                                    <li>The user will receive an email with a secure link to set their password</li>
                                    <li>Invitation links expire after 7 days</li>
                                    <li>Links can only be used once</li>
                                    <li>You can resend invitations if needed</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
