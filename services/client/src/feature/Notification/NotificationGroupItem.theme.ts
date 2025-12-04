import { cva } from 'class-variance-authority';
import type { VariantProps } from 'class-variance-authority';

export const notificationCardVariants = cva(
  'rounded-2xl transition-all duration-300 ease-out overflow-hidden',
  {
    variants: {
      read: {
        true: 'bg-gray-50 dark:bg-white/2',
        false: 'bg-white dark:bg-white/4 shadow-sm dark:shadow-none',
      },
    },
    defaultVariants: {
      read: false,
    },
  },
);

export const notificationButtonVariants = cva(
  'group w-full text-left flex items-start gap-4 p-4 transition-colors',
  {
    variants: {
      read: {
        true: 'hover:bg-gray-100 dark:hover:bg-white/4',
        false: 'hover:bg-gray-50 dark:hover:bg-white/6',
      },
    },
    defaultVariants: {
      read: false,
    },
  },
);

export const notificationIconVariants = cva(
  'shrink-0 w-10 h-10 rounded-xl flex items-center justify-center transition-transform duration-300 group-hover:scale-105',
  {
    variants: {
      type: {
        comment: 'bg-blue-500/10 dark:bg-blue-500/20',
        comment_reply: 'bg-violet-500/10 dark:bg-violet-500/20',
        added_to_favorite: 'bg-rose-500/10 dark:bg-rose-500/20',
      },
    },
    defaultVariants: {
      type: 'comment',
    },
  },
);

export const notificationIconColorVariants = cva('w-5 h-5', {
  variants: {
    type: {
      comment: 'text-blue-500',
      comment_reply: 'text-violet-500',
      added_to_favorite: 'text-rose-500',
    },
  },
  defaultVariants: {
    type: 'comment',
  },
});

export const caretVariants = cva(
  'w-4 h-4 text-gray-400 transition-transform duration-200',
  {
    variants: {
      expanded: {
        true: 'rotate-180',
        false: '',
      },
    },
    defaultVariants: {
      expanded: false,
    },
  },
);

export type NotificationCardVariant = VariantProps<
  typeof notificationCardVariants
>;
export type NotificationButtonVariant = VariantProps<
  typeof notificationButtonVariants
>;
export type NotificationIconVariant = VariantProps<
  typeof notificationIconVariants
>;
export type NotificationIconColorVariant = VariantProps<
  typeof notificationIconColorVariants
>;
export type CaretVariant = VariantProps<typeof caretVariants>;
