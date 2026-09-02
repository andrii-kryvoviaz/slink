import { cva } from 'class-variance-authority';

export const InputGroupShell = cva(
  'group relative flex w-full items-center overflow-hidden rounded-lg border transition-all duration-200 focus-within:ring-2 focus-within:ring-ring/20',
  {
    variants: {
      variant: {
        default:
          'border-border/50 bg-muted/50 hover:bg-muted/70 focus-within:border-border/50',
        success:
          'border-success-border/50 dark:border-success-border/15 bg-success-wash/80 dark:bg-success-wash/16 hover:bg-success-wash/50 dark:hover:bg-success-wash/10 focus-within:border-success-border/50 dark:focus-within:border-success-border/15 focus-within:ring-success/20',
      },
      size: {
        sm: 'max-w-xs text-xs',
        md: 'max-w-md text-sm',
        lg: 'max-w-lg text-base',
      },
      fluid: {
        true: 'w-full max-w-none',
        false: '',
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'md',
      fluid: false,
    },
  },
);

export const InputGroupField = cva(
  'w-full bg-transparent border-0 focus:outline-none focus:ring-0 placeholder-foreground-subtle dark:placeholder-foreground-muted',
  {
    variants: {
      variant: {
        default: 'text-foreground-soft',
        success: 'text-success-text',
      },
      size: {
        sm: 'px-3 py-2 text-xs',
        md: 'px-4 py-2.5 text-sm',
        lg: 'px-4 py-3 text-base',
      },
      mono: {
        true: 'font-mono',
        false: '',
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'md',
      mono: false,
    },
  },
);
