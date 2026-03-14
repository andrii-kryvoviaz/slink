import { type VariantProps, tv } from 'tailwind-variants';

export const splitButtonVariants = tv({
  slots: {
    wrapper:
      'flex items-center overflow-hidden duration-200 bg-transparent dark:bg-transparent text-slate-700 dark:text-white group-hover:bg-slate-100 dark:group-hover:bg-slate-800',
    label: 'flex items-center font-medium transition-colors',
    aside:
      'flex items-center justify-center transition-all duration-200 bg-slate-300 dark:bg-slate-700 text-slate-700 dark:text-slate-200 group-hover:bg-slate-300 dark:group-hover:bg-slate-600',
  },
  variants: {
    asidePosition: {
      end: { wrapper: 'flex-row' },
      start: { wrapper: 'flex-row-reverse' },
    },
    size: {
      xs: {
        label: 'px-2.5 py-1 text-xs',
        aside: 'px-2.5 py-1',
      },
      sm: {
        label: 'px-3 py-1.5 text-xs',
        aside: 'px-3 py-1.5',
      },
      md: {
        label: 'px-4 py-2 text-sm',
        aside: 'px-3.5 py-2',
      },
      lg: {
        label: 'px-5 py-2.5 text-base',
        aside: 'px-4 py-2.5',
      },
    },
    rounded: {
      none: { wrapper: 'rounded-none', aside: 'rounded-none' },
      sm: { wrapper: 'rounded-sm', aside: 'rounded-sm' },
      md: { wrapper: 'rounded-md', aside: 'rounded-md' },
      lg: { wrapper: 'rounded-lg', aside: 'rounded-lg' },
      xl: { wrapper: 'rounded-xl', aside: 'rounded-xl' },
      full: { wrapper: 'rounded-full', aside: 'rounded-full' },
    },
  },
  defaultVariants: {
    asidePosition: 'end',
    size: 'xs',
    rounded: 'md',
  },
});

export type SplitButtonVariants = VariantProps<typeof splitButtonVariants>;
