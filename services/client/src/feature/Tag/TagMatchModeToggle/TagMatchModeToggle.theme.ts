import { cva } from 'class-variance-authority';
import type { VariantProps } from 'class-variance-authority';

export const activeFilterToggleButtonVariants = cva(
  'inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-xs font-medium transition-all duration-200',
  {
    variants: {
      matchMode: {
        all: 'text-violet-600 dark:text-violet-400',
        any: 'text-blue-600 dark:text-blue-400',
      },
      disabled: {
        true: 'opacity-50 cursor-not-allowed',
        false: 'cursor-pointer hover:bg-blue-100 dark:hover:bg-blue-900/30',
      },
    },
    defaultVariants: {
      matchMode: 'any',
      disabled: false,
    },
  },
);

export const activeFilterTooltipIconWrapperVariants = cva(
  'shrink-0 mt-0.5 flex items-center justify-center w-7 h-7 rounded-lg',
  {
    variants: {
      matchMode: {
        all: 'bg-violet-100 dark:bg-violet-900/40',
        any: 'bg-blue-100 dark:bg-blue-900/40',
      },
    },
    defaultVariants: {
      matchMode: 'any',
    },
  },
);

export const activeFilterTooltipIconVariants = cva('w-4 h-4', {
  variants: {
    matchMode: {
      all: 'text-violet-600 dark:text-violet-400',
      any: 'text-blue-600 dark:text-blue-400',
    },
  },
  defaultVariants: {
    matchMode: 'any',
  },
});

export type ActiveFilterToggleButtonVariants = VariantProps<
  typeof activeFilterToggleButtonVariants
>;
