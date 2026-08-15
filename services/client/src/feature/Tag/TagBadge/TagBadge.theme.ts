import { cva } from 'class-variance-authority';
import type { VariantProps } from 'class-variance-authority';

export const tagBadgeCloseButtonVariants = cva(
  'h-4 w-4 p-0 rounded-full ml-1 transition-colors flex items-center justify-center opacity-60 hover:opacity-100',
  {
    variants: {
      variant: {
        default:
          'text-foreground-soft hover:text-foreground hover:bg-foreground-muted/20',
        blue: 'text-info-text hover:text-info-text-strong hover:bg-info/20',
        emerald:
          'text-decor-emerald-text hover:text-decor-emerald-text-strong hover:bg-decor-emerald/20',
        slate:
          'text-foreground-soft hover:text-foreground hover:bg-foreground-muted/20',
        purple:
          'text-accent-text hover:text-accent-text-strong hover:bg-accent/20',
        amber:
          'text-warning-text hover:text-warning-text-strong hover:bg-warning/20',
        orange:
          'text-decor-orange-text hover:text-decor-orange-text-strong hover:bg-decor-orange/20',
        red: 'text-danger-text hover:text-danger-text-strong hover:bg-danger/20',
        success:
          'text-success-text hover:text-success-text-strong hover:bg-success/20',
        destructive:
          'text-danger-text hover:text-danger-text-strong hover:bg-danger/20',
        warning:
          'text-warning-text hover:text-warning-text-strong hover:bg-warning/20',
        info: 'text-info-text hover:text-info-text-strong hover:bg-info/20',
        indigo:
          'text-accent-text hover:text-accent-text-strong hover:bg-accent/20',
        pink: 'text-decor-pink-text hover:text-decor-pink-text-strong hover:bg-decor-pink/20',
        neutral:
          'text-foreground-soft hover:text-foreground hover:bg-foreground-muted/20',
        gradient: 'text-on-accent hover:bg-on-accent/20',
        neon: 'text-info dark:text-info-text hover:text-info-text dark:hover:text-info-text-strong hover:bg-info/20',
        minimal:
          'text-foreground-muted hover:text-foreground hover:bg-foreground-muted/20',
        glass:
          'text-on-surface-inverse/70 hover:text-on-surface-inverse hover:bg-on-surface-inverse/10',
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
