import { cva } from 'class-variance-authority';

export const historyCardVariants = cva(
  'group break-inside-avoid overflow-hidden rounded-lg border bg-white dark:bg-gray-900/60 transition-all duration-200 hover:shadow-md dark:hover:shadow-gray-900/50',
  {
    variants: {
      selected: {
        true: 'ring-2 ring-blue-500 border-blue-400 dark:border-blue-500',
        false:
          'border-gray-200 dark:border-gray-800 hover:border-gray-300 dark:hover:border-gray-700/80',
      },
    },
    defaultVariants: {
      selected: false,
    },
  },
);

export const historyListRowVariants = cva(
  'group relative flex flex-col sm:flex-row w-full overflow-hidden rounded-lg border bg-white dark:bg-gray-900/60 transition-all duration-200 hover:shadow-md dark:hover:shadow-gray-900/50',
  {
    variants: {
      selected: {
        true: 'bg-blue-50/80 dark:bg-blue-500/10 border-blue-300 dark:border-blue-500 ring-2 ring-blue-500',
        false:
          'border-gray-200 dark:border-gray-800 hover:border-gray-300 dark:hover:border-gray-700',
      },
      selectionMode: {
        true: 'cursor-pointer',
        false: '',
      },
    },
    defaultVariants: {
      selected: false,
      selectionMode: false,
    },
  },
);

export const selectionCheckboxVariants = cva(
  'flex items-center justify-center w-6 h-6 rounded-full border-2 shadow-md transition-all duration-150',
  {
    variants: {
      selected: {
        true: 'bg-blue-500 border-blue-500',
        false:
          'bg-white/90 dark:bg-gray-900/90 border-white dark:border-gray-700',
      },
    },
    defaultVariants: {
      selected: false,
    },
  },
);

export const actionBarVisibilityVariants = cva('absolute top-2 right-2', {
  variants: {
    selectionMode: {
      true: 'opacity-0 pointer-events-none',
      false:
        'opacity-0 group-hover:opacity-100 transition-opacity duration-200',
    },
  },
  defaultVariants: {
    selectionMode: false,
  },
});

export const listActionBarVisibilityVariants = cva('shrink-0', {
  variants: {
    selectionMode: {
      true: 'opacity-0 pointer-events-none',
      false:
        'opacity-0 group-hover:opacity-100 sm:opacity-100 transition-opacity duration-200',
    },
  },
  defaultVariants: {
    selectionMode: false,
  },
});

export const linkVariants = cva('block', {
  variants: {
    selectionMode: {
      true: 'cursor-pointer',
      false: '',
    },
  },
  defaultVariants: {
    selectionMode: false,
  },
});
