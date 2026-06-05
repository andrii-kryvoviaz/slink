import { type VariantProps, cva } from 'class-variance-authority';

export const scrollAreaViewportTheme = cva(
  'ring-ring/10 dark:ring-ring/20 dark:outline-ring/40 outline-ring/50 size-full rounded-[inherit] transition-[color,box-shadow] focus-visible:outline-1 focus-visible:ring-4',
  {
    variants: {
      maxHeight: {
        none: '',
        sm: 'max-h-60',
        md: 'max-h-64',
      },
    },
    defaultVariants: {
      maxHeight: 'none',
    },
  },
);

export const scrollAreaScrollbarTheme = cva(
  'flex touch-none select-none p-px transition-colors',
  {
    variants: {
      orientation: {
        vertical: 'h-full w-2 border-l border-l-transparent',
        horizontal: 'h-2 flex-col border-t border-t-transparent',
      },
    },
    defaultVariants: {
      orientation: 'vertical',
    },
  },
);

export type ScrollAreaViewportVariants = VariantProps<
  typeof scrollAreaViewportTheme
>;
