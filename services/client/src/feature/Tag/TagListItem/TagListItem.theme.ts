import { cva } from 'class-variance-authority';
import type { VariantProps } from 'class-variance-authority';

export const tagListItemVariants = cva(
  'flex-1 flex items-center gap-3 px-4 py-3 text-sm font-medium text-left transition-all duration-200 rounded-lg',
  {
    variants: {
      variant: {
        default: [
          'text-slate-700 dark:text-slate-200',
          'hover:bg-slate-50 dark:hover:bg-slate-800/60',
          'hover:text-slate-900 dark:hover:text-slate-100',
          'focus:bg-slate-100 dark:focus:bg-slate-800',
          'focus:text-slate-900 dark:focus:text-slate-100',
          'focus:outline-none focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-700',
          'active:scale-[0.98]',
        ],
        neon: [
          'text-indigo-700 dark:text-indigo-200',
          'hover:bg-indigo-50 dark:hover:bg-indigo-900/30',
          'hover:text-indigo-800 dark:hover:text-indigo-100',
          'focus:bg-indigo-100 dark:focus:bg-indigo-900/40',
          'focus:text-indigo-900 dark:focus:text-indigo-100',
          'focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-600',
          'active:scale-[0.98]',
        ],
        minimal: [
          'text-slate-600 dark:text-slate-300',
          'hover:bg-slate-50 dark:hover:bg-slate-800/50',
          'hover:text-slate-800 dark:hover:text-slate-200',
          'focus:bg-slate-100 dark:focus:bg-slate-800',
          'focus:text-slate-900 dark:focus:text-slate-100',
          'focus:outline-none focus:ring-1 focus:ring-slate-300 dark:focus:ring-slate-600',
          'active:scale-[0.98]',
        ],
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  },
);

export const tagListIconVariants = cva('h-4 w-4 flex-shrink-0', {
  variants: {
    variant: {
      default: 'text-slate-500 dark:text-slate-400',
      neon: 'text-indigo-500 dark:text-indigo-400',
      minimal: 'text-slate-500 dark:text-slate-400',
    },
  },
  defaultVariants: {
    variant: 'default',
  },
});

export const tagListBadgeVariants = cva(
  'text-xs font-medium px-2 py-1 rounded-md transition-all duration-200 flex-shrink-0',
  {
    variants: {
      variant: {
        default: [
          'text-slate-600 bg-slate-100 dark:text-slate-300 dark:bg-slate-700',
          'group-hover:bg-slate-200 dark:group-hover:bg-slate-600',
        ],
        neon: [
          'text-indigo-600 bg-indigo-100 dark:text-indigo-300 dark:bg-indigo-900/50',
          'group-hover:bg-indigo-200 dark:group-hover:bg-indigo-800/60',
        ],
        minimal: [
          'text-slate-600 bg-slate-100 dark:text-slate-400 dark:bg-slate-800',
          'group-hover:bg-slate-200 dark:group-hover:bg-slate-700',
        ],
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  },
);

export const tagListActionButtonVariants = cva(
  'flex items-center justify-center w-9 h-9 mx-1 rounded-lg transition-all duration-200 flex-shrink-0',
  {
    variants: {
      variant: {
        default: [
          'text-slate-400 hover:text-slate-600 hover:bg-slate-100',
          'dark:text-slate-500 dark:hover:text-slate-300 dark:hover:bg-slate-800',
          'focus:outline-none focus:bg-slate-200 focus:text-slate-700',
          'dark:focus:bg-slate-700 dark:focus:text-slate-200',
          'active:scale-95',
        ],
        neon: [
          'text-indigo-400 hover:text-indigo-600 hover:bg-indigo-100',
          'dark:text-indigo-500 dark:hover:text-indigo-300 dark:hover:bg-indigo-900/40',
          'focus:outline-none focus:bg-indigo-200 focus:text-indigo-700',
          'dark:focus:bg-indigo-800/60 dark:focus:text-indigo-200',
          'active:scale-95',
        ],
        minimal: [
          'text-slate-400 hover:text-slate-600 hover:bg-slate-100',
          'dark:text-slate-500 dark:hover:text-slate-300 dark:hover:bg-slate-800',
          'focus:outline-none focus:bg-slate-200 focus:text-slate-700',
          'dark:focus:bg-slate-700 dark:focus:text-slate-200',
          'active:scale-95',
        ],
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  },
);

export type TagListItemVariants = VariantProps<typeof tagListItemVariants>;
export type TagListIconVariants = VariantProps<typeof tagListIconVariants>;
export type TagListBadgeVariants = VariantProps<typeof tagListBadgeVariants>;
export type TagListActionButtonVariants = VariantProps<
  typeof tagListActionButtonVariants
>;
