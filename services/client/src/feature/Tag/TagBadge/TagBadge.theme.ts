import { cva } from 'class-variance-authority';
import type { VariantProps } from 'class-variance-authority';

export const tagBadgeCloseButtonVariants = cva(
  'h-4 w-4 p-0 rounded-full ml-1 transition-colors flex items-center justify-center opacity-60 hover:opacity-100',
  {
    variants: {
      variant: {
        default:
          'text-foreground-soft hover:text-foreground hover:bg-muted-foreground/20',
        blue: 'text-info-subtle-foreground hover:text-info-deep-foreground hover:bg-info/20',
        emerald:
          'text-decor-emerald-subtle-foreground hover:text-decor-emerald-deep-foreground hover:bg-decor-emerald/20',
        slate:
          'text-foreground-soft hover:text-foreground hover:bg-muted-foreground/20',
        purple:
          'text-accent-subtle-foreground hover:text-accent-deep-foreground hover:bg-accent/20',
        amber:
          'text-warning-subtle-foreground hover:text-warning-deep-foreground hover:bg-warning/20',
        orange:
          'text-decor-orange-subtle-foreground hover:text-decor-orange-deep-foreground hover:bg-decor-orange/20',
        red: 'text-danger-subtle-foreground hover:text-danger-deep-foreground hover:bg-danger/20',
        success:
          'text-success-subtle-foreground hover:text-success-deep-foreground hover:bg-success/20',
        destructive:
          'text-danger-subtle-foreground hover:text-danger-deep-foreground hover:bg-danger/20',
        warning:
          'text-warning-subtle-foreground hover:text-warning-deep-foreground hover:bg-warning/20',
        info: 'text-info-subtle-foreground hover:text-info-deep-foreground hover:bg-info/20',
        indigo:
          'text-accent-subtle-foreground hover:text-accent-deep-foreground hover:bg-accent/20',
        pink: 'text-decor-pink-subtle-foreground hover:text-decor-pink-deep-foreground hover:bg-decor-pink/20',
        neutral:
          'text-foreground-soft hover:text-foreground hover:bg-muted-foreground/20',
        gradient: 'text-accent-foreground hover:bg-accent-foreground/20',
        neon: 'text-info dark:text-info-subtle-foreground hover:text-info-subtle-foreground dark:hover:text-info-deep-foreground hover:bg-info/20',
        minimal:
          'text-muted-foreground hover:text-foreground hover:bg-muted-foreground/20',
        glass:
          'text-surface-inverse-foreground/70 hover:text-surface-inverse-foreground hover:bg-surface-inverse-foreground/10',
      },
    },
    defaultVariants: {
      variant: 'neon',
    },
  },
);

export type TagBadgeCloseButtonVariants = VariantProps<
  typeof tagBadgeCloseButtonVariants
>;

export const tagBadgeCollapsedVariants = cva('flex items-center gap-1', {
  variants: {
    disableHover: {
      true: '',
      false: 'group-hover:hidden',
    },
  },
  defaultVariants: {
    disableHover: false,
  },
});
