import { cva } from 'class-variance-authority';

export const InputVariants = cva(
  `mt-2 block w-full border bg-input-default text-input-default focus:outline-none focus:ring focus:ring-opacity-40`,
  {
    variants: {
      variant: {
        default:
          'border-input-default hover:bg-input-hover-default focus:border-input-focus-default focus:ring-input-focus-default',
        error:
          'border-input-error focus:border-input-error focus:ring-input-focus-error',
      },
      size: {
        sm: 'text-sm py-1 px-2',
        md: 'text-md py-2 px-4',
        lg: 'text-lg py-3 px-6',
      },
      rounded: {
        sm: 'rounded-sm',
        md: 'rounded-md',
        lg: 'rounded-lg',
      },
    },
  }
);
