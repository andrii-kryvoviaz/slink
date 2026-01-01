import { cva } from 'class-variance-authority';
import type { VariantProps } from 'class-variance-authority';

export const dimensionsBadgeContainerTheme = cva(
  'flex items-center transition-colors duration-300',
  {
    variants: {
      variant: {
        overlay:
          'gap-1.5 rounded-full px-2.5 py-1 bg-white/95 border-gray-300/50 dark:bg-black/60 backdrop-blur-md shadow-lg border dark:border-white/10',
        compact:
          'gap-1.5 rounded-md px-2 py-1 bg-gray-50/50 dark:bg-gray-800/30',
      },
    },
    defaultVariants: {
      variant: 'overlay',
    },
  },
);

export const dimensionsBadgeIconTheme = cva('shrink-0', {
  variants: {
    variant: {
      overlay: 'h-3 w-3 text-gray-600 dark:text-gray-300',
      compact: 'h-3 w-3 text-gray-500 dark:text-gray-400',
    },
  },
  defaultVariants: {
    variant: 'overlay',
  },
});

export const dimensionsBadgeValueTheme = cva('font-medium', {
  variants: {
    variant: {
      overlay: 'text-[11px] text-gray-700 dark:text-gray-200',
      compact: 'text-xs text-gray-600 dark:text-gray-300',
    },
  },
  defaultVariants: {
    variant: 'overlay',
  },
});

export type DimensionsBadgeVariant = NonNullable<
  VariantProps<typeof dimensionsBadgeContainerTheme>['variant']
>;
