import { cva } from 'class-variance-authority';

export const filterTileTheme = cva(
  'flex flex-col items-center gap-1.5 rounded-lg p-1.5 transition-all duration-150',
  {
    variants: {
      selected: {
        true: 'ring-2 ring-violet-500 dark:ring-violet-400 bg-violet-50 dark:bg-violet-500/10',
        false: 'hover:bg-gray-100 dark:hover:bg-white/5 hover:scale-105',
      },
    },
    defaultVariants: {
      selected: false,
    },
  },
);

export const filterLabelTheme = cva('text-xs', {
  variants: {
    selected: {
      true: 'text-violet-700 dark:text-violet-300 font-medium',
      false: 'text-gray-600 dark:text-gray-400',
    },
  },
  defaultVariants: {
    selected: false,
  },
});
