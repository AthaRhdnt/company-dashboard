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
                sans: ['system-ui', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'primary': {
                    50: '#f0faff',
                    100: '#d9f3fb',
                    200: '#b1e4f5',
                    300: '#7bd3ef',
                    400: '#40bee2',
                    500: '#0ea5e9',
                    600: '#0284c7',
                    700: '#0369a1',
                    800: '#075985',
                    900: '#0c4a6e',
                    950: '#082f49',
                },
                'secondary': {
                    50: '#f5f6ff',
                    100: '#e6e8fb',
                    200: '#c4c8f0',
                    300: '#a2a7e0',
                    400: '#8085cc',
                    500: '#6066b3',
                    600: '#464a8f',
                    700: '#33356b',
                    800: '#232545',
                    900: '#18192f',
                    950: '#0a0b49',
                },
                'accent': {
                    50: '#fefbe9',
                    100: '#fdf2c0',
                    200: '#fce28e',
                    300: '#f9cb58',
                    400: '#f4b02d',
                    500: '#e89a10',
                    600: '#c47d0b',
                    700: '#9c5f0c',
                    800: '#7a4710',
                    900: '#61370f',
                    950: '#3a2106',
                },
                'warning': {
                    50: '#fffcf0',
                    100: '#fff6d4',
                    200: '#ffecaa',
                    300: '#ffe074',
                    400: '#ffcf3b',
                    500: '#fcbf1e',
                    600: '#d39a10',
                    700: '#a8740b',
                    800: '#7f580c',
                    900: '#5c420c',
                    950: '#352604',
                },
            },
        },
    },

    plugins: [forms],
};