import { type VariantProps, tv } from 'tailwind-variants';

export const notificationEntry = tv({
  slots: {
    row: 'group/entry grid grid-cols-[40px_minmax(0,1fr)_4.5rem] items-start gap-x-3 py-2',
    thumbButton:
      'relative size-10 rounded-lg outline-none transition-opacity motion-reduce:transition-none focus-visible:ring-2 focus-visible:ring-ring/50 before:absolute before:-inset-0.5 before:content-[""]',
    thumb: 'size-10 overflow-hidden rounded-lg',
    image: 'h-10 w-10 object-cover',
    badge:
      'absolute -right-[5px] -bottom-[5px] grid size-[18px] place-items-center rounded-full border border-border-strong bg-background',
    unreadDot:
      'absolute -top-[3px] -left-[3px] size-2.5 rounded-full bg-accent ring-2 ring-background',
    badgeIcon: 'size-[11px] text-foreground-muted',
    sentence: 'min-h-10 content-center min-w-0 text-sm truncate',
    thread: 'col-span-2 col-start-2 min-w-0',
    name: 'font-medium',
    verb: 'text-foreground-muted',
    aside: 'grid min-h-10 items-center justify-items-end pr-3',
    time: 'transition-opacity [grid-area:1/1]',
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
