import { cva } from 'class-variance-authority';
import type { VariantProps } from 'class-variance-authority';

export const tagSelectorContainerVariants = cva(
  'min-h-[3.5rem] w-full px-3 py-2 transition-all duration-300 rounded-xl shadow-sm',
  {
    variants: {
      variant: {
        default: [
          'bg-white dark:bg-slate-900',
          'border border-slate-200/60 dark:border-slate-700/60',
          'transition-all duration-300',
          'hover:border-blue-300/70 dark:hover:border-blue-600/50',
          'hover:shadow-md hover:shadow-blue-200/40 dark:hover:shadow-blue-900/30',
          'hover:bg-blue-50/30 dark:hover:bg-blue-950/20',
          'focus-within:border-blue-400/80 dark:focus-within:border-blue-500/60',
          'focus-within:shadow-lg focus-within:shadow-blue-200/50 dark:focus-within:shadow-blue-900/40',
          'focus-within:bg-blue-50/40 dark:focus-within:bg-blue-950/30',
          'focus-within:ring-2 focus-within:ring-blue-300/30 dark:focus-within:ring-blue-600/20',
        ],
        neon: [
          'bg-white dark:bg-slate-900',
          'border border-slate-200/60 dark:border-slate-700/60',
          'transition-all duration-300',
          'hover:border-blue-300/70 dark:hover:border-blue-600/50',
          'hover:shadow-md hover:shadow-blue-200/40 dark:hover:shadow-blue-900/30',
          'hover:bg-blue-50/30 dark:hover:bg-blue-950/20',
          'focus-within:border-blue-400/80 dark:focus-within:border-blue-500/60',
          'focus-within:shadow-lg focus-within:shadow-blue-200/50 dark:focus-within:shadow-blue-900/40',
          'focus-within:bg-blue-50/40 dark:focus-within:bg-blue-950/30',
          'focus-within:ring-2 focus-within:ring-blue-300/30 dark:focus-within:ring-blue-600/20',
        ],
        minimal: [
          'bg-white dark:bg-slate-900',
          'border border-slate-200/60 dark:border-slate-700/60',
          'transition-all duration-300',
          'hover:border-blue-300/70 dark:hover:border-blue-500/50',
          'hover:shadow-md hover:shadow-blue-100/50 dark:hover:shadow-blue-900/30',
          'hover:bg-blue-50/30 dark:hover:bg-blue-950/20',
          'focus-within:border-blue-400/80 dark:focus-within:border-blue-400/60',
          'focus-within:shadow-lg focus-within:shadow-blue-200/40 dark:focus-within:shadow-blue-900/40',
          'focus-within:bg-blue-50/40 dark:focus-within:bg-blue-950/30',
          'focus-within:ring-2 focus-within:ring-blue-300/30 dark:focus-within:ring-blue-500/20',
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
  'w-8 h-8 rounded-lg flex items-center justify-center transition-all duration-300 group',
  {
    variants: {
      variant: {
        default: [
          'bg-blue-50 dark:bg-blue-900/30',
          'border border-blue-200/50 dark:border-blue-700/40',
          'text-blue-600 dark:text-blue-400',
          'transition-all duration-300',
          'hover:bg-blue-100 dark:hover:bg-blue-800/40',
          'hover:border-blue-300/70 dark:hover:border-blue-600/60',
          'hover:shadow-sm hover:shadow-blue-200/40 dark:hover:shadow-blue-700/20',
        ],
        neon: [
          'bg-blue-50 dark:bg-blue-900/30',
          'border border-blue-200/50 dark:border-blue-700/40',
          'text-blue-600 dark:text-blue-400',
          'transition-all duration-300',
          'hover:bg-blue-100 dark:hover:bg-blue-800/40',
          'hover:border-blue-300/70 dark:hover:border-blue-600/60',
          'hover:shadow-sm hover:shadow-blue-200/40 dark:hover:shadow-blue-700/20',
        ],
        minimal: [
          'bg-blue-50 dark:bg-blue-900/30',
          'border border-blue-200/50 dark:border-blue-700/40',
          'text-blue-600 dark:text-blue-400',
          'transition-all duration-300',
          'hover:bg-blue-100 dark:hover:bg-blue-900/50',
          'hover:border-blue-300/70 dark:hover:border-blue-600/60',
          'hover:shadow-sm hover:shadow-blue-200/40 dark:hover:shadow-blue-500/20',
        ],
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
        default:
          'text-blue-600 dark:text-blue-400 group-hover:text-blue-700 dark:group-hover:text-blue-300 group-hover:scale-110 transition-all',
        neon: 'text-blue-600 dark:text-blue-400 group-hover:text-blue-700 dark:group-hover:text-blue-300 group-hover:scale-110 transition-all',
        minimal:
          'text-blue-600 dark:text-blue-400 group-hover:text-blue-700 dark:group-hover:text-blue-300 group-hover:scale-110 transition-all',
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
