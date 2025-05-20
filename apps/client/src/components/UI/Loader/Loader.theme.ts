import { cva } from 'class-variance-authority';

export const LoaderTheme = cva(`rounded-full`, {
  variants: {
    variant: {
      default:
        'border-loader-default border-2 border-r-transparent border-r-transparent',
      simple:
        'block w-full h-full border-2 border-transparent border-[inherit] border-t-transparent',
    },
    size: {
      xs: 'h-4 w-4 border',
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
