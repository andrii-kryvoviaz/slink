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

export const modalOverlayVariants = cva(['fixed inset-0 z-50'], {
  variants: {
    backdrop: {
      enabled: 'bg-black/50',
      subtle: 'bg-black/30',
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
    'border rounded-2xl shadow-lg',
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
          'bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-800/80 dark:to-slate-700/60',
          'border-slate-200/50 dark:border-slate-700/30',
          'backdrop-blur-sm',
        ],
        solid: [
          'bg-slate-50 dark:bg-slate-800',
          'border-slate-200 dark:border-slate-700',
        ],
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
    'w-12 h-12 rounded-xl flex items-center justify-center',
    'shadow-md backdrop-blur-sm border flex-shrink-0',
    'bg-gradient-to-br',
  ],
  {
    variants: {
      variant: {
        blue: [
          'from-blue-500/10 to-indigo-600/15',
          'dark:from-blue-400/20 dark:to-indigo-500/25',
          'border-blue-300/40 dark:border-blue-600/50',
        ],
        green: [
          'from-green-500/10 to-emerald-600/15',
          'dark:from-green-400/20 dark:to-emerald-500/25',
          'border-green-300/40 dark:border-green-600/50',
        ],
        purple: [
          'from-indigo-500/10 to-purple-600/15',
          'dark:from-indigo-400/20 dark:to-purple-500/25',
          'border-indigo-300/40 dark:border-indigo-600/50',
        ],
        amber: [
          'from-amber-500/10 to-orange-600/15',
          'dark:from-amber-400/20 dark:to-orange-500/25',
          'border-amber-300/40 dark:border-amber-600/50',
        ],
        neutral: [
          'from-slate-500/10 to-gray-600/15',
          'dark:from-slate-400/20 dark:to-gray-500/25',
          'border-slate-300/40 dark:border-slate-600/50',
        ],
        danger: [
          'from-red-500/10 to-rose-600/15',
          'dark:from-red-400/20 dark:to-rose-500/25',
          'border-red-300/40 dark:border-red-600/50',
        ],
      },
    },
    defaultVariants: {
      variant: 'blue',
    },
  },
);

export const modalHeaderIconVariants = cva(
  ['drop-shadow-sm [&>svg]:h-6 [&>svg]:w-6'],
  {
    variants: {
      variant: {
        blue: 'text-blue-700 dark:text-blue-300',
        green: 'text-green-700 dark:text-green-300',
        purple: 'text-indigo-700 dark:text-indigo-300',
        amber: 'text-amber-700 dark:text-amber-300',
        neutral: 'text-slate-700 dark:text-slate-300',
        danger: 'text-red-700 dark:text-red-300',
      },
    },
    defaultVariants: {
      variant: 'blue',
    },
  },
);

export const modalFooterSubmitVariants = cva(
  ['flex-1 shadow-lg hover:shadow-xl transition-shadow duration-200'],
  {
    variants: {
      variant: {
        default: '',
        success: '',
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  },
);

export const buttonVariantMap: Record<ModalVariant, string> = {
  blue: 'gradient-purple',
  green: 'gradient-green',
  purple: 'gradient-purple',
  amber: 'gradient-purple',
  neutral: 'gradient-purple',
  danger: 'gradient-purple',
};

export type ModalOverlayVariants = VariantProps<typeof modalOverlayVariants>;
export type ModalContentVariants = VariantProps<typeof modalContentVariants>;
export type ModalHeaderIconContainerVariants = VariantProps<
  typeof modalHeaderIconContainerVariants
>;
export type ModalHeaderIconVariants = VariantProps<
  typeof modalHeaderIconVariants
>;
