import { cva } from 'class-variance-authority';
import type { VariantProps } from 'class-variance-authority';

export const activeFilterToggleButtonVariants = cva(
  'inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-xs font-medium transition-all duration-200',
  {
    variants: {
      matchMode: {
        all: 'text-decor-violet',
        any: 'text-info',
      },
      disabled: {
        true: 'opacity-50 cursor-not-allowed',
        false: 'cursor-pointer hover:bg-primary-solid/12',
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
        all: 'bg-decor-violet/12',
        any: 'bg-primary-solid/12',
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
      all: 'text-decor-violet',
      any: 'text-info',
    },
  },
  defaultVariants: {
    matchMode: 'any',
  },
});

export type ActiveFilterToggleButtonVariants = VariantProps<
  typeof activeFilterToggleButtonVariants
>;
