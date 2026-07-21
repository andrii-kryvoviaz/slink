import { tv } from 'tailwind-variants';
import type { VariantProps } from 'tailwind-variants';

export const copyLinkCapsuleVariants = tv({
  slots: {
    capsule:
      'inline-flex items-stretch overflow-hidden rounded-full select-none',
    trigger: 'flex h-full items-stretch',
    copy: 'group flex h-full cursor-pointer items-center justify-center transition-all duration-200 disabled:pointer-events-none',
    caret:
      'flex cursor-pointer items-center justify-center border-l-[0.5px] transition-all duration-200 disabled:pointer-events-none',
    icon: 'transition-all duration-200 group-hover:scale-110',
    caretIcon: '',
  },
  variants: {
    size: {
      sm: {
        capsule: 'h-[26px]',
        copy: 'pr-1.5 pl-2.5',
        caret: 'w-5',
        icon: 'h-4 w-4',
        caretIcon: 'h-3 w-3',
      },
      md: {
        capsule: 'h-7',
        copy: 'pr-1.5 pl-2.5',
        caret: 'w-6',
        icon: 'h-5 w-5',
        caretIcon: 'h-3.5 w-3.5',
      },
    },
    variant: {
      glass: {
        capsule: 'bg-white/10',
        copy: 'text-white/80 hover:bg-indigo-600/80 dark:hover:bg-indigo-500/80 hover:text-white',
        caret:
          'border-white/15 text-white/80 hover:bg-indigo-600/80 dark:hover:bg-indigo-500/80 hover:text-white',
      },
      overlay: {
        capsule: 'bg-black/60 backdrop-blur-sm shadow-lg',
        copy: 'text-white/80 hover:bg-indigo-600/80 dark:hover:bg-indigo-500/80 hover:text-white',
        caret:
          'border-white/20 text-white/80 hover:bg-indigo-600/80 dark:hover:bg-indigo-500/80 hover:text-white',
      },
    },
    copied: {
      true: {
        copy: 'text-white',
      },
      false: {},
    },
  },
  defaultVariants: {
    size: 'md',
    variant: 'glass',
    copied: false,
  },
});

export type CopyLinkButtonSize = NonNullable<
  VariantProps<typeof copyLinkCapsuleVariants>['size']
>;
export type CopyLinkButtonVariant = NonNullable<
  VariantProps<typeof copyLinkCapsuleVariants>['variant']
>;
