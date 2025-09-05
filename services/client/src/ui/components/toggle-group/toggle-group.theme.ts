import { cva } from 'class-variance-authority';

export const toggleGroupTheme = cva(
  'flex items-center bg-gradient-to-br from-slate-50 to-slate-100/50 dark:from-slate-800/50 dark:to-slate-700/30 rounded-xl p-1 shadow-lg border border-slate-200 dark:border-slate-700',
  {
    variants: {
      size: {
        sm: 'min-w-[120px] p-0.5',
        md: 'min-w-[140px] p-1',
        lg: 'min-w-[160px] p-1.5',
      },
      orientation: {
        horizontal: 'flex-row',
        vertical: 'flex-col',
      },
    },
    defaultVariants: {
      size: 'md',
      orientation: 'horizontal',
    },
  },
);

export const toggleGroupItemTheme = cva(
  'flex items-center justify-center text-sm font-medium rounded-lg transition-colors duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50',
  {
    variants: {
      variant: {
        active:
          'bg-slate-200 dark:bg-slate-600 text-slate-900 dark:text-white shadow-sm',
        inactive:
          'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-300 hover:bg-slate-100/50 dark:hover:bg-slate-700/30',
      },
      size: {
        sm: 'px-2 py-1 text-xs',
        md: 'px-3 py-1.5 text-sm',
        lg: 'px-4 py-2 text-base',
      },
    },
    defaultVariants: {
      variant: 'inactive',
      size: 'md',
    },
  },
);

export const toggleGroupIconTheme = cva('shrink-0', {
  variants: {
    size: {
      sm: 'w-3 h-3',
      md: 'w-4 h-4',
      lg: 'w-5 h-5',
    },
    hasLabel: {
      true: '',
      false: '',
    },
  },
  compoundVariants: [
    {
      size: 'sm',
      hasLabel: true,
      className: 'mr-1',
    },
    {
      size: 'md',
      hasLabel: true,
      className: 'mr-1.5',
    },
    {
      size: 'lg',
      hasLabel: true,
      className: 'mr-2',
    },
  ],
  defaultVariants: {
    size: 'md',
    hasLabel: false,
  },
});
