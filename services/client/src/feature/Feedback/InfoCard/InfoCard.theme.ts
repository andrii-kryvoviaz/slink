import { cva } from 'class-variance-authority';

export const InfoCardTheme = cva('rounded-lg p-4 transition-colors', {
  variants: {
    variant: {
      default: 'bg-slate-50 dark:bg-slate-800/50',
      info: 'bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800',
      success:
        'bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800',
      warning:
        'bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800',
      danger:
        'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800',
      rose: 'bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800',
      purple:
        'bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800',
      neutral: 'bg-slate-50 dark:bg-slate-800/50',
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
      default: 'text-slate-500 dark:text-slate-400',
      info: 'text-blue-600 dark:text-blue-400',
      success: 'text-emerald-600 dark:text-emerald-400',
      warning: 'text-amber-600 dark:text-amber-400',
      danger: 'text-red-600 dark:text-red-400',
      rose: 'text-rose-600 dark:text-rose-400',
      purple: 'text-purple-600 dark:text-purple-400',
      neutral: 'text-slate-500 dark:text-slate-400',
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
      default: 'text-slate-900 dark:text-slate-100',
      info: 'text-blue-800 dark:text-blue-200',
      success: 'text-emerald-800 dark:text-emerald-200',
      warning: 'text-amber-800 dark:text-amber-200',
      danger: 'text-red-800 dark:text-red-200',
      rose: 'text-rose-800 dark:text-rose-200',
      purple: 'text-purple-800 dark:text-purple-200',
      neutral: 'text-slate-900 dark:text-slate-100',
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
      default: 'text-slate-600 dark:text-slate-400',
      info: 'text-blue-700 dark:text-blue-300',
      success: 'text-emerald-700 dark:text-emerald-300',
      warning: 'text-amber-700 dark:text-amber-300',
      danger: 'text-red-700 dark:text-red-300',
      rose: 'text-rose-700 dark:text-rose-300',
      purple: 'text-purple-700 dark:text-purple-300',
      neutral: 'text-slate-600 dark:text-slate-400',
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
