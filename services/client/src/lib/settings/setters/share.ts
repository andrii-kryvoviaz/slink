import { derived } from 'svelte/store';

import type { Setter } from '@slink/lib/settings/Settings.types';

export type ShareFormat = 'direct' | 'markdown' | 'bbcode' | 'html' | 'image';

export type ShareSettings = {
  format: ShareFormat;
};

export const ShareSetter: Setter<'share', ShareSettings> = (value) => {
  return {
    value,
    format: derived(value, ($value) => $value.format),
  };
};
