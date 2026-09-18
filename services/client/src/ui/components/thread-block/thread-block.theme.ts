import { type VariantProps, tv } from 'tailwind-variants';

export const threadBlock = tv({
  slots: {
    root: 'flex flex-col gap-2.5 rounded-lg bg-muted-soft px-3 py-2',
    row: 'min-w-0 text-[13px] text-foreground-soft',
    content:
      'flex flex-col gap-2.5 overflow-hidden data-[state=closed]:animate-collapsible-up data-[state=open]:animate-collapsible-down motion-reduce:animate-none',
    trigger:
      'group relative inline-flex w-fit items-center gap-1 self-start rounded-sm text-xs font-medium text-accent-text outline-none before:absolute before:inset-x-0 before:-inset-y-2 before:content-[""] focus-visible:ring-2 focus-visible:ring-ring/50',
    chevron:
      'size-3.5 transition-transform group-data-[state=open]:rotate-180 motion-reduce:transition-none',
  },
});

export type ThreadBlockVariants = VariantProps<typeof threadBlock>;
