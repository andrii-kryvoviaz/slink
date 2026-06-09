import {
  InputGroupField,
  InputGroupShell,
} from '@slink/ui/components/input-group';
import { cva } from 'class-variance-authority';

export const CopyContainerTheme = InputGroupShell;
export const CopyContainerInputTheme = InputGroupField;

export const CopyContainerButtonTheme = cva('transition-all duration-200', {
  variants: {
    size: {
      sm: 'text-xs min-w-[4rem]',
      md: 'text-sm min-w-[5rem]',
      lg: 'text-base min-w-[6rem]',
    },
  },
  defaultVariants: {
    size: 'md',
  },
});
