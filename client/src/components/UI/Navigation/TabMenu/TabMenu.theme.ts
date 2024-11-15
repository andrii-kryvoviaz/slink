import { cva } from 'class-variance-authority';

export const TabMenuTheme = cva(`relative justify-between flex gap-2`, {
  variants: {
    variant: {
      default: 'dark:bg-gray-800 bg-gray-200',
    },
    size: {
      xs: 'text-xs',
      sm: 'text-sm',
      md: 'text-base',
      lg: 'text-lg',
    },
    orientation: {
      horizontal: 'flex-row px-1',
      vertical: 'flex-col py-1',
    },
    rounded: {
      xs: 'rounded-xs',
      sm: 'rounded-sm',
      md: 'rounded-md',
      lg: 'rounded-lg',
    },
  },
});
