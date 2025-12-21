import { cva } from 'class-variance-authority';

export const fractionPickerContainerTheme = cva(
  'inline-flex items-center bg-gradient-to-br from-slate-50 to-slate-100/50 dark:from-slate-800/50 dark:to-slate-700/30 border border-slate-200 dark:border-slate-700',
  {
    variants: {
      size: {
        sm: 'rounded-md p-0.5',
        md: 'rounded-lg p-0.5',
        lg: 'rounded-xl p-1',
      },
    },
    defaultVariants: {
      size: 'md',
    },
  },
);

export const fractionPickerItemTheme = cva(
  'font-medium transition-all duration-200 first:rounded-l-md last:rounded-r-md focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-1 disabled:pointer-events-none disabled:opacity-50',
  {
    variants: {
      variant: {
        active:
          'bg-slate-200 dark:bg-slate-600 text-slate-900 dark:text-white shadow-sm',
        inactive:
          'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 hover:bg-slate-100/50 dark:hover:bg-slate-700/30',
      },
      size: {
        sm: 'px-2 py-0.5 text-xs',
        md: 'px-2.5 py-1 text-xs',
        lg: 'px-3 py-1.5 text-sm',
      },
    },
    defaultVariants: {
      variant: 'inactive',
      size: 'md',
    },
  },
);

export const fractionPickerLabelTheme = cva('font-medium', {
  variants: {
    size: {
      sm: 'text-xs',
      md: 'text-xs',
      lg: 'text-sm',
    },
  },
  defaultVariants: {
    size: 'md',
  },
});
