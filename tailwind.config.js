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
      colors: {
        uth: {
          50:  '#ebf8ec',
          100: '#d3f0d6',
          200: '#a7e1ae',
          300: '#79d186',
          400: '#40b84e',
          500: '#0a8e00',
          600: '#087d00',    // color institucional
          700: '#066300',
          800: '#044a00',
          900: '#033700',
        },
      },
      fontFamily: {
        sans: ['Inter', ...defaultTheme.fontFamily.sans],
      },
      boxShadow: {
        soft: '0 10px 30px rgba(0,0,0,0.06)',
      },
    },
  },
  plugins: [forms],
};
