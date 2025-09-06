import { cva } from 'class-variance-authority';

export const CopyContainerTheme = cva(
  'group relative flex w-full items-center rounded-lg border transition-all duration-200 focus-within:ring-2 focus-within:ring-blue-500/20',
  {
    variants: {
      variant: {
        default:
          'border-gray-200/50 dark:border-gray-700/30 bg-gray-50/80 dark:bg-gray-800/50 hover:bg-gray-100/50 dark:hover:bg-gray-800/70 focus-within:border-gray-200/50 dark:focus-within:border-gray-700/30',
        success:
          'border-green-200/50 dark:border-green-700/30 bg-green-50/80 dark:bg-green-900/30 hover:bg-green-100/50 dark:hover:bg-green-900/50 focus-within:border-green-200/50 dark:focus-within:border-green-700/30 focus-within:ring-green-500/20',
      },
      size: {
        sm: 'max-w-xs text-xs',
        md: 'max-w-md text-sm',
        lg: 'max-w-lg text-base',
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'md',
    },
  },
);

export const CopyContainerInputTheme = cva(
  'w-full bg-transparent border-0 focus:outline-none focus:ring-0 font-mono placeholder-gray-400',
  {
    variants: {
      variant: {
        default: 'text-gray-700 dark:text-gray-300',
        success: 'text-green-700 dark:text-green-300',
      },
      size: {
        sm: 'px-3 py-2 text-xs',
        md: 'px-4 py-2.5 text-sm',
        lg: 'px-4 py-3 text-base',
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'md',
    },
  },
);

export const CopyContainerButtonTheme = cva('transition-all duration-200', {
  variants: {
    size: {
      sm: 'text-xs min-w-[4rem]',
      md: 'text-sm min-w-[5rem]',
      lg: 'text-base min-w-[6rem]',
    },
  },
  defaultVariants: {
    size: 'md',
  },
});
