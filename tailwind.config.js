import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Filament/**/*.php',
        './vendor/filament/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50: '#fef7f7',
                    100: '#fdeaea',
                    200: '#fad4d4',
                    300: '#f6b3b3',
                    400: '#f08585',
                    500: '#bf4036',
                    600: '#a8352d',
                    700: '#8b2a24',
                    800: '#6e211c',
                    900: '#511814',
                    950: '#2d0f0c',
                },
                primary: {
                    50: '#fef7f7',
                    100: '#fdeaea',
                    200: '#fad4d4',
                    300: '#f6b3b3',
                    400: '#f08585',
                    500: '#bf4036',
                    600: '#a8352d',
                    700: '#8b2a24',
                    800: '#6e211c',
                    900: '#511814',
                    950: '#2d0f0c',
                },
                accent: {
                    50: '#f8fafc',
                    100: '#f1f5f9',
                    200: '#e2e8f0',
                    300: '#cbd5e1',
                    400: '#94a3b8',
                    500: '#64748b',
                    600: '#475569',
                    700: '#334155',
                    800: '#1e293b',
                    900: '#0f172a',
                    950: '#020617',
                }
            }
        },
    },

    plugins: [forms],
};
