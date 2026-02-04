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
export type ModalBackground = 'glass' | 'solid';

export const modalOverlayVariants = cva(['fixed inset-0 z-30'], {
  variants: {
    backdrop: {
      enabled: 'bg-black/30',
      subtle: 'bg-black/10',
      disabled: 'bg-transparent',
    },
    animation: {
      fade: 'data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0',
      slide:
        'data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0',
      none: '',
    },
  },
  defaultVariants: {
    backdrop: 'enabled',
    animation: 'fade',
  },
});

export const modalContentVariants = cva(
  [
    'group fixed left-[50%] top-[50%] z-50 grid w-full max-w-[calc(100%-2rem)] translate-x-[-50%] translate-y-[-50%] gap-4 p-6 duration-200',
    'border rounded-2xl shadow-sm',
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
        fade: 'data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95',
        slide:
          'data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:slide-out-to-bottom-4 data-[state=open]:slide-in-from-bottom-4',
        none: '',
      },
      background: {
        glass: [
          'dark:bg-black/30 bg-white/80 backdrop-blur-xl',
          'border-white/10',
        ],
        solid: ['bg-black/90 backdrop-blur-xl', 'border-white/10'],
      },
    },
    defaultVariants: {
      size: 'md',
      animation: 'fade',
      background: 'glass',
    },
  },
);

export const modalHeaderIconContainerVariants = cva(
  [
    'w-10 h-10 rounded-lg flex items-center justify-center',
    'border flex-shrink-0',
  ],
  {
    variants: {
      variant: {
        blue: [
          'bg-blue-50/80 dark:bg-blue-950/80',
          'border-blue-200/60 dark:border-blue-800/60',
        ],
        green: [
          'bg-green-50/80 dark:bg-green-950/80',
          'border-green-200/60 dark:border-green-800/60',
        ],
        purple: [
          'bg-indigo-50/80 dark:bg-indigo-950/80',
          'border-indigo-200/60 dark:border-indigo-800/60',
        ],
        amber: [
          'bg-amber-50/80 dark:bg-amber-950/80',
          'border-amber-200/60 dark:border-amber-800/60',
        ],
        neutral: [
          'bg-slate-50/80 dark:bg-slate-800/80',
          'border-slate-200/60 dark:border-slate-700/60',
        ],
        danger: [
          'bg-red-50/80 dark:bg-red-950/80',
          'border-red-200/60 dark:border-red-800/60',
        ],
      },
    },
    defaultVariants: {
      variant: 'blue',
    },
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
export type ModalHeaderIconContainerVariants = VariantProps<
  typeof modalHeaderIconContainerVariants
>;
export type ModalHeaderIconVariants = VariantProps<
  typeof modalHeaderIconVariants
>;
