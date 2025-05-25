import { cva } from 'class-variance-authority';

export const HeadingText = cva(`capitalize text-gray-800 dark:text-white`, {
  variants: {
    fontWeight: {
      light: 'font-light',
      normal: 'font-normal',
      semibold: 'font-semibold',
      bold: 'font-bold',
    },
    size: {
      xs: 'text-md lg:text-lg',
      sm: 'text-lg lg:text-xl',
      md: 'text-2xl lg:text-3xl mb-2',
      lg: 'text-4xl lg:text-5xl mb-2',
    },
  },
});

export const HeadingDecoration = cva(`rounded-full inline-block`, {
  variants: {
    variant: {
      default: 'bg-gray-200 dark:bg-gray-800',
      primary: 'bg-indigo-500',
    },
    size: {
      xs: 'h-0.5',
      sm: 'h-0.5',
      md: 'h-1',
      lg: 'h-1',
    },
  },
});

export const HeadingContainer = cva(`flex w-full flex-col items-center`, {
  variants: {
    alignment: {
      left: 'items-start',
      center: 'items-center',
      right: 'items-end',
    },
  },
});
