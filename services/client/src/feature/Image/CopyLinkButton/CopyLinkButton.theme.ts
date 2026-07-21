import { tv } from 'tailwind-variants';
import type { VariantProps } from 'tailwind-variants';

export const copyLinkIconVariants = tv({
  slots: {
    icon: 'transition-all duration-200',
    caretIcon: 'h-3 w-3',
  },
  variants: {
    variant: {
      toolbar: {
        icon: 'h-4 w-4',
      },
      overlay: {
        icon: 'h-4 w-4 group-hover:scale-110',
      },
    },
  },
  defaultVariants: {
    variant: 'toolbar',
  },
});

export type CopyLinkButtonVariant = NonNullable<
  VariantProps<typeof copyLinkIconVariants>['variant']
>;
