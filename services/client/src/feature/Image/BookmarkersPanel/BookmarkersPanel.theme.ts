import { cva } from 'class-variance-authority';

export const bookmarkersPanelListTheme = cva([
  'border-t border-accent-wash dark:border-accent-wash/20',
]);

export const bookmarkersPanelItemTheme = cva([
  'flex items-center gap-3 px-4 py-3',
  'border-b border-accent-wash dark:border-accent-wash/20',
  'last:border-b-0',
  'hover:bg-accent-wash/50 dark:hover:bg-accent-wash/10',
  'transition-colors duration-150',
]);

export const bookmarkersPanelItemNameTheme = cva([
  'text-sm font-medium',
  'text-foreground',
  'truncate',
]);

export const bookmarkersPanelItemDateTheme = cva([
  'text-xs',
  'text-foreground-muted',
]);

export const bookmarkersPanelEmptyTheme = cva([
  'py-6 px-4',
  'text-center text-sm',
  'text-foreground-muted',
]);

export const bookmarkersPanelErrorTheme = cva([
  'px-4 py-3',
  'text-sm text-danger',
]);

export const bookmarkersPanelMoreTheme = cva([
  'w-full flex items-center justify-center gap-2 px-4 py-3',
  'text-sm font-medium',
  'text-accent',
  'hover:bg-accent-wash/50 dark:hover:bg-accent-wash/10',
  'disabled:opacity-50 disabled:cursor-not-allowed',
  'transition-colors duration-150',
]);
