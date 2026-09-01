import { cva } from 'class-variance-authority';

export const filterTileTheme = cva(
  'flex flex-col items-center gap-1.5 rounded-lg p-1.5 transition-all duration-150',
  {
    variants: {
      selected: {
        true: 'ring-2 ring-accent bg-accent-wash dark:bg-accent-wash/30',
        false: 'hover:bg-hover hover:scale-105',
      },
    },
    defaultVariants: {
      selected: false,
    },
  },
);

export const filterLabelTheme = cva('w-16 truncate text-center text-xs', {
  variants: {
    selected: {
      true: 'text-accent-text font-medium',
      false: 'text-foreground-muted',
    },
  },
  defaultVariants: {
    selected: false,
  },
});
