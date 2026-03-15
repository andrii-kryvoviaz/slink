import { type VariantProps, tv } from 'tailwind-variants';

export const viewModeLayoutVariants = tv({
  base: 'flex flex-col',
  variants: {
    spacing: {
      xs: 'gap-1',
      sm: 'gap-2',
      md: 'gap-4',
      lg: 'gap-6',
    },
  },
  defaultVariants: {
    spacing: 'md',
  },
});

export type ViewModeLayoutVariants = VariantProps<
  typeof viewModeLayoutVariants
>;
