import { cva } from 'class-variance-authority';
import type { VariantProps } from 'class-variance-authority';

export const tagSelectorContainerVariants = cva(
  'min-h-[3.5rem] w-full px-3 py-2 transition-all duration-200 rounded-lg',
  {
    variants: {
      variant: {
        default: [
          'bg-blue-500/10 dark:bg-blue-400/10 text-blue-600 dark:text-blue-400',
          'border border-blue-500/20 dark:border-blue-400/30',
          'shadow-xs',
          'transition-all duration-200',
          'hover:bg-blue-500/15 dark:hover:bg-blue-400/15',
          'hover:border-blue-500/30 dark:hover:border-blue-400/40',
          'focus-within:bg-blue-500/15 dark:focus-within:bg-blue-400/15',
          'focus-within:border-blue-500/40 dark:focus-within:border-blue-400/50',
          'focus-within:ring-2 focus-within:ring-indigo-500/20 dark:focus-within:ring-indigo-400/30',
        ],
        neon: [
          'bg-blue-500/10 dark:bg-blue-400/10 text-blue-600 dark:text-blue-400',
          'border border-blue-500/20 dark:border-blue-400/30',
          'shadow-xs',
          'transition-all duration-200',
          'hover:bg-blue-500/15 dark:hover:bg-blue-400/15',
          'hover:border-blue-500/30 dark:hover:border-blue-400/40',
          'focus-within:bg-blue-500/15 dark:focus-within:bg-blue-400/15',
          'focus-within:border-blue-500/40 dark:focus-within:border-blue-400/50',
          'focus-within:ring-2 focus-within:ring-indigo-500/20 dark:focus-within:ring-indigo-400/30',
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
  'w-8 h-8 rounded-md flex items-center justify-center transition-all duration-200 group',
  {
    variants: {
      variant: {
        default: [
          'text-blue-600 dark:text-blue-400',
          'hover:bg-blue-500/20 dark:hover:bg-blue-400/20',
          'hover:text-blue-700 dark:hover:text-blue-300',
          'rounded-md',
        ],
        neon: [
          'text-blue-600 dark:text-blue-400',
          'hover:bg-blue-500/20 dark:hover:bg-blue-400/20',
          'hover:text-blue-700 dark:hover:text-blue-300',
          'rounded-md',
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
        default:
          'text-blue-600 dark:text-blue-400 group-hover:text-blue-700 dark:group-hover:text-blue-300 transition-colors',
        neon: 'text-blue-600 dark:text-blue-400 group-hover:text-blue-700 dark:group-hover:text-blue-300 transition-colors',
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
