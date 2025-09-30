import { cva } from 'class-variance-authority';
import type { VariantProps } from 'class-variance-authority';

export const tagListItemVariants = cva(
  'flex-1 flex items-center gap-3 px-4 py-3 text-sm font-medium text-left transition-all duration-300 rounded-xl',
  {
    variants: {
      variant: {
        default: [
          'text-slate-700 dark:text-slate-200',
          'hover:bg-blue-100 dark:hover:bg-blue-800/40',
          'hover:text-blue-600 dark:hover:text-blue-300',
          'hover:shadow-sm hover:shadow-blue-200/40 dark:hover:shadow-blue-900/30',
          'focus:bg-blue-100 dark:focus:bg-blue-800/40',
          'focus:text-blue-600 dark:focus:text-blue-300',
          'focus:outline-none focus:ring-2 focus:ring-blue-300/50 dark:focus:ring-blue-600/50',
          'active:scale-[0.98]',
        ],
        neon: [
          'text-slate-700 dark:text-slate-200',
          'hover:bg-blue-100 dark:hover:bg-blue-800/40',
          'hover:text-blue-600 dark:hover:text-blue-300',
          'hover:shadow-sm hover:shadow-blue-200/40 dark:hover:shadow-blue-900/30',
          'focus:bg-blue-100 dark:focus:bg-blue-800/40',
          'focus:text-blue-600 dark:focus:text-blue-300',
          'focus:outline-none focus:ring-2 focus:ring-blue-300/50 dark:focus:ring-blue-600/50',
          'active:scale-[0.98]',
        ],
        minimal: [
          'text-slate-700 dark:text-slate-200',
          'hover:bg-blue-50/60 dark:hover:bg-blue-950/30',
          'hover:text-slate-900 dark:hover:text-slate-100',
          'hover:shadow-sm hover:shadow-blue-100/50 dark:hover:shadow-blue-900/30',
          'focus:bg-blue-50/80 dark:focus:bg-blue-950/40',
          'focus:text-slate-900 dark:focus:text-slate-100',
          'focus:outline-none focus:ring-1 focus:ring-blue-300/50 dark:focus:ring-blue-600/50',
          'active:scale-[0.98]',
        ],
      },
      highlighted: {
        true: '',
        false: '',
      },
    },
    compoundVariants: [
      {
        variant: 'default',
        highlighted: true,
        class:
          'bg-blue-100 dark:bg-blue-800/40 text-blue-600 dark:text-blue-300 shadow-sm shadow-blue-200/40 dark:shadow-blue-900/30',
      },
      {
        variant: 'neon',
        highlighted: true,
        class:
          'bg-blue-100 dark:bg-blue-800/40 text-blue-600 dark:text-blue-300 shadow-sm shadow-blue-200/40 dark:shadow-blue-900/30',
      },
      {
        variant: 'minimal',
        highlighted: true,
        class:
          'bg-blue-50/80 dark:bg-blue-950/40 text-slate-900 dark:text-slate-100 shadow-sm shadow-blue-100/50 dark:shadow-blue-900/30',
      },
    ],
    defaultVariants: {
      variant: 'default',
      highlighted: false,
    },
  },
);

export const tagListIconVariants = cva('h-4 w-4 flex-shrink-0', {
  variants: {
    variant: {
      default: 'text-slate-500 dark:text-slate-400',
      neon: 'text-blue-500 dark:text-blue-400 group-hover:text-blue-600 dark:group-hover:text-blue-300',
      minimal: 'text-slate-500 dark:text-slate-400',
    },
  },
  defaultVariants: {
    variant: 'default',
  },
});

export const tagListBadgeVariants = cva(
  'text-xs font-medium px-2 py-1 rounded-lg transition-all duration-300 flex-shrink-0',
  {
    variants: {
      variant: {
        default: [
          'text-slate-600 bg-slate-100/80 dark:text-slate-300 dark:bg-slate-700/60',
          'border border-slate-200/50 dark:border-slate-600/40',
          'group-hover:bg-blue-100/80 dark:group-hover:bg-blue-800/40',
          'group-hover:text-blue-700 dark:group-hover:text-blue-300',
          'group-hover:border-blue-200/60 dark:group-hover:border-blue-700/50',
        ],
        neon: [
          'text-slate-600 bg-slate-100/80 dark:text-slate-300 dark:bg-slate-700/60',
          'border border-slate-200/50 dark:border-slate-600/40',
          'group-hover:bg-blue-100/80 dark:group-hover:bg-blue-800/40',
          'group-hover:text-blue-700 dark:group-hover:text-blue-300',
          'group-hover:border-blue-200/60 dark:group-hover:border-blue-700/50',
        ],
        minimal: [
          'text-slate-600 bg-slate-100/80 dark:text-slate-300 dark:bg-slate-700/60',
          'border border-slate-200/50 dark:border-slate-600/40',
          'group-hover:bg-blue-100/80 dark:group-hover:bg-blue-900/40',
          'group-hover:text-blue-700 dark:group-hover:text-blue-300',
          'group-hover:border-blue-200/60 dark:group-hover:border-blue-700/50',
        ],
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  },
);

export const tagListActionButtonVariants = cva(
  'flex items-center justify-center w-9 h-9 mx-1 rounded-xl transition-all duration-300 flex-shrink-0',
  {
    variants: {
      variant: {
        default: [
          'text-slate-400 hover:text-blue-600 hover:bg-blue-100/80',
          'dark:text-slate-500 dark:hover:text-blue-300 dark:hover:bg-blue-800/40',
          'hover:shadow-sm hover:shadow-blue-200/40 dark:hover:shadow-blue-900/30',
          'focus:outline-none focus:bg-blue-100/80 focus:text-blue-700',
          'dark:focus:bg-blue-800/60 dark:focus:text-blue-200',
          'focus:ring-2 focus:ring-blue-300/50 dark:focus:ring-blue-600/50',
          'active:scale-95',
        ],
        neon: [
          'text-slate-400 hover:text-blue-600 hover:bg-blue-100/80',
          'dark:text-slate-500 dark:hover:text-blue-300 dark:hover:bg-blue-800/40',
          'hover:shadow-sm hover:shadow-blue-200/40 dark:hover:shadow-blue-900/30',
          'focus:outline-none focus:bg-blue-100/80 focus:text-blue-700',
          'dark:focus:bg-blue-800/60 dark:focus:text-blue-200',
          'focus:ring-2 focus:ring-blue-300/50 dark:focus:ring-blue-600/50',
          'active:scale-95',
        ],
        minimal: [
          'text-slate-400 hover:text-blue-600 hover:bg-blue-100/80',
          'dark:text-slate-500 dark:hover:text-blue-300 dark:hover:bg-blue-900/40',
          'hover:shadow-sm hover:shadow-blue-100/50 dark:hover:shadow-blue-900/30',
          'focus:outline-none focus:bg-blue-200/80 focus:text-blue-700',
          'dark:focus:bg-blue-800/60 dark:focus:text-blue-200',
          'focus:ring-1 focus:ring-blue-300/50 dark:focus:ring-blue-600/50',
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
