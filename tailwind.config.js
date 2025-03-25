import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                blue: {
                    100: '#ebf8ff',
                    200: '#bee3f8',
                    300: '#90cdf4',
                    400: '#63b3ed',
                    500: '#4299e1',
                    600: '#3182ce',
                    700: '#2b6cb0',
                    800: '#2c5282',
                    900: '#2a4365',
                },
                yellow: {
                    100: '#fffff0',
                    200: '#fefcbf',
                    300: '#faf089',
                    400: '#f6e05e',
                    500: '#ecc94b',
                    600: '#d69e2e',
                    700: '#b7791f',
                    800: '#975a16',
                    900: '#744210',
                },
                purple: {
                    100: '#faf5ff',
                    200: '#e9d8fd',
                    300: '#d6bcfa',
                    400: '#b794f4',
                    500: '#9f7aea',
                    600: '#805ad5',
                    700: '#6b46c1',
                    800: '#553c9a',
                    900: '#44337a',
                },
                indigo: {
                    100: '#ebf4ff',
                    200: '#c3dafe',
                    300: '#a3bffa',
                    400: '#7f9cf5',
                    500: '#667eea',
                    600: '#5a67d8',
                    700: '#4c51bf',
                    800: '#434190',
                    900: '#3c366b',
                },
            },
        },
    },
    plugins: [],
};
