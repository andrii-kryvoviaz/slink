import { cva } from 'class-variance-authority';

export const bookmarkStatContainerTheme = cva(
  'flex items-center transition-colors duration-300',
  {
    variants: {
      variant: {
        card: 'gap-3 rounded-md px-3 py-2 bg-indigo-50/30 dark:bg-indigo-900/20 hover:bg-indigo-100/50 dark:hover:bg-indigo-800/30',
        compact:
          'gap-2 rounded-md px-2 py-1.5 bg-indigo-50/50 dark:bg-indigo-900/30 hover:bg-indigo-100/50 dark:hover:bg-indigo-800/40',
        block:
          'gap-3 rounded-lg px-4 py-3 bg-gray-50/50 dark:bg-gray-800/30 hover:bg-indigo-50/50 dark:hover:bg-indigo-900/20',
        overlay:
          'gap-1.5 rounded-full px-2.5 py-1 bg-black/60 backdrop-blur-sm hover:bg-black/70 shadow-lg',
      },
    },
    defaultVariants: {
      variant: 'card',
    },
  },
);

export const bookmarkStatIconWrapperTheme = cva(
  'flex items-center justify-center rounded-full shrink-0',
  {
    variants: {
      variant: {
        card: 'h-8 w-8 bg-indigo-100/50 dark:bg-indigo-900/30',
        compact: '',
        block: 'h-10 w-10 bg-indigo-100/50 dark:bg-indigo-900/30',
        overlay: '',
      },
    },
    defaultVariants: {
      variant: 'card',
    },
  },
);

export const bookmarkStatIconTheme = cva(
  'text-indigo-600 dark:text-indigo-400',
  {
    variants: {
      variant: {
        card: 'h-4 w-4',
        compact: 'h-3 w-3',
        block: 'h-5 w-5',
        overlay: 'h-3.5 w-3.5 text-white',
      },
    },
    defaultVariants: {
      variant: 'card',
    },
  },
);

export const bookmarkStatLabelTheme = cva(
  'font-medium text-gray-500 dark:text-gray-500',
  {
    variants: {
      variant: {
        card: 'text-xs',
        compact: 'hidden',
        block: 'text-sm text-gray-500 dark:text-gray-400',
        overlay: 'hidden',
      },
    },
    defaultVariants: {
      variant: 'card',
    },
  },
);

export const bookmarkStatValueTheme = cva('font-medium truncate', {
  variants: {
    variant: {
      card: 'text-sm text-gray-700 dark:text-gray-300',
      compact: 'text-xs text-indigo-600 dark:text-indigo-400',
      block: 'text-base font-semibold text-gray-900 dark:text-white',
      overlay: 'text-xs text-white',
    },
  },
  defaultVariants: {
    variant: 'card',
  },
});

export type BookmarkStatVariant = 'card' | 'compact' | 'block' | 'overlay';
