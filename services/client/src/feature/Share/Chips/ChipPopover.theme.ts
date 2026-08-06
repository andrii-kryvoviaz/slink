import { cva } from 'class-variance-authority';
import type { VariantProps } from 'class-variance-authority';

export const ChipPopoverSurface = cva('', {
  variants: {
    variant: {
      default: '',
      dark: 'dark:bg-background/95 dark:border-border/80',
    },
  },
  defaultVariants: {
    variant: 'dark',
  },
});

export type ChipPopoverVariant = NonNullable<
  VariantProps<typeof ChipPopoverSurface>['variant']
>;
