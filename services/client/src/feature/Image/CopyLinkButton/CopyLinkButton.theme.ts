import { tv } from 'tailwind-variants';
import type { VariantProps } from 'tailwind-variants';

export const copyLinkCapsuleVariants = tv({
  slots: {
    capsule: 'inline-flex items-stretch select-none',
    trigger: 'flex h-full items-stretch',
    copy: 'group flex cursor-pointer items-center justify-center transition-all duration-200 disabled:pointer-events-none focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-slate-400/70',
    caret:
      'flex cursor-pointer items-center justify-center transition-all duration-200 disabled:pointer-events-none focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-slate-400/70',
    icon: 'transition-all duration-200',
    caretIcon: '',
  },
  variants: {
    size: {
      sm: {
        icon: 'h-4 w-4',
        caretIcon: 'h-3 w-3',
      },
      md: {
        icon: 'h-5 w-5',
        caretIcon: 'h-3.5 w-3.5',
      },
    },
    variant: {
      toolbar: {
        capsule: 'h-full',
        copy: 'h-full w-9 rounded-none text-white/70 hover:bg-white/12 hover:text-white',
        caret:
          'h-full w-[26px] rounded-none text-white/70 hover:bg-white/12 hover:text-white',
        icon: 'h-4 w-4',
        caretIcon: 'h-3 w-3',
      },
      overlay: {
        capsule:
          'h-7 overflow-hidden rounded-full bg-black/60 backdrop-blur-sm shadow-lg',
        copy: 'h-full w-9 rounded-none text-white/80 hover:bg-white/18 hover:text-white',
        caret:
          'h-full w-[26px] rounded-none text-white/80 hover:bg-white/18 hover:text-white',
        icon: 'group-hover:scale-110',
      },
    },
    copied: {
      true: {},
      false: {},
    },
  },
  compoundVariants: [
    {
      variant: 'toolbar',
      copied: true,
      class: {
        copy: 'bg-white/22 text-white',
      },
    },
    {
      variant: 'overlay',
      copied: true,
      class: {
        copy: 'bg-white/22 text-white',
      },
    },
  ],
  defaultVariants: {
    size: 'md',
    variant: 'toolbar',
    copied: false,
  },
});

export type CopyLinkButtonSize = NonNullable<
  VariantProps<typeof copyLinkCapsuleVariants>['size']
>;
export type CopyLinkButtonVariant = NonNullable<
  VariantProps<typeof copyLinkCapsuleVariants>['variant']
>;
