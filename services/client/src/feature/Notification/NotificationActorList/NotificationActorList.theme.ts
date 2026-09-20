import { type VariantProps, tv } from 'tailwind-variants';

export const notificationActorList = tv({
  slots: {
    row: 'flex items-center gap-2',
    avatar: 'size-5 shrink-0',
    name: 'min-w-0 truncate',
    time: 'ml-auto shrink-0',
    toggleLabel: 'tabular-nums',
  },
});

export type NotificationActorListVariants = VariantProps<
  typeof notificationActorList
>;
