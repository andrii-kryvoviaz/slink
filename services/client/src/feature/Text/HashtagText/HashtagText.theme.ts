import { cva } from 'class-variance-authority';
import type { VariantProps } from 'class-variance-authority';

export const hashtagVariants = cva(
  [
    'inline-block rounded-md px-1 py-0.5 font-semibold transition-all duration-200',
    'cursor-pointer select-none',
    'focus:outline-none focus:ring-2 focus:ring-offset-1',
    'active:scale-95',
  ],
  {
    variants: {
      variant: {
        default: [
          'border border-info-border dark:border-info-border/30 bg-info-wash dark:bg-info-wash/20 text-info-text',
          'hover:border-info/50 hover:bg-info-solid/20',
          'active:bg-info-solid/30',
          'focus:ring-info/50',
          'dark:border-info/60',
          'dark:hover:border-info/70',
        ],
        primary: [
          'border border-accent-border dark:border-accent-border/30 bg-accent-wash dark:bg-accent-wash/20 text-accent-text',
          'hover:border-accent/50 hover:bg-accent/20',
          'active:bg-accent/30',
          'focus:ring-accent/50',
          'dark:border-accent/60',
          'dark:hover:border-accent/70',
        ],
        secondary: [
          'border border-border bg-muted-soft text-foreground-soft',
          'hover:border-border-strong hover:bg-muted hover:text-foreground',
          'active:bg-foreground-muted/20',
          'focus:ring-border-bold/50',
          'dark:border-foreground-muted/40',
          'dark:hover:border-foreground-muted/50',
        ],
        success: [
          'border border-success-border dark:border-success-border/30 bg-success-wash dark:bg-success-wash/20 text-success-text',
          'hover:border-success/50 hover:bg-success/20',
          'active:bg-success/30',
          'focus:ring-success/50',
          'dark:border-success/50',
          'dark:hover:border-success/60',
        ],
        warning: [
          'border border-warning-border dark:border-warning-border/30 bg-warning-wash dark:bg-warning-wash/20 text-warning-text',
          'hover:border-warning/50 hover:bg-warning/20',
          'active:bg-warning/30',
          'focus:ring-warning/50',
          'dark:border-warning-strong/60',
          'dark:hover:border-warning/60',
        ],
        danger: [
          'border border-danger-border dark:border-danger-border/30 bg-danger-wash dark:bg-danger-wash/20 text-danger-text',
          'hover:border-danger/50 hover:bg-danger/20',
          'active:bg-danger/30',
          'focus:ring-danger/50',
          'dark:border-danger/60',
          'dark:hover:border-danger/70',
        ],
        minimal: [
          'border border-transparent bg-transparent text-foreground-muted',
          'hover:bg-muted-soft hover:text-foreground',
          'active:bg-foreground-muted/20',
          'focus:ring-border-bold/50',
        ],
        glass: [
          'border border-transparent bg-on-surface-inverse/10 text-info',
          'hover:bg-on-surface-inverse/20',
          'active:bg-on-surface-inverse/30',
          'focus:ring-on-surface-inverse/30',
        ],
      },
      size: {
        sm: 'px-1 py-0.5 text-xs rounded',
        md: 'px-1 py-0.5 text-sm rounded-md',
        lg: 'px-1.5 py-1 text-base rounded-md',
      },
      rounded: {
        none: 'rounded-none',
        sm: 'rounded-sm',
        md: 'rounded-md',
        lg: 'rounded-lg',
        full: 'rounded-full',
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'md',
      rounded: 'md',
    },
  },
);

export type HashtagVariant = VariantProps<typeof hashtagVariants>;
