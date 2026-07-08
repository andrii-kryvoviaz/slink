import type { Snippet } from 'svelte';

import type { ToggleGroupOption } from '../toggle-group/toggle-group.types';

export interface TogglePillsItemSnippetProps<T extends string = string> {
  option: ToggleGroupOption<T>;
  pressed: boolean;
}

export interface TogglePillsProps<T extends string = string> {
  options: ToggleGroupOption<T>[];
  value: T[];
  onValueChange?: (value: T[]) => void;
  minItems?: number;
  size?: 'sm' | 'md';
  disabled?: boolean;
  className?: string;
  'aria-label'?: string;
  item?: Snippet<[TogglePillsItemSnippetProps<T>]>;
}
