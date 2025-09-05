export type ViewMode = 'grid' | 'list';

export interface ViewModeOption {
  value: ViewMode;
  label: string;
  icon: string;
}

export interface ViewModeToggleProps {
  value: ViewMode;
  options?: ViewModeOption[];
  size?: 'sm' | 'md' | 'lg';
  className?: string;
  onValueChange: (value: ViewMode) => void;
}
