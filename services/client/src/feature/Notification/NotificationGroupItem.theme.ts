import { cva } from 'class-variance-authority';
import type { VariantProps } from 'class-variance-authority';

export const notificationCardVariants = cva(
  'rounded-2xl transition-all duration-300 ease-out overflow-hidden',
  {
    variants: {
      read: {
        true: 'bg-muted-subtle',
        false:
          'bg-card dark:bg-surface-inverse-foreground/5 shadow-sm dark:shadow-none',
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
        true: 'hover:bg-ghost-hover',
        false: 'hover:bg-ghost-hover/60',
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
        comment: 'bg-info-fill/10 dark:bg-info-fill/20',
        comment_reply: 'bg-decor-violet/10 dark:bg-decor-violet/20',
        added_to_favorite: 'bg-decor-rose/10 dark:bg-decor-rose/20',
        added_to_bookmarks: 'bg-accent/10 dark:bg-accent/20',
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
      comment: 'text-info',
      comment_reply: 'text-decor-violet',
      added_to_favorite: 'text-decor-rose',
      added_to_bookmarks: 'text-accent',
    },
  },
  defaultVariants: {
    type: 'comment',
  },
});

export const caretVariants = cva(
  'w-4 h-4 text-muted-foreground transition-transform duration-200',
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
