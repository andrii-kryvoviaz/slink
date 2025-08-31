import { cva } from 'class-variance-authority';
import type { VariantProps } from 'class-variance-authority';

export const hashtagVariants = cva(
  [
    'inline-block rounded-md px-1 py-0.5 font-semibold transition-all duration-200',
    'cursor-pointer select-none',
    'focus:outline-none focus:ring-2 focus:ring-offset-1',
    'active:scale-95',
  ],
  {
    variants: {
      variant: {
        default: [
          'border border-blue-200 bg-blue-50 text-blue-700',
          'hover:border-blue-300 hover:bg-blue-100 hover:text-blue-800',
          'active:bg-blue-200',
          'focus:ring-blue-500/50',
          'dark:border-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
          'dark:hover:border-blue-700 dark:hover:bg-blue-900/50 dark:hover:text-blue-200',
          'dark:active:bg-blue-900/70',
        ],
        primary: [
          'border border-indigo-200 bg-indigo-50 text-indigo-700',
          'hover:border-indigo-300 hover:bg-indigo-100 hover:text-indigo-800',
          'active:bg-indigo-200',
          'focus:ring-indigo-500/50',
          'dark:border-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300',
          'dark:hover:border-indigo-700 dark:hover:bg-indigo-900/50 dark:hover:text-indigo-200',
          'dark:active:bg-indigo-900/70',
        ],
        secondary: [
          'border border-gray-200 bg-gray-50 text-gray-700',
          'hover:border-gray-300 hover:bg-gray-100 hover:text-gray-800',
          'active:bg-gray-200',
          'focus:ring-gray-500/50',
          'dark:border-gray-700 dark:bg-gray-800/50 dark:text-gray-300',
          'dark:hover:border-gray-600 dark:hover:bg-gray-700/50 dark:hover:text-gray-200',
          'dark:active:bg-gray-700/70',
        ],
        success: [
          'border border-green-200 bg-green-50 text-green-700',
          'hover:border-green-300 hover:bg-green-100 hover:text-green-800',
          'active:bg-green-200',
          'focus:ring-green-500/50',
          'dark:border-green-800 dark:bg-green-900/30 dark:text-green-300',
          'dark:hover:border-green-700 dark:hover:bg-green-900/50 dark:hover:text-green-200',
          'dark:active:bg-green-900/70',
        ],
        warning: [
          'border border-amber-200 bg-amber-50 text-amber-700',
          'hover:border-amber-300 hover:bg-amber-100 hover:text-amber-800',
          'active:bg-amber-200',
          'focus:ring-amber-500/50',
          'dark:border-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
          'dark:hover:border-amber-700 dark:hover:bg-amber-900/50 dark:hover:text-amber-200',
          'dark:active:bg-amber-900/70',
        ],
        danger: [
          'border border-red-200 bg-red-50 text-red-700',
          'hover:border-red-300 hover:bg-red-100 hover:text-red-800',
          'active:bg-red-200',
          'focus:ring-red-500/50',
          'dark:border-red-800 dark:bg-red-900/30 dark:text-red-300',
          'dark:hover:border-red-700 dark:hover:bg-red-900/50 dark:hover:text-red-200',
          'dark:active:bg-red-900/70',
        ],
        minimal: [
          'border border-transparent bg-transparent text-gray-600',
          'hover:bg-gray-100 hover:text-gray-800',
          'active:bg-gray-200',
          'focus:ring-gray-500/50',
          'dark:text-gray-400',
          'dark:hover:bg-gray-800/50 dark:hover:text-gray-200',
          'dark:active:bg-gray-700/50',
        ],
      },
      size: {
        sm: 'px-1 py-0.5 text-xs rounded',
        md: 'px-1 py-0.5 text-sm rounded-md',
        lg: 'px-1.5 py-1 text-base rounded-md',
      },
      rounded: {
        none: 'rounded-none',
        sm: 'rounded-sm',
        md: 'rounded-md',
        lg: 'rounded-lg',
        full: 'rounded-full',
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'md',
      rounded: 'md',
    },
  },
);

export type HashtagVariant = VariantProps<typeof hashtagVariants>;
