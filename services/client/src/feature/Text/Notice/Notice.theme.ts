import { cva } from 'class-variance-authority';

export const NoticeTheme = cva(``, {
  variants: {
    variant: {
      info: 'bg-violet-50/50 text-violet-600 dark:bg-violet-900/10 dark:text-violet-400',
      success:
        'bg-green-50/50 text-green-600 dark:bg-green-900/10 dark:text-green-400',
      warning:
        'bg-amber-50/50 text-amber-600 dark:bg-amber-900/10 dark:text-amber-400',
      error: 'bg-red-50/50 text-red-600 dark:bg-red-900/10 dark:text-red-400',
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
      class: 'border-violet-400 text-violet-700 dark:border-violet-500',
    },
    {
      variant: 'success',
      appearance: 'bordered',
      class: 'border-green-400 text-green-700 dark:border-green-600',
    },
    {
      variant: 'warning',
      appearance: 'bordered',
      class: 'border-amber-400 text-amber-700 dark:border-amber-500',
    },
    {
      variant: 'error',
      appearance: 'bordered',
      class: 'border-red-400 text-red-700 dark:border-red-500',
    },
  ],
  defaultVariants: {
    appearance: 'bordered',
  },
});
