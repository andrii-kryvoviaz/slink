import { cva } from 'class-variance-authority';
import type { VariantProps } from 'class-variance-authority';

export const tagDropdownContentVariants = cva(
  'z-50 w-[var(--bits-popover-anchor-width)] data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[side=bottom]:slide-in-from-top-2',
  {
    variants: {
      variant: {
        default: [
          'bg-white dark:bg-gray-900/95',
          'backdrop-blur-md',
          'border border-gray-200/80 dark:border-white/10',
          'rounded-lg',
          'shadow-xl shadow-gray-200/50 dark:shadow-black/60',
        ],
        neon: [
          'bg-white dark:bg-gray-900/95',
          'backdrop-blur-md',
          'border border-gray-200/60 dark:border-white/10',
          'rounded-lg',
          'shadow-2xl shadow-gray-500/5 dark:shadow-black/50',
          'ring-1 ring-gray-100/20 dark:ring-white/5',
        ],
        minimal: [
          'bg-white dark:bg-gray-900/95',
          'backdrop-blur-md',
          'border border-gray-200/60 dark:border-white/10',
          'rounded-lg',
          'shadow-lg shadow-gray-200/40 dark:shadow-black/50',
        ],
        subtle: [
          'bg-white dark:bg-gray-900/95',
          'backdrop-blur-md',
          'border border-gray-200/60 dark:border-white/10',
          'rounded-lg',
          'shadow-xl shadow-gray-200/50 dark:shadow-black/60',
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
      default: 'border-t border-gray-200/30 dark:border-white/10',
      neon: 'border-t border-gray-200/20 dark:border-white/10',
      minimal: 'border-t border-gray-200/25 dark:border-white/10',
      subtle: 'border-t border-gray-100 dark:border-white/10',
    },
  },
  defaultVariants: {
    variant: 'default',
  },
});

export const tagDropdownEmptyStateVariants = cva('px-4 py-6 text-center', {
  variants: {
    variant: {
      default:
        'text-gray-500 dark:text-white/50 transition-colors duration-200',
      neon: 'text-blue-600 dark:text-blue-400 transition-colors duration-200',
      minimal:
        'text-gray-500 dark:text-white/50 transition-colors duration-200',
      subtle: 'text-gray-500 dark:text-white/50',
    },
  },
  defaultVariants: {
    variant: 'default',
  },
});

export type TagDropdownContentVariants = VariantProps<
  typeof tagDropdownContentVariants
>;
export type TagDropdownDividerVariants = VariantProps<
  typeof tagDropdownDividerVariants
>;
export type TagDropdownEmptyStateVariants = VariantProps<
  typeof tagDropdownEmptyStateVariants
>;
