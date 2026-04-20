import { tv } from 'tailwind-variants';

export const indicators = tv({
  slots: {
    wrap: 'flex items-center gap-1.5',
    chip: 'inline-flex items-center rounded-full font-medium leading-none transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30 border px-2 py-0.5 text-[11px] gap-1',
    chipIcon: 'h-3 w-3',
  },
  variants: {
    kind: {
      active: {
        chip: 'border-gray-200/60 bg-gray-100 text-gray-600 dark:border-gray-700/60 dark:bg-gray-800/60 dark:text-gray-400',
      },
      expired: {
        chip: 'border-red-500/30 bg-red-500/10 text-red-600 dark:text-red-400',
      },
      protected: {
        chip: 'border-amber-500/30 bg-amber-500/10 text-amber-600 dark:text-amber-400',
      },
    },
  },
});
