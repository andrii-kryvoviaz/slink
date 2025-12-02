import { cva } from 'class-variance-authority';
import type { VariantProps } from 'class-variance-authority';

export const notificationItemVariants = cva(
  'w-full flex items-start gap-4 p-4 rounded-xl transition-all duration-200 text-left cursor-pointer',
  {
    variants: {
      read: {
        true: 'bg-white/30 dark:bg-gray-800/30 hover:bg-white/50 dark:hover:bg-gray-800/50',
        false:
          'bg-indigo-50/80 dark:bg-indigo-950/30 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 border-l-2 border-indigo-500',
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
        comment: 'bg-blue-100 dark:bg-blue-900/30',
        comment_reply: 'bg-indigo-100 dark:bg-indigo-900/30',
        added_to_favorite: 'bg-pink-100 dark:bg-pink-900/30',
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
      comment: 'text-blue-500',
      comment_reply: 'text-indigo-500',
      added_to_favorite: 'text-pink-500',
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
