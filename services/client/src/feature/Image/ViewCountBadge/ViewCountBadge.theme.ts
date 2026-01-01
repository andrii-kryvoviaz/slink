import { cva } from 'class-variance-authority';

export const viewCountBadgeContainerTheme = cva(
  'flex items-center transition-colors duration-300',
  {
    variants: {
      variant: {
        card: 'gap-3 rounded-md px-3 py-2 bg-gray-50/30 dark:bg-gray-800/20 hover:bg-gray-100/50 dark:hover:bg-gray-700/30',
        compact:
          'gap-2 rounded-md px-2 py-1.5 bg-gray-50/50 dark:bg-gray-800/30 hover:bg-gray-100/50 dark:hover:bg-gray-700/40',
        overlay:
          'gap-1.5 rounded-full px-2.5 py-1 bg-white/95 border-gray-300/50 dark:bg-black/60 backdrop-blur-md shadow-lg border dark:border-white/10',
        badge:
          'gap-2 rounded-md bg-gray-50 dark:bg-gray-800/40 px-3 py-2 border border-gray-200 dark:border-gray-700',
      },
    },
    defaultVariants: {
      variant: 'card',
    },
  },
);

export const viewCountBadgeIconWrapperTheme = cva(
  'flex items-center justify-center rounded-full shrink-0',
  {
    variants: {
      variant: {
        card: 'h-8 w-8 bg-gray-100/50 dark:bg-gray-700/30',
        compact: '',
        overlay: '',
        badge: '',
      },
    },
    defaultVariants: {
      variant: 'card',
    },
  },
);

export const viewCountBadgeIconTheme = cva('shrink-0', {
  variants: {
    variant: {
      card: 'h-4 w-4 text-gray-600 dark:text-gray-400',
      compact: 'h-3 w-3 text-gray-500 dark:text-gray-400',
      overlay: 'h-3 w-3 text-gray-600 dark:text-gray-300',
      badge: 'h-4 w-4 text-gray-500 dark:text-gray-400',
    },
  },
  defaultVariants: {
    variant: 'card',
  },
});

export const viewCountBadgeLabelTheme = cva(
  'font-medium text-gray-500 dark:text-gray-500',
  {
    variants: {
      variant: {
        card: 'text-xs',
        compact: 'hidden',
        overlay: 'hidden',
        badge: 'hidden',
      },
    },
    defaultVariants: {
      variant: 'card',
    },
  },
);

export const viewCountBadgeValueTheme = cva('font-medium truncate', {
  variants: {
    variant: {
      card: 'text-sm text-gray-700 dark:text-gray-300',
      compact: 'text-xs text-gray-600 dark:text-gray-300',
      overlay: 'text-[11px] text-gray-700 dark:text-gray-200',
      badge: 'text-sm font-medium text-gray-700 dark:text-gray-300',
    },
  },
  defaultVariants: {
    variant: 'card',
  },
});

export type ViewCountBadgeVariant = 'card' | 'compact' | 'overlay' | 'badge';
