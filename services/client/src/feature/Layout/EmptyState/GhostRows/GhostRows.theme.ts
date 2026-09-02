import { cva } from 'class-variance-authority';

export const ghostRowsVariants = cva(
  'mx-auto flex w-full max-w-md flex-col gap-2.5 sm:max-w-lg',
);

export const ghostRowVariants = cva(
  'flex items-center gap-3 rounded-xl border border-border/80 dark:border-border-strong/50 bg-muted/60 dark:bg-muted/30 px-3.5 py-2.5',
);

export const ghostRowThumbVariants = cva(
  'h-8 w-8 shrink-0 rounded-lg bg-border/70',
);

export const ghostRowBarVariants = cva('h-2 rounded-full bg-border/70', {
  variants: {
    width: {
      long: 'flex-1',
      short: 'w-1/4 shrink-0',
    },
  },
  defaultVariants: {
    width: 'long',
  },
});
