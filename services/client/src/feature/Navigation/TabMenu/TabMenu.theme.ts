import { cva } from 'class-variance-authority';

export const TabMenuTheme = cva(
  'relative flex gap-1 p-1 rounded-xl bg-muted/80 backdrop-blur-sm border border-border/50',
  {
    variants: {
      variant: {
        default: '',
        minimal: 'bg-transparent border-0 gap-0 p-0',
        pills: 'gap-2 bg-transparent border-0 p-0',
        underline:
          'bg-transparent border-0 border-b border-border rounded-none p-0 gap-0',
      },
      size: {
        xs: 'text-xs min-h-8',
        sm: 'text-sm min-h-9',
        md: 'text-base min-h-10',
        lg: 'text-lg min-h-12',
      },
      orientation: {
        horizontal: 'flex-row',
        vertical: 'flex-col',
      },
      rounded: {
        none: 'rounded-none',
        sm: 'rounded-lg',
        md: 'rounded-xl',
        lg: 'rounded-2xl',
        full: 'rounded-full',
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'sm',
      orientation: 'horizontal',
      rounded: 'md',
    },
  },
);

export const TabMenuItemTheme = cva(
  'relative z-10 flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 ease-out cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent/20 focus-visible:ring-offset-1 select-none',
  {
    variants: {
      variant: {
        default: '',
        minimal: 'rounded-none px-3',
        pills: 'rounded-full bg-muted hover:bg-hover-strong',
        underline: 'rounded-none border-b-2 border-transparent px-3 pb-3',
      },
      active: {
        true: 'text-foreground font-semibold',
        false:
          'text-foreground-muted hover:text-foreground hover:bg-surface-raised/30',
      },
    },
    compoundVariants: [
      {
        variant: 'underline',
        active: true,
        class: 'border-accent',
      },
      {
        variant: 'pills',
        active: true,
        class: 'bg-accent/12 text-accent-text',
      },
    ],
    defaultVariants: {
      variant: 'default',
      active: false,
    },
  },
);
