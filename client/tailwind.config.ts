import type { Config } from 'tailwindcss';

import { ThemeCustomizerPlugin } from 'tailwindcss-theme-customizer';

import { defaultTheme, tailwindcssTheme } from './src/theme.default';
import daisyui from 'daisyui';

export default {
  content: [
    './src/**/*.{html,js,svelte,ts}',
    './node_modules/flowbite-svelte/**/*.{html,js,svelte,ts}',
  ],
  theme: {
    extend: {
      animation: {
        'spin-slow': 'spin 2s linear infinite',
      },
      ...tailwindcssTheme,
    },
    screens: {
      xs: '480px',
      sm: '640px',
      md: '768px',
      lg: '1024px',
      xl: '1280px',
      '2xl': '1536px',
      '3xl': '1920px',
    },
  },
  darkMode: 'class',
  plugins: [ThemeCustomizerPlugin(defaultTheme), daisyui],
} satisfies Config;
