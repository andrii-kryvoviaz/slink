import daisyui from 'daisyui';
import { ThemeCustomizerPlugin } from 'tailwindcss-theme-customizer';

import { defaultTheme, tailwindcssTheme } from './src/theme.default';

import type { Config } from 'tailwindcss';
export default {
  content: ['./src/**/*.{html,js,svelte,ts}'],
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
  daisyui: {
    logs: false,
  },
  plugins: [ThemeCustomizerPlugin(defaultTheme), daisyui],
} satisfies Config;
