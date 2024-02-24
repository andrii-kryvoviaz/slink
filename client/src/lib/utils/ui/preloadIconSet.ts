import { browser } from '$app/environment';
import { loadIcons } from '@iconify/svelte';

export const preloadIconSet = (icons: string[]) => {
  if (!browser) return;

  loadIcons(icons, (loaded, missing, pending, unsubscribe) => {
    if (loaded.length === icons.length) {
      unsubscribe();
    }
  });
};
