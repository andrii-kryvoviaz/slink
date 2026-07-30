import { tv } from 'tailwind-variants';
import type { VariantProps } from 'tailwind-variants';

export const toolbarVariants = tv({
  base: 'inline-flex h-8 items-stretch overflow-hidden rounded-full',
  variants: {
    tone: {
      dark: 'bg-white/8',
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
      dark: 'bg-white/8',
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
        'h-7 overflow-hidden rounded-full bg-black/60 backdrop-blur-sm shadow-lg',
    },
  },
  defaultVariants: {
    surface: 'floating',
  },
});

export const toolbarButtonVariants = tv({
  base: 'relative inline-flex cursor-pointer items-center justify-center select-none transition-all duration-200 disabled:pointer-events-none focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-slate-400/70',
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
      class: 'text-white/70 hover:bg-white/12 hover:text-white',
    },
    {
      shape: 'segment',
      surface: 'floating',
      tone: 'dark',
      class: 'text-white/80 hover:bg-white/18 hover:text-white',
    },
    {
      shape: 'pill',
      tone: 'dark',
      class: 'bg-black/60 backdrop-blur-sm shadow-lg hover:bg-neutral-700/70',
    },
    {
      active: true,
      tone: 'dark',
      class: 'bg-white/22 text-white',
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
