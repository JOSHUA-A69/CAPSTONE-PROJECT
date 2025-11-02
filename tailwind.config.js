import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
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
                dark: {
                    bg: '#181818',
                    card: '#232323',
                    accent: '#FF6A2A',
                    text: '#FFFFFF',
                    muted: '#CCCCCC',
                    border: '#333333',
                },
            },
        },
    },

    plugins: [forms],
};
