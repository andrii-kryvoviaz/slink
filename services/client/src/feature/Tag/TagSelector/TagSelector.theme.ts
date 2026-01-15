import { cva } from 'class-variance-authority';
import type { VariantProps } from 'class-variance-authority';

export const tagSelectorContainerVariants = cva(
  'min-h-[2.75rem] w-full px-3 py-2 rounded-lg transition-colors duration-200',
  {
    variants: {
      variant: {
        default: [
          'bg-white dark:bg-gray-900/60',
          'backdrop-blur-sm',
          'border border-gray-200/80 dark:border-white/10',
          'hover:bg-gray-50 dark:hover:bg-gray-900/70',
          'focus-within:border-gray-300 dark:focus-within:border-white/20',
          'focus-within:bg-white dark:focus-within:bg-gray-900/80',
        ],
        neon: [
          'bg-white dark:bg-gray-900/60',
          'backdrop-blur-sm',
          'border border-gray-200/60 dark:border-white/10',
          'shadow-sm transition-all duration-300',
          'hover:border-blue-300/40 dark:hover:border-blue-500/25',
          'hover:shadow-sm hover:shadow-blue-200/20 dark:hover:shadow-blue-900/10',
          'hover:bg-blue-50/15 dark:hover:bg-blue-500/6',
          'focus-within:border-blue-400/60 dark:focus-within:border-blue-500/40',
          'focus-within:shadow-md focus-within:shadow-blue-200/30 dark:focus-within:shadow-blue-900/20',
          'focus-within:bg-blue-50/25 dark:focus-within:bg-blue-500/10',
          'focus-within:ring-1 focus-within:ring-blue-300/20 dark:focus-within:ring-blue-500/15',
        ],
        minimal: [
          'bg-white dark:bg-gray-900/60',
          'backdrop-blur-sm',
          'border border-gray-200/60 dark:border-white/10',
          'hover:bg-gray-50/80 dark:hover:bg-gray-900/70',
          'focus-within:border-gray-300/80 dark:focus-within:border-white/20',
        ],
        subtle: [
          'bg-gray-50/50 dark:bg-gray-900/60',
          'backdrop-blur-sm',
          'border border-gray-200/60 dark:border-white/8',
          'hover:bg-gray-100/50 dark:hover:bg-gray-900/70',
          'focus-within:border-gray-300 dark:focus-within:border-white/15',
          'focus-within:bg-white dark:focus-within:bg-gray-900/80',
        ],
      },
      disabled: {
        true: 'opacity-50 cursor-not-allowed',
        false: '',
      },
    },
    defaultVariants: {
      variant: 'default',
      disabled: false,
    },
  },
);

export const tagSelectorIconContainerVariants = cva(
  'w-7 h-7 rounded-lg flex items-center justify-center',
  {
    variants: {
      variant: {
        default: [
          'bg-gray-100 dark:bg-white/10',
          'text-gray-500 dark:text-white/60',
        ],
        neon: [
          'bg-blue-50 dark:bg-blue-500/20',
          'border border-blue-200/50 dark:border-blue-500/30',
          'text-blue-600 dark:text-blue-400',
        ],
        minimal: [
          'bg-gray-100/80 dark:bg-white/10',
          'text-gray-500 dark:text-white/60',
        ],
        subtle: ['bg-transparent', 'text-gray-400 dark:text-white/50'],
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  },
);

export const tagSelectorIconVariants = cva('h-3.5 w-3.5 transition-colors', {
  variants: {
    variant: {
      default: 'text-gray-500 dark:text-white/60',
      neon: 'text-blue-600 dark:text-blue-400',
      minimal: 'text-gray-500 dark:text-white/60',
      subtle: 'text-gray-400 dark:text-white/50',
    },
  },
  defaultVariants: {
    variant: 'default',
  },
});

export type TagSelectorContainerVariants = VariantProps<
  typeof tagSelectorContainerVariants
>;
export type TagSelectorIconContainerVariants = VariantProps<
  typeof tagSelectorIconContainerVariants
>;
export type TagSelectorIconVariants = VariantProps<
  typeof tagSelectorIconVariants
>;
