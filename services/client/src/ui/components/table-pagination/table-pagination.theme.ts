import { tv } from 'tailwind-variants';

export const tablePaginationContainerTheme = tv({
  base: 'flex items-center gap-1 rounded-lg border',
  variants: {
    variant: {
      default: 'bg-card/80 border-border/60',
      neutral: 'bg-card/50 dark:bg-card/20 border-border/70',
    },
  },
  defaultVariants: {
    variant: 'default',
  },
});

export const tablePaginationPageInputTheme = tv({
  base: 'h-7 w-8 text-xs font-medium text-center rounded-md border outline-none transition-all duration-200 focus:outline-none hover:border-border-strong',
  variants: {
    variant: {
      default: 'bg-muted/80',
      neutral: 'bg-muted/70 dark:bg-muted/50',
    },
    status: {
      default:
        'border-border/60 text-foreground placeholder:text-foreground-subtle',
      error: 'border-danger text-danger placeholder:text-danger/70',
    },
  },
  compoundVariants: [
    {
      variant: 'neutral',
      status: 'default',
      class: 'border-border/70',
    },
  ],
  defaultVariants: {
    variant: 'default',
    status: 'default',
  },
});

export const tablePaginationPageButtonTheme = tv({
  base: 'text-xs sm:text-sm font-medium transition-all duration-200 w-8 h-7 flex items-center justify-center rounded-md tabular-nums',
  variants: {
    variant: {
      default: '',
      neutral: '',
    },
    status: {
      interactive:
        'cursor-pointer hover:bg-muted focus:outline-none focus:ring-2 focus:ring-ring/50 active:scale-95 transform',
      static: 'cursor-default text-foreground-subtle',
    },
  },
  compoundVariants: [
    {
      variant: 'neutral',
      status: 'interactive',
      class: 'hover:bg-muted-soft/80 focus:ring-ring/40',
    },
    {
      variant: 'neutral',
      status: 'static',
      class: 'text-foreground-muted',
    },
  ],
  defaultVariants: {
    variant: 'default',
    status: 'static',
  },
});

export type TablePaginationVariant = 'default' | 'neutral';
