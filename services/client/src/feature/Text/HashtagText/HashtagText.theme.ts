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
          'border border-info-border bg-info-subtle text-info-subtle-foreground',
          'hover:border-info/50 hover:bg-info-fill/20',
          'active:bg-info-fill/30',
          'focus:ring-info/50',
          'dark:border-info/60',
          'dark:hover:border-info/70',
        ],
        primary: [
          'border border-accent-border bg-accent-subtle text-accent-subtle-foreground',
          'hover:border-accent/50 hover:bg-accent/20',
          'active:bg-accent/30',
          'focus:ring-accent/50',
          'dark:border-accent/60',
          'dark:hover:border-accent/70',
        ],
        secondary: [
          'border border-border bg-muted-subtle text-foreground-soft',
          'hover:border-border-strong hover:bg-muted hover:text-foreground',
          'active:bg-muted-foreground/20',
          'focus:ring-border-stronger/50',
          'dark:border-muted-foreground/40',
          'dark:hover:border-muted-foreground/50',
        ],
        success: [
          'border border-success-border bg-success-subtle text-success-subtle-foreground',
          'hover:border-success/50 hover:bg-success/20',
          'active:bg-success/30',
          'focus:ring-success/50',
          'dark:border-success/50',
          'dark:hover:border-success/60',
        ],
        warning: [
          'border border-warning-border bg-warning-subtle text-warning-subtle-foreground',
          'hover:border-warning/50 hover:bg-warning/20',
          'active:bg-warning/30',
          'focus:ring-warning/50',
          'dark:border-warning-strong/60',
          'dark:hover:border-warning/60',
        ],
        danger: [
          'border border-danger-border bg-danger-subtle text-danger-subtle-foreground',
          'hover:border-danger/50 hover:bg-danger/20',
          'active:bg-danger/30',
          'focus:ring-danger/50',
          'dark:border-danger/60',
          'dark:hover:border-danger/70',
        ],
        minimal: [
          'border border-transparent bg-transparent text-muted-foreground',
          'hover:bg-muted-subtle hover:text-foreground',
          'active:bg-muted-foreground/20',
          'focus:ring-border-stronger/50',
        ],
        glass: [
          'border border-transparent bg-surface-inverse-foreground/10 text-info',
          'hover:bg-surface-inverse-foreground/20',
          'active:bg-surface-inverse-foreground/30',
          'focus:ring-surface-inverse-foreground/30',
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
