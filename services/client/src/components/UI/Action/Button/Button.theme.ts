import { cva } from 'class-variance-authority';

export const ButtonTheme = cva(
  `inline-flex items-center justify-center text-center select-none cursor-pointer focus:outline-hidden focus-visible:outline-none whitespace-nowrap`,
  {
    variants: {
      variant: {
        default:
          'border bg-none text-button-default dark:text-gray-200 border-bc-button-default dark:border-gray-700 hover:bg-button-default-hover dark:hover:bg-gray-800 hover:text-button-hover-default dark:hover:text-white',
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
        modern:
          'bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-full shadow-sm hover:shadow-md transition-all duration-200 font-medium text-gray-700 dark:text-gray-200 hover:text-gray-900 dark:hover:text-white',
        glass:
          'bg-white/80 dark:bg-gray-900/80 border border-gray-200/60 dark:border-gray-700/60 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-white dark:hover:bg-gray-800 hover:border-gray-300 dark:hover:border-gray-600 hover:shadow-lg hover:shadow-gray-200/40 dark:hover:shadow-gray-900/40 focus-visible:ring-blue-500/20 transition-all duration-200',
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
        subtle: 'hover:scale-105 active:scale-95',
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
