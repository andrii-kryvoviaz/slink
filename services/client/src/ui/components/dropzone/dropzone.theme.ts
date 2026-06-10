import { cva } from 'class-variance-authority';

export const dropzoneInputTheme = cva(
  'relative overflow-hidden flex flex-col justify-center items-center w-full bg-card-primary cursor-pointer hover:bg-card-secondary',
);

export const dropzoneOverlayTheme = cva('', {
  variants: {
    visible: {
      true: 'opacity-100',
      false: 'opacity-0',
    },
  },
  defaultVariants: {
    visible: false,
  },
});
