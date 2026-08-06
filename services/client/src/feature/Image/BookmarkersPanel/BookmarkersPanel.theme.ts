import { cva } from 'class-variance-authority';

export const bookmarkersPanelListTheme = cva(['border-t border-accent-subtle']);

export const bookmarkersPanelItemTheme = cva([
  'flex items-center gap-3 px-4 py-3',
  'border-b border-accent-subtle',
  'last:border-b-0',
  'hover:bg-accent-subtle/50',
  'transition-colors duration-150',
]);

export const bookmarkersPanelItemNameTheme = cva([
  'text-sm font-medium',
  'text-foreground',
  'truncate',
]);

export const bookmarkersPanelItemDateTheme = cva([
  'text-xs',
  'text-muted-foreground',
]);

export const bookmarkersPanelEmptyTheme = cva([
  'py-6 px-4',
  'text-center text-sm',
  'text-muted-foreground',
]);

export const bookmarkersPanelErrorTheme = cva([
  'px-4 py-3',
  'text-sm text-danger',
]);

export const bookmarkersPanelMoreTheme = cva([
  'w-full flex items-center justify-center gap-2 px-4 py-3',
  'text-sm font-medium',
  'text-accent',
  'hover:bg-accent-subtle/50',
  'disabled:opacity-50 disabled:cursor-not-allowed',
  'transition-colors duration-150',
]);
