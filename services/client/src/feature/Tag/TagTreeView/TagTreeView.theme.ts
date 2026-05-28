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
    row: 'group flex items-center gap-1 rounded-lg pr-1.5 transition-colors duration-150 hover:bg-slate-100/80 dark:hover:bg-slate-800/50',
    toggle:
      'flex min-w-0 flex-1 items-center gap-2 rounded-lg py-1.5 pl-1 text-left outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30',
    chevron:
      'flex h-5 w-5 shrink-0 items-center justify-center text-slate-400 dark:text-slate-500',
    chevronIcon: 'h-3.5 w-3.5 transition-transform duration-200',
    spacer: 'h-5 w-5 shrink-0',
    icon: 'h-3.5 w-3.5 shrink-0 text-slate-400 dark:text-slate-500',
    name: 'truncate text-sm text-slate-700 dark:text-slate-200',
    path: 'truncate text-xs text-slate-400 dark:text-slate-500',
    count:
      'inline-flex shrink-0 items-center rounded-md bg-slate-100 px-1.5 py-0.5 tabular-nums text-xs font-medium text-slate-500 transition-colors hover:bg-blue-50 hover:text-blue-600 dark:bg-slate-800/60 dark:text-slate-400 dark:hover:bg-blue-500/15 dark:hover:text-blue-400',
    countEmpty:
      'inline-flex shrink-0 items-center rounded-md px-1.5 py-0.5 tabular-nums text-xs text-slate-300 dark:text-slate-600',
    actions: 'shrink-0',
  },
  variants: {
    expanded: {
      true: { chevronIcon: 'rotate-90' },
      false: { chevronIcon: '' },
    },
  },
});
