import { cva } from 'class-variance-authority';
import type { VariantProps } from 'class-variance-authority';

export const tagInputVariants = cva(
  'w-full bg-transparent border-0 outline-none placeholder:text-muted-foreground resize-none transition-all duration-200',
  {
    variants: {
      size: {
        sm: 'text-xs py-1',
        md: 'text-sm py-2',
        lg: 'text-base py-3',
      },
      variant: {
        default: 'placeholder:text-muted-foreground',
        neon: 'placeholder:text-blue-400/60',
        minimal: 'placeholder:text-slate-400',
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
