import { cva } from 'class-variance-authority';

export const ButtonTheme = cva(
  `inline-flex items-center justify-center text-center select-none cursor-pointer focus:outline-hidden focus-visible:outline-none whitespace-nowrap`,
  {
    variants: {
      variant: {
        default:
          'border bg-none text-button-default border-bc-button-default hover:bg-button-default-hover hover:text-button-hover-default',
        primary:
          'bg-button-accent/80 text-button-accent hover:bg-button-accent/100',
        secondary:
          'border bg-none text-button-default border-bc-button-default hover:bg-button-accent hover:text-button-accent hover:border-button-accent',
        dark: 'bg-button-dark/80 text-button-accent hover:bg-button-dark/100',
        invisible:
          'bg-none text-button-invisible hover:bg-button-invisible-hover/25 hover:text-button-primary',
        outline:
          'border bg-none text-button-default border-bc-button-default hover:bg-button-default-hover hover:text-button-hover-default',
        form: 'border bg-input-default text-input-default focus:outline-hidden focus:ring-3 border-bc-input-default hover:bg-input-default-hover focus:border-bc-input-default-focus focus:ring-rc-input-default-focus',
        link: 'bg-transparent dark:bg-transparent underline-offset-4 hover:underline text-button-default hover:bg-transparent',
        success:
          'bg-success/80 text-button-accent hover:bg-success focus:ring-3 focus:ring-rc-success/40',
        danger:
          'bg-danger/80 text-button-accent hover:bg-danger focus:ring-3 focus:ring-rc-danger/40',
        warning:
          'bg-warning/80 text-button-accent hover:bg-warning focus:ring-3 focus:ring-rc-warning/40',
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
      motion: {
        none: 'transition-none',
        'hover:opacity':
          'transition-opacity duration-300 ease-in-out opacity-75 hover:opacity-100',
        'hover:scale':
          'transition-transform duration-300 ease-in-out transform hover:scale-110',
        'hover:translate':
          'transition-transform duration-300 ease-in-out transform hover:translate-y-1',
        'hover:rotate':
          'transition-transform duration-300 ease-in-out transform hover:rotate-180',
      },
      status: {
        active: 'active',
        disabled: 'cursor-not-allowed pointer-events-none opacity-70',
      },
    },
  },
);
