import { cva } from 'class-variance-authority';
import type { VariantProps } from 'class-variance-authority';

export const dimensionsBadgeContainerTheme = cva(
  'flex items-center transition-colors duration-300',
  {
    variants: {
      variant: {
        overlay:
          'gap-1.5 rounded-full px-2.5 py-1 bg-card/95 border-border-strong/50 dark:bg-scrim/60 dark:border-border/50 backdrop-blur-md shadow-lg border',
        compact: 'gap-1.5 rounded-md px-2 py-1 bg-muted/30',
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
      overlay: 'h-3 w-3 text-foreground-soft',
      compact: 'h-3 w-3 text-foreground-muted',
    },
  },
  defaultVariants: {
    variant: 'overlay',
  },
});

export const dimensionsBadgeValueTheme = cva('font-medium', {
  variants: {
    variant: {
      overlay: 'text-[11px] text-foreground-soft',
      compact: 'text-xs text-foreground-soft',
    },
  },
  defaultVariants: {
    variant: 'overlay',
  },
});

export type DimensionsBadgeVariant = NonNullable<
  VariantProps<typeof dimensionsBadgeContainerTheme>['variant']
>;
