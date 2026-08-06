import { cva } from 'class-variance-authority';

export const ghostChipsVariants = cva(
  'mx-auto flex w-full max-w-md flex-wrap justify-center gap-2.5 sm:max-w-lg',
);

export const ghostChipVariants = cva(
  'h-7 rounded-full border border-border/80 dark:border-border-strong/50 bg-muted dark:bg-muted/50',
  {
    variants: {
      width: {
        sm: 'w-14',
        md: 'w-20',
        lg: 'w-28',
      },
    },
    defaultVariants: {
      width: 'md',
    },
  },
);
