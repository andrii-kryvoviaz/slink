import { cva } from 'class-variance-authority';

export const ToastItemTheme = cva(
  'w-full transition-all duration-300 ease-out',
  {
    variants: {
      variant: {
        default:
          'bg-white/60 text-slate-700 shadow-md shadow-black/3 border border-slate-200/20 backdrop-blur-xl dark:bg-slate-900/60 dark:text-slate-200 dark:border-slate-700/20 dark:shadow-black/15',
        success:
          'bg-emerald-25/50 text-emerald-700 border border-emerald-200/20 shadow-md shadow-emerald-500/4 backdrop-blur-xl dark:bg-emerald-950/50 dark:text-emerald-200 dark:border-emerald-800/20 dark:shadow-emerald-900/10',
        warning:
          'bg-amber-25/50 text-amber-700 border border-amber-200/20 shadow-md shadow-amber-500/4 backdrop-blur-xl dark:bg-amber-950/50 dark:text-amber-200 dark:border-amber-800/20 dark:shadow-amber-900/10',
        error:
          'bg-red-25/50 text-red-700 border border-red-200/20 shadow-md shadow-red-500/4 backdrop-blur-xl dark:bg-red-950/50 dark:text-red-200 dark:border-red-800/20 dark:shadow-red-900/10',
        info: 'bg-blue-25/50 text-blue-700 border border-blue-200/20 shadow-md shadow-blue-500/4 backdrop-blur-xl dark:bg-blue-950/50 dark:text-blue-200 dark:border-blue-800/20 dark:shadow-blue-900/10',
        unsupported:
          'bg-purple-25/50 text-purple-700 border border-purple-200/20 shadow-md shadow-purple-500/4 backdrop-blur-xl dark:bg-purple-950/50 dark:text-purple-200 dark:border-purple-800/20 dark:shadow-purple-900/10',
        component:
          'bg-transparent text-inherit border-none shadow-none rounded-none p-0',
      },
      size: {
        none: '',
        xs: 'p-3 text-xs',
        sm: 'p-4 text-sm',
        md: 'p-5 text-sm',
        lg: 'p-6 text-base',
        xl: 'p-8 text-lg',
      },
      rounded: {
        none: 'rounded-none',
        sm: 'rounded-lg',
        md: 'rounded-xl',
        lg: 'rounded-2xl',
        xl: 'rounded-3xl',
        full: 'rounded-full',
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'md',
      rounded: 'xl',
    },
  },
);
