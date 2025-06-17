import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    DEFAULT: '#0d6efd',
                    light: '#3b82f6',
                    dark: '#0a58ca',
                },
                secondary: {
                    DEFAULT: '#6c757d',
                    light: '#adb5bd',
                    dark: '#495057',
                },
            },
        },
    },

    plugins: [forms],
};
