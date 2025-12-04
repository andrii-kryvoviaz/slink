import { cva } from 'class-variance-authority';

export const bookmarkersPanelContainerTheme = cva([
  'relative',
  'rounded-lg',
  'border border-indigo-200/50 dark:border-indigo-500/20',
  'bg-gradient-to-br from-indigo-50/80 via-violet-50/60 to-purple-50/50',
  'dark:from-indigo-950/30 dark:via-violet-950/20 dark:to-purple-950/10',
  'overflow-visible',
]);

export const bookmarkersPanelHeaderTheme = cva([
  'w-full',
  'flex items-center gap-3 p-4',
  'cursor-pointer',
  'rounded-lg',
  'hover:bg-indigo-100/50 dark:hover:bg-indigo-900/20',
  'transition-colors duration-150',
]);

export const bookmarkersPanelIconWrapperTheme = cva([
  'flex-shrink-0 w-10 h-10',
  'rounded-lg',
  'bg-gradient-to-br from-indigo-500 to-violet-500',
  'flex items-center justify-center',
  'shadow-sm shadow-indigo-500/20 dark:shadow-indigo-500/10',
]);

export const bookmarkersPanelIconTheme = cva(['w-5 h-5 text-white']);

export const bookmarkersPanelLabelTheme = cva([
  'text-xs font-medium uppercase tracking-wide',
  'text-indigo-600/70 dark:text-indigo-400/70',
]);

export const bookmarkersPanelValueTheme = cva([
  'text-sm font-semibold',
  'text-gray-900 dark:text-white',
]);

export const bookmarkersPanelChevronTheme = cva([
  'w-5 h-5',
  'text-gray-400 dark:text-gray-500',
  'transition-transform duration-200',
]);

export const bookmarkersPanelListTheme = cva([
  'absolute left-0 right-0 top-full mt-2 z-50',
  'rounded-lg',
  'border border-indigo-200/50 dark:border-indigo-500/20',
  'bg-white dark:bg-gray-900',
  'shadow-lg shadow-indigo-500/10 dark:shadow-black/20',
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
