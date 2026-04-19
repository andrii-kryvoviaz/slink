import { tv } from 'tailwind-variants';

export const toolbar = tv({
  slots: {
    header: 'flex items-center justify-between gap-3',
    titleRow: 'flex min-w-0 items-center gap-2',
    title: 'text-lg font-semibold text-gray-900 dark:text-white',
    trigger:
      'inline-flex items-center justify-center shrink-0 rounded-md h-8 w-8 text-gray-500 transition-colors cursor-pointer hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30 dark:text-gray-400 dark:hover:bg-gray-800/60 dark:hover:text-white data-[state=open]:bg-gray-100 data-[state=open]:text-gray-900 dark:data-[state=open]:bg-gray-800/60 dark:data-[state=open]:text-white',
    triggerIcon: 'h-4 w-4',
  },
});
