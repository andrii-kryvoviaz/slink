import { cva } from 'class-variance-authority';

export const KeyboardKeyTheme = cva(`kbd min-w-9`, {
  variants: {
    variant: {
      default: '',
    },
    size: {
      xs: 'kbd-xs',
      sm: 'kbd-sm py-1',
      md: 'kbd-md py-2',
      lg: 'kbd-lg py-4',
      xl: 'kbd-xl py-4',
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
});
