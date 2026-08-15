import { tv } from 'tailwind-variants';

export const tagTreeViewVariants = tv({
  slots: {
    root: 'flex flex-col',
    flatList: 'flex flex-col',
  },
});

export const tagTreeNodeVariants = tv({
  slots: {
    node: 'flex flex-col',
    row: 'group flex items-center gap-1 rounded-lg pr-1.5 transition-colors duration-150 hover:bg-hover',
    toggle:
      'flex min-w-0 flex-1 items-center gap-2 rounded-lg py-1.5 pl-1 text-left outline-none focus-visible:ring-2 focus-visible:ring-ring/30',
    chevron:
      'flex h-5 w-5 shrink-0 items-center justify-center text-foreground-subtle',
    chevronIcon: 'h-3.5 w-3.5 transition-transform duration-200',
    spacer: 'h-5 w-5 shrink-0',
    icon: 'h-3.5 w-3.5 shrink-0 text-foreground-subtle',
    name: 'truncate text-sm text-foreground-soft',
    path: 'truncate text-xs text-foreground-subtle',
    count:
      'inline-flex shrink-0 items-center rounded-md bg-muted px-1.5 py-0.5 tabular-nums text-xs font-medium text-foreground-muted transition-colors hover:bg-primary-solid/8 hover:text-info',
    countEmpty:
      'inline-flex shrink-0 items-center rounded-md px-1.5 py-0.5 tabular-nums text-xs text-border-strong',
    actions: 'shrink-0',
  },
  variants: {
    expanded: {
      true: { chevronIcon: 'rotate-90' },
      false: { chevronIcon: '' },
    },
  },
});
