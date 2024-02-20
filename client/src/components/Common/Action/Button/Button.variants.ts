import { cva } from 'class-variance-authority';

export const ButtonVariants = cva(
  `inline-flex items-center justify-center text-center select-none cursor-pointer focus:outline-none focus-visable:outline-none whitespace-nowrap`,
  {
    variants: {
      variant: {
        default:
          'border bg-none text-button-default border-button-default hover:bg-button-hover-default hover:text-button-hover-default',
        primary:
          'bg-button-accent text-button-accent bg-opacity-80 hover:bg-opacity-100',
        secondary:
          'border bg-none text-button-default border-button-default hover:bg-button-accent hover:text-button-accent hover:border-button-accent',
        dark: 'bg-button-dark text-button-accent bg-opacity-80 hover:bg-opacity-100',
        invisible:
          'rounded bg-none text-button-invisible hover:bg-button-accent hover:text-button-accent',
        link: 'bg-transparent dark:bg-transparent underline-offset-4 hover:underline text-button-default hover:bg-transparent',
        success:
          'bg-button-success text-button-accent bg-opacity-80 hover:bg-opacity-100',
        danger:
          'bg-button-danger text-button-accent bg-opacity-80 hover:bg-opacity-100',
        warning:
          'bg-button-warning text-button-accent bg-opacity-80 hover:bg-opacity-100',
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
      motion: {
        none: 'transition-none',
        'hover:opacity': 'transition-opacity duration-300 ease-in-out opacity-75 hover:opacity-100',
        'hover:scale': 'transition-transform duration-300 ease-in-out transform hover:scale-110',
        'hover:translate': 'transition-transform duration-300 ease-in-out transform hover:translate-y-1',
        'hover:rotate': 'transition-transform duration-300 ease-in-out transform hover:rotate-180',
      }
    },
  }
);