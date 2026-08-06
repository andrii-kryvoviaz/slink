import { cva } from 'class-variance-authority';

export const ghostFoldersVariants = cva(
  'mx-auto grid w-full max-w-md grid-cols-2 gap-3 sm:max-w-lg sm:grid-cols-3 sm:gap-4',
);

export const ghostFolderCardVariants = cva(
  'rounded-xl border border-border/80 dark:border-border-strong/50 bg-muted dark:bg-muted/50 p-4',
);

export const ghostFolderIconVariants = cva(
  'mb-4 h-8 w-10 rounded-md bg-border/80',
);

export const ghostFolderBarVariants = cva('h-2 rounded-full bg-border/80', {
  variants: {
    width: {
      long: 'mb-2 w-2/3',
      short: 'w-2/5',
    },
  },
  defaultVariants: {
    width: 'long',
  },
});
