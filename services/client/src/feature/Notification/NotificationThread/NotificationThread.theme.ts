import { type VariantProps, tv } from 'tailwind-variants';

export const notificationThread = tv({
  slots: {
    row: 'relative flex items-baseline gap-2',
    target:
      'shrink-0 cursor-pointer outline-none before:absolute before:inset-0 before:rounded-sm before:content-[""] focus-visible:before:ring-2 focus-visible:before:ring-ring/50',
    author: 'font-medium text-foreground',
    text: 'min-w-0 flex-1 truncate [&_[data-hashtag]]:relative [&_[data-hashtag]]:inline [&_[data-hashtag]]:py-0',
    time: 'ml-auto shrink-0 text-xs text-foreground-muted',
    toggleLabel: 'tabular-nums',
  },
});

export type NotificationThreadVariants = VariantProps<
  typeof notificationThread
>;
