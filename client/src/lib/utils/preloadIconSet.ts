import { loadIcons } from '@iconify/svelte';

export const preloadIconSet = (icons: string[]) => {
  loadIcons(icons, (loaded, missing, pending, unsubscribe) => {
    if (loaded.length === icons.length) {
      unsubscribe();
    }
  });
};