import { cva } from 'class-variance-authority';

export const BadgeTheme = cva(`badge`, {
  variants: {
    variant: {
      default: 'badge-default',
      primary: 'badge-primary',
      secondary: 'badge-secondary',
      neutral: 'badge-neutral',
      accent: 'badge-accent',
      ghost: 'badge-ghost',
      info: 'badge-info',
      success: 'badge-success',
      warning: 'badge-warning',
      error: 'badge-error',
    },
    size: {
      xs: 'badge-xs',
      sm: 'badge-sm',
      md: 'badge-md',
      lg: 'badge-lg',
    },
    outline: {
      true: 'badge-outline',
    },
  },
});
