import { cva } from 'class-variance-authority';

export const ToggleGroupContainer = cva(
  'flex items-center bg-gradient-to-br from-slate-50 to-slate-100/50 dark:from-slate-800/50 dark:to-slate-700/30 rounded-xl p-1 shadow-lg border border-slate-200 dark:border-slate-700',
  {
    variants: {
      size: {
        sm: 'min-w-[120px] p-0.5',
        md: 'min-w-[160px] p-1',
        lg: 'min-w-[200px] p-1.5',
      },
      variant: {
        default: '',
        minimal:
          'bg-slate-100 dark:bg-slate-800 shadow-none border-slate-300 dark:border-slate-600',
        outlined: 'bg-transparent border-2',
      },
    },
    defaultVariants: {
      size: 'md',
      variant: 'default',
    },
  },
);

export const ToggleGroupItem = cva(
  'flex items-center justify-center text-sm font-medium rounded-lg transition-colors duration-200 cursor-pointer',
  {
    variants: {
      variant: {
        active:
          'bg-slate-200 dark:bg-slate-600 text-slate-900 dark:text-white shadow-sm',
        inactive:
          'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300',
        disabled: 'text-slate-300 dark:text-slate-600 cursor-not-allowed',
      },
      size: {
        sm: 'px-2 py-1 text-xs gap-1',
        md: 'px-3 py-1.5 text-sm gap-1.5',
        lg: 'px-4 py-2 text-base gap-2',
      },
      width: {
        auto: 'min-w-0',
        equal: 'flex-1',
        fixed: 'w-20',
      },
    },
    defaultVariants: {
      variant: 'inactive',
      size: 'md',
      width: 'fixed',
    },
  },
);

export const ToggleGroupIcon = cva('shrink-0', {
  variants: {
    size: {
      sm: 'w-3 h-3',
      md: 'w-4 h-4',
      lg: 'w-5 h-5',
    },
  },
  defaultVariants: {
    size: 'md',
  },
});
