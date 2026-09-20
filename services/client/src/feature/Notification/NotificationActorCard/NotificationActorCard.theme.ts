import { tv } from 'tailwind-variants';

export const notificationActorCard = tv({
  slots: {
    seeAll:
      'relative mt-0.5 inline-flex items-center gap-1 rounded-sm text-xs font-medium tabular-nums text-foreground-muted outline-none hover:text-foreground before:absolute before:inset-x-0 before:-inset-y-3.5 before:content-[""] focus-visible:ring-2 focus-visible:ring-ring/50',
    seeAllIcon: 'size-3.5',
    card: 'w-64 overflow-hidden',
    header:
      'flex items-center gap-2 border-b border-border/70 bg-muted/70 px-3 py-2',
    headerIcon: 'size-4 shrink-0 text-accent',
    headerLabel:
      'min-w-0 flex-1 truncate text-xs font-medium uppercase tracking-wide text-foreground-muted',
    headerCount: 'text-xs font-semibold tabular-nums text-foreground',
    list: 'flex flex-col outline-none focus-visible:ring-2 focus-visible:ring-ring/50',
    row: 'flex items-center gap-2.5 border-b border-border/50 px-3 py-1 transition-colors duration-150 last:border-b-0 hover:bg-hover',
    name: 'min-w-0 flex-1 truncate text-sm font-medium text-foreground',
    footer:
      'flex items-center gap-2 border-t border-border/70 bg-muted/50 px-3 py-2 text-xs text-foreground-muted',
    footerIcon: 'size-3.5 shrink-0 text-foreground-subtle',
  },
});
