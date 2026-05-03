import React, { useState } from 'react';
import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

createInertiaApp({
    title: (title) => `${title} - ASPIRE`,
    resolve: (name) => resolvePageComponent(
        `./Pages/${name}.tsx`,
        import.meta.glob('./Pages/**/*.tsx')
    ),
    setup({ el, App, props }) {
        const root = createRoot(el);
        root.render(<App {...props} />);
    },
});
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

function App(): React.JSX.Element {
    const [apiData, setApiData] = useState<ApiData | null>(null);
    const [loading, setLoading] = useState<boolean>(false);
    const [error, setError] = useState<string | null>(null);

    const fetchApiData = async (): Promise<void> => {
        setLoading(true);
        setError(null);
        try {
            const response = await window.axios.get('/api/test');
            setApiData(response.data);
        } catch (err) {
            setError('Failed to fetch data from API');
            console.error('API Error:', err);
        } finally {
            setLoading(false);
        }
    };

    return (
        <div className="min-h-screen">
            <Dashboard /> p-8
            h1 className="text-3xl font-bold text-gray-900 mb-6">ASPIRE <h1
            <div className="mt-8 p-6 bg-white rounded-lg shadow-md">
                <h2 classNamxt-xl font-semibold mb-4 text-gray-800">API Integration Test</h2>
                <button
                    onClick={fetchApiData}
                    disabled={loading}
                    className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:bg-gray-400 transition-colors"
                >
                    {loading ? 'Loading...' : 'Test API Call'}
                </button>
                
                {error && (
                    <div className="mt-4 p-3 bg-red-100 text-red-700 rounded">
                        {error}
                    </div>
                )}
                
                {apiData && (
                    <div className="mt-4 p-3 bg-green-100 text-green-700 rounded">
                        <pre className="text-sm">{JSON.stringify(apiData, null, 2)}</pre>
                    </div>
                )}
            </div>
        </div>
    );
}

export default App;
