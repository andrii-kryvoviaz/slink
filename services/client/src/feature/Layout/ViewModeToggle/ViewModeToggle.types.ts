import type { ViewMode } from '@slink/lib/settings';

export type ViewModeConfig = {
  label: string;
  icon: string;
};

export interface ViewModeToggleProps {
  value: ViewMode;
  modes: readonly ViewMode[];
  size?: 'sm' | 'md' | 'lg' | 'xl';
  rounded?: 'md' | 'lg' | 'pill';
  labelMode?: 'none' | 'active';
  className?: string;
  disabled?: boolean;
  on: {
    change: (newMode: ViewMode) => void;
  };
}
