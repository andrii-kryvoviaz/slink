import { cva } from 'class-variance-authority';

export const actionBarContainerVariants = cva('flex items-center', {
  variants: {
    layout: {
      default: '',
      hero: 'gap-3',
    },
  },
  defaultVariants: {
    layout: 'default',
  },
});

export const actionBarSecondaryGroupVariants = cva('flex items-center', {
  variants: {
    layout: {
      default: '',
      hero: 'gap-1',
    },
  },
  defaultVariants: {
    layout: 'default',
  },
});

export const heroButtonOverrides = cva('', {
  variants: {
    intent: {
      default:
        'h-auto min-w-0 p-2 rounded-lg bg-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800',
      primary:
        'h-auto min-w-0 px-5 py-2.5 rounded-lg gap-2 bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white font-medium text-sm shadow-sm hover:shadow-md',
      destructive:
        'h-auto min-w-0 p-2 rounded-lg bg-transparent text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20',
    },
  },
  defaultVariants: {
    intent: 'default',
  },
});

export const iconSizeVariants = cva('', {
  variants: {
    layout: {
      default: 'h-3.5 w-3.5',
      hero: 'h-4 w-4',
    },
    size: {
      sm: 'h-3.5 w-3.5',
      md: 'h-4 w-4',
      lg: 'h-5 w-5',
    },
  },
  defaultVariants: {
    layout: 'default',
  },
});
