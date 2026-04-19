import { tv } from 'tailwind-variants';

export const accentIcon = tv({
  base: 'flex items-center justify-center shrink-0 rounded-full shadow-sm bg-blue-100 border border-blue-200/40 text-blue-600 dark:bg-blue-900/30 dark:border-blue-800/30 dark:text-blue-400',
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
