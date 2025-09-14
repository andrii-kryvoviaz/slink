import { cva } from 'class-variance-authority';
import type { VariantProps } from 'class-variance-authority';

export const inputVariants = cva(
  'aria-invalid:border-purple-300/60 dark:aria-invalid:border-purple-600/40 aria-invalid:focus-visible:border-purple-400/70 dark:aria-invalid:focus-visible:border-purple-500/50 aria-invalid:focus-visible:ring-purple-500/20 dark:aria-invalid:focus-visible:ring-purple-400/20 aria-invalid:bg-purple-25/30 dark:aria-invalid:bg-purple-950/20 aria-invalid:hover:bg-purple-50/40 dark:aria-invalid:hover:bg-purple-950/30 transition-all duration-200',
  {
    variants: {
      variant: {
        modern: '',
      },
      size: {
        sm: 'h-8 px-2 text-sm',
        md: 'h-9 px-3 text-sm',
        lg: 'h-10 px-4 text-base',
      },

      rounded: {
        sm: 'rounded-sm',
        md: 'rounded-md',
        lg: 'rounded-lg',
      },
      hasLeftIcon: {
        true: 'pl-10',
        false: '',
      },
      hasRightIcon: {
        true: 'pr-10',
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
