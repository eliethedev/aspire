import React, { useState, useEffect } from 'react';
import { dashboardApi } from '../../api';
import { TeacherDashboardStats } from '../../api/dashboard';
import { Observation, Prediction } from '../../types';

interface DashboardData {
    stats: TeacherDashboardStats | null;
    observations: Observation[];
    predictions: Prediction[];
    loading: boolean;
    error: string | null;
}

export default function TeacherDashboard(): React.JSX.Element {
    const [data, setData] = useState<DashboardData>({
        stats: null,
        observations: [],
        predictions: [],
        loading: true,
        error: null,
    });

    useEffect(() => {
        loadDashboardData();
    }, []);

    const loadDashboardData = async (): Promise<void> => {
        try {
            const [statsRes, observationsRes, predictionsRes] = await Promise.all([
                dashboardApi.getTeacherStats(),
                dashboardApi.getTeacherRecentObservations(),
                dashboardApi.getTeacherPredictions(),
            ]);

            setData({
                stats: statsRes.data,
                observations: observationsRes.data,
                predictions: predictionsRes.data,
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

    if (data.error) {
        return (
            <div className="p-8 text-center text-red-600">
                {data.error}
                <button 
                    onClick={loadDashboardData}
                    className="ml-4 px-4 py-2 bg-blue-600 text-white rounded"
                >
                    Retry
                </button>
            </div>
        );
    }

    return (
        <div className="p-6 space-y-6">
            <h1 className="text-2xl font-bold text-gray-800">Teacher Dashboard</h1>
            
            {/* Stats Cards */}
            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                <StatCard 
                    title="Total Observations" 
                    value={data.stats?.total_observations ?? 0} 
                    icon="👁️" 
                />
                <StatCard 
                    title="Completed" 
                    value={data.stats?.completed_observations ?? 0} 
                    icon="✅" 
                />
                <StatCard 
                    title="Avg COT Score" 
                    value={`${data.stats?.average_cot_score ?? 0}%`} 
                    icon="📊" 
                />
                <StatCard 
                    title="Pending Feedback" 
                    value={data.stats?.pending_feedback_count ?? 0} 
                    icon="💬" 
                />
            </div>

            {/* Recent Predictions */}
            <div className="bg-white rounded-lg shadow p-6">
                <h2 className="text-lg font-semibold mb-4">AI Predictions</h2>
                {data.predictions.length === 0 ? (
                    <p className="text-gray-500">No predictions available yet.</p>
                ) : (
                    <div className="space-y-3">
                        {data.predictions.map(prediction => (
                            <div 
                                key={prediction.id} 
                                className="border-l-4 border-blue-500 pl-4 py-2"
                            >
                                <p className="font-medium capitalize">{prediction.prediction_type}</p>
                                <p className="text-sm text-gray-600">{prediction.predicted_outcome}</p>
                                <p className="text-xs text-gray-400">
                                    Confidence: {prediction.confidence_level}%
                                </p>
                            </div>
                        ))}
                    </div>
                )}
            </div>

            {/* Recent Observations */}
            <div className="bg-white rounded-lg shadow p-6">
                <h2 className="text-lg font-semibold mb-4">Recent Observations</h2>
                {data.observations.length === 0 ? (
                    <p className="text-gray-500">No observations recorded yet.</p>
                ) : (
                    <div className="space-y-3">
                        {data.observations.map(obs => (
                            <div 
                                key={obs.id} 
                                className="flex items-center justify-between border-b py-2"
                            >
                                <div>
                                    <p className="font-medium">{obs.subject}</p>
                                    <p className="text-sm text-gray-500">
                                        {obs.grade_section} • {obs.observation_date}
                                    </p>
                                </div>
                                <span className={`px-3 py-1 rounded-full text-sm ${
                                    obs.status === 'completed' 
                                        ? 'bg-green-100 text-green-800' 
                                        : 'bg-yellow-100 text-yellow-800'
                                }`}>
                                    {obs.status}
                                </span>
                            </div>
                        ))}
                    </div>
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
