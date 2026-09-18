import type { ViewMode } from '@slink/lib/settings';
import { localize } from '@slink/lib/utils/i18n';

import type { ViewModeConfig } from './ViewModeToggle.types';

export const viewModeRegistry: Record<ViewMode, ViewModeConfig> = {
  grid: {
    get label() {
      return localize('Grid');
    },
    icon: 'heroicons:squares-2x2',
  },
  list: {
    get label() {
      return localize('List');
    },
    icon: 'heroicons:bars-3',
  },
  table: {
    get label() {
      return localize('Table');
    },
    icon: 'heroicons:table-cells',
  },
  tree: {
    get label() {
      return localize('Tree');
    },
    icon: 'lucide:list-tree',
  },
};
