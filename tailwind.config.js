import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.tsx',
        './resources/js/**/*.ts',
        './resources/js/**/*.jsx',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                sidebar: {
                    bg: '#f8fafc',
                    hover: '#f1f5f9',
                    active: '#eef2ff',
                    accent: '#6366f1',
                    muted: '#94a3b8',
                },
            },
            boxShadow: {
                'sidebar': '4px 0 6px -1px rgba(0,0,0,0.03), 2px 0 4px -2px rgba(0,0,0,0.02)',
                'sidebar-floating': '0 0 20px rgba(0,0,0,0.05), 4px 0 15px rgba(99,102,241,0.06)',
            },
            transitionTimingFunction: {
                'sidebar': 'cubic-bezier(0.4, 0, 0.2, 1)',
            },
            backgroundImage: {
                'gradient-sidebar': 'linear-gradient(180deg, #f0f4ff 0%, #f1f5f9 50%, #f8fafc 100%)',
                'gradient-accent': 'linear-gradient(180deg, #6366f1, #818cf8)',
                'gradient-brand': 'linear-gradient(135deg, #6366f1, #8b5cf6)',
            },
        },
    },

    plugins: [forms],
};
