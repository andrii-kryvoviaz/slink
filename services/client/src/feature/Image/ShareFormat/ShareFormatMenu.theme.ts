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
          'border-white/10 bg-neutral-900/95 text-white/85 backdrop-blur-md',
        item: 'text-white/85 hover:bg-white/10 hover:text-white data-highlighted:bg-white/10 data-highlighted:text-white',
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
