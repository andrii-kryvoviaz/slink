import { cva } from 'class-variance-authority';

export const BadgeTheme = cva(
  'inline-flex items-center justify-center rounded-full border font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2',
  {
    variants: {
      variant: {
        default:
          'border-muted-foreground/20 bg-muted-foreground/10 text-foreground-soft [a&]:hover:bg-muted-foreground/15 focus-visible:ring-border-stronger/20 dark:border-muted-foreground/30',
        blue: 'border-info/20 bg-info/10 text-info-subtle-foreground [a&]:hover:bg-info/15 focus-visible:ring-info/20 dark:border-info/30',
        emerald:
          'border-decor-emerald/20 bg-decor-emerald/10 text-decor-emerald-subtle-foreground [a&]:hover:bg-decor-emerald/15 focus-visible:ring-decor-emerald/20 dark:border-decor-emerald/30',
        slate:
          'border-muted-foreground/20 bg-muted-foreground/10 text-foreground-soft [a&]:hover:bg-muted-foreground/15 focus-visible:ring-border-stronger/20 dark:border-muted-foreground/30',
        purple:
          'border-accent/20 bg-accent/10 text-accent-subtle-foreground [a&]:hover:bg-accent/15 focus-visible:ring-accent/20 dark:border-accent/30',
        amber:
          'border-warning/20 bg-warning/10 text-warning-subtle-foreground [a&]:hover:bg-warning/15 focus-visible:ring-warning/20 dark:border-warning/30',
        orange:
          'border-decor-orange/20 bg-decor-orange/10 text-decor-orange-subtle-foreground [a&]:hover:bg-decor-orange/15 focus-visible:ring-decor-orange/20 dark:border-decor-orange/30',
        red: 'border-danger/20 bg-danger/10 text-danger-subtle-foreground [a&]:hover:bg-danger/15 focus-visible:ring-danger/20 dark:border-danger/30',
        success:
          'border-success/20 bg-success/10 text-success-subtle-foreground [a&]:hover:bg-success/15 focus-visible:ring-success/20 dark:border-success/30',
        destructive:
          'border-danger/20 bg-danger/10 text-danger-subtle-foreground [a&]:hover:bg-danger/15 focus-visible:ring-danger/20 dark:border-danger/30',
        warning:
          'border-warning/20 bg-warning/10 text-warning-subtle-foreground [a&]:hover:bg-warning/15 focus-visible:ring-warning/20 dark:border-warning/30',
        info: 'border-info/20 bg-info/10 text-info-subtle-foreground [a&]:hover:bg-info/15 focus-visible:ring-info/20 dark:border-info/30',
        indigo:
          'border-accent/20 bg-accent-fill/10 text-accent-subtle-foreground [a&]:hover:bg-accent-fill/15 focus-visible:ring-accent/20 dark:border-accent/30',
        pink: 'border-decor-pink/20 bg-decor-pink/10 text-decor-pink-subtle-foreground [a&]:hover:bg-decor-pink/15 focus-visible:ring-decor-pink/20 dark:border-decor-pink/30',
        neutral:
          'border-muted-foreground/20 bg-muted-foreground/10 text-foreground-soft [a&]:hover:bg-muted-foreground/15 focus-visible:ring-border-stronger/20 dark:border-muted-foreground/30',
        gradient:
          'border-0 bg-gradient-to-br from-info-fill to-accent-strong text-accent-foreground font-semibold [a&]:hover:from-info-strong [a&]:hover:to-accent focus-visible:ring-info/20',
        neon: [
          'bg-info-fill/20 text-info dark:text-info-subtle-foreground',
          'border border-info/20 dark:border-info/30',
          'hover:bg-info-fill/30',
          'focus-within:ring-2 focus-within:ring-info/30 focus-within:ring-offset-2',
          'transition-all duration-200 cursor-pointer',
        ],
        minimal:
          'bg-muted-subtle text-foreground-soft border border-muted dark:border-muted-foreground/30',
        glass:
          'border-transparent bg-surface-inverse-foreground/5 text-surface-inverse-foreground/70 backdrop-blur-sm',
      },
      size: {
        xs: 'px-1.5 py-0.5 text-[10px]',
        sm: 'px-2 py-0.5 text-xs',
        md: 'px-3 py-1 text-sm',
        lg: 'px-3.5 py-1.5 text-sm',
      },
      outline: {
        true: 'bg-transparent',
      },
    },
    compoundVariants: [
      {
        variant: 'neon',
        class: 'rounded-lg',
      },
      {
        variant: 'minimal',
        class: 'rounded-lg',
      },
      {
        variant: 'glass',
        class: 'rounded-md',
      },
    ],
    defaultVariants: {
      variant: 'default',
      size: 'md',
    },
  },
);
