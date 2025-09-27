import { cva } from 'class-variance-authority';
import type { VariantProps } from 'class-variance-authority';

export const tagFilterContainerVariants = cva(
  'rounded-lg border px-3 py-3 transition-all duration-200 cursor-pointer',
  {
    variants: {
      variant: {
        default: [
          'border-border bg-background dark:bg-input/30',
          'shadow-xs',
          'hover:bg-muted/50',
          'ring-offset-background',
        ],
        neon: [
          'bg-white dark:bg-slate-900',
          'border-slate-200/60 dark:border-slate-700/60',
          'shadow-[0_0_12px_rgba(99,102,241,0.06)] dark:shadow-[0_0_12px_rgba(99,102,241,0.1)]',
          'hover:border-indigo-300/70 dark:hover:border-indigo-600/70',
          'hover:shadow-[0_0_16px_rgba(99,102,241,0.15)] dark:hover:shadow-[0_0_16px_rgba(99,102,241,0.25)]',
          'hover:bg-indigo-50/50 dark:hover:bg-indigo-900/20',
        ],
        minimal: [
          'border-slate-200 dark:border-slate-700',
          'hover:bg-slate-50 dark:hover:bg-slate-800/50',
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

export type TagFilterContainerVariants = VariantProps<
  typeof tagFilterContainerVariants
>;
