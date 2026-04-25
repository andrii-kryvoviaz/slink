import { cva } from 'class-variance-authority';

export const searchBarTrigger = cva('sm:hidden');

export const searchBarBack = cva('h-8 w-8 px-0 shrink-0 sm:hidden');

export const searchBarContainer = cva(
  'flex items-center gap-2 transition-all duration-200',
  {
    variants: {
      expanded: {
        true: 'absolute left-3 right-3 top-1/2 -translate-y-1/2 z-50 sm:static sm:translate-y-0 sm:inset-auto sm:z-40 sm:flex',
        false: 'relative z-40 hidden sm:flex',
      },
    },
    defaultVariants: {
      expanded: false,
    },
  },
);

export const searchBarBackdrop = cva(
  'absolute inset-0 z-40 sm:hidden bg-[var(--modal-overlay-tint)] backdrop-blur-sm cursor-default',
);

export const searchBarField = cva(
  'flex-1 min-w-0 sm:flex-none sm:min-w-50 sm:max-w-80',
  {
    variants: {
      focused: {
        true: 'ring-2 ring-blue-500/20 border-blue-300/60 shadow-md dark:border-blue-500/40',
      },
    },
    defaultVariants: {
      focused: false,
    },
  },
);

export const searchBarDropdownChevron = cva(
  'w-3 h-3 transition-transform duration-200 shrink-0',
  {
    variants: {
      open: {
        true: 'rotate-180',
      },
    },
    defaultVariants: {
      open: false,
    },
  },
);
