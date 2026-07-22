import { cva } from 'class-variance-authority';
import { tv } from 'tailwind-variants';

export const actionButtonVariants = cva('rounded-full', {
  variants: {
    layout: {
      default: '',
      hero: 'h-auto min-w-0 flex-1',
    },
    variant: {
      default: '',
      destructive:
        'text-red-600 dark:text-red-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30',
    },
  },
  compoundVariants: [
    {
      layout: 'hero',
      variant: 'default',
      class:
        'p-2 bg-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800',
    },
    {
      layout: 'hero',
      variant: 'destructive',
      class: 'p-2 bg-transparent',
    },
  ],
  defaultVariants: {
    layout: 'default',
    variant: 'default',
  },
});

export const shareCapsuleVariants = tv({
  slots: {
    capsule: 'flex items-stretch overflow-hidden rounded-full',
    download:
      'h-full rounded-none bg-blue-600 text-white hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 focus-visible:ring-inset focus-visible:ring-offset-0',
    copy: 'h-full rounded-none focus-visible:ring-inset focus-visible:ring-offset-0',
    caret:
      'h-full w-6 min-w-0 flex-none rounded-none border-l-[0.5px] border-gray-300 px-0 dark:border-gray-600 focus-visible:ring-inset focus-visible:ring-offset-0',
    downloadIcon: 'shrink-0',
    label: 'font-medium truncate',
  },
  variants: {
    layout: {
      default: {
        capsule: 'h-8',
        download: 'gap-1.5 px-3',
        copy: 'gap-1.5 px-2.5',
        label: 'text-xs',
      },
      hero: {
        capsule:
          'flex-1 rounded-xl shadow-sm transition-shadow hover:shadow-md',
        download: 'h-auto min-w-0 flex-1 gap-2 px-5 py-2.5 text-sm',
        downloadIcon: 'h-5 w-5',
      },
    },
  },
  compoundSlots: [
    {
      slots: ['copy', 'caret'],
      class:
        'bg-gray-200/75 text-gray-700 hover:bg-gray-200 dark:bg-gray-700/55 dark:text-gray-300 dark:hover:bg-gray-700/75',
    },
  ],
  defaultVariants: {
    layout: 'default',
  },
});

export const iconSizeVariants = cva('', {
  variants: {
    layout: {
      default: 'h-3.5 w-3.5',
      hero: 'h-4 w-4',
    },
  },
  defaultVariants: {
    layout: 'default',
  },
});

export type ActionLayout = 'default' | 'hero';
