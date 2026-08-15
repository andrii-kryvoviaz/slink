import { tv } from 'tailwind-variants';

export const panel = tv({
  slots: {
    root: '',
    header: 'flex items-start justify-between gap-3',
    titleBlock: 'flex min-w-0 items-start gap-3',
    textBlock: 'flex min-w-0 flex-col',
    titleRow: 'font-semibold text-foreground leading-tight',
    description: 'text-xs text-foreground-muted leading-snug',
    optionsSlot: 'flex shrink-0 items-center',
    body: '',
    footer: 'flex items-center gap-3',
  },
  variants: {
    variant: {
      card: { header: 'mb-3', titleRow: 'text-base', footer: 'mt-4' },
      plain: { root: 'space-y-4', titleRow: 'text-sm' },
    },
  },
  defaultVariants: {
    variant: 'card',
  },
});
