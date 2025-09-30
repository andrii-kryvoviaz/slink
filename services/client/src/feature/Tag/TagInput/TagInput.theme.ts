import { cva } from 'class-variance-authority';
import type { VariantProps } from 'class-variance-authority';

export const tagInputVariants = cva(
  'w-full bg-transparent border-0 outline-none placeholder:text-muted-foreground resize-none transition-all duration-300',
  {
    variants: {
      size: {
        sm: 'text-xs py-1',
        md: 'text-sm py-2',
        lg: 'text-base py-3',
      },
      variant: {
        default:
          'placeholder:text-slate-400 dark:placeholder:text-slate-500 text-slate-700 dark:text-slate-200',
        neon: 'placeholder:text-slate-400 dark:placeholder:text-slate-500 text-slate-700 dark:text-slate-200',
        minimal:
          'placeholder:text-slate-400 dark:placeholder:text-slate-500 text-slate-700 dark:text-slate-200',
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
