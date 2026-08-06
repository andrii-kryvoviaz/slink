import { cva } from 'class-variance-authority';

export const KeyboardKeyTheme = cva(
  'inline-flex items-center justify-center font-mono border transition-colors duration-200 whitespace-nowrap',
  {
    variants: {
      variant: {
        default: 'border-border-strong bg-muted text-foreground',
        subtle: 'border-border bg-muted-subtle text-foreground-soft',
        modern:
          'border-border-strong bg-card text-foreground shadow-sm hover:shadow-md',
        glass: 'border-border/50 bg-card/80 text-foreground backdrop-blur-sm',
        minimal: 'border-border bg-transparent text-foreground-soft',
      },
      size: {
        xs: 'px-2 h-6 min-w-[1.5rem] text-xs',
        sm: 'px-2.5 h-7 min-w-[1.75rem] text-xs',
        md: 'px-3 h-8 min-w-[2rem] text-sm',
        lg: 'px-4 h-10 min-w-[2.5rem] text-base',
        xl: 'px-5 h-12 min-w-[3rem] text-lg',
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
        normal: 'font-normal',
        medium: 'font-medium',
        semibold: 'font-semibold',
        bold: 'font-bold',
      },
      shadow: {
        none: '',
        sm: 'shadow-sm',
        md: 'shadow-md',
        lg: 'shadow-lg',
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'md',
      rounded: 'md',
      fontWeight: 'medium',
      shadow: 'none',
    },
  },
);
