import { cva } from 'class-variance-authority';

export const UserDropdownTrigger = cva(
  'inline-flex items-center gap-2 px-3 py-2 rounded-full transition-all duration-200 hover:bg-neutral-100 active:bg-neutral-200 dark:hover:bg-neutral-800 dark:active:bg-neutral-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-neutral-900 focus-visible:ring-offset-2 dark:focus-visible:ring-neutral-100 cursor-pointer select-none',
  {
    variants: {
      size: {
        sm: 'h-8 px-2 text-xs gap-1.5',
        md: 'h-10 px-3 text-sm gap-2',
        lg: 'h-12 px-4 text-base gap-2.5',
      },
    },
    defaultVariants: {
      size: 'md',
    },
  },
);

export const UserDropdownContent = cva(
  'z-50 min-w-[200px] overflow-hidden rounded-2xl bg-white/95 backdrop-blur-xl p-2 shadow-xl shadow-black/[0.08] dark:bg-neutral-900/95 dark:shadow-black/40 border border-neutral-200/50 dark:border-neutral-700/50 data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2',
);

export const UserDropdownItem = cva(
  'flex items-center gap-3 w-full px-3 py-2.5 text-sm font-medium rounded-xl transition-all duration-150 cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-neutral-900/20 dark:focus-visible:ring-neutral-100/20',
  {
    variants: {
      variant: {
        default:
          'text-neutral-700 hover:bg-neutral-100/70 dark:text-neutral-300 dark:hover:bg-neutral-800/70',
        destructive:
          'text-red-600 hover:bg-red-50/70 dark:text-red-400 dark:hover:bg-red-950/30',
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  },
);
