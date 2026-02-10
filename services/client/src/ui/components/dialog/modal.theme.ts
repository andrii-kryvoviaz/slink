import { cva } from 'class-variance-authority';
import type { VariantProps } from 'class-variance-authority';

export type ModalVariant =
  | 'blue'
  | 'green'
  | 'purple'
  | 'amber'
  | 'neutral'
  | 'danger';
export type ModalBackdrop = 'enabled' | 'subtle' | 'disabled';
export type ModalAnimation = 'fade' | 'slide' | 'none';
export type ModalSize = 'sm' | 'md' | 'lg' | 'xl' | '2xl';
export type ModalBackground = 'glass' | 'frosted' | 'solid';

export const modalOverlayVariants = cva(
  ['fixed inset-0 z-30 backdrop-blur-sm'],
  {
    variants: {
      backdrop: {
        enabled: 'bg-[var(--modal-overlay-tint)]',
        subtle: 'bg-black/10 backdrop-blur-[2px]',
        disabled: 'bg-transparent backdrop-blur-none',
      },
      animation: {
        fade: 'data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 duration-300',
        slide:
          'data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 duration-300',
        none: '',
      },
    },
    defaultVariants: { backdrop: 'enabled', animation: 'fade' },
  },
);

export const modalContentVariants = cva(
  [
    'group fixed left-[50%] top-[50%] z-50 grid w-full max-w-[calc(100%-2rem)] translate-x-[-50%] translate-y-[-50%] gap-4 p-6',
    'rounded-3xl',
    'modal-glass-highlight',
  ],
  {
    variants: {
      size: {
        sm: 'sm:max-w-sm',
        md: 'sm:max-w-lg',
        lg: 'sm:max-w-2xl',
        xl: 'sm:max-w-4xl',
        '2xl': 'sm:max-w-6xl',
      },
      animation: {
        fade: [
          'data-[state=open]:animate-in data-[state=closed]:animate-out',
          'data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0',
          'data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95',
          'data-[state=closed]:duration-200 data-[state=open]:duration-300',
        ],
        slide: [
          'data-[state=open]:animate-in data-[state=closed]:animate-out',
          'data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0',
          'data-[state=closed]:slide-out-to-bottom-4 data-[state=open]:slide-in-from-bottom-4',
          'data-[state=closed]:duration-200 data-[state=open]:duration-300',
        ],
        none: '',
      },
      background: {
        glass: [
          'backdrop-blur-[28px] backdrop-saturate-[1.6]',
          'bg-card/65',
          'shadow-2xl',
          'border border-border/15 ring-1 ring-white/[0.05]',
        ],
        frosted: [
          'backdrop-blur-[28px] backdrop-saturate-[1.6]',
          'bg-background/70',
          'shadow-2xl',
          'border border-border/15 ring-1 ring-white/[0.05]',
        ],
        solid: ['bg-black/90 backdrop-blur-xl', 'border border-white/10'],
      },
    },
    defaultVariants: { size: 'md', animation: 'fade', background: 'frosted' },
  },
);

export const modalAccentVariants = cva(
  'shadow-[0_0_0_1px_rgb(100_116_139/0.06)]',
  {
    variants: {
      variant: {
        blue: '',
        green: '',
        purple: '',
        amber: '',
        neutral: '',
        danger: '',
      },
    },
    defaultVariants: { variant: 'blue' },
  },
);

export const modalHeaderIconContainerVariants = cva(
  [
    'w-10 h-10 rounded-xl flex items-center justify-center',
    'flex-shrink-0',
    'backdrop-blur-sm ring-1 ring-white/[0.08]',
  ],
  {
    variants: {
      variant: {
        blue: 'bg-blue-500/10 dark:bg-blue-400/10',
        green: 'bg-green-500/10 dark:bg-green-400/10',
        purple: 'bg-indigo-500/10 dark:bg-indigo-400/10',
        amber: 'bg-amber-500/10 dark:bg-amber-400/10',
        neutral: 'bg-slate-500/10 dark:bg-slate-400/10',
        danger: 'bg-red-500/10 dark:bg-red-400/10',
      },
    },
    defaultVariants: { variant: 'blue' },
  },
);

export const modalHeaderIconVariants = cva(['[&>svg]:h-5 [&>svg]:w-5'], {
  variants: {
    variant: {
      blue: 'text-blue-600 dark:text-blue-400',
      green: 'text-green-600 dark:text-green-400',
      purple: 'text-indigo-600 dark:text-indigo-400',
      amber: 'text-amber-600 dark:text-amber-400',
      neutral: 'text-slate-600 dark:text-slate-400',
      danger: 'text-red-600 dark:text-red-400',
    },
  },
  defaultVariants: {
    variant: 'blue',
  },
});

export const modalFooterSubmitVariants = cva(['flex-1'], {
  variants: {
    variant: {
      default: '',
      success: '',
    },
  },
  defaultVariants: {
    variant: 'default',
  },
});

export const buttonVariantMap: Record<ModalVariant, string> = {
  blue: 'outline-blue',
  green: 'outline-green',
  purple: 'outline-purple',
  amber: 'outline-amber',
  neutral: 'outline-blue',
  danger: 'outline-danger',
};

export type ModalOverlayVariants = VariantProps<typeof modalOverlayVariants>;
export type ModalContentVariants = VariantProps<typeof modalContentVariants>;
export type ModalAccentVariants = VariantProps<typeof modalAccentVariants>;
export type ModalHeaderIconContainerVariants = VariantProps<
  typeof modalHeaderIconContainerVariants
>;
export type ModalHeaderIconVariants = VariantProps<
  typeof modalHeaderIconVariants
>;
