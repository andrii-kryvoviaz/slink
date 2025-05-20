import { cva } from 'class-variance-authority';

export const InputTheme = cva(
  `mt-2 block w-full border bg-input text-input focus:outline-hidden focus:ring-3`,
  {
    variants: {
      variant: {
        default:
          'border-bc-input hover:bg-input-hover focus:border-bc-input-focus focus:ring-rc-input-focus/40',
        error:
          'border-input-error focus:border-input-error focus:ring-input-error/40',
      },
      size: {
        sm: 'text-sm py-1 px-2',
        md: 'text-md py-2 px-4',
        lg: 'text-lg py-3 px-6',
      },
      rounded: {
        sm: 'rounded-xs',
        md: 'rounded-md',
        lg: 'rounded-lg',
      },
    },
  },
);
