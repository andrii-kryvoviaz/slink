import { tv } from 'tailwind-variants';

export const shareAttributeChips = tv({
  slots: {
    root: '@container/attrs flex flex-nowrap items-center gap-2 overflow-hidden',
    chip: 'inline-flex shrink-0 items-center justify-center gap-1 rounded-md h-6 w-6',
    icon: 'h-3.5 w-3.5 shrink-0',
    text: 'hidden text-xs whitespace-nowrap',
    label: 'text-xs text-foreground-muted',
    value: 'text-sm text-foreground',
    empty: 'text-xs text-foreground-subtle',
  },
  variants: {
    tone: {
      muted: { chip: 'bg-muted/30 text-foreground-muted' },
      accent: { chip: 'bg-accent-solid/10 text-accent-text' },
    },
    mono: {
      true: { value: 'font-mono', text: 'font-mono' },
    },
    reveal: {
      dimensions: {
        chip: '@min-[200px]/attrs:w-auto @min-[200px]/attrs:px-1.5',
        text: '@min-[200px]/attrs:inline',
      },
      format: {
        chip: '@min-[240px]/attrs:w-auto @min-[240px]/attrs:px-1.5',
        text: '@min-[240px]/attrs:inline',
      },
      filter: {
        chip: '@min-[300px]/attrs:w-auto @min-[300px]/attrs:px-1.5',
        text: '@min-[300px]/attrs:inline',
      },
      protection: {
        chip: '@min-[364px]/attrs:w-auto @min-[364px]/attrs:px-1.5',
        text: '@min-[364px]/attrs:inline',
      },
    },
  },
  defaultVariants: {
    tone: 'muted',
  },
});
