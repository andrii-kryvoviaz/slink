import { cva } from 'class-variance-authority';

export const LoaderTheme = cva(`rounded-full`, {
  variants: {
    variant: {
      default:
        'border-accent border-2 border-r-transparent border-r-transparent',
      simple:
        'block w-full h-full border-2 border-transparent border-[inherit] border-t-transparent',
      subtle: 'border-2 border-border border-t-accent',
      minimal: 'border border-border-strong/50 border-t-foreground-soft',
      modern: 'border-2 border-muted border-t-accent shadow-sm',
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
      smooth: 'animate-spin duration-1000 ease-in-out',
    },
  },
});
