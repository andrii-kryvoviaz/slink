import { derived } from 'svelte/store';

import type { Setter } from '@slink/lib/settings/Settings.types';
import type { ViewMode } from '@slink/lib/settings/setters/viewMode';

export type HistoryViewMode = ViewMode;

export type HistorySettings = {
  viewMode: HistoryViewMode;
};

export const HistorySetter: Setter<'history', HistorySettings> = (value) => {
  return {
    value,
    viewMode: derived(value, ($value) => $value.viewMode),
  };
};
