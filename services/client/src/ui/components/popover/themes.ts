import { cva } from 'class-variance-authority';

export const PopoverContentTheme = cva(
  'z-50 overflow-hidden border shadow-lg outline-none',
  {
    variants: {
      variant: {
        default: 'bg-card border-border text-foreground',
        glass: 'bg-card/95 backdrop-blur-sm border-border/60 text-foreground',
        floating:
          'bg-card border border-border shadow-xl hover:shadow-2xl text-foreground',
        minimal: 'bg-card border-0 shadow-sm text-foreground',
        modern:
          'bg-card/90 backdrop-blur-xl border border-border/50 shadow-xl shadow-scrim/5 dark:shadow-scrim/20 text-foreground',
      },
      size: {
        none: '',
        xs: 'p-2 max-w-xs',
        sm: 'p-3 max-w-sm',
        md: 'p-4 max-w-md',
        lg: 'p-5 max-w-lg',
        xl: 'p-6 max-w-xl',
        auto: 'p-4',
      },
      rounded: {
        none: 'rounded-none',
        sm: 'rounded-sm',
        md: 'rounded-md',
        lg: 'rounded-lg',
        xl: 'rounded-xl',
        '2xl': 'rounded-2xl',
        full: 'rounded-full',
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'md',
      rounded: 'lg',
    },
  },
);

export const PopoverArrowTheme = cva('fill-current', {
  variants: {
    variant: {
      default: 'text-card',
      glass: 'text-card/95',
      floating: 'text-card',
      minimal: 'text-card',
      modern: 'text-card/90',
    },
  },
  defaultVariants: {
    variant: 'default',
  },
});

export const PopoverTriggerTheme = cva(
  'inline-flex items-center justify-center cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-ring/20 transition-all duration-200',
  {
    variants: {
      variant: {
        default: '',
        button:
          'bg-card border border-border rounded-lg px-3 py-2 text-sm font-medium text-foreground-soft hover:bg-hover hover:text-foreground',
        ghost:
          'text-foreground-muted hover:text-foreground-soft hover:bg-hover/60 rounded-md p-1',
        minimal: 'hover:opacity-75',
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  },
);
