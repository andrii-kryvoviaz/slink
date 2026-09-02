import { cva } from 'class-variance-authority';

export const ModeSwitchTheme = cva(
  'group relative inline-flex items-center justify-center cursor-pointer rounded-full focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed',
  {
    variants: {
      variant: {
        default:
          'bg-card/80 border border-border/60 text-foreground-muted hover:text-foreground hover:bg-hover hover:border-border-strong hover:shadow-lg hover:shadow-border/40 dark:hover:shadow-card/40 focus-visible:ring-ring/20',
        minimal:
          'bg-transparent hover:bg-hover/60 text-foreground-muted hover:text-foreground-soft focus-visible:ring-ring/20',
        glass:
          'bg-overlay/10 backdrop-blur-md border border-on-surface-inverse/20 dark:border-on-surface-inverse/10 text-foreground-soft hover:bg-overlay/20 hover:border-on-surface-inverse/30 dark:hover:border-on-surface-inverse/20 focus-visible:ring-on-surface-inverse/30',
        floating:
          'bg-card border border-border text-foreground-muted hover:text-foreground shadow-sm hover:shadow-xl hover:shadow-border/30 dark:hover:shadow-card/30 hover:-translate-y-0.5 focus-visible:ring-ring/20',
        pill: 'bg-muted text-foreground-muted hover:bg-hover hover:text-foreground focus-visible:ring-ring/20',
      },
      size: {
        sm: 'h-8 w-8',
        md: 'h-9 w-9',
        lg: 'h-10 w-10',
        xl: 'h-12 w-12',
      },
      animation: {
        none: '',
        subtle: 'hover:scale-105 active:scale-95',
        bounce: 'hover:rotate-12',
        smooth: 'hover:scale-[1.02] active:scale-[0.98]',
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'md',
      animation: 'subtle',
    },
  },
);

export const ModeSwitchIcon = cva('transition-all duration-300 ease-out', {
  variants: {
    variant: {
      default: 'h-4 w-4',
      minimal: 'h-4 w-4',
      glass: 'h-4 w-4',
      floating: 'h-4 w-4',
      pill: 'h-4 w-4',
    },
    size: {
      sm: 'h-3 w-3',
      md: 'h-4 w-4',
      lg: 'h-5 w-5',
      xl: 'h-6 w-6',
    },
    animation: {
      none: '',
      subtle: 'group-hover:scale-110',
      bounce: 'group-hover:scale-110 group-active:scale-90',
      smooth: 'group-hover:scale-[1.02]',
      rotate: 'group-hover:rotate-180',
      scale: 'group-hover:scale-110',
      pulse: 'group-hover:animate-pulse',
      spin: 'group-hover:animate-spin-slow',
    },
  },
  defaultVariants: {
    variant: 'default',
    size: 'md',
    animation: 'rotate',
  },
});

export const ModeSwitchContainer = cva('relative inline-flex items-center', {
  variants: {
    tooltip: {
      true: 'group/tooltip',
      false: '',
    },
  },
  defaultVariants: {
    tooltip: false,
  },
});

export const ModeSwitchTooltip = cva(
  'absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs font-medium text-foreground bg-muted rounded-md opacity-0 group-hover/tooltip:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-50 shadow-lg',
);
