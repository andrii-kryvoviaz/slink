import { cva } from 'class-variance-authority';

export const SelectTheme = cva(
  `text-left select-none appearance-none cursor-pointer min-w-64 inline-flex items-center justify-between`,
  {
    variants: {
      variant: {
        default:
          'border bg-input-default text-input-default focus:outline-none focus:ring focus:ring-opacity-40 border-input-default hover:bg-input-hover-default focus:border-input-focus-default focus:ring-input-focus-default',
      },
      size: {
        xs: 'text-xs px-3 py-1.5',
        sm: 'text-xs px-3.5 py-2',
        md: 'text-sm px-5 py-2.5',
        lg: 'text-base px-6 py-3',
      },
      rounded: {
        none: 'rounded-none',
        sm: 'rounded',
        md: 'rounded-md',
        lg: 'rounded-lg',
        xl: 'rounded-xl',
        full: 'rounded-full',
      },
      fontWeight: {
        light: 'font-light',
        medium: 'font-medium',
        bold: 'font-bold',
      },
    },
  },
);
