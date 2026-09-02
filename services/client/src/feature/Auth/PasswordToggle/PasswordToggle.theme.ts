import { cva } from 'class-variance-authority';

export const passwordToggleVariants = cva(
  'flex items-center transition-colors',
  {
    variants: {
      variant: {
        default: 'text-foreground-muted hover:text-foreground-soft',
      },
      inline: {
        true: '',
        false: 'pr-4',
      },
    },
    defaultVariants: {
      variant: 'default',
      inline: false,
    },
  },
);

export const passwordToggleIconVariants = cva('', {
  variants: {
    size: {
      sm: 'w-4 h-4',
      md: 'w-5 h-5',
      lg: 'w-6 h-6',
    },
  },
  defaultVariants: {
    size: 'md',
  },
});
