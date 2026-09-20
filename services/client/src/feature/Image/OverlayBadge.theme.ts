import { cva } from 'class-variance-authority';
import type { VariantProps } from 'class-variance-authority';

export const overlayBadgeBaseClass =
  'flex items-center whitespace-nowrap transition-colors duration-300';

export const overlayBadgeSurfaceClass =
  'gap-1.5 rounded-full px-2.5 py-1 bg-card/95 border-border-strong/50 dark:bg-scrim/60 dark:border-border/50 backdrop-blur-md shadow-lg border';

export const overlayBadgeContainerTheme = cva(overlayBadgeBaseClass, {
  variants: {
    variant: {
      overlay: overlayBadgeSurfaceClass,
      compact: 'gap-1.5 rounded-md px-2 py-1 bg-muted/30',
    },
  },
  defaultVariants: {
    variant: 'overlay',
  },
});

export const overlayBadgeIconTheme = cva('shrink-0', {
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

export const overlayBadgeValueTheme = cva('font-medium', {
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

export type OverlayBadgeVariant = NonNullable<
  VariantProps<typeof overlayBadgeContainerTheme>['variant']
>;
