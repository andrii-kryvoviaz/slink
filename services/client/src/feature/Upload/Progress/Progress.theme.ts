import { cva } from 'class-variance-authority';

export const UploadProgressNumberTheme = cva('font-light tracking-tight', {
  variants: {
    size: {
      sm: 'text-3xl',
      md: 'text-4xl sm:text-5xl',
      lg: 'text-5xl sm:text-6xl',
    },
    shimmer: {
      true: 'bg-gradient-to-r from-slate-700 via-slate-400 to-slate-700 dark:from-slate-200 dark:via-slate-500 dark:to-slate-200 bg-[length:200%_100%] animate-shimmer bg-clip-text text-transparent',
      false: 'text-slate-900 dark:text-white',
    },
    animated: {
      true: 'tabular-nums',
      false: '',
    },
  },
  defaultVariants: {
    size: 'sm',
    animated: false,
    shimmer: false,
  },
});

export const UploadProgressStatusIconTheme = cva('w-5 h-5', {
  variants: {
    status: {
      completed: 'text-slate-600 dark:text-slate-300',
      error: 'text-red-500 dark:text-red-400',
      cancelled: 'text-slate-400 dark:text-slate-500',
      pending: 'text-slate-300 dark:text-slate-600',
    },
  },
});
