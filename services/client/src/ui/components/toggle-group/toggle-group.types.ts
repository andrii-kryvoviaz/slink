export interface ToggleGroupOption<T = string> {
  value: T;
  label?: string;
  icon?: string;
  disabled?: boolean;
}

export interface ToggleGroupProps<T = string> {
  value: T;
  options: ToggleGroupOption<T>[];
  onValueChange: (value: T) => void;
  size?: 'sm' | 'md' | 'lg';
  orientation?: 'horizontal' | 'vertical';
  className?: string;
  'aria-label'?: string;
  disabled?: boolean;
}
