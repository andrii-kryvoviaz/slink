import { tv } from 'tailwind-variants';
import type { VariantProps } from 'tailwind-variants';

export const shareFormatMenuTheme = tv({
  slots: {
    content: 'min-w-[180px]',
    item: 'pl-8!',
  },
  variants: {
    tone: {
      default: {},
      dark: {
        content:
          'border-on-surface-inverse/10 bg-surface-inverse/95 text-on-surface-inverse/85 backdrop-blur-md',
        item: 'text-on-surface-inverse/85 hover:bg-on-surface-inverse/10 hover:text-on-surface-inverse data-highlighted:bg-on-surface-inverse/10 data-highlighted:text-on-surface-inverse',
      },
    },
  },
  defaultVariants: {
    tone: 'default',
  },
});

export type ShareFormatMenuTone = NonNullable<
  VariantProps<typeof shareFormatMenuTheme>['tone']
>;
