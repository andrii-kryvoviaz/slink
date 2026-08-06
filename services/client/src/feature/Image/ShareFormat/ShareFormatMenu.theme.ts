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
          'border-surface-inverse-foreground/10 bg-surface-inverse/95 text-surface-inverse-foreground/85 backdrop-blur-md',
        item: 'text-surface-inverse-foreground/85 hover:bg-surface-inverse-foreground/10 hover:text-surface-inverse-foreground data-highlighted:bg-surface-inverse-foreground/10 data-highlighted:text-surface-inverse-foreground',
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
