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
                sans: ['Syne', ...defaultTheme.fontFamily.sans],
                mono: ['"JetBrains Mono"', '"Fira Code"', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                base: {
                    bg: '#0d0d0d',
                    surface: '#141414',
                    elevated: '#1c1c1c',
                    border: '#282828',
                    hover: '#202020',
                },
                ink: {
                    primary: '#ede9e3',
                    secondary: '#8a8480',
                    tertiary: '#4e4b48',
                },
                accent: {
                    DEFAULT: '#e85d26',
                    dark: '#d64000',
                    darker: '#b83200',
                    darkest: '#8c2800',
                },
                community: '#5865F2',
                ok: '#22c55e',
                warn: '#f59e0b',
                danger: '#ef4444',
            },
        },
    },

    plugins: [forms],
};
