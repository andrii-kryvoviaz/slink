import type { ButtonVariant } from '@slink/ui/components/button';
import { cva } from 'class-variance-authority';

export const containerVariants = cva(
  'flex flex-col items-center justify-center text-center',
  {
    variants: {
      size: {
        sm: 'py-12',
        md: 'py-16',
        lg: 'py-20',
      },
    },
    defaultVariants: {
      size: 'md',
    },
  },
);

export const iconContainerVariants = cva(
  'relative rounded-3xl flex items-center justify-center border backdrop-blur-sm shadow-lg',
  {
    variants: {
      size: {
        sm: 'w-16 h-16 mb-6',
        md: 'w-20 h-20 mb-8',
        lg: 'w-24 h-24 mb-8',
      },
      variant: {
        default:
          'bg-gradient-to-br from-slate-100/80 to-slate-200/60 dark:from-slate-800/60 dark:to-slate-700/40 border-slate-200/50 dark:border-slate-700/30 shadow-slate-200/20 dark:shadow-slate-900/40',
        blue: 'bg-gradient-to-br from-blue-100/80 to-indigo-100/60 dark:from-blue-900/30 dark:to-indigo-900/20 border-blue-200/50 dark:border-indigo-700/30 shadow-blue-200/20 dark:shadow-indigo-900/40',
        purple:
          'bg-gradient-to-br from-purple-100/80 to-violet-100/60 dark:from-purple-900/30 dark:to-violet-900/20 border-purple-200/50 dark:border-violet-700/30 shadow-purple-200/20 dark:shadow-violet-900/40',
        pink: 'bg-gradient-to-br from-pink-100/80 to-rose-100/60 dark:from-pink-900/30 dark:to-rose-900/20 border-pink-200/50 dark:border-rose-700/30 shadow-pink-200/20 dark:shadow-rose-900/40',
        red: 'bg-gradient-to-br from-red-100/80 to-rose-100/60 dark:from-red-900/30 dark:to-rose-900/20 border-red-200/50 dark:border-rose-700/30 shadow-red-200/20 dark:shadow-rose-900/40',
      },
    },
    defaultVariants: {
      size: 'md',
      variant: 'default',
    },
  },
);

export const iconVariants = cva('relative z-10', {
  variants: {
    size: {
      sm: 'w-8 h-8',
      md: 'w-10 h-10',
      lg: 'w-12 h-12',
    },
    variant: {
      default: 'text-slate-600 dark:text-slate-400',
      blue: 'text-blue-600 dark:text-blue-400',
      purple: 'text-purple-600 dark:text-purple-400',
      pink: 'text-pink-600 dark:text-pink-400',
      red: 'text-red-600 dark:text-red-400',
    },
  },
  defaultVariants: {
    size: 'md',
    variant: 'default',
  },
});

export const titleVariants = cva(
  'font-semibold bg-gradient-to-r bg-clip-text text-transparent',
  {
    variants: {
      size: {
        sm: 'text-lg',
        md: 'text-xl',
        lg: 'text-2xl',
      },
      variant: {
        default:
          'from-slate-800 to-slate-600 dark:from-slate-200 dark:to-slate-400',
        blue: 'from-blue-800 to-indigo-600 dark:from-blue-200 dark:to-indigo-400',
        purple:
          'from-purple-800 to-violet-600 dark:from-purple-200 dark:to-violet-400',
        pink: 'from-pink-800 to-rose-600 dark:from-pink-200 dark:to-rose-400',
        red: 'from-red-800 to-rose-600 dark:from-red-200 dark:to-rose-400',
      },
    },
    defaultVariants: {
      size: 'md',
      variant: 'default',
    },
  },
);

export const descriptionVariants = cva(
  'text-slate-600 dark:text-slate-400 max-w-md mx-auto leading-relaxed',
  {
    variants: {
      size: {
        sm: 'text-sm',
        md: 'text-base',
        lg: 'text-lg',
      },
    },
    defaultVariants: {
      size: 'md',
    },
  },
);

export const emptyStateButtonVariantMap: Record<string, ButtonVariant> = {
  default: 'soft-blue',
  blue: 'soft-blue',
  purple: 'soft-violet',
  pink: 'soft-red',
  red: 'soft-red',
};
