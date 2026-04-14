import type { ViewMode } from '@slink/lib/settings';

export type ViewModeConfig = {
  label: string;
  icon: string;
};

class ViewModeRegistryState {
  get grid(): ViewModeConfig {
    return { label: 'Grid', icon: 'heroicons:squares-2x2' };
  }
  get list(): ViewModeConfig {
    return { label: 'List', icon: 'heroicons:bars-3' };
  }
  get table(): ViewModeConfig {
    return { label: 'Table', icon: 'heroicons:table-cells' };
  }
}

export const viewModeRegistry: Record<ViewMode, ViewModeConfig> =
  new ViewModeRegistryState();

export interface ViewModeToggleProps {
  value: ViewMode;
  modes: ViewMode[];
  className?: string;
  disabled?: boolean;
  on: {
    change: (newMode: ViewMode) => void;
  };
}
