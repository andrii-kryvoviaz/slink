import { derived, writable } from 'svelte/store';

import en from './locales/en.json';
import zh from './locales/zh.json';

const messages = { en, zh } as const;
type Locale = keyof typeof messages;

export const locale = writable<Locale>('en');

export const t = derived(locale, ($locale) => (key: string) => {
  const value = key
    .split('.')
    .reduce<any>((obj, part) => obj?.[part], messages[$locale]);
  return typeof value === 'string' ? value : key;
});
