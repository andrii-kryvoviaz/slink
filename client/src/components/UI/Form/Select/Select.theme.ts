import { cva } from 'class-variance-authority';

export const SelectTheme = cva(
  `text-left select-none appearance-none cursor-pointer min-w-64 inline-flex items-center justify-between gap-2`,
  {
    variants: {
      variant: {
        default:
          'border bg-input text-input focus:outline-hidden focus:ring-3 border-bc-input hover:bg-input-hover focus:border-bc-input-focus focus:ring-rc-input-focus/40',
        invisible:
          'bg-none text-button-invisible hover:bg-button-invisible-hover/25 hover:text-button-primary',
      },
      size: {
        xs: 'text-xs px-3 py-1.5',
        sm: 'text-xs px-3.5 py-2',
        md: 'text-sm px-5 py-2.5',
        lg: 'text-base px-6 py-3',
      },
      rounded: {
        none: 'rounded-none',
        sm: 'rounded-sm',
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
