import { tv } from 'tailwind-variants';

export const accentIcon = tv({
  base: 'flex items-center justify-center shrink-0 rounded-full shadow-sm bg-info-solid/12 border border-info-border/40 dark:border-info-border/12 text-info',
  variants: {
    size: {
      md: 'h-10 w-10',
      lg: 'h-12 w-12',
    },
  },
  defaultVariants: {
    size: 'md',
  },
});
