import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '../../layouts/AuthenticatedLayout';

interface Invitation {
    id: number;
    email: string;
    role: string;
    expires_at: string;
    accepted_at: string | null;
    is_used: boolean;
    resend_count: number;
    user: {
        id: number;
        name: string;
    };
    invited_by: {
        id: number;
        name: string;
    };
    school: {
        id: number;
        name: string;
    } | null;
}

interface IndexProps {
    invitations: {
        data: Invitation[];
        current_page: number;
        last_page: number;
    };
    statistics: {
        total: number;
        pending: number;
        used: number;
        expired: number;
    };
    schools: Array<{
        id: number;
        name: string;
    }>;
}

export default function IndexInvitations({ invitations, statistics, schools }: IndexProps) {
    const [filter, setFilter] = useState({
        status: '',
        role: '',
        school_id: '',
    });

    const handleFilterChange = (key: string, value: string) => {
        setFilter(prev => ({ ...prev, [key]: value }));
        router.get(route('admin.invitations.index'), { ...filter, [key]: value }, {
            preserveState: true,
        });
    };

    const handleResend = (invitationId: number) => {
        if (confirm('Are you sure you want to resend this invitation?')) {
            router.post(route('admin.invitations.resend', invitationId));
        }
    };

    const handleCancel = (invitationId: number) => {
        if (confirm('Are you sure you want to cancel this invitation? This will delete the associated user account.')) {
            router.post(route('admin.invitations.cancel', invitationId));
        }
    };

    const handleDelete = (invitationId: number) => {
        if (confirm('Are you sure you want to delete this invitation?')) {
            router.delete(route('admin.invitations.destroy', invitationId));
        }
    };

    const getStatusBadge = (invitation: Invitation) => {
        if (invitation.is_used) {
            return <span className="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Accepted</span>;
        }
        if (new Date(invitation.expires_at) < new Date()) {
            return <span className="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">Expired</span>;
        }
        return <span className="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">Pending</span>;
    };

    return (
        <AuthenticatedLayout>
            <Head title="Invitations - ASPIRE" />

            <div className="py-8 px-4 sm:px-6 lg:px-8">
                <div className="mb-8">
                    <h1 className="text-3xl font-bold text-gray-900">User Invitations</h1>
                    <p className="mt-2 text-gray-600">Manage user invitations and track their status</p>
                </div>

                {/* Statistics Cards */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div className="bg-white rounded-lg shadow p-6">
                        <div className="text-sm font-medium text-gray-600">Total Invitations</div>
                        <div className="mt-2 text-3xl font-bold text-gray-900">{statistics.total}</div>
                    </div>
                    <div className="bg-white rounded-lg shadow p-6">
                        <div className="text-sm font-medium text-gray-600">Pending</div>
                        <div className="mt-2 text-3xl font-bold text-yellow-600">{statistics.pending}</div>
                    </div>
                    <div className="bg-white rounded-lg shadow p-6">
                        <div className="text-sm font-medium text-gray-600">Accepted</div>
                        <div className="mt-2 text-3xl font-bold text-green-600">{statistics.used}</div>
                    </div>
                    <div className="bg-white rounded-lg shadow p-6">
                        <div className="text-sm font-medium text-gray-600">Expired</div>
                        <div className="mt-2 text-3xl font-bold text-red-600">{statistics.expired}</div>
                    </div>
                </div>

                {/* Filters and Actions */}
                <div className="bg-white shadow rounded-lg mb-6">
                    <div className="p-6 border-b border-gray-200">
                        <div className="flex flex-wrap gap-4 items-center justify-between">
                            <div className="flex flex-wrap gap-4">
                                <select
                                    value={filter.status}
                                    onChange={(e) => handleFilterChange('status', e.target.value)}
                                    className="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="">All Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="used">Accepted</option>
                                    <option value="expired">Expired</option>
                                </select>

                                <select
                                    value={filter.role}
                                    onChange={(e) => handleFilterChange('role', e.target.value)}
                                    className="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="">All Roles</option>
                                    <option value="teacher">Teacher</option>
                                    <option value="supervisor">Supervisor</option>
                                    <option value="school_head">School Head</option>
                                    <option value="admin">Admin</option>
                                </select>

                                <select
                                    value={filter.school_id}
                                    onChange={(e) => handleFilterChange('school_id', e.target.value)}
                                    className="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="">All Schools</option>
                                    {schools?.map((school) => (
                                        <option key={school.id} value={school.id}>
                                            {school.name}
                                        </option>
                                    ))}
                                </select>
                            </div>

                            <Link
                                href={route('admin.invitations.create')}
                                className="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors"
                            >
                                Send New Invitation
                            </Link>
                        </div>
                    </div>

                    {/* Invitations Table */}
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        User
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Email
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Role
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        School
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Expires
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Invited By
                                    </th>
                                    <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="bg-white divide-y divide-gray-200">
                                {invitations.data.length === 0 ? (
                                    <tr>
                                        <td colSpan={8} className="px-6 py-12 text-center text-gray-500">
                                            No invitations found
                                        </td>
                                    </tr>
                                ) : (
                                    invitations.data.map((invitation) => (
                                        <tr key={invitation.id} className="hover:bg-gray-50">
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="text-sm font-medium text-gray-900">
                                                    {invitation.user.name}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="text-sm text-gray-900">{invitation.email}</div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="text-sm text-gray-900 capitalize">
                                                    {invitation.role.replace('_', ' ')}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="text-sm text-gray-900">
                                                    {invitation.school?.name || 'Not assigned'}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                {getStatusBadge(invitation)}
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="text-sm text-gray-900">
                                                    {new Date(invitation.expires_at).toLocaleDateString()}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="text-sm text-gray-900">
                                                    {invitation.invited_by.name}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div className="flex justify-end space-x-2">
                                                    {!invitation.is_used && new Date(invitation.expires_at) > new Date() && (
                                                        <button
                                                            onClick={() => handleResend(invitation.id)}
                                                            className="text-indigo-600 hover:text-indigo-900"
                                                            title="Resend invitation"
                                                        >
                                                            Resend
                                                        </button>
                                                    )}
                                                    {!invitation.is_used && (
                                                        <button
                                                            onClick={() => handleCancel(invitation.id)}
                                                            className="text-red-600 hover:text-red-900 ml-3"
                                                            title="Cancel invitation"
                                                        >
                                                            Cancel
                                                        </button>
                                                    )}
                                                    <button
                                                        onClick={() => handleDelete(invitation.id)}
                                                        className="text-gray-600 hover:text-gray-900 ml-3"
                                                        title="Delete invitation"
                                                    >
                                                        Delete
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>

                    {/* Pagination */}
                    {invitations.last_page > 1 && (
                        <div className="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                            <div className="text-sm text-gray-700">
                                Page {invitations.current_page} of {invitations.last_page}
                            </div>
                            <div className="flex space-x-2">
                                {invitations.current_page > 1 && (
                                    <button
                                        onClick={() => router.get(route('admin.invitations.index'), { page: invitations.current_page - 1 })}
                                        className="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
                                    >
                                        Previous
                                    </button>
                                )}
                                {invitations.current_page < invitations.last_page && (
                                    <button
                                        onClick={() => router.get(route('admin.invitations.index'), { page: invitations.current_page + 1 })}
                                        className="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
                                    >
                                        Next
                                    </button>
                                )}
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
