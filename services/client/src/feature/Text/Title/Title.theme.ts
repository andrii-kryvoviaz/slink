import { cva } from 'class-variance-authority';

export const TitleTheme = cva(
  'bg-gradient-to-r from-foreground-soft to-foreground dark:to-foreground-muted bg-clip-text text-transparent',
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
