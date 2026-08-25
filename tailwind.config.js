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
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50: '#EEF1FF',
                    100: '#E0E6FF',
                    200: '#C7D2FE',
                    500: '#3B5BDB',
                    600: '#2F4BC0',
                    700: '#243A9E',
                },
                navy: {
                    800: '#152A4A',
                    900: '#0B1B36',
                    950: '#071229',
                },
                canvas: '#F3F5F9',
            },
            boxShadow: {
                card: '0 1px 2px rgba(16, 24, 40, 0.04), 0 8px 24px rgba(16, 24, 40, 0.04)',
            },
        },
    },

    plugins: [forms],
};
