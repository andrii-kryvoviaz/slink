import { tv } from 'tailwind-variants';

export const shareableCell = tv({
  slots: {
    root: 'flex items-center gap-3 min-w-0 group/shareable',
    thumb:
      'shrink-0 overflow-hidden rounded-md bg-gray-100 dark:bg-gray-800/50 flex items-center justify-center',
    thumbIcon: 'text-gray-400 dark:text-gray-600',
    name: 'font-medium text-gray-900 dark:text-white truncate text-sm group-hover/shareable:text-blue-600 dark:group-hover/shareable:text-blue-400 transition-colors',
    meta: 'text-xs text-gray-500 dark:text-gray-400',
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
