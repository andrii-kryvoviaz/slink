import { cva } from 'class-variance-authority';

export const visibilityTheme = cva('transition-all duration-200', {
  variants: {
    visible: {
      true: 'opacity-100 visible',
      false: 'opacity-0 invisible',
    },
  },
  defaultVariants: {
    visible: false,
  },
});
