import { tv } from 'tailwind-variants';

export const sharesFilterBar = tv({
  slots: {
    root: 'relative',
    shellLayout: 'flex flex-col gap-3 lg:flex-row lg:items-center lg:gap-3',
    searchGroup: 'flex items-center gap-3 min-w-0 flex-1',
    searchInput: [
      'flex-1 min-w-0',
      'bg-transparent',
      'text-sm text-slate-700 dark:text-slate-200',
      'placeholder:text-slate-400 dark:placeholder:text-slate-500',
      'border-0 outline-none focus:ring-0',
    ],
    divider: 'hidden h-6 w-px bg-slate-200/70 dark:bg-slate-700/70 lg:block',
    filterGroup: 'flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-2',
    expirySelect: 'w-full sm:w-40',
    protectionSelect: 'w-full sm:w-44',
    typeSelect: 'w-full sm:w-40',
    searchChipDivider:
      'w-px h-3.5 bg-slate-300 dark:bg-slate-600 hidden sm:block',
    searchChip: [
      'inline-flex items-center gap-1',
      'text-xs text-slate-500 dark:text-slate-400',
    ],
    searchChipText: [
      'max-w-[160px] truncate font-medium',
      'text-slate-700 dark:text-slate-200',
    ],
  },
});
