import { tv } from 'tailwind-variants';

export const publishedLinks = tv({
  slots: {
    header: 'mb-4 flex items-start justify-between gap-4',
    title: ['text-lg font-semibold tracking-tight', 'text-foreground', 'mb-1'],
    subtitle: 'text-xs text-muted-foreground',
    list: [
      'rounded-lg overflow-hidden',
      'border border-border/60',
      'divide-y divide-border/40',
      'bg-card/50',
    ],
    row: [
      'group',
      'flex items-center gap-2',
      'pl-3 pr-1.5 py-2',
      'hover:bg-muted/40',
      'transition-colors duration-150',
    ],
    content: ['min-w-0 flex-1', 'flex items-center gap-2 flex-wrap'],
    dimensions: ['text-sm font-medium tabular-nums', 'text-foreground'],
    modifiers: [
      'inline-flex items-baseline gap-1.5',
      'px-1.5 py-0.5',
      'rounded',
      'bg-muted/60',
    ],
    modifierFilter: [
      'text-[10px] font-medium uppercase tracking-wider',
      'text-muted-foreground',
    ],
    modifierFormat: [
      'text-[10px] font-mono uppercase tracking-wider',
      'text-muted-foreground',
    ],
    date: ['flex-shrink-0', 'text-[11px] tabular-nums', 'text-ring'],
    actions: ['flex-shrink-0 self-center', 'flex items-center'],
    actionTrigger: [
      'h-7 w-7',
      'opacity-70 group-hover:opacity-100',
      'transition-all duration-150',
    ],
    pagination: 'flex-shrink-0 flex justify-end [&>div]:justify-end',
  },
});
