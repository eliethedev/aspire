// resources/js/Pages/Dashboard.tsx
import React from 'react';
import { Head } from '@inertiajs/react';
import TeacherDashboard from '../../components/dashboard/TeacherDashboard';
import AuthenticatedLayout from '../../layouts/AuthenticatedLayout';

interface DashboardProps {
    auth: {
        user: {
            id: number;
            name: string;
            email: string;
            role?: string;
        };
    };
}

export default function Dashboard({ auth }: DashboardProps): React.JSX.Element {
    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Dashboard - ASPIRE" />
            <TeacherDashboard />
        </AuthenticatedLayout>
    );
}