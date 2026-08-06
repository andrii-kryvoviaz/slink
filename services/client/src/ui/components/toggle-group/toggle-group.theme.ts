import { cva } from 'class-variance-authority';

export const toggleGroupTheme = cva(
  'inline-flex items-center bg-gradient-to-br from-muted-subtle to-muted/50 border border-border',
  {
    variants: {
      size: {
        sm: 'p-0.5',
        md: 'p-0.5',
        lg: 'p-1',
      },
      orientation: {
        horizontal: 'flex-row',
        vertical: 'flex-col',
      },
      rounded: {
        none: 'rounded-none',
        sm: 'rounded-sm',
        md: 'rounded-md',
        lg: 'rounded-lg',
        xl: 'rounded-xl',
        full: 'rounded-full',
      },
    },
    defaultVariants: {
      size: 'md',
      orientation: 'horizontal',
      rounded: 'lg',
    },
  },
);

export const toggleGroupInnerTheme = cva('flex overflow-hidden', {
  variants: {
    orientation: {
      horizontal: 'flex-row',
      vertical: 'flex-col',
    },
    rounded: {
      none: 'rounded-none',
      sm: 'rounded-sm',
      md: 'rounded-md',
      lg: 'rounded-lg',
      xl: 'rounded-xl',
      full: 'rounded-full',
    },
  },
  defaultVariants: {
    orientation: 'horizontal',
    rounded: 'md',
  },
});

export const toggleGroupItemTheme = cva(
  'flex items-center justify-center text-sm font-medium transition-colors duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50',
  {
    variants: {
      variant: {
        active: 'bg-muted text-foreground',
        inactive:
          'text-muted-foreground hover:text-foreground-soft hover:bg-ghost-hover',
      },
      size: {
        sm: 'px-2 py-0.5 text-xs',
        md: 'px-2.5 py-1 text-xs',
        lg: 'px-3 py-1.5 text-sm',
      },
      orientation: {
        horizontal: '',
        vertical: '',
      },
    },
    defaultVariants: {
      variant: 'inactive',
      size: 'md',
      orientation: 'horizontal',
    },
  },
);

export const toggleGroupIconTheme = cva('shrink-0', {
  variants: {
    size: {
      sm: 'w-3 h-3',
      md: 'w-3.5 h-3.5',
      lg: 'w-4 h-4',
    },
    hasLabel: {
      true: '',
      false: '',
    },
  },
  compoundVariants: [
    {
      size: 'sm',
      hasLabel: true,
      className: 'mr-1',
    },
    {
      size: 'md',
      hasLabel: true,
      className: 'mr-1',
    },
    {
      size: 'lg',
      hasLabel: true,
      className: 'mr-1.5',
    },
  ],
  defaultVariants: {
    size: 'md',
    hasLabel: false,
  },
});
