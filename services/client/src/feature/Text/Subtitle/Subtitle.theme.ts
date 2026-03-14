import { cva } from 'class-variance-authority';

export const SubtitleTheme = cva('mt-1 text-slate-500 dark:text-slate-400', {
  variants: {
    size: {
      sm: 'text-xs',
      md: 'text-sm',
      lg: 'text-lg',
    },
  },
  defaultVariants: {
    size: 'md',
  },
});
