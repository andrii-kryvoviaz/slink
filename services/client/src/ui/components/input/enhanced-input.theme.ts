import { cva } from 'class-variance-authority';
import type { VariantProps } from 'class-variance-authority';

export const inputVariants = cva(
  'aria-invalid:border-accent-border dark:aria-invalid:border-accent-border/30 aria-invalid:focus-visible:border-accent-strong/50 aria-invalid:focus-visible:ring-accent-strong/20 dark:aria-invalid:bg-accent-wash/20 aria-invalid:hover:bg-accent-wash/40 dark:aria-invalid:hover:bg-accent-wash/8 transition-all duration-200',
  {
    variants: {
      variant: {
        modern: '',
      },
      size: {
        sm: 'h-8 px-3 text-sm',
        md: 'h-9 px-3 text-sm',
        lg: 'h-11 px-4 text-sm',
      },

      rounded: {
        sm: 'rounded-sm',
        md: 'rounded-md',
        lg: 'rounded-lg',
      },
      hasLeftIcon: {
        true: 'pl-12',
        false: '',
      },
      hasRightIcon: {
        true: 'pr-12',
        false: '',
      },
    },
    defaultVariants: {
      variant: 'modern',
      size: 'md',

      rounded: 'lg',
      hasLeftIcon: false,
      hasRightIcon: false,
    },
  },
);

export type InputVariants = VariantProps<typeof inputVariants>;
