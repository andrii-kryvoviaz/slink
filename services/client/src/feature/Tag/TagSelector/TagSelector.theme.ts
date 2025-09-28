import { cva } from 'class-variance-authority';
import type { VariantProps } from 'class-variance-authority';

export const tagSelectorContainerVariants = cva(
  'min-h-[3.5rem] w-full px-3 py-2 transition-all duration-200 rounded-lg',
  {
    variants: {
      variant: {
        default: [
          'border-border bg-background dark:bg-input/30',
          'border shadow-xs',
          'ring-offset-background',
          'transition-[color,box-shadow]',
          'focus-within:border-ring',
          'focus-within:ring-ring/50 focus-within:ring-[3px]',
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
          'focus-within:bg-gradient-to-br focus-within:from-blue-50/30 focus-within:via-white/70 focus-within:to-slate-50/50 dark:focus-within:from-blue-950/15 focus-within:via-slate-800/70 focus-within:to-slate-900/50',
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
  'w-8 h-8 rounded-md flex items-center justify-center transition-all duration-200',
  {
    variants: {
      variant: {
        default: ['text-muted-foreground'],
        neon: [
          'bg-indigo-100/50 dark:bg-indigo-900/30',
          'border border-indigo-200/50 dark:border-purple-300/30',
          'transition-all duration-300',
          'hover:bg-indigo-200/60 dark:hover:bg-indigo-800/40',
          'hover:border-indigo-300/60 dark:hover:border-purple-400/40',
        ],
        minimal: [
          'bg-slate-50 dark:bg-slate-800',
          'border border-slate-200 dark:border-slate-700',
          'transition-all duration-150',
          'hover:bg-blue-100 dark:hover:bg-blue-800/40',
          'hover:border-blue-200 dark:hover:border-blue-600',
        ],
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  },
);

export const tagSelectorIconVariants = cva(
  'h-4 w-4 transition-all duration-200',
  {
    variants: {
      variant: {
        default: 'text-muted-foreground',
        neon: 'text-indigo-600 dark:text-purple-400',
        minimal: 'text-muted-foreground',
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
