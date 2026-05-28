import { tv } from 'tailwind-variants';

export const publishedLinks = tv({
  slots: {
    title: [
      'text-lg font-semibold tracking-tight',
      'text-gray-900 dark:text-white',
      'mb-1',
    ],
    subtitle: 'text-xs text-gray-500 dark:text-gray-400 mb-4',
    list: [
      'rounded-lg overflow-hidden',
      'border border-gray-200/70 dark:border-gray-700/50',
      'divide-y divide-gray-200/60 dark:divide-gray-800/60',
      'bg-white/50 dark:bg-gray-900/20',
    ],
    row: [
      'group',
      'flex items-center gap-2',
      'pl-3 pr-1.5 py-2',
      'hover:bg-gray-50/80 dark:hover:bg-gray-800/40',
      'transition-colors duration-150',
    ],
    content: ['min-w-0 flex-1', 'flex items-center gap-2 flex-wrap'],
    dimensions: [
      'text-sm font-medium tabular-nums',
      'text-gray-900 dark:text-gray-100',
    ],
    modifiers: [
      'inline-flex items-baseline gap-1.5',
      'px-1.5 py-0.5',
      'rounded',
      'bg-gray-100/70 dark:bg-gray-800/50',
    ],
    modifierFilter: [
      'text-[10px] font-medium uppercase tracking-wider',
      'text-gray-500 dark:text-gray-400',
    ],
    modifierFormat: [
      'text-[10px] font-mono uppercase tracking-wider',
      'text-gray-500 dark:text-gray-400',
    ],
    date: [
      'flex-shrink-0',
      'text-[11px] tabular-nums',
      'text-gray-400 dark:text-gray-500',
    ],
    actions: ['flex-shrink-0 self-center', 'flex items-center'],
    actionTrigger: [
      'h-7 w-7',
      'opacity-70 group-hover:opacity-100',
      'transition-all duration-150',
    ],
  },
});
