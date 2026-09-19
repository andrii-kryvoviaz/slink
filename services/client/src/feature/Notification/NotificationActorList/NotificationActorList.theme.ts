import { type VariantProps, tv } from 'tailwind-variants';

export const notificationActorList = tv({
  slots: {
    row: 'flex items-center gap-2',
    avatar: 'size-5 shrink-0',
    name: 'min-w-0 truncate font-medium text-foreground',
    time: 'ml-auto shrink-0 text-xs text-foreground-muted',
    toggleLabel: 'tabular-nums',
  },
});

export type NotificationActorListVariants = VariantProps<
  typeof notificationActorList
>;
