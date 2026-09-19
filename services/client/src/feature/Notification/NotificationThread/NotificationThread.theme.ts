import { type VariantProps, tv } from 'tailwind-variants';

export const notificationThread = tv({
  slots: {
    row: 'relative grid grid-cols-[minmax(0,1fr)_auto] items-baseline gap-x-2',
    flow: 'min-w-0 wrap-break-word',
    target:
      'cursor-pointer outline-none before:absolute before:inset-0 before:rounded-sm before:content-[""] focus-visible:before:ring-2 focus-visible:before:ring-ring/50',
    hiddenAuthor: 'sr-only',
    text: '[&_[data-hashtag]]:relative [&_[data-hashtag]]:inline-block [&_[data-hashtag]]:max-w-full [&_[data-hashtag]]:truncate [&_[data-hashtag]]:py-0 [&_[data-hashtag]]:leading-none [&_[data-hashtag]]:whitespace-nowrap [&_[data-hashtag]]:align-bottom',
    time: 'text-xs text-foreground-muted',
    toggleLabel: 'tabular-nums',
  },
});

export type NotificationThreadVariants = VariantProps<
  typeof notificationThread
>;
