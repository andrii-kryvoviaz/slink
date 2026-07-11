import { cva } from 'class-variance-authority';

export const bookmarkersPanelListTheme = cva([
  'border-t border-indigo-100/50 dark:border-indigo-500/10',
]);

export const bookmarkersPanelItemTheme = cva([
  'flex items-center gap-3 px-4 py-3',
  'border-b border-indigo-100/50 dark:border-indigo-500/10',
  'last:border-b-0',
  'hover:bg-indigo-50/50 dark:hover:bg-indigo-900/20',
  'transition-colors duration-150',
]);

export const bookmarkersPanelItemNameTheme = cva([
  'text-sm font-medium',
  'text-gray-900 dark:text-white',
  'truncate',
]);

export const bookmarkersPanelItemDateTheme = cva([
  'text-xs',
  'text-gray-500 dark:text-gray-400',
]);

export const bookmarkersPanelEmptyTheme = cva([
  'py-6 px-4',
  'text-center text-sm',
  'text-gray-500 dark:text-gray-400',
]);

export const bookmarkersPanelErrorTheme = cva([
  'px-4 py-3',
  'text-sm text-red-500 dark:text-red-400',
]);

export const bookmarkersPanelMoreTheme = cva([
  'w-full flex items-center justify-center gap-2 px-4 py-3',
  'text-sm font-medium',
  'text-indigo-600 dark:text-indigo-400',
  'hover:bg-indigo-50/50 dark:hover:bg-indigo-900/20',
  'disabled:opacity-50 disabled:cursor-not-allowed',
  'transition-colors duration-150',
]);
