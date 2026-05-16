import { tv } from 'tailwind-variants';

export const sortableHeaderVariants = tv({
  slots: {
    base: 'inline-flex items-center gap-1.5 select-none cursor-pointer transition-colors duration-150 hover:text-slate-900 dark:hover:text-slate-100',
    icon: 'w-3.5 h-3.5 transition-opacity duration-150 shrink-0',
  },
  variants: {
    state: {
      asc: {
        icon: 'opacity-100',
      },
      desc: {
        icon: 'opacity-100',
      },
      none: {
        icon: 'opacity-30',
      },
    },
  },
  defaultVariants: {
    state: 'none',
  },
});
