import { tv } from 'tailwind-variants';

export const viewModeSliderTheme = tv({
  slots: {
    root: 'inline-flex shrink-0 select-none',
    track:
      'relative isolate flex items-stretch bg-gradient-to-br from-muted/60 to-muted/70 border border-border p-0.5',
    thumb:
      'absolute top-0.5 bottom-0.5 left-0.5 z-0 bg-surface-raised shadow-sm ring-1 ring-border/90 motion-safe:transition-transform motion-safe:duration-300 motion-safe:ease-out pointer-events-none',
    step: 'group relative z-10 flex items-center justify-center bg-transparent border-0 cursor-pointer outline-none focus-visible:ring-2 focus-visible:ring-ring/60 disabled:cursor-not-allowed disabled:opacity-50 aria-checked:cursor-default',
    icon: 'shrink-0 motion-safe:transition-colors motion-safe:duration-200',
    dot: 'block rounded-full bg-ring/60 dark:bg-ring/70 motion-safe:transition-[transform,background-color] motion-safe:duration-200 group-hover:scale-125 group-hover:bg-foreground-muted dark:group-hover:bg-foreground-soft',
    label: 'whitespace-nowrap',
    disabled: 'pointer-events-none opacity-60',
  },
  variants: {
    size: {
      sm: {
        track: 'h-6',
        step: 'w-7',
        icon: 'w-3 h-3',
        dot: 'w-1 h-1',
      },
      md: {
        track: 'h-7',
        step: 'w-9',
        icon: 'w-3.5 h-3.5',
        dot: 'w-1.5 h-1.5',
      },
      lg: {
        track: 'h-8',
        step: 'w-10',
        icon: 'w-4 h-4',
        dot: 'w-1.5 h-1.5',
      },
      xl: {
        track: 'h-11',
        step: 'w-11',
        icon: 'w-4 h-4',
        dot: 'w-1.5 h-1.5',
        label: 'text-sm font-medium',
      },
    },
    rounded: {
      md: {
        track: 'rounded-md',
        thumb: 'rounded-sm',
        step: 'rounded-sm',
      },
      lg: {
        track: 'rounded-lg',
        thumb: 'rounded-md',
        step: 'rounded-md',
      },
      pill: {
        track: 'rounded-full',
        thumb: 'rounded-full',
        step: 'rounded-full',
      },
    },
    labelMode: {
      none: {
        icon: 'text-foreground-soft dark:text-foreground',
      },
      active: {
        step: 'w-auto px-3 gap-1.5 aria-checked:bg-surface-raised aria-checked:shadow-sm aria-checked:ring-1 aria-checked:ring-border/90',
        icon: 'text-foreground-muted group-aria-checked:text-foreground-soft dark:group-aria-checked:text-foreground',
        label: 'hidden sm:inline',
      },
    },
  },
  defaultVariants: {
    size: 'md',
    rounded: 'lg',
    labelMode: 'none',
  },
});
