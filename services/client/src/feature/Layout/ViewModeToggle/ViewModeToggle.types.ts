import type { ViewMode } from '@slink/lib/settings';

export type ViewModeConfig = {
  label: string;
  icon: string;
};

export const viewModeRegistry: Record<ViewMode, ViewModeConfig> = {
  grid: { label: 'Grid', icon: 'heroicons:squares-2x2' },
  list: { label: 'List', icon: 'heroicons:bars-3' },
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
