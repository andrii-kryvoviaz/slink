import { type VariantProps, cva } from 'class-variance-authority';

export const cardTheme = cva(
  'rounded-xl bg-card/80 dark:bg-muted/50 border border-border/70 backdrop-blur-sm',
  {
    variants: {
      elevation: {
        raised:
          'relative overflow-hidden shadow-lg shadow-scrim/5 dark:shadow-scrim/10',
        flat: 'shadow-sm',
      },
    },
    defaultVariants: {
      elevation: 'raised',
    },
  },
);

export const cardTitleTheme = cva(
  'font-semibold bg-gradient-to-r from-foreground-soft to-foreground dark:to-foreground-muted bg-clip-text text-transparent',
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
