import { type VariantProps, tv } from 'tailwind-variants';

export const notificationEntry = tv({
  slots: {
    row: 'group/entry grid grid-cols-[40px_minmax(0,1fr)_4.5rem] items-center gap-x-3',
    thumbButton:
      'relative size-10 rounded-lg outline-none transition-opacity motion-reduce:transition-none focus-visible:ring-2 focus-visible:ring-ring/50 before:absolute before:-inset-0.5 before:content-[""]',
    thumb: 'size-10 overflow-hidden rounded-lg',
    image: 'h-10 w-10 object-cover',
    badge:
      'absolute -right-[5px] -bottom-[5px] grid size-[18px] place-items-center rounded-full border border-border-strong bg-background',
    badgeIcon: 'size-[11px] text-foreground-muted',
    sentence: 'min-w-0 truncate text-sm',
    thread: 'col-start-2 min-w-0',
    name: 'font-medium',
    verb: 'text-foreground-muted',
    aside: 'grid items-center justify-items-end',
    time: 'text-xs text-foreground-muted transition-opacity [grid-area:1/1]',
    markRead:
      'relative inline-flex size-7 items-center justify-center rounded-full text-foreground-muted opacity-0 pointer-events-none outline-none transition-opacity [grid-area:1/1] before:absolute before:inset-x-0 before:-inset-y-2 before:content-[""] hover:bg-hover hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring/50 group-hover/entry:opacity-100 group-hover/entry:pointer-events-auto group-focus-within/entry:opacity-100 group-focus-within/entry:pointer-events-auto [@media(hover:none)]:opacity-100 [@media(hover:none)]:pointer-events-auto',
    markReadIcon: 'size-4',
  },
  variants: {
    read: {
      true: {
        thumbButton: 'opacity-60',
        name: 'text-foreground-muted',
      },
      false: {
        name: 'text-foreground',
        time: 'group-hover/entry:opacity-0 group-focus-within/entry:opacity-0 [@media(hover:none)]:opacity-0',
      },
    },
  },
  defaultVariants: {
    read: false,
  },
});

export type NotificationEntryVariants = VariantProps<typeof notificationEntry>;
