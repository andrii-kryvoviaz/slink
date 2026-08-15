import { type VariantProps, tv } from 'tailwind-variants';

export const splitButtonVariants = tv({
  slots: {
    wrapper:
      'flex items-center overflow-hidden duration-200 bg-transparent dark:bg-transparent text-foreground-soft dark:text-foreground group-hover:bg-hover',
    label: 'flex items-center font-medium transition-colors',
    aside:
      'flex items-center justify-center transition-all duration-200 bg-border-strong dark:bg-muted text-foreground-soft dark:group-hover:bg-border-strong',
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
      sm: { wrapper: 'rounded-none', aside: 'rounded-none' },
      md: { wrapper: 'rounded-sm', aside: 'rounded-none' },
      lg: { wrapper: 'rounded-md', aside: 'rounded-sm' },
      xl: { wrapper: 'rounded-lg', aside: 'rounded-md' },
      full: { wrapper: 'rounded-full', aside: 'rounded-full' },
    },
  },
  defaultVariants: {
    asidePosition: 'end',
    size: 'xs',
    rounded: 'lg',
  },
});

export type SplitButtonVariants = VariantProps<typeof splitButtonVariants>;
