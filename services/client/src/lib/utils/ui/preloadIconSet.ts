import { loadIcons } from '@iconify/svelte';

export const preloadIconSet = (icons: string[]): Promise<void> => {
  return new Promise((resolve) => {
    loadIcons(icons, (loaded, missing, pending, unsubscribe) => {
      if (pending.length === 0) {
        unsubscribe();
        resolve();
      }
    });
  });
};
