import { cva } from 'class-variance-authority';
import type { VariantProps } from 'class-variance-authority';

export const textEllipsisTheme = cva(
  'flex max-h-5 overflow-hidden text-ellipsis text-sm font-medium',
  {
    variants: {
      maxWidth: {
        xs: 'max-w-[6rem]',
        sm: 'max-w-[8rem]',
        md: 'max-w-[10rem]',
        lg: 'max-w-[14rem]',
        xl: 'max-w-[18rem]',
        full: 'max-w-full',
      },
    },
    defaultVariants: {
      maxWidth: 'md',
    },
  },
);

export const textEllipsisFadeTheme = cva(
  'absolute inset-y-0 right-0 z-0 w-8 bg-linear-to-l to-transparent',
  {
    variants: {
      background: {
        default: 'from-bg-start',
        card: 'from-card',
        muted: 'from-muted',
        adaptive: 'from-card/80',
      },
    },
    defaultVariants: {
      background: 'default',
    },
  },
);

export type TextEllipsisVariants = VariantProps<typeof textEllipsisTheme>;
export type TextEllipsisFadeVariants = VariantProps<
  typeof textEllipsisFadeTheme
>;
