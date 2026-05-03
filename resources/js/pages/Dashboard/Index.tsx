// resources/js/Pages/Dashboard.tsx
import React, { useState } from 'react';
import axios from 'axios';

interface ApiData {
    message: string;
    status: string;
    timestamp: string;
    data: {
        app_name: string;
        version: string;
        integration: string;
    };
}

export default function Dashboard() {
    const [apiData, setApiData] = useState<ApiData | null>(null);
    const [loading, setLoading] = useState<boolean>(false);
    const [error, setError] = useState<string | null>(null);

    const fetchApiData = async () => {
        setLoading(true);
        setError(null);

        try {
            const response = await axios.get('/api/test');
            setApiData(response.data);
        } catch (err) {
            setError('Failed to fetch data from API');
            console.error('API Error:', err);
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="min-h-screen bg-gray-50 p-8">
            <div className="max-w-4xl mx-auto">
                <h1 className="text-4xl font-bold text-gray-900 mb-2">ASPIRE</h1>
                <p className="text-gray-600 mb-8">Automated Supervision Platform for Instructional Reform & Excellence</p>

                <div className="bg-white rounded-xl shadow-sm p-8">
                    <h2 className="text-2xl font-semibold mb-6 text-gray-800">API Integration Test</h2>
                    
                    <button
                        onClick={fetchApiData}
                        disabled={loading}
                        className="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400 transition-colors font-medium"
                    >
                        {loading ? 'Loading...' : 'Test API Call'}
                    </button>

                    {error && (
                        <div className="mt-4 p-4 bg-red-100 text-red-700 rounded-lg">
                            {error}
                        </div>
                    )}

                    {apiData && (
                        <div className="mt-6 p-4 bg-green-100 text-green-800 rounded-lg overflow-auto">
                            <pre className="text-sm">{JSON.stringify(apiData, null, 2)}</pre>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}