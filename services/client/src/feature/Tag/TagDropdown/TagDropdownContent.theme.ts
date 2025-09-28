import { cva } from 'class-variance-authority';
import type { VariantProps } from 'class-variance-authority';

export const tagDropdownContentVariants = cva(
  'absolute top-full left-0 right-0 mt-2 z-50 backdrop-blur-sm transition-all duration-300',
  {
    variants: {
      variant: {
        default: [
          'bg-gradient-to-br from-white via-white to-slate-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900',
          'border border-slate-200/60 dark:border-slate-700/60',
          'rounded-2xl shadow-xl shadow-slate-500/5 dark:shadow-black/20',
          'ring-1 ring-slate-100/20 dark:ring-slate-600/20',
        ],
        neon: [
          'bg-white/95 dark:bg-slate-900/95',
          'border border-slate-200/60 dark:border-slate-700/60',
          'rounded-2xl',
          'ring-1 ring-indigo-100/20 dark:ring-indigo-600/20',
        ],
        minimal: [
          'bg-gradient-to-br from-white/80 via-white/60 to-slate-50/40 dark:from-slate-900/80 dark:via-slate-800/60 dark:to-slate-900/40',
          'border border-slate-200/50 dark:border-slate-700/50',
          'rounded-2xl shadow-lg shadow-slate-500/8 dark:shadow-black/18',
          'ring-1 ring-slate-100/15 dark:ring-slate-600/15',
        ],
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  },
);

export const tagDropdownDividerVariants = cva('mx-2 my-1', {
  variants: {
    variant: {
      default: 'border-t border-slate-200/60 dark:border-slate-700/60',
      neon: 'border-t border-indigo-200/60 dark:border-indigo-700/60',
      minimal: 'border-t border-slate-200/50 dark:border-slate-700/50',
    },
  },
  defaultVariants: {
    variant: 'default',
  },
});

export const tagDropdownEmptyStateVariants = cva(
  'px-4 py-6 text-center transition-colors duration-200',
  {
    variants: {
      variant: {
        default: 'text-slate-500 dark:text-slate-400',
        neon: 'text-indigo-400 dark:text-indigo-300',
        minimal: 'text-slate-500 dark:text-slate-400',
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  },
);

export type TagDropdownContentVariants = VariantProps<
  typeof tagDropdownContentVariants
>;
export type TagDropdownDividerVariants = VariantProps<
  typeof tagDropdownDividerVariants
>;
export type TagDropdownEmptyStateVariants = VariantProps<
  typeof tagDropdownEmptyStateVariants
>;
