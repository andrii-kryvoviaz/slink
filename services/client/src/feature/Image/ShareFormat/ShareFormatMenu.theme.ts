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
          'border-white/10 bg-neutral-900/95 text-white/85 backdrop-blur-md dark:border-white/10 dark:bg-neutral-900/95 dark:text-white/85',
        item: 'text-white/85 hover:bg-white/10 hover:text-white data-highlighted:bg-white/10 data-highlighted:text-white dark:text-white/85 dark:hover:bg-white/10 dark:hover:text-white dark:data-highlighted:bg-white/10 dark:data-highlighted:text-white',
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
