import { cva } from 'class-variance-authority';

export const togglePillsTheme = cva('inline-flex flex-wrap gap-2');

export const togglePillsItemTheme = cva(
  'inline-flex cursor-pointer items-center rounded-full border font-medium transition-colors duration-200 border-border bg-transparent text-muted-foreground data-[state=off]:hover:text-foreground-soft data-[state=off]:hover:bg-ghost-hover/60 data-[state=on]:border-info-border data-[state=on]:bg-info-subtle data-[state=on]:text-info-subtle-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50',
  {
    variants: {
      size: {
        sm: 'gap-1 px-2 py-0.5 text-[11px]',
        md: 'gap-1 px-2.5 py-1 text-xs',
      },
    },
    defaultVariants: {
      size: 'md',
    },
  },
);

export const togglePillsIconTheme = cva('shrink-0', {
  variants: {
    size: {
      sm: 'w-2.5 h-2.5',
      md: 'w-3 h-3',
    },
  },
  defaultVariants: {
    size: 'md',
  },
});
