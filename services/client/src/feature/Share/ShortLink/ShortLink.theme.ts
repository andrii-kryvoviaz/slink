import { tv } from 'tailwind-variants';

export const shortLink = tv({
  slots: {
    meta: 'font-mono text-xs truncate min-w-0',
    srOnly: 'sr-only',
    copy: 'group/copy flex max-w-full min-w-0 self-start cursor-pointer items-center gap-1 rounded-md text-foreground-muted hover:text-foreground transition-colors',
    copyIcon:
      'h-3.5 w-3.5 shrink-0 [@media(hover:hover)]:opacity-0 [@media(hover:hover)]:group-hover/row:opacity-100 [@media(hover:none)]:opacity-100 group-focus-visible/copy:opacity-100 transition-opacity duration-150',
  },
  variants: {
    copied: {
      true: {
        copyIcon: 'text-success-text',
      },
    },
  },
});
