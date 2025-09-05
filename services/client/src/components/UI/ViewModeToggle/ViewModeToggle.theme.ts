import { cva } from 'class-variance-authority';

export const ViewModeToggleContainer = cva(
  'flex items-center bg-gradient-to-br from-slate-50 to-slate-100/50 dark:from-slate-800/50 dark:to-slate-700/30 rounded-xl p-1 shadow-lg border border-slate-200 dark:border-slate-700',
  {
    variants: {
      size: {
        sm: 'min-w-[140px]',
        md: 'min-w-[168px]',
        lg: 'min-w-[200px]',
      },
    },
    defaultVariants: {
      size: 'md',
    },
  },
);

export const ViewModeToggleButton = cva(
  'flex items-center justify-center px-3 py-1.5 text-sm font-medium rounded-lg transition-colors duration-200',
  {
    variants: {
      variant: {
        active:
          'bg-slate-200 dark:bg-slate-600 text-slate-900 dark:text-white shadow-sm',
        inactive:
          'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300',
      },
      size: {
        sm: 'w-16 py-1',
        md: 'w-20 py-1.5',
        lg: 'w-24 py-2',
      },
    },
    defaultVariants: {
      variant: 'inactive',
      size: 'md',
    },
  },
);

export const ViewModeToggleIcon = cva('shrink-0', {
  variants: {
    size: {
      sm: 'w-3 h-3 mr-1',
      md: 'w-4 h-4 mr-1.5',
      lg: 'w-5 h-5 mr-2',
    },
  },
  defaultVariants: {
    size: 'md',
  },
});
