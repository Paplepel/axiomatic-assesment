import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    red: '#CE2031',
                    navy: '#14212B',
                    'navy-light': '#1D2D3A',
                    blue: '#0170B9',
                    orange: '#EB5B25',
                    'light-blue': '#C2D9EB',
                },
            },
        },
    },

    plugins: [forms],
};
