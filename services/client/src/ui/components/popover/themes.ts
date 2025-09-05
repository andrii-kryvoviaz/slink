import { cva } from 'class-variance-authority';

export const PopoverContentTheme = cva(
  'z-50 overflow-hidden border shadow-lg outline-none',
  {
    variants: {
      variant: {
        default:
          'bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100',
        glass:
          'bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm border-gray-200/60 dark:border-gray-700/60 text-gray-900 dark:text-gray-100',
        floating:
          'bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 shadow-xl hover:shadow-2xl text-gray-900 dark:text-gray-100',
        minimal:
          'bg-white dark:bg-gray-900 border-0 shadow-sm text-gray-900 dark:text-gray-100',
        modern:
          'bg-white/90 dark:bg-gray-900/90 backdrop-blur-xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl shadow-gray-900/5 dark:shadow-gray-900/20 text-gray-900 dark:text-gray-100',
      },
      size: {
        xs: 'p-2 max-w-xs',
        sm: 'p-3 max-w-sm',
        md: 'p-4 max-w-md',
        lg: 'p-5 max-w-lg',
        xl: 'p-6 max-w-xl',
        auto: 'p-4',
      },
      rounded: {
        none: 'rounded-none',
        sm: 'rounded-sm',
        md: 'rounded-md',
        lg: 'rounded-lg',
        xl: 'rounded-xl',
        '2xl': 'rounded-2xl',
        full: 'rounded-full',
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'md',
      rounded: 'lg',
    },
  },
);

export const PopoverArrowTheme = cva('fill-current', {
  variants: {
    variant: {
      default: 'text-white dark:text-gray-900',
      glass: 'text-white/95 dark:text-gray-900/95',
      floating: 'text-white dark:text-gray-900',
      minimal: 'text-white dark:text-gray-900',
      modern: 'text-white/90 dark:text-gray-900/90',
    },
  },
  defaultVariants: {
    variant: 'default',
  },
});

export const PopoverTriggerTheme = cva(
  'inline-flex items-center justify-center cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/20 transition-all duration-200',
  {
    variants: {
      variant: {
        default: '',
        button:
          'bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-100',
        ghost:
          'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-100/60 dark:hover:bg-gray-800/60 rounded-md p-1',
        minimal: 'hover:opacity-75',
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  },
);
