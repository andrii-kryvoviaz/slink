import { derived } from 'svelte/store';

import type { Setter } from '@slink/lib/settings/Settings.types';

export type HistoryViewMode = 'grid' | 'list';

export type HistorySettings = {
  viewMode: HistoryViewMode;
};

export const HistorySetter: Setter<'history', HistorySettings> = (value) => {
  return {
    value,
    viewMode: derived(value, ($value) => $value.viewMode),
  };
};
