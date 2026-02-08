import { cva } from 'class-variance-authority';

export const actionBarContainerVariants = cva('flex items-center', {
  variants: {
    layout: {
      default: '',
      hero: 'gap-3',
    },
  },
  defaultVariants: {
    layout: 'default',
  },
});

export const actionBarSecondaryGroupVariants = cva('flex items-center', {
  variants: {
    layout: {
      default: '',
      hero: 'gap-1',
    },
  },
  defaultVariants: {
    layout: 'default',
  },
});

export const actionButtonVariants = cva('', {
  variants: {
    layout: {
      default: '',
      hero: 'h-auto min-w-0 rounded-lg',
    },
    variant: {
      default: '',
      primary: '',
      secondary: '',
      destructive: '',
    },
  },
  compoundVariants: [
    {
      layout: 'hero',
      variant: ['default', 'secondary'],
      class:
        'p-2 bg-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800',
    },
    {
      layout: 'hero',
      variant: 'primary',
      class:
        'px-5 py-2.5 gap-2 bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 text-white font-medium text-sm shadow-sm hover:shadow-md',
    },
    {
      layout: 'hero',
      variant: 'destructive',
      class:
        'p-2 bg-transparent text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20',
    },
    {
      layout: 'default',
      variant: 'primary',
      class: 'gap-1.5 px-3 min-w-fit flex-3',
    },
    {
      layout: 'default',
      variant: 'secondary',
      class: 'gap-1.5 px-2.5',
    },
  ],
  defaultVariants: {
    layout: 'default',
    variant: 'default',
  },
});

export const iconSizeVariants = cva('', {
  variants: {
    layout: {
      default: 'h-3.5 w-3.5',
      hero: 'h-4 w-4',
    },
    size: {
      sm: 'h-3.5 w-3.5',
      md: 'h-4 w-4',
      lg: 'h-5 w-5',
    },
  },
  defaultVariants: {
    layout: 'default',
  },
});

export const downloadIconVariants = cva('shrink-0', {
  variants: {
    layout: {
      default: '',
      hero: 'h-5 w-5',
    },
  },
  defaultVariants: {
    layout: 'default',
  },
});

export const downloadLabelVariants = cva('font-medium truncate', {
  variants: {
    layout: {
      default: 'text-xs',
      hero: '',
    },
  },
  defaultVariants: {
    layout: 'default',
  },
});

export type ActionButton =
  | 'download'
  | 'visibility'
  | 'delete'
  | 'copy'
  | 'collection';
export type ActionLayout = 'default' | 'hero';
