import { cva } from 'class-variance-authority';

export const ghostGridVariants = cva(
  'mx-auto grid w-full max-w-md grid-cols-4 gap-3 sm:max-w-lg sm:gap-4',
);

export const ghostTileVariants = cva(
  'aspect-[4/3] rounded-xl border border-border/80 dark:border-border-strong/50 bg-muted dark:bg-muted/50',
);
