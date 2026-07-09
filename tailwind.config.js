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
                    bg: '#ffffff',
                    hover: '#f1f5f9',
                    active: '#eef2ff',
                    accent: '#4f46e5',
                    muted: '#94a3b8',
                },
            },
            boxShadow: {
                'sidebar': '1px 0 0 0 rgba(0,0,0,0.06)',
                'sidebar-floating': '0 1px 3px rgba(0,0,0,0.05), 1px 0 0 rgba(0,0,0,0.06)',
            },
            transitionTimingFunction: {
                'sidebar': 'cubic-bezier(0.4, 0, 0.2, 1)',
            },
        },
    },

    plugins: [forms],
};
