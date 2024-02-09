import { loadIcons } from '@iconify/svelte';

const icons: string[] = [
  'material-symbols-light:upload',
  'mingcute:loading-line',
  'clarity:success-standard-line',
  'clarity:error-standard-line',
  'clarity:info-standard-line',
  'clarity:warning-standard-line',
  'system-uicons:external',
  'mdi:check',
  'carbon:link',
  'carbon:unlink',
  'ep:info-filled',
  'akar-icons:github-fill',
  'ph:moon-thin',
  'ph:sun-thin',
];

export const Icons = {
  init: () => {
    loadIcons(icons, (loaded, missing, pending, unsubscribe) => {
      if (loaded.length === icons.length) {
        unsubscribe();
      }
    });
  },
  list: icons,
};
