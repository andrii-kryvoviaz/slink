import { cva } from 'class-variance-authority';
import type { VariantProps } from 'class-variance-authority';

export const ChipPopoverSurface = cva('', {
  variants: {
    variant: {
      default: '',
      dark: 'dark:bg-gray-950/95 dark:border-gray-800/80',
    },
  },
  defaultVariants: {
    variant: 'dark',
  },
});

export type ChipPopoverVariant = NonNullable<
  VariantProps<typeof ChipPopoverSurface>['variant']
>;
