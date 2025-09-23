import { cva } from 'class-variance-authority';
import type { VariantProps } from 'class-variance-authority';

export const tagSelectorContainerVariants = cva(
  'min-h-[3.5rem] w-full px-4 py-3 transition-all duration-300 rounded-2xl',
  {
    variants: {
      variant: {
        default: [
          'relative overflow-hidden',
          'bg-gradient-to-br from-white via-white to-slate-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900',
          'border border-slate-200/60 dark:border-slate-700/60',
          'shadow-xl shadow-slate-500/5 dark:shadow-black/20',
          'backdrop-blur-sm',
          'transition-all duration-300',
          'hover:border-blue-400 dark:hover:border-blue-300',
          'hover:shadow-xl shadow-blue-500/10 dark:shadow-blue-500/15',
          'hover:bg-gradient-to-br hover:from-blue-50/50 hover:via-white hover:to-slate-50 dark:hover:from-blue-950/20 dark:hover:via-slate-800 dark:hover:to-slate-900',
          'focus-within:border-blue-400 dark:focus-within:border-blue-300',
          'focus-within:shadow-xl shadow-blue-500/15 dark:shadow-blue-500/20',
          'focus-within:ring-1 focus-within:ring-blue-200/30 dark:focus-within:ring-blue-700/30',
        ],
        neon: [
          'relative overflow-hidden',
          'bg-white dark:bg-slate-900',
          'border border-slate-200/60 dark:border-slate-700/60',
          'shadow-[0_0_12px_rgba(99,102,241,0.06)] dark:shadow-[0_0_12px_rgba(99,102,241,0.1)]',
          'backdrop-blur-sm',
          'transition-all duration-300',
          'hover:border-indigo-300/70 dark:hover:border-indigo-600/70',
          'hover:shadow-[0_0_16px_rgba(99,102,241,0.15)] dark:hover:shadow-[0_0_16px_rgba(99,102,241,0.25)]',
          'hover:bg-indigo-50/50 dark:hover:bg-indigo-900/20',
          'focus-within:border-indigo-400/70 dark:focus-within:border-indigo-500/70',
          'focus-within:bg-indigo-50/30 dark:focus-within:bg-indigo-900/30',
          'focus-within:shadow-[0_0_20px_rgba(99,102,241,0.2)] dark:focus-within:shadow-[0_0_20px_rgba(99,102,241,0.3)]',
          'focus-within:ring-1 focus-within:ring-indigo-300/30 dark:focus-within:ring-indigo-600/30',
        ],
        minimal: [
          'bg-gradient-to-br from-white/80 via-white/60 to-slate-50/40 dark:from-slate-900/80 dark:via-slate-800/60 dark:to-slate-900/40',
          'border border-slate-200/50 dark:border-slate-700/50',
          'transition-all duration-300',
          'hover:border-blue-400 dark:hover:border-blue-300',
          'hover:bg-gradient-to-br hover:from-blue-50/50 hover:via-white/80 hover:to-slate-50/60 dark:hover:from-blue-950/20 dark:hover:via-slate-800/80 dark:hover:to-slate-900/60',
          'focus-within:border-blue-400 dark:focus-within:border-blue-300',
          'focus-within:bg-gradient-to-br focus-within:from-blue-50/30 focus-within:via-white/70 focus-within:to-slate-50/50 dark:focus-within:from-blue-950/15 dark:focus-within:via-slate-800/70 dark:focus-within:to-slate-900/50',
        ],
      },
      disabled: {
        true: 'opacity-50 cursor-not-allowed',
        false: '',
      },
    },
    defaultVariants: {
      variant: 'default',
      disabled: false,
    },
  },
);

export const tagSelectorIconContainerVariants = cva(
  'w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-300',
  {
    variants: {
      variant: {
        default: [
          'bg-slate-100/50 dark:bg-slate-800/50',
          'border border-slate-200/50 dark:border-slate-600/30',
          'transition-all duration-150',
          'hover:bg-blue-100 dark:hover:bg-blue-800/40',
          'hover:border-blue-200 dark:hover:border-blue-600',
        ],
        neon: [
          'bg-indigo-100/50 dark:bg-indigo-900/30',
          'border border-indigo-200/50 dark:border-purple-300/30',
          'transition-all duration-300',
          'hover:bg-indigo-200/60 dark:hover:bg-indigo-800/40',
          'hover:border-indigo-300/60 dark:hover:border-purple-400/40',
        ],
        minimal:
          'bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 transition-all duration-150 hover:bg-blue-100 dark:hover:bg-blue-800/40 hover:border-blue-200 dark:hover:border-blue-600',
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  },
);

export const tagSelectorIconVariants = cva(
  'h-4 w-4 transition-all duration-300',
  {
    variants: {
      variant: {
        default: 'text-slate-600 dark:text-slate-400',
        neon: 'text-indigo-600 dark:text-purple-400',
        minimal: 'text-slate-500',
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  },
);

export type TagSelectorContainerVariants = VariantProps<
  typeof tagSelectorContainerVariants
>;
export type TagSelectorIconContainerVariants = VariantProps<
  typeof tagSelectorIconContainerVariants
>;
export type TagSelectorIconVariants = VariantProps<
  typeof tagSelectorIconVariants
>;
