import { cva } from 'class-variance-authority';

export const viewCountBadgeContainerTheme = cva(
  'flex items-center transition-colors duration-300',
  {
    variants: {
      variant: {
        card: 'gap-3 rounded-md px-3 py-2 bg-muted/25 hover:bg-muted-hover/30 dark:hover:bg-muted-hover/45',
        compact:
          'gap-2 rounded-md px-2 py-1.5 bg-muted/30 hover:bg-muted-hover/30 dark:hover:bg-muted-hover/55',
        overlay:
          'gap-1.5 rounded-full px-2.5 py-1 bg-card/95 border-border-strong/50 dark:bg-scrim/60 dark:border-border/50 backdrop-blur-md shadow-lg border',
        badge: 'gap-2 rounded-md bg-muted/50 px-3 py-2 border border-border',
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
        card: 'h-8 w-8 bg-border/30',
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
      card: 'h-4 w-4 text-foreground-muted',
      compact: 'h-3 w-3 text-foreground-muted',
      overlay: 'h-3 w-3 text-foreground-soft',
      badge: 'h-4 w-4 text-foreground-muted',
    },
  },
  defaultVariants: {
    variant: 'card',
  },
});

export const viewCountBadgeLabelTheme = cva(
  'font-medium text-foreground-muted',
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
      card: 'text-sm text-foreground-soft',
      compact: 'text-xs text-foreground-soft',
      overlay: 'text-[11px] text-foreground-soft',
      badge: 'text-sm font-medium text-foreground-soft',
    },
  },
  defaultVariants: {
    variant: 'card',
  },
});

export type ViewCountBadgeVariant = 'card' | 'compact' | 'overlay' | 'badge';
