import { derived, type Readable } from 'svelte/store';
import { cookie } from '@slink/utils/cookie';
import { cookieReadableStore } from '@slink/store/provider/cookieStore';

export enum Theme {
  LIGHT = 'light',
  DARK = 'dark',
}

const theme = cookieReadableStore<Theme>('theme', Theme.DARK);

export const setTheme = (value: Theme) => {
  cookie.set('theme', value);
};

export const isDarkTheme: Readable<boolean> = derived(theme, ($theme): boolean => $theme === Theme.DARK);
export const isLightTheme: Readable<boolean> = derived(theme, ($theme): boolean => $theme === Theme.LIGHT);
export const currentTheme: Readable<Theme> = theme;