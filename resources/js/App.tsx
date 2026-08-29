import React from 'react';
import { createRoot } from 'react-dom/client';
import { createInertiaApp, router } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import '../css/app.css';

router.onError((error) => {
    // Inertia receives a non-2xx response from the server.
    // The server already rendered the appropriate Blade error page
    // (404, 403, 500, etc.) — Inertia will display it as a full
    // page visit. No extra handling needed for most cases.

    // Handle network errors (server unreachable, timeout, etc.)
    if (error.message?.includes('NetworkError') || error.message?.includes('Failed to fetch')) {
        window.location.href = '/errors/500';
    }
});

router.on('invalid', (event) => {
    // A page component could not be resolved — likely a stale link
    // after a deploy. Reload to pick up the new asset manifest.
    event.preventDefault();
    window.location.reload();
});

createInertiaApp({
    title: (title) => `${title} - ASPIRE`,
    resolve: (name) => resolvePageComponent(
        `./pages/${name}.tsx`,
        import.meta.glob('./pages/**/*.tsx')
    ),
    setup({ el, App, props }) {
        const root = createRoot(el);
        root.render(<App {...props} />);
    },
    progress: {
        color: '#4B5563',
    },
});
