import type { Config } from 'tailwindcss';

import { ThemeCustomizerPlugin } from 'tailwindcss-theme-cutomizer';

import { defaultTheme, tailwindcssTheme } from './theme.default';

export default {
  content: [
    './src/**/*.{html,js,svelte,ts}',
    './node_modules/flowbite-svelte/**/*.{html,js,svelte,ts}',
  ],
  theme: {
    extend: tailwindcssTheme
  },
  plugins: [ThemeCustomizerPlugin(defaultTheme)],
} satisfies Config;
