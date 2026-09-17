import { cva } from 'class-variance-authority';

export const searchBarField = cva('flex-1 min-w-0', {
  variants: {
    focused: {
      true: 'ring-2 ring-info/20 border-info-border/60 dark:border-info-border/18 shadow-md',
    },
  },
  defaultVariants: {
    focused: false,
  },
});

export const searchBarDropdownChevron = cva(
  'w-3 h-3 transition-transform duration-200 shrink-0',
  {
    variants: {
      open: {
        true: 'rotate-180',
      },
    },
    defaultVariants: {
      open: false,
    },
  },
);
