import { cva } from 'class-variance-authority';

export const SubtitleTheme = cva('mt-1 text-muted-foreground', {
  variants: {
    size: {
      sm: 'text-xs',
      md: 'text-sm',
      lg: 'text-lg',
    },
  },
  defaultVariants: {
    size: 'md',
  },
});
