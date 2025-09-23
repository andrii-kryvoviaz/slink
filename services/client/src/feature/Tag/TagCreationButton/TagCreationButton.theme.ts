import { cva } from 'class-variance-authority';
import type { VariantProps } from 'class-variance-authority';

// Legacy theme - kept for backwards compatibility
// New implementations should use the button variants in the main Button component

export const tagCreationButtonVariants = cva(
  'w-full flex items-center justify-start gap-2 px-3 py-2 text-sm transition-all duration-200 rounded-md mx-1 my-0.5',
  {
    variants: {
      variant: {
        default: [
          'text-slate-500 dark:text-slate-400',
          'hover:text-slate-700 dark:hover:text-slate-200',
          'hover:bg-slate-50/50 dark:hover:bg-slate-800/30',
          'focus:text-slate-900 dark:focus:text-slate-100',
          'focus:bg-slate-100/50 dark:focus:bg-slate-800/50',
          'focus:outline-none',
        ],
        neon: [
          'text-indigo-400 dark:text-indigo-500',
          'hover:text-indigo-600 dark:hover:text-indigo-300',
          'hover:bg-indigo-50/30 dark:hover:bg-indigo-900/20',
          'focus:text-indigo-700 dark:focus:text-indigo-200',
          'focus:bg-indigo-100/40 dark:focus:bg-indigo-900/30',
          'focus:outline-none',
        ],
        minimal: [
          'text-slate-500 dark:text-slate-400',
          'hover:text-slate-700 dark:hover:text-slate-200',
          'hover:bg-slate-50/30 dark:hover:bg-slate-800/20',
          'focus:text-slate-900 dark:focus:text-slate-100',
          'focus:bg-slate-100/40 dark:focus:bg-slate-800/40',
          'focus:outline-none',
        ],
      },
      creating: {
        true: 'opacity-60 cursor-not-allowed pointer-events-none',
        false: '',
      },
    },
    defaultVariants: {
      variant: 'default',
      creating: false,
    },
  },
);

export const tagCreationIconVariants = cva('h-4 w-4 flex-shrink-0', {
  variants: {
    variant: {
      default: 'text-slate-400 dark:text-slate-500',
      neon: 'text-indigo-400 dark:text-indigo-500',
      minimal: 'text-slate-400 dark:text-slate-500',
    },
    creating: {
      true: 'animate-spin',
      false: '',
    },
  },
  defaultVariants: {
    variant: 'default',
    creating: false,
  },
});

export type TagCreationButtonVariants = VariantProps<
  typeof tagCreationButtonVariants
>;
export type TagCreationIconVariants = VariantProps<
  typeof tagCreationIconVariants
>;
