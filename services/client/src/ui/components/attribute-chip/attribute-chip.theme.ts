import { type VariantProps, tv } from 'tailwind-variants';

export const attributeChip = tv({
  slots: {
    root: 'inline-flex items-center rounded-full text-xs font-medium transition-colors',
    body: 'relative inline-flex h-7 min-w-0 cursor-pointer items-center gap-1.5 rounded-full outline-none transition-colors before:absolute before:inset-x-0 before:-inset-y-2 before:content-[""] focus-visible:ring-2 focus-visible:ring-accent/40 [&_img]:size-4 [&_img]:shrink-0',
    plusIcon: 'h-3.5 w-3.5 shrink-0',
    label: 'truncate leading-none',
    remove:
      'relative mr-1 inline-flex shrink-0 cursor-pointer items-center justify-center rounded-full p-1 outline-none transition-colors after:absolute after:-inset-y-2 after:inset-x-0 after:content-[""] hover:bg-foreground/10 focus-visible:ring-2 focus-visible:ring-accent/40',
    removeIcon: 'h-3 w-3',
  },
  variants: {
    state: {
      ghost: {
        root: 'border border-dashed border-border-strong text-muted-foreground hover:border-foreground-subtle hover:bg-foreground/5 hover:text-foreground-soft',
        body: 'px-3',
      },
      set: {
        root: 'bg-accent-subtle text-accent-subtle-foreground',
        body: 'pl-2.5 pr-1.5 hover:text-foreground-soft',
      },
    },
    disabled: {
      true: {
        root: 'pointer-events-none opacity-50',
      },
      false: {},
    },
  },
  defaultVariants: {
    state: 'ghost',
    disabled: false,
  },
});

export type AttributeChipVariants = VariantProps<typeof attributeChip>;
export type AttributeChipState = NonNullable<AttributeChipVariants['state']>;
