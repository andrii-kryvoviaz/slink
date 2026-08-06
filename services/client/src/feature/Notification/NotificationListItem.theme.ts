import { cva } from 'class-variance-authority';
import type { VariantProps } from 'class-variance-authority';

export const notificationItemVariants = cva(
  'w-full flex items-start gap-4 p-4 rounded-xl transition-all duration-200 text-left cursor-pointer',
  {
    variants: {
      read: {
        true: 'bg-card/30 hover:bg-card/50',
        false:
          'bg-accent-subtle/80 hover:bg-accent-subtle border-l-2 border-accent',
      },
    },
    defaultVariants: {
      read: false,
    },
  },
);

export const notificationItemIconWrapperVariants = cva(
  'shrink-0 w-10 h-10 rounded-full flex items-center justify-center',
  {
    variants: {
      type: {
        comment: 'bg-info-fill/12',
        comment_reply: 'bg-accent/12',
        added_to_favorite: 'bg-decor-pink/12',
        added_to_bookmarks: 'bg-accent/12',
      },
    },
    defaultVariants: {
      type: 'comment',
    },
  },
);

export const notificationItemIconVariants = cva('w-5 h-5', {
  variants: {
    type: {
      comment: 'text-info',
      comment_reply: 'text-accent',
      added_to_favorite: 'text-decor-pink',
      added_to_bookmarks: 'text-accent',
    },
  },
  defaultVariants: {
    type: 'comment',
  },
});

export type NotificationItemVariant = VariantProps<
  typeof notificationItemVariants
>;
export type NotificationItemIconWrapperVariant = VariantProps<
  typeof notificationItemIconWrapperVariants
>;
export type NotificationItemIconVariant = VariantProps<
  typeof notificationItemIconVariants
>;
