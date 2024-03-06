import { cva } from 'class-variance-authority';

export const LoaderTheme = cva(`rounded-full border-b-2 border-t-2 `, {
  variants: {
    variant: {
      default: 'border-loader-default',
    },
    size: {
      sm: 'h-5 w-5',
      md: 'h-7 w-7',
      lg: 'h-10 w-10',
      xl: 'h-12 w-12',
    },
    speed: {
      default: 'animate-spin',
      slow: 'animate-spin-slow',
    },
  },
});
