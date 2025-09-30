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
        'bg-blue-100 dark:bg-blue-800/40 text-blue-600 dark:text-blue-300 shadow-sm shadow-blue-200/40 dark:shadow-blue-900/30',
    },
    {
      variant: 'neon',
      highlighted: true,
      class:
        'bg-blue-100 dark:bg-blue-800/40 text-blue-600 dark:text-blue-300 shadow-sm shadow-blue-200/40 dark:shadow-blue-900/30',
    },
    {
      variant: 'minimal',
      highlighted: true,
      class:
        'bg-blue-50/80 dark:bg-blue-950/40 text-blue-900 dark:text-blue-100 shadow-sm shadow-blue-100/50 dark:shadow-blue-900/30',
    },
  ],
  defaultVariants: {
    variant: 'default',
    creating: false,
    highlighted: false,
  },
});

export const tagCreationIconVariants = cva(
  'h-4 w-4 flex-shrink-0 transition-all duration-300',
  {
    variants: {
      variant: {
        default: 'text-blue-500 dark:text-blue-400',
        neon: 'text-blue-500 dark:text-blue-400',
        minimal: 'text-blue-500 dark:text-blue-400',
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
  },
);

export type TagCreationButtonVariants = VariantProps<
  typeof tagCreationButtonVariants
>;
export type TagCreationIconVariants = VariantProps<
  typeof tagCreationIconVariants
>;
