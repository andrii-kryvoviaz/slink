import type { ViewMode } from '@slink/lib/settings';

export type ViewModeConfig = {
  labelKey: string;
  icon: string;
};

export const viewModeRegistry: Record<ViewMode, ViewModeConfig> = {
  grid: { labelKey: 'table.view_modes.grid', icon: 'heroicons:squares-2x2' },
  list: { labelKey: 'table.view_modes.list', icon: 'heroicons:bars-3' },
  table: { labelKey: 'table.view_modes.table', icon: 'heroicons:table-cells' },
};

export interface ViewModeToggleProps {
  value: ViewMode;
  modes: ViewMode[];
  className?: string;
  disabled?: boolean;
  on: {
    change: (newMode: ViewMode) => void;
  };
}
