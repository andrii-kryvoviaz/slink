import { cva } from 'class-variance-authority';
import { tv } from 'tailwind-variants';

import type { ImageListingItem } from '@slink/api/Response';

import type { ActionButton } from '../ActionBar/ImageActionBar.theme';

export const historyActionBarButtons: ActionButton[] = [
  'download',
  'collection',
  'tag',
  'copy',
  'visibility',
  'delete',
];

export function createActionBarImage(item: ImageListingItem) {
  return {
    id: item.id,
    fileName: item.attributes.fileName,
    isPublic: item.attributes.isPublic,
    collectionIds: item.collections?.map((c) => c.id),
    tagIds: item.tags?.map((t) => t.id),
  };
}

export const historyCardVariants = cva(
  'group break-inside-avoid overflow-hidden rounded-lg border bg-white dark:bg-gray-900/60 transition-all duration-200 hover:shadow-md dark:hover:shadow-gray-900/50',
  {
    variants: {
      selected: {
        true: 'ring-2 ring-blue-500 border-blue-400 dark:border-blue-500',
        false:
          'border-gray-200 dark:border-gray-800 hover:border-gray-300 dark:hover:border-gray-700/80',
      },
    },
    defaultVariants: {
      selected: false,
    },
  },
);

export const historyListRowVariants = cva(
  'group relative flex flex-col @xl:flex-row w-full overflow-hidden rounded-lg border bg-white dark:bg-gray-900/60 transition-all duration-200 hover:shadow-md dark:hover:shadow-gray-900/50',
  {
    variants: {
      selected: {
        true: 'bg-blue-50/80 dark:bg-blue-500/10 border-blue-300 dark:border-blue-500 ring-2 ring-blue-500',
        false:
          'border-gray-200 dark:border-gray-800 hover:border-gray-300 dark:hover:border-gray-700',
      },
      selectionMode: {
        true: 'cursor-pointer',
        false: '',
      },
    },
    defaultVariants: {
      selected: false,
      selectionMode: false,
    },
  },
);

export const actionBarVisibilityVariants = cva('absolute top-2 right-2', {
  variants: {
    selectionMode: {
      true: 'opacity-0 pointer-events-none',
      false:
        '[@media(hover:hover)]:opacity-0 [@media(hover:hover)]:group-hover:opacity-100 [@media(hover:none)]:opacity-100 [&:has([data-state=open])]:opacity-100 transition-opacity duration-200',
    },
  },
  defaultVariants: {
    selectionMode: false,
  },
});

export const historyItemActionsVariants = tv({
  slots: {
    bar: '@max-2xl:hidden',
    menu: '@2xl:hidden',
  },
  variants: {
    layout: {
      table: {
        bar: 'flex items-center justify-end',
      },
      list: {
        bar: 'shrink-0',
        menu: 'shrink-0',
      },
    },
    hoverReveal: {
      true: {},
      false: {},
    },
    selectionMode: {
      true: {
        bar: 'opacity-0 pointer-events-none',
        menu: 'opacity-0 pointer-events-none',
      },
      false: {},
    },
  },
  compoundVariants: [
    {
      hoverReveal: true,
      selectionMode: false,
      class: {
        bar: 'opacity-0 group-hover:opacity-100 @2xl:opacity-100 [&:has([data-state=open])]:opacity-100 transition-opacity duration-200',
      },
    },
  ],
  defaultVariants: {
    layout: 'table',
    hoverReveal: false,
    selectionMode: false,
  },
});

export const checkboxVariants = cva(
  'absolute top-2 left-2 z-20 transition-opacity duration-150',
  {
    variants: {
      selectionMode: {
        true: 'opacity-100',
        false:
          'opacity-0 [@media(hover:hover)]:group-hover:opacity-100 [@media(hover:none)]:hidden',
      },
    },
    defaultVariants: {
      selectionMode: false,
    },
  },
);

export const linkVariants = cva('block', {
  variants: {
    selectionMode: {
      true: 'pointer-events-none',
      false: '',
    },
  },
  defaultVariants: {
    selectionMode: false,
  },
});
