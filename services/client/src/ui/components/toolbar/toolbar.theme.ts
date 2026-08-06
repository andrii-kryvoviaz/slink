import { tv } from 'tailwind-variants';
import type { VariantProps } from 'tailwind-variants';

export const toolbarVariants = tv({
  base: 'inline-flex h-8 items-stretch overflow-hidden rounded-full',
  variants: {
    tone: {
      dark: 'bg-surface-inverse-foreground/8',
    },
  },
  defaultVariants: {
    tone: 'dark',
  },
});

export const toolbarSeparatorVariants = tv({
  base: 'self-center h-[18px] w-px',
  variants: {
    tone: {
      dark: 'bg-surface-inverse-foreground/8',
    },
  },
  defaultVariants: {
    tone: 'dark',
  },
});

export const toolbarGroupVariants = tv({
  base: 'inline-flex items-stretch select-none',
  variants: {
    surface: {
      toolbar: 'h-full',
      floating:
        'h-7 overflow-hidden rounded-full bg-scrim/60 backdrop-blur-sm shadow-lg',
    },
  },
  defaultVariants: {
    surface: 'floating',
  },
});

export const toolbarButtonVariants = tv({
  base: 'relative inline-flex cursor-pointer items-center justify-center select-none transition-all duration-200 disabled:pointer-events-none focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-surface-inverse-foreground/45',
  variants: {
    shape: {
      segment: 'h-full w-9 rounded-none',
      pill: 'h-7 rounded-full px-2.5',
    },
    surface: {
      toolbar: '',
      floating: '',
    },
    tone: {
      dark: '',
    },
    active: {
      true: '',
      false: '',
    },
    loading: {
      true: 'pointer-events-none opacity-70',
      false: '',
    },
  },
  compoundVariants: [
    {
      shape: 'segment',
      surface: 'toolbar',
      tone: 'dark',
      class:
        'text-surface-inverse-foreground/70 hover:bg-surface-inverse-foreground/12 hover:text-surface-inverse-foreground',
    },
    {
      shape: 'segment',
      surface: 'floating',
      tone: 'dark',
      class:
        'text-surface-inverse-foreground/80 hover:bg-surface-inverse-foreground/18 hover:text-surface-inverse-foreground',
    },
    {
      shape: 'pill',
      tone: 'dark',
      class:
        'bg-scrim/60 backdrop-blur-sm shadow-lg hover:bg-surface-inverse/70',
    },
    {
      active: true,
      tone: 'dark',
      class: 'bg-surface-inverse-foreground/22 text-surface-inverse-foreground',
    },
  ],
  defaultVariants: {
    shape: 'segment',
    surface: 'toolbar',
    tone: 'dark',
    active: false,
    loading: false,
  },
});

export const toolbarTierVariants = tv({
  slots: {
    full: '@max-[20rem]:hidden',
    compact: '@[20rem]:hidden',
  },
});

export type ToolbarButtonShape = NonNullable<
  VariantProps<typeof toolbarButtonVariants>['shape']
>;
