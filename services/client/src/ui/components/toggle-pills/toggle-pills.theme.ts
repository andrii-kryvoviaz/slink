import { cva } from 'class-variance-authority';

export const togglePillsTheme = cva('inline-flex flex-wrap gap-2');

export const togglePillsItemTheme = cva(
  'inline-flex cursor-pointer items-center rounded-full border font-medium transition-colors duration-200 border-slate-200 dark:border-slate-700 bg-transparent text-slate-500 dark:text-slate-400 data-[state=off]:hover:text-slate-700 dark:data-[state=off]:hover:text-slate-300 data-[state=off]:hover:bg-slate-100/60 dark:data-[state=off]:hover:bg-slate-700/40 data-[state=on]:border-blue-200 data-[state=on]:bg-blue-100 data-[state=on]:text-blue-600 dark:data-[state=on]:border-blue-800/60 dark:data-[state=on]:bg-blue-800/40 dark:data-[state=on]:text-blue-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50',
  {
    variants: {
      size: {
        sm: 'gap-1 px-2 py-0.5 text-[11px]',
        md: 'gap-1 px-2.5 py-1 text-xs',
      },
    },
    defaultVariants: {
      size: 'md',
    },
  },
);

export const togglePillsIconTheme = cva('shrink-0', {
  variants: {
    size: {
      sm: 'w-2.5 h-2.5',
      md: 'w-3 h-3',
    },
  },
  defaultVariants: {
    size: 'md',
  },
});
