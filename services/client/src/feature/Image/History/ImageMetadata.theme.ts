import { cva } from 'class-variance-authority';

export const metadataContainerTheme = cva(
  'flex items-center overflow-hidden whitespace-nowrap tabular-nums text-xs text-foreground-subtle',
  {
    variants: {
      gap: {
        sm: 'gap-2.5',
        md: 'gap-3',
      },
    },
    defaultVariants: {
      gap: 'sm',
    },
  },
);
