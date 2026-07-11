import { cva } from 'class-variance-authority';

export const statDisclosureContainerTheme = cva(['rounded-lg', 'border'], {
  variants: {
    variant: {
      indigo: [
        'border-indigo-200/50 dark:border-indigo-500/20',
        'bg-gradient-to-br from-indigo-50/80 via-violet-50/60 to-purple-50/50',
        'dark:from-indigo-950/30 dark:via-violet-950/20 dark:to-purple-950/10',
      ],
    },
  },
  defaultVariants: {
    variant: 'indigo',
  },
});

export const statDisclosureTriggerTheme = cva(
  [
    'group',
    'w-full',
    'flex items-center gap-3 p-4',
    'cursor-pointer',
    'rounded-lg',
    'transition-colors duration-150',
  ],
  {
    variants: {
      variant: {
        indigo: ['hover:bg-indigo-100/50 dark:hover:bg-indigo-900/20'],
      },
    },
    defaultVariants: {
      variant: 'indigo',
    },
  },
);

export const statDisclosureIconTileTheme = cva(
  [
    'flex-shrink-0 w-10 h-10',
    'rounded-lg',
    'flex items-center justify-center',
    'shadow-sm',
  ],
  {
    variants: {
      variant: {
        indigo: [
          'bg-gradient-to-br from-indigo-500 to-violet-500',
          'shadow-indigo-500/20 dark:shadow-indigo-500/10',
        ],
      },
    },
    defaultVariants: {
      variant: 'indigo',
    },
  },
);

export const statDisclosureIconTheme = cva(['w-5 h-5 text-white']);

export const statDisclosureLabelTheme = cva(
  ['text-xs font-medium uppercase tracking-wide'],
  {
    variants: {
      variant: {
        indigo: ['text-indigo-600/70 dark:text-indigo-400/70'],
      },
    },
    defaultVariants: {
      variant: 'indigo',
    },
  },
);

export const statDisclosureValueTheme = cva([
  'text-sm font-semibold',
  'text-gray-900 dark:text-white',
]);

export const statDisclosureChevronTheme = cva([
  'w-5 h-5 flex-shrink-0',
  'text-gray-400 dark:text-gray-500',
  'transition-transform duration-200',
  'group-data-[state=open]:rotate-180',
]);

export const statDisclosureContentTheme = cva([
  'overflow-hidden',
  'data-[state=closed]:animate-collapsible-up',
  'data-[state=open]:animate-collapsible-down',
]);

export type StatDisclosureVariant = 'indigo';
