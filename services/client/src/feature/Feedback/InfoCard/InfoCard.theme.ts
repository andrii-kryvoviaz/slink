import { cva } from 'class-variance-authority';

export const InfoCardTheme = cva('rounded-lg p-4 transition-colors', {
  variants: {
    variant: {
      default: 'bg-muted-soft',
      info: 'bg-info-wash dark:bg-info-wash/20 border border-info-border dark:border-info-border/30',
      success:
        'bg-success-wash-strong dark:bg-success-wash-strong/20 border border-success-border dark:border-success-border/30',
      warning:
        'bg-warning-wash dark:bg-warning-wash/20 border border-warning-border dark:border-warning-border/30',
      danger:
        'bg-danger-wash dark:bg-danger-wash/20 border border-danger-border dark:border-danger-border/30',
      rose: 'bg-danger-wash-strong dark:bg-danger-wash-strong/20 border border-danger-border dark:border-danger-border/30',
      purple:
        'bg-accent-wash-strong dark:bg-accent-wash-strong/20 border border-accent-border dark:border-accent-border/30',
      neutral: 'bg-muted-soft',
    },
    size: {
      sm: 'p-3',
      md: 'p-4',
      lg: 'p-6',
    },
  },
  defaultVariants: {
    variant: 'default',
    size: 'md',
  },
});

export const InfoCardIconTheme = cva('flex-shrink-0 mt-0.5', {
  variants: {
    variant: {
      default: 'text-foreground-muted',
      info: 'text-info',
      success: 'text-success-strong',
      warning: 'text-warning',
      danger: 'text-danger',
      rose: 'text-danger-strong',
      purple: 'text-accent-strong',
      neutral: 'text-foreground-muted',
    },
    size: {
      sm: 'w-4 h-4',
      md: 'w-5 h-5',
      lg: 'w-6 h-6',
    },
  },
  defaultVariants: {
    variant: 'default',
    size: 'md',
  },
});

export const InfoCardTitleTheme = cva('font-semibold mb-1', {
  variants: {
    variant: {
      default: 'text-foreground',
      info: 'text-info-text',
      success: 'text-success-text',
      warning: 'text-warning-text',
      danger: 'text-danger-text',
      rose: 'text-danger-text',
      purple: 'text-accent-text',
      neutral: 'text-foreground',
    },
    size: {
      sm: 'text-xs',
      md: 'text-sm',
      lg: 'text-base',
    },
  },
  defaultVariants: {
    variant: 'default',
    size: 'md',
  },
});

export const InfoCardContentTheme = cva('', {
  variants: {
    variant: {
      default: 'text-foreground-muted',
      info: 'text-info-text',
      success: 'text-success-text',
      warning: 'text-warning-text',
      danger: 'text-danger-text',
      rose: 'text-danger-text',
      purple: 'text-accent-text',
      neutral: 'text-foreground-muted',
    },
    size: {
      sm: 'text-xs',
      md: 'text-sm',
      lg: 'text-base',
    },
  },
  defaultVariants: {
    variant: 'default',
    size: 'md',
  },
});
