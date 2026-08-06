import { cva } from 'class-variance-authority';

export const tooltipVariants = cva(
  'z-50 overflow-hidden origin-[--bits-tooltip-content-transform-origin] animate-in fade-in-0 zoom-in-95 data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=closed]:zoom-out-95 data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2',
  {
    variants: {
      variant: {
        default:
          'bg-muted-subtle text-foreground border border-border shadow-sm backdrop-blur-sm',
        subtle:
          'bg-muted/95 text-foreground border border-border/50 shadow-sm backdrop-blur-sm',
        glass:
          'bg-card/80 text-foreground border border-border/30 shadow-lg backdrop-blur-md backdrop-saturate-150',
        contrast:
          'bg-foreground text-background border-0 shadow-lg font-medium',
        floating:
          'bg-card text-foreground border border-border shadow-lg ring-1 ring-ghost-hover',
        minimal:
          'bg-muted-subtle/90 text-muted-foreground border-0 shadow-sm backdrop-blur-sm',
        success:
          'bg-success-subtle text-success-deep-foreground border border-success-border shadow-sm',
        destructive:
          'bg-danger-subtle text-danger-deep-foreground border border-danger-border shadow-sm',
        info: 'bg-info-subtle text-info-deep-foreground border border-info-border shadow-sm',
        warning:
          'bg-warning-subtle text-warning-deep-foreground border border-warning-border shadow-sm',
        primary: 'bg-accent text-accent-foreground border-0 shadow-md',
        secondary: 'bg-muted text-foreground border border-border shadow-sm',
        dark: 'bg-surface-inverse text-surface-inverse-foreground/90 border border-surface-inverse-foreground/10 shadow-lg backdrop-blur-sm',
      },
      size: {
        xs: 'text-xs px-2 py-1 max-w-48',
        sm: 'text-xs px-2.5 py-1.5 max-w-56',
        md: 'text-sm px-3 py-2 max-w-64',
        lg: 'text-sm px-4 py-2.5 max-w-72',
        xl: 'text-base px-5 py-3 max-w-80',
      },
      rounded: {
        none: 'rounded-none',
        sm: 'rounded-sm',
        md: 'rounded-md',
        lg: 'rounded-lg',
        xl: 'rounded-xl',
        full: 'rounded-full',
      },
      width: {
        auto: 'w-auto',
        fit: 'w-fit',
        xs: 'w-24',
        sm: 'w-32',
        md: 'w-40',
        lg: 'w-48',
        xl: 'w-56',
        '2xl': 'w-64',
        '3xl': 'w-80',
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'sm',
      rounded: 'md',
      width: 'auto',
    },
  },
);

export const tooltipArrowVariants = cva('z-50 size-2.5', {
  variants: {
    variant: {
      default: 'bg-muted-subtle border-l border-t border-border',
      subtle: 'bg-muted border-l border-t border-border/50',
      glass: 'bg-card/80 border-l border-t border-border/30',
      contrast: 'bg-foreground border-0',
      floating: 'bg-card border-l border-t border-border',
      minimal: 'bg-muted-subtle/90 border-0',
      success: 'bg-success-subtle border-l border-t border-success-border',
      destructive: 'bg-danger-subtle border-l border-t border-danger-border',
      info: 'bg-info-subtle border-l border-t border-info-border',
      warning: 'bg-warning-subtle border-l border-t border-warning-border',
      primary: 'bg-accent border-0',
      secondary: 'bg-muted border-l border-t border-border',
      dark: 'bg-surface-inverse border-l border-t border-surface-inverse-foreground/10',
    },
    rounded: {
      none: 'rounded-none',
      sm: 'rounded-[1px]',
      md: 'rounded-[2px]',
      lg: 'rounded-[3px]',
      xl: 'rounded-[4px]',
      full: 'rounded-full',
    },
  },
  defaultVariants: {
    variant: 'default',
    rounded: 'sm',
  },
});

export type TooltipVariant = NonNullable<
  Parameters<typeof tooltipVariants>[0]
>['variant'];
export type TooltipSize = NonNullable<
  Parameters<typeof tooltipVariants>[0]
>['size'];
export type TooltipRounded = NonNullable<
  Parameters<typeof tooltipVariants>[0]
>['rounded'];
export type TooltipWidth = NonNullable<
  Parameters<typeof tooltipVariants>[0]
>['width'];
