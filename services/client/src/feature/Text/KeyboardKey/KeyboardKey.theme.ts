import { cva } from 'class-variance-authority';

export const KeyboardKeyTheme = cva(
  'inline-flex items-center justify-center font-mono border transition-colors duration-200 whitespace-nowrap',
  {
    variants: {
      variant: {
        default:
          'border-gray-300 bg-gray-100 text-gray-900 dark:border-gray-600 dark:bg-gray-800 dark:text-white',
        subtle:
          'border-gray-200 bg-gray-50 text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300',
        modern:
          'border-gray-300 bg-white text-gray-900 shadow-sm hover:shadow-md dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:hover:bg-gray-750',
        glass:
          'border-gray-200/50 bg-white/80 text-gray-900 backdrop-blur-sm dark:border-gray-600/50 dark:bg-gray-800/80 dark:text-white',
        minimal:
          'border-gray-200 bg-transparent text-gray-700 dark:border-gray-700 dark:text-gray-300',
      },
      size: {
        xs: 'px-2 h-6 min-w-[1.5rem] text-xs',
        sm: 'px-2.5 h-7 min-w-[1.75rem] text-xs',
        md: 'px-3 h-8 min-w-[2rem] text-sm',
        lg: 'px-4 h-10 min-w-[2.5rem] text-base',
        xl: 'px-5 h-12 min-w-[3rem] text-lg',
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
        normal: 'font-normal',
        medium: 'font-medium',
        semibold: 'font-semibold',
        bold: 'font-bold',
      },
      shadow: {
        none: '',
        sm: 'shadow-sm',
        md: 'shadow-md',
        lg: 'shadow-lg',
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'md',
      rounded: 'md',
      fontWeight: 'medium',
      shadow: 'none',
    },
  },
);
