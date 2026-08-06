import { cva } from 'class-variance-authority';

export const InfoCardTheme = cva('rounded-lg p-4 transition-colors', {
  variants: {
    variant: {
      default: 'bg-muted-subtle',
      info: 'bg-info-subtle border border-info-border',
      success: 'bg-success-subtle-strong border border-success-border',
      warning: 'bg-warning-subtle border border-warning-border',
      danger: 'bg-danger-subtle border border-danger-border',
      rose: 'bg-danger-subtle-strong border border-danger-border',
      purple: 'bg-accent-subtle-strong border border-accent-border',
      neutral: 'bg-muted-subtle',
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
      default: 'text-muted-foreground',
      info: 'text-info',
      success: 'text-success-strong',
      warning: 'text-warning',
      danger: 'text-danger',
      rose: 'text-danger-strong',
      purple: 'text-accent-strong',
      neutral: 'text-muted-foreground',
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
      info: 'text-info-subtle-foreground',
      success: 'text-success-subtle-foreground',
      warning: 'text-warning-subtle-foreground',
      danger: 'text-danger-subtle-foreground',
      rose: 'text-danger-subtle-foreground',
      purple: 'text-accent-subtle-foreground',
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
      default: 'text-muted-foreground',
      info: 'text-info-subtle-foreground',
      success: 'text-success-subtle-foreground',
      warning: 'text-warning-subtle-foreground',
      danger: 'text-danger-subtle-foreground',
      rose: 'text-danger-subtle-foreground',
      purple: 'text-accent-subtle-foreground',
      neutral: 'text-muted-foreground',
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
