import { cva } from 'class-variance-authority';
import type { VariantProps } from 'class-variance-authority';

export const tagListItemVariants = cva(
  'flex-1 flex items-center gap-3 px-4 py-3 text-sm font-medium text-left rounded-lg',
  {
    variants: {
      variant: {
        default: [
          'text-gray-700 dark:text-white',
          'hover:bg-blue-100 dark:hover:bg-blue-500/20',
          'hover:text-blue-600 dark:hover:text-blue-300',
          'hover:shadow-sm hover:shadow-blue-200/40 dark:hover:shadow-blue-900/20',
          'focus:bg-blue-100 dark:focus:bg-blue-500/20',
          'focus:text-blue-600 dark:focus:text-blue-300',
          'focus:outline-none focus:ring-2 focus:ring-blue-300/50 dark:focus:ring-blue-500/30',
          'active:scale-[0.98]',
          'transition-all duration-300',
        ],
        neon: [
          'text-gray-700 dark:text-white',
          'hover:bg-blue-100 dark:hover:bg-blue-500/20',
          'hover:text-blue-600 dark:hover:text-blue-300',
          'hover:shadow-sm hover:shadow-blue-200/40 dark:hover:shadow-blue-900/20',
          'focus:bg-blue-100 dark:focus:bg-blue-500/20',
          'focus:text-blue-600 dark:focus:text-blue-300',
          'focus:outline-none focus:ring-2 focus:ring-blue-300/50 dark:focus:ring-blue-500/30',
          'active:scale-[0.98]',
          'transition-all duration-300',
        ],
        minimal: [
          'text-gray-700 dark:text-white',
          'hover:bg-gray-100 dark:hover:bg-white/10',
          'focus:bg-gray-100 dark:focus:bg-white/10',
          'focus:outline-none',
          'transition-colors duration-150',
          'rounded-md',
          'px-3 py-2',
        ],
        subtle: [
          'text-gray-700 dark:text-white',
          'hover:bg-gray-50 dark:hover:bg-white/8',
          'focus:bg-gray-50 dark:focus:bg-white/8',
          'focus:outline-none',
          'transition-colors duration-150',
          'rounded-lg',
          'px-3 py-2',
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
          'bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-300 shadow-sm shadow-blue-200/40 dark:shadow-blue-900/20',
      },
      {
        variant: 'neon',
        highlighted: true,
        class:
          'bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-300 shadow-sm shadow-blue-200/40 dark:shadow-blue-900/20',
      },
      {
        variant: 'minimal',
        highlighted: true,
        class: 'bg-gray-100 dark:bg-white/10 text-gray-900 dark:text-white',
      },
      {
        variant: 'subtle',
        highlighted: true,
        class: 'bg-gray-100 dark:bg-white/10',
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
      default: 'text-gray-500 dark:text-white/60',
      neon: 'text-blue-500 dark:text-blue-400 group-hover:text-blue-600 dark:group-hover:text-blue-300',
      minimal: 'text-gray-500 dark:text-white/60',
      subtle: 'text-gray-400 dark:text-white/50',
    },
  },
  defaultVariants: {
    variant: 'default',
  },
});

export const tagListBadgeVariants = cva(
  'text-xs font-medium px-2 py-1 rounded-lg flex-shrink-0',
  {
    variants: {
      variant: {
        default: [
          'text-gray-600 bg-gray-100/80 dark:text-white/70 dark:bg-white/10',
          'border border-gray-200/50 dark:border-white/10',
          'group-hover:bg-blue-100/80 dark:group-hover:bg-blue-500/20',
          'group-hover:text-blue-700 dark:group-hover:text-blue-300',
          'group-hover:border-blue-200/60 dark:group-hover:border-blue-500/30',
          'transition-all duration-300',
        ],
        neon: [
          'text-gray-600 bg-gray-100/80 dark:text-white/70 dark:bg-white/10',
          'border border-gray-200/50 dark:border-white/10',
          'group-hover:bg-blue-100/80 dark:group-hover:bg-blue-500/20',
          'group-hover:text-blue-700 dark:group-hover:text-blue-300',
          'group-hover:border-blue-200/60 dark:group-hover:border-blue-500/30',
          'transition-all duration-300',
        ],
        minimal: [
          'text-gray-600 bg-gray-100/80 dark:text-white/70 dark:bg-white/10',
          'border border-gray-200/50 dark:border-white/10',
          'group-hover:bg-blue-100/80 dark:group-hover:bg-blue-500/20',
          'group-hover:text-blue-700 dark:group-hover:text-blue-300',
          'group-hover:border-blue-200/60 dark:group-hover:border-blue-500/30',
          'transition-all duration-300',
        ],
        subtle: ['text-gray-400 dark:text-white/50'],
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  },
);

export const tagListActionButtonVariants = cva(
  'flex items-center justify-center w-9 h-9 mx-1 rounded-lg flex-shrink-0',
  {
    variants: {
      variant: {
        default: [
          'text-gray-400 hover:text-blue-600 hover:bg-blue-100/80',
          'dark:text-white/40 dark:hover:text-blue-300 dark:hover:bg-blue-500/20',
          'hover:shadow-sm hover:shadow-blue-200/40 dark:hover:shadow-blue-900/20',
          'focus:outline-none focus:bg-blue-100/80 focus:text-blue-700',
          'dark:focus:bg-blue-500/30 dark:focus:text-blue-200',
          'focus:ring-2 focus:ring-blue-300/50 dark:focus:ring-blue-500/30',
          'active:scale-95',
          'transition-all duration-300',
        ],
        neon: [
          'text-gray-400 hover:text-blue-600 hover:bg-blue-100/80',
          'dark:text-white/40 dark:hover:text-blue-300 dark:hover:bg-blue-500/20',
          'hover:shadow-sm hover:shadow-blue-200/40 dark:hover:shadow-blue-900/20',
          'focus:outline-none focus:bg-blue-100/80 focus:text-blue-700',
          'dark:focus:bg-blue-500/30 dark:focus:text-blue-200',
          'focus:ring-2 focus:ring-blue-300/50 dark:focus:ring-blue-500/30',
          'active:scale-95',
          'transition-all duration-300',
        ],
        minimal: [
          'text-gray-400 hover:text-blue-600 hover:bg-blue-100/80',
          'dark:text-white/40 dark:hover:text-blue-300 dark:hover:bg-blue-500/20',
          'hover:shadow-sm hover:shadow-blue-100/50 dark:hover:shadow-blue-900/20',
          'focus:outline-none focus:bg-blue-200/80 focus:text-blue-700',
          'dark:focus:bg-blue-500/30 dark:focus:text-blue-200',
          'focus:ring-1 focus:ring-blue-300/50 dark:focus:ring-blue-500/30',
          'active:scale-95',
          'transition-all duration-300',
        ],
        subtle: [
          'text-gray-400 hover:text-blue-500',
          'dark:text-white/40 dark:hover:text-blue-400',
          'hover:bg-gray-100 dark:hover:bg-white/10',
          'focus:outline-none',
          'w-7 h-7 mx-0 rounded-lg',
          'transition-colors duration-150',
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
