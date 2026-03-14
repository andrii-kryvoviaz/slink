import { cva } from 'class-variance-authority';

export const TitleTheme = cva(
  'bg-gradient-to-r from-slate-700 to-slate-900 dark:from-slate-200 dark:to-slate-400 bg-clip-text text-transparent',
  {
    variants: {
      size: {
        sm: 'text-xl',
        md: 'text-2xl sm:text-3xl',
        lg: 'text-3xl',
        xl: 'text-4xl',
      },
      weight: {
        semibold: 'font-semibold',
        bold: 'font-bold',
      },
    },
    defaultVariants: {
      size: 'lg',
      weight: 'semibold',
    },
  },
);
