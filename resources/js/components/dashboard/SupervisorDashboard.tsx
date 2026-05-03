import React, { useState, useEffect } from 'react';
import { dashboardApi } from '../../api';
import { SupervisorDashboardStats, Observation } from '../../api/dashboard';

interface DashboardData {
    stats: SupervisorDashboardStats | null;
    observations: Observation[];
    loading: boolean;
    error: string | null;
}

export default function SupervisorDashboard(): React.JSX.Element {
    const [data, setData] = useState<DashboardData>({
        stats: null,
        observations: [],
        loading: true,
        error: null,
    });

    useEffect(() => {
        loadDashboardData();
    }, []);

    const loadDashboardData = async (): Promise<void> => {
        try {
            const [statsRes, observationsRes] = await Promise.all([
                dashboardApi.getSupervisorStats(),
                dashboardApi.getSupervisorRecentObservations(),
            ]);

            setData({
                stats: statsRes.data,
                observations: observationsRes.data,
                loading: false,
                error: null,
            });
        } catch (err) {
            setData(prev => ({
                ...prev,
                loading: false,
                error: 'Failed to load dashboard data',
            }));
        }
    };

    if (data.loading) {
        return <div className="p-8 text-center">Loading dashboard...</div>;
    }

    return (
        <div className="p-6 space-y-6">
            <h1 className="text-2xl font-bold text-gray-800">Supervisor Dashboard</h1>
            
            {/* Stats Cards */}
            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                <StatCard 
                    title="Total Teachers" 
                    value={data.stats?.total_teachers ?? 0} 
                    icon="👨‍🏫" 
                />
                <StatCard 
                    title="Pending Observations" 
                    value={data.stats?.pending_observations ?? 0} 
                    icon="⏳" 
                />
                <StatCard 
                    title="Completed This Month" 
                    value={data.stats?.completed_this_month ?? 0} 
                    icon="📅" 
                />
                <StatCard 
                    title="Active Reviews" 
                    value={data.observations.length} 
                    icon="🔍" 
                />
            </div>

            {/* Action Items */}
            <div className="bg-white rounded-lg shadow p-6">
                <h2 className="text-lg font-semibold mb-4">Action Items</h2>
                <div className="space-y-2">
                    {data.stats && data.stats.pending_observations > 0 ? (
                        <div className="flex items-center justify-between bg-yellow-50 p-3 rounded">
                            <span>Observations requiring scheduling</span>
                            <button className="px-4 py-2 bg-yellow-600 text-white rounded text-sm">
                                Schedule Now
                            </button>
                        </div>
                    ) : (
                        <p className="text-gray-500">No pending actions.</p>
                    )}
                </div>
            </div>

            {/* Recent Observations */}
            <div className="bg-white rounded-lg shadow p-6">
                <h2 className="text-lg font-semibold mb-4">Recent Observations</h2>
                {data.observations.length === 0 ? (
                    <p className="text-gray-500">No recent observations.</p>
                ) : (
                    <table className="w-full text-left">
                        <thead>
                            <tr className="border-b">
                                <th className="py-2">Teacher</th>
                                <th className="py-2">Subject</th>
                                <th className="py-2">Date</th>
                                <th className="py-2">Status</th>
                                <th className="py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {data.observations.map(obs => (
                                <tr key={obs.id} className="border-b">
                                    <td className="py-2">{obs.teacher?.user?.name ?? 'Unknown'}</td>
                                    <td className="py-2">{obs.subject}</td>
                                    <td className="py-2">{obs.observation_date}</td>
                                    <td className="py-2">
                                        <span className={`px-2 py-1 rounded text-xs ${
                                            obs.status === 'completed' 
                                                ? 'bg-green-100 text-green-800' 
                                                : 'bg-yellow-100 text-yellow-800'
                                        }`}>
                                            {obs.status}
                                        </span>
                                    </td>
                                    <td className="py-2">
                                        <button className="text-blue-600 text-sm hover:underline">
                                            View Details
                                        </button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                )}
            </div>
        </div>
    );
}

interface StatCardProps {
    title: string;
    value: string | number;
    icon: string;
}

function StatCard({ title, value, icon }: StatCardProps): JSX.Element {
    return (
        <div className="bg-white rounded-lg shadow p-4 flex items-center space-x-4">
            <span className="text-2xl">{icon}</span>
            <div>
                <p className="text-gray-500 text-sm">{title}</p>
                <p className="text-2xl font-bold">{value}</p>
            </div>
        </div>
    );
}
