import { cva } from 'class-variance-authority';
import type { VariantProps } from 'class-variance-authority';

export const tagDropdownContentVariants = cva(
  'z-50 w-[var(--bits-popover-anchor-width)] data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[side=bottom]:slide-in-from-top-2',
  {
    variants: {
      variant: {
        default: [
          'bg-popover',
          'backdrop-blur-md',
          'border border-border/80 dark:border-border/50',
          'rounded-xl',
          'shadow-xl shadow-border/50 dark:shadow-scrim/60',
        ],
        neon: [
          'bg-popover',
          'backdrop-blur-md',
          'border border-border/60 dark:border-border/50',
          'rounded-xl',
          'shadow-2xl shadow-foreground-muted/5 dark:shadow-scrim/50',
          'ring-1 ring-muted/20 dark:ring-border/25',
        ],
        minimal: [
          'bg-popover',
          'backdrop-blur-md',
          'border border-border/60 dark:border-border/50',
          'rounded-xl',
          'shadow-lg shadow-border/40 dark:shadow-scrim/50',
        ],
        subtle: [
          'bg-popover',
          'backdrop-blur-md',
          'border border-border/60 dark:border-border/50',
          'rounded-xl',
          'shadow-xl shadow-border/50 dark:shadow-scrim/60',
        ],
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  },
);

export const tagDropdownDividerVariants = cva('mx-3 my-1.5', {
  variants: {
    variant: {
      default: 'border-t border-border/30 dark:border-border/50',
      neon: 'border-t border-border/20 dark:border-border/50',
      minimal: 'border-t border-border/25 dark:border-border/50',
      subtle: 'border-t border-muted dark:border-border/50',
    },
  },
  defaultVariants: {
    variant: 'default',
  },
});

export type TagDropdownContentVariants = VariantProps<
  typeof tagDropdownContentVariants
>;
export type TagDropdownDividerVariants = VariantProps<
  typeof tagDropdownDividerVariants
>;
