import { cva } from 'class-variance-authority';

export const tooltipVariants = cva(
  'z-50 overflow-hidden origin-[--bits-tooltip-content-transform-origin] animate-in fade-in-0 zoom-in-95 data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=closed]:zoom-out-95 data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2',
  {
    variants: {
      variant: {
        default:
          'bg-slate-100 text-slate-900 border border-slate-300 shadow-sm backdrop-blur-sm dark:bg-slate-900 dark:text-slate-100 dark:border-slate-700',
        subtle:
          'bg-slate-100/95 text-slate-900 border border-slate-200/50 shadow-sm backdrop-blur-sm dark:bg-slate-800/95 dark:text-slate-100 dark:border-slate-600/50',
        glass:
          'bg-white/80 text-slate-900 border border-slate-200/30 shadow-lg backdrop-blur-md backdrop-saturate-150 dark:bg-slate-900/80 dark:text-slate-100 dark:border-slate-700/30',
        contrast:
          'bg-slate-900 text-white border-0 shadow-lg font-medium dark:bg-white dark:text-slate-900',
        floating:
          'bg-white text-slate-900 border border-slate-200 shadow-lg ring-1 ring-slate-900/5 dark:bg-slate-800 dark:text-slate-100 dark:border-slate-700 dark:ring-white/10',
        minimal:
          'bg-slate-50/90 text-slate-600 border-0 shadow-sm backdrop-blur-sm dark:bg-slate-800/90 dark:text-slate-400',
        success:
          'bg-green-50 text-green-900 border border-green-200 shadow-sm dark:bg-green-950 dark:text-green-100 dark:border-green-800',
        destructive:
          'bg-red-50 text-red-900 border border-red-200 shadow-sm dark:bg-red-950 dark:text-red-100 dark:border-red-800',
        info: 'bg-blue-50 text-blue-900 border border-blue-200 shadow-sm dark:bg-blue-950 dark:text-blue-100 dark:border-blue-800',
        warning:
          'bg-yellow-50 text-yellow-900 border border-yellow-200 shadow-sm dark:bg-yellow-950 dark:text-yellow-100 dark:border-yellow-800',
        primary:
          'bg-violet-600 text-white border-0 shadow-md dark:bg-violet-500',
        secondary:
          'bg-slate-100 text-slate-900 border border-slate-200 shadow-sm dark:bg-slate-800 dark:text-slate-100 dark:border-slate-700',
        dark: 'bg-neutral-900 text-white/90 border border-white/10 shadow-lg backdrop-blur-sm',
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
      default:
        'bg-slate-100 border-l border-t border-slate-300 dark:bg-slate-900 dark:border-slate-700',
      subtle:
        'bg-slate-100 border-l border-t border-slate-200/50 dark:bg-slate-800 dark:border-slate-600/50',
      glass:
        'bg-white/80 border-l border-t border-slate-200/30 dark:bg-slate-900/80 dark:border-slate-700/30',
      contrast: 'bg-slate-900 border-0 dark:bg-white',
      floating:
        'bg-white border-l border-t border-slate-200 dark:bg-slate-800 dark:border-slate-700',
      minimal: 'bg-slate-50/90 border-0 dark:bg-slate-800/90',
      success:
        'bg-green-50 border-l border-t border-green-200 dark:bg-green-950 dark:border-green-800',
      destructive:
        'bg-red-50 border-l border-t border-red-200 dark:bg-red-950 dark:border-red-800',
      info: 'bg-blue-50 border-l border-t border-blue-200 dark:bg-blue-950 dark:border-blue-800',
      warning:
        'bg-yellow-50 border-l border-t border-yellow-200 dark:bg-yellow-950 dark:border-yellow-800',
      primary: 'bg-violet-600 border-0 dark:bg-violet-500',
      secondary:
        'bg-slate-100 border-l border-t border-slate-200 dark:bg-slate-800 dark:border-slate-700',
      dark: 'bg-neutral-900 border-l border-t border-white/10',
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
