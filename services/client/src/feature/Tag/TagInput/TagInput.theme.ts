import { cva } from 'class-variance-authority';
import type { VariantProps } from 'class-variance-authority';

export const tagInputVariants = cva(
  'w-full bg-transparent border-0 outline-none placeholder:text-muted-foreground resize-none',
  {
    variants: {
      size: {
        sm: 'text-xs py-1',
        md: 'text-sm py-2',
        lg: 'text-base py-3',
      },
      variant: {
        default:
          'placeholder:text-gray-400 dark:placeholder:text-white/40 text-gray-700 dark:text-white transition-all duration-300',
        neon: 'placeholder:text-gray-400 dark:placeholder:text-white/40 text-gray-700 dark:text-white transition-all duration-300',
        minimal:
          'placeholder:text-gray-500 dark:placeholder:text-white/40 text-gray-700 dark:text-white transition-colors duration-150',
        subtle:
          'placeholder:text-gray-400 dark:placeholder:text-white/40 text-gray-700 dark:text-white transition-colors duration-150',
      },
      disabled: {
        true: 'opacity-50 cursor-not-allowed',
        false: '',
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'md',
      disabled: false,
    },
  },
);

export type TagInputVariants = VariantProps<typeof tagInputVariants>;
