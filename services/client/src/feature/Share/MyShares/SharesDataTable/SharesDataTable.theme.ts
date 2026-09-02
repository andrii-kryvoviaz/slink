import { tv } from 'tailwind-variants';

export const shareableCell = tv({
  slots: {
    root: 'flex items-center gap-3 min-w-0 group/shareable',
    thumbWrap: 'relative shrink-0',
    thumb:
      'shrink-0 overflow-hidden rounded-md bg-muted flex items-center justify-center',
    thumbIcon: 'text-foreground-subtle',
    corner:
      'absolute -bottom-1 -right-1 h-4 w-4 rounded-md bg-card border border-border/60 flex items-center justify-center text-foreground-soft',
    cornerIcon: 'h-2.5 w-2.5',
    text: 'flex flex-col min-w-0 leading-tight gap-0.5',
    name: 'font-medium text-foreground truncate text-sm group-hover/shareable:text-info hover:text-info transition-colors',
    linkRow: 'flex items-center gap-1 min-w-0',
    meta: 'font-mono text-xs text-foreground-muted truncate',
    copy: 'inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-md text-foreground-muted hover:text-foreground [@media(hover:hover)]:opacity-0 [@media(hover:hover)]:group-hover/row:opacity-100 [@media(hover:none)]:opacity-100 focus-visible:opacity-100 transition-opacity duration-150',
    copyIcon: 'h-3.5 w-3.5',
  },
  variants: {
    size: {
      md: {
        thumb: 'h-10 w-10',
        thumbIcon: 'h-5 w-5',
      },
      sm: {
        thumb: 'h-8 w-8',
        thumbIcon: 'h-4 w-4',
      },
    },
    copied: {
      true: {
        copyIcon: 'text-success-text',
      },
    },
  },
  defaultVariants: {
    size: 'md',
  },
});

export const expiresCell = tv({
  slots: {
    label: 'tabular-nums',
    empty: 'text-xs text-foreground-subtle',
    card: 'flex flex-col gap-0.5',
    cardLabel: 'whitespace-nowrap',
    cardRelative: 'text-[11px] opacity-70',
  },
  variants: {
    tone: {
      default: { label: 'text-foreground-soft' },
      warning: { label: 'text-warning-text' },
      danger: { label: 'text-danger-text font-medium' },
    },
  },
  defaultVariants: {
    tone: 'default',
  },
});
