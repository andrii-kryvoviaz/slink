import { type VariantProps, tv } from 'tailwind-variants';

export const timeline = tv({
  slots: {
    root: 'relative flex flex-col gap-7',
    rail: 'absolute inset-y-0 left-[3px] w-px bg-border',
    group: 'flex flex-col',
    label: 'text-xs text-foreground-muted pl-[19px] pb-2',
    entry:
      'relative grid grid-cols-[7px_minmax(0,1fr)] gap-x-3 py-2 items-start',
    dotCell: 'flex h-10 items-center justify-center self-start',
    dot: 'size-[7px] rounded-full border',
    content: 'min-w-0',
  },
  variants: {
    active: {
      true: {
        dot: 'bg-accent border-accent',
      },
      false: {
        dot: 'bg-background border-border-strong',
      },
    },
  },
  defaultVariants: {
    active: false,
  },
});

export type TimelineVariants = VariantProps<typeof timeline>;
