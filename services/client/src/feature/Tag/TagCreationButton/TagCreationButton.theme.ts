import { cva } from 'class-variance-authority';
import type { VariantProps } from 'class-variance-authority';

// Legacy theme - kept for backwards compatibility
// New implementations should use the button variants in the main Button component

export const tagCreationButtonVariants = cva('flex px-2 my-0.5', {
  variants: {
    variant: {
      default: [],
      neon: [],
      minimal: [],
      subtle: [],
    },
    creating: {
      true: 'opacity-60 cursor-not-allowed pointer-events-none',
      false: '',
    },
    highlighted: {
      true: '',
      false: '',
    },
  },
  compoundVariants: [
    {
      variant: 'default',
      highlighted: true,
      class:
        'bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-300 shadow-sm shadow-blue-200/40 dark:shadow-blue-900/20',
    },
    {
      variant: 'neon',
      highlighted: true,
      class:
        'bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-300 shadow-sm shadow-blue-200/40 dark:shadow-blue-900/20',
    },
    {
      variant: 'minimal',
      highlighted: true,
      class: 'bg-gray-100 dark:bg-white/10 text-gray-900 dark:text-white',
    },
    {
      variant: 'subtle',
      highlighted: true,
      class: 'bg-gray-100 dark:bg-white/10',
    },
  ],
  defaultVariants: {
    variant: 'default',
    creating: false,
    highlighted: false,
  },
});

export const tagCreationIconVariants = cva('h-4 w-4 flex-shrink-0', {
  variants: {
    variant: {
      default: 'text-blue-500 dark:text-blue-400 transition-all duration-300',
      neon: 'text-blue-500 dark:text-blue-400 transition-all duration-300',
      minimal:
        'text-gray-500 dark:text-white/60 transition-colors duration-150',
      subtle: 'text-blue-500 dark:text-blue-400',
    },
    creating: {
      true: 'animate-spin',
      false: '',
    },
  },
  defaultVariants: {
    variant: 'default',
    creating: false,
  },
});

export type TagCreationButtonVariants = VariantProps<
  typeof tagCreationButtonVariants
>;
export type TagCreationIconVariants = VariantProps<
  typeof tagCreationIconVariants
>;
