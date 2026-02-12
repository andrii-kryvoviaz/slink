import { cva } from 'class-variance-authority';

export const metadataContainerTheme = cva(
  'flex flex-wrap items-center gap-y-1 text-xs text-gray-500 dark:text-gray-400',
  {
    variants: {
      gap: {
        sm: 'gap-x-1.5 -ml-1.5',
        md: 'gap-x-2 -ml-2',
      },
    },
    defaultVariants: {
      gap: 'sm',
    },
  },
);

export const metadataItemTheme = cva('inline-flex items-center gap-1', {
  variants: {
    gap: {
      sm: 'pl-1.5',
      md: 'pl-2',
    },
  },
  defaultVariants: {
    gap: 'sm',
  },
});

export const metadataIconTheme = cva('', {
  variants: {
    gap: {
      sm: 'w-3 h-3',
      md: 'w-3.5 h-3.5',
    },
  },
  defaultVariants: {
    gap: 'sm',
  },
});

export const metadataDividerTheme = cva(
  'absolute left-0 top-1/2 -translate-y-1/2 w-px bg-gray-300/60 dark:bg-gray-700/60',
  {
    variants: {
      gap: {
        sm: 'h-3',
        md: 'h-3.5',
      },
    },
    defaultVariants: {
      gap: 'sm',
    },
  },
);
