import { cva } from 'class-variance-authority';

export const NoticeTheme = cva(``, {
  variants: {
    variant: {
      info: 'bg-decor-violet-wash/50 text-decor-violet dark:bg-decor-violet/10',
      success: 'bg-success-wash/50 text-success dark:bg-success/10',
      warning: 'bg-warning-wash/50 text-warning dark:bg-warning/10',
      error: 'bg-danger-wash/50 text-danger dark:bg-danger/10',
    },
    appearance: {
      bordered: 'border-l-2 rounded-r',
      subtle: '',
    },
    size: {
      xs: 'text-xs px-3 py-2',
      sm: 'text-xs px-3 py-2',
      md: 'text-sm px-4 py-3',
      lg: 'text-base px-5 py-4',
    },
  },
  compoundVariants: [
    {
      variant: 'info',
      appearance: 'bordered',
      class:
        'border-decor-violet/70 text-decor-violet-text dark:border-decor-violet',
    },
    {
      variant: 'success',
      appearance: 'bordered',
      class: 'border-success/70 text-success-text',
    },
    {
      variant: 'warning',
      appearance: 'bordered',
      class: 'border-warning/70 text-warning-text',
    },
    {
      variant: 'error',
      appearance: 'bordered',
      class: 'border-danger/70 text-danger-text',
    },
  ],
  defaultVariants: {
    appearance: 'bordered',
  },
});
