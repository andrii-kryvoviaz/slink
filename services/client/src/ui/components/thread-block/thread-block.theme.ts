import { type VariantProps, tv } from 'tailwind-variants';

const control =
  'group relative inline-flex w-fit items-center gap-1 self-start rounded-sm text-xs font-medium text-foreground-muted outline-none hover:text-foreground before:absolute before:inset-x-0 before:-inset-y-3.5 before:content-[""] focus-visible:ring-2 focus-visible:ring-ring/50';

const rail = 'border-l-2 border-border pl-3 pr-3';

export const threadBlock = tv({
  slots: {
    root: 'flex flex-col gap-2.5',
    row: 'min-w-0 text-[13px] text-foreground-muted',
    content:
      'overflow-hidden data-[state=closed]:animate-collapsible-up data-[state=open]:animate-collapsible-down motion-reduce:animate-none',
    panel: 'flex flex-col gap-2.5',
    trigger: control,
    more: control,
    chevron:
      'size-3.5 transition-transform group-data-[state=open]:rotate-180 motion-reduce:transition-none',
    moreChevron: 'size-3.5',
  },
  variants: {
    latest: {
      true: { root: rail },
      false: { panel: rail },
    },
  },
  defaultVariants: {
    latest: true,
  },
});

export type ThreadBlockVariants = VariantProps<typeof threadBlock>;
