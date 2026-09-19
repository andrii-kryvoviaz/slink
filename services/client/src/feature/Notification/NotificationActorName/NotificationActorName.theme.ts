import { type VariantProps, tv } from 'tailwind-variants';

export const notificationActorName = tv({
  base: 'font-medium',
  variants: {
    read: {
      true: 'text-foreground-muted',
      false: 'text-foreground-soft',
    },
  },
  defaultVariants: {
    read: false,
  },
});

export type NotificationActorNameVariants = VariantProps<
  typeof notificationActorName
>;
