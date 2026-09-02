import { tv } from 'tailwind-variants';

export const shareAttributeChips = tv({
  slots: {
    root: 'flex flex-wrap items-center gap-2',
    chip: 'inline-flex items-center justify-center rounded-md h-6 w-6',
    icon: 'h-3.5 w-3.5',
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
      true: { value: 'font-mono' },
    },
  },
  defaultVariants: {
    tone: 'muted',
  },
});
