import { tv } from 'tailwind-variants';

export const shareableCell = tv({
  slots: {
    root: 'flex items-center gap-3 min-w-0 group/shareable',
    thumb:
      'shrink-0 overflow-hidden rounded-md bg-muted flex items-center justify-center',
    thumbIcon: 'text-foreground-subtle',
    name: 'font-medium text-foreground truncate text-sm group-hover/shareable:text-info transition-colors',
    meta: 'text-xs text-foreground-muted',
  },
  variants: {
    size: {
      md: {
        thumb: 'h-10 w-10',
        thumbIcon: 'h-5 w-5',
      },
      sm: {
        thumb: 'h-8 w-8',
        thumbIcon: 'h-4 w-4',
      },
    },
  },
  defaultVariants: {
    size: 'md',
  },
});
