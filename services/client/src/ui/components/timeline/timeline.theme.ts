import { tv } from 'tailwind-variants';

export const timeline = tv({
  slots: {
    root: 'flex flex-col gap-7',
    group: 'flex flex-col',
    label: 'pb-2 text-xs text-foreground-muted',
  },
});
