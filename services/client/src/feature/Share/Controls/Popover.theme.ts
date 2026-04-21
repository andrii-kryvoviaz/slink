import { tv } from 'tailwind-variants';

export const controls = {
  intro: tv({
    slots: {
      wrap: 'flex flex-col items-center text-center gap-3 py-2',
      title:
        'text-sm font-semibold text-gray-900 dark:text-white leading-tight',
      description:
        'text-xs text-gray-500 dark:text-gray-400 leading-snug max-w-[18rem]',
      actions: 'mt-2 flex w-full flex-col gap-2',
    },
  }),

  list: tv({
    slots: {
      wrap: 'flex flex-col gap-2',
      header: 'w-full',
      item: 'flex w-full items-center gap-3 rounded-lg px-2.5 py-2.5 text-left transition-colors',
      icon: 'h-5 w-5 shrink-0 text-gray-400 dark:text-gray-500',
      labels: 'flex min-w-0 flex-1 flex-col',
      label: 'text-sm font-medium text-gray-900 dark:text-white leading-tight',
      sublabel:
        'text-xs text-gray-500 dark:text-gray-400 leading-snug truncate',
      chevron: 'h-4 w-4 shrink-0 text-gray-400 dark:text-gray-500',
      badge:
        'inline-flex items-center rounded-full px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide bg-gray-100 text-gray-400 dark:bg-gray-800/40 dark:text-gray-500',
    },
    variants: {
      state: {
        interactive: {
          item: 'hover:bg-gray-100 dark:hover:bg-gray-800/60 focus:outline-none focus-visible:bg-gray-100 dark:focus-visible:bg-gray-800/60 cursor-pointer',
        },
        disabled: {
          item: 'opacity-60 cursor-not-allowed',
        },
      },
    },
    defaultVariants: {
      state: 'interactive',
    },
  }),

  detail: tv({
    slots: {
      root: 'flex flex-col gap-3 px-1.5 py-1',
      header: 'flex items-start gap-2',
      back: 'inline-flex items-center justify-center shrink-0 rounded-md mt-0.5 h-6 w-6 text-gray-500 transition-colors cursor-pointer hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30 dark:text-gray-400 dark:hover:bg-gray-800/60 dark:hover:text-white',
      backIcon: 'h-3.5 w-3.5',
      labels: 'flex min-w-0 flex-1 flex-col gap-1',
      titleRow: 'flex items-center justify-between gap-3',
      titleGroup: 'flex min-w-0 items-center gap-2',
      title: 'text-sm font-medium text-gray-900 dark:text-white leading-tight',
      description: 'text-xs text-gray-500 dark:text-gray-400 leading-snug',
      body: 'space-y-2',
      presets: 'flex flex-wrap gap-1.5',
      chip: 'inline-flex items-center rounded-full font-medium leading-none transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30 px-2.5 py-1 text-xs cursor-pointer',
      field:
        'h-auto border-transparent bg-transparent shadow-none rounded-lg px-2 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-800/60 dark:bg-transparent focus-visible:ring-0 focus-visible:ring-offset-0 focus-visible:border-transparent',
      passwordRow: 'flex items-center gap-1.5',
      passwordInput:
        'flex-1 h-9 rounded-md border border-gray-200 bg-white px-2.5 text-sm text-gray-900 placeholder:text-gray-400 outline-none transition-colors focus-visible:border-blue-500/60 focus-visible:ring-2 focus-visible:ring-blue-500/30 dark:border-gray-700 dark:bg-gray-900/40 dark:text-white dark:placeholder:text-gray-500 disabled:cursor-not-allowed disabled:opacity-60',
      passwordToggle:
        'inline-flex items-center justify-center shrink-0 rounded-md h-8 w-8 text-gray-500 transition-colors cursor-pointer hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30 dark:text-gray-400 dark:hover:bg-gray-800/60 dark:hover:text-white',
      passwordToggleIcon: 'h-4 w-4',
      passwordHelper:
        'text-[11px] text-gray-500 dark:text-gray-400 leading-snug',
      passwordHint:
        'text-[11px] text-amber-600 dark:text-amber-400 leading-snug',
      replaceButton:
        'inline-flex items-center self-start rounded-full px-2.5 py-1 text-xs font-medium leading-none transition-colors cursor-pointer bg-gray-100 text-gray-600 hover:bg-gray-200 hover:text-gray-900 dark:bg-gray-800/60 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30',
      placeholderRow:
        'flex items-center justify-between gap-2 rounded-md px-2.5 py-2 text-sm text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800/40',
      placeholderDots:
        'tracking-[0.2em] font-mono text-gray-400 dark:text-gray-500',
    },
    variants: {
      chipActive: {
        true: {
          chip: 'bg-gray-900 text-white dark:bg-white dark:text-gray-900',
        },
        false: {
          chip: 'bg-gray-100 text-gray-600 hover:bg-gray-200 hover:text-gray-900 dark:bg-gray-800/60 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white',
        },
      },
    },
    defaultVariants: {
      chipActive: false,
    },
  }),
};
