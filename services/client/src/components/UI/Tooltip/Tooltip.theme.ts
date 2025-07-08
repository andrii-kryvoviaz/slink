import { cva } from 'class-variance-authority';

export const TooltipContent = cva(`border z-9999 text-center`, {
  variants: {
    variant: {
      default:
        'bg-white border-gray-200 shadow-lg text-gray-900 dark:bg-gray-700 dark:text-white dark:border-gray-600/40',
    },
    rounded: {
      sm: 'rounded-xs',
      md: 'rounded-md',
      lg: 'rounded-lg',
      xl: 'rounded-xl',
      full: 'rounded-full',
    },
    size: {
      xs: 'text-xs py-1 px-2',
      sm: 'text-sm py-2 px-3',
      md: 'text-base py-3 px-4',
      lg: 'text-lg py-4 px-5',
      xl: 'text-xl py-5 px-6',
    },
    width: {
      xs: 'w-24',
      sm: 'w-32',
      md: 'w-40',
      lg: 'w-48',
      xl: 'w-56',
      auto: 'w-auto',
    },
  },
});

export const TooltipArrow = cva(`z-9999`, {
  variants: {
    variant: {
      default: 'text-white dark:text-gray-700',
    },
  },
});
