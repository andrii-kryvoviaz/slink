import { cva } from 'class-variance-authority';

export const LoaderTheme = cva(`rounded-full`, {
  variants: {
    variant: {
      default:
        'border-loader-default border-2 border-r-transparent border-r-transparent',
      simple:
        'block w-full h-full border-2 border-transparent border-[inherit] border-t-transparent',
      subtle:
        'border-2 border-gray-200 dark:border-gray-700 border-t-indigo-500 dark:border-t-indigo-400',
      minimal:
        'border border-gray-300/50 dark:border-gray-600/50 border-t-gray-600 dark:border-t-gray-300',
      modern:
        'border-2 border-gray-100 dark:border-gray-800 border-t-indigo-400 dark:border-t-indigo-500 shadow-sm',
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
