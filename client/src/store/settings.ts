import { derived, writable } from 'svelte/store';
import { browser } from '$app/environment';

type Theme = 'light' | 'dark';

let initialTheme: Theme = 'dark';

if (browser) {
  initialTheme = localStorage.getItem('theme') as Theme || 'dark';
}

const theme = writable<Theme>(initialTheme);

export const setTheme = (value: Theme) => {
  if (browser) {
    localStorage.setItem('theme', value);
  }
  theme.set(value);
};

export const isDarkTheme = derived(theme, ($theme) => $theme === 'dark');

// run side effect to toggle dark mode
theme.subscribe(($theme) => {
  if(!browser) return;

  if ($theme === 'dark') {
    document.documentElement.classList.add('dark');
  } else {
    document.documentElement.classList.remove('dark');
  }
});