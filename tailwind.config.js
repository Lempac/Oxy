import defaultTheme from 'tailwindcss/defaultTheme';
import daisyui from "daisyui";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        "./resources/**/*.blade.php",
        "./resources/**/*.{vue,ts,js}",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                night: {
                    DEFAULT: '#090c08',
                    100: '#020202',
                    200: '#040503',
                    300: '#060705',
                    400: '#070a07',
                    500: '#090c08',
                    600: '#35472f',
                    700: '#618256',
                    800: '#93b189',
                    900: '#c9d8c4'
                },
                resolution_blue: {
                    DEFAULT: '#14248a',
                    100: '#04071c',
                    200: '#080e37',
                    300: '#0c1553',
                    400: '#101c6f',
                    500: '#14248a',
                    600: '#1d33c8',
                    700: '#465be5',
                    800: '#8492ed',
                    900: '#c1c8f6'
                },
                vista_blue: {
                    DEFAULT: '#9dacff',
                    100: '#000c53',
                    200: '#0019a5',
                    300: '#0025f8',
                    400: '#4b66ff',
                    500: '#9dacff',
                    600: '#b1bdff',
                    700: '#c5ceff',
                    800: '#d8deff',
                    900: '#ecefff'
                },
                tea_green: {
                    DEFAULT: '#c9e4ca',
                    100: '#1d391e',
                    200: '#39723b',
                    300: '#57aa5a',
                    400: '#90c792',
                    500: '#c9e4ca',
                    600: '#d4e9d4',
                    700: '#deefdf',
                    800: '#e9f4ea',
                    900: '#f4faf4'
                },
                mint_cream: {
                    DEFAULT: '#f1f7ed',
                    100: '#2d431e',
                    200: '#5a863c',
                    300: '#89bb68',
                    400: '#bdd9ab',
                    500: '#f1f7ed',
                    600: '#f4f9f1',
                    700: '#f7faf4',
                    800: '#fafcf8',
                    900: '#fcfdfb'
                }
            },
        },
    },

    plugins: [daisyui],
};
