/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './src/**/*.{html,js,svelte,ts}',
    './node_modules/flowbite-svelte/**/*.{html,js,svelte,ts}',
  ],
  theme: {
    extend: {
      backgroundColor: {
        card: {
          primary: 'var(--bg-card-primary)',
          secondary: 'var(--bg-card-secondary)',
        },
      },
      borderColor: {
        primary: 'var(--color-border-primary)',
        secondary: 'var(--color-border-secondary)',
        accent: 'var(--color-accent)',
      },
      textColor: {
        color: {
          primary: 'var(--color-text-primary)',
          secondary: 'var(--color-text-secondary)',
          disabled: 'var(--color-text-disabled)',
          accent: 'var(--color-accent)',
        },
      },
      gradientColorStops: {
        bg: {
          start: 'var(--color-bg-from)',
          end: 'var(--color-bg-to)',
        },
      },
    },
  },
  plugins: [],
  darkMode: 'class',
};
