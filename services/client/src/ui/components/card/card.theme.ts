import { type VariantProps, cva } from 'class-variance-authority';

export const cardTheme = cva(
  'rounded-xl bg-white/80 dark:bg-slate-800/50 border border-slate-200/70 dark:border-slate-700/50 backdrop-blur-sm',
  {
    variants: {
      elevation: {
        raised:
          'relative overflow-hidden shadow-lg shadow-slate-500/5 dark:shadow-black/10',
        flat: 'shadow-sm',
      },
    },
    defaultVariants: {
      elevation: 'raised',
    },
  },
);

export const cardTitleTheme = cva(
  'font-semibold bg-gradient-to-r from-slate-700 to-slate-900 dark:from-slate-200 dark:to-slate-400 bg-clip-text text-transparent',
  {
    variants: {
      size: {
        lg: 'text-xl sm:text-2xl',
        md: 'block text-base sm:text-lg',
      },
    },
    defaultVariants: {
      size: 'lg',
    },
  },
);

export type CardVariants = VariantProps<typeof cardTheme>;
export type CardTitleVariants = VariantProps<typeof cardTitleTheme>;
