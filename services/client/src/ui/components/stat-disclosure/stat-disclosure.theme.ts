import { cva } from 'class-variance-authority';

export const statDisclosureContainerTheme = cva(['rounded-lg', 'border'], {
  variants: {
    variant: {
      indigo: [
        'border-accent-border/50',
        'bg-gradient-to-br from-accent-subtle/80 via-accent-subtle-strong/60 to-accent-subtle/50',
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
        indigo: ['hover:bg-accent-subtle/50'],
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
          'bg-gradient-to-br from-accent to-accent-strong',
          'shadow-accent/20 dark:shadow-accent/10',
        ],
      },
    },
    defaultVariants: {
      variant: 'indigo',
    },
  },
);

export const statDisclosureIconTheme = cva(['w-5 h-5 text-accent-foreground']);

export const statDisclosureLabelTheme = cva(
  ['text-xs font-medium uppercase tracking-wide'],
  {
    variants: {
      variant: {
        indigo: ['text-accent/70'],
      },
    },
    defaultVariants: {
      variant: 'indigo',
    },
  },
);

export const statDisclosureValueTheme = cva([
  'text-sm font-semibold',
  'text-foreground',
]);

export const statDisclosureChevronTheme = cva([
  'w-5 h-5 flex-shrink-0',
  'text-foreground-subtle',
  'transition-transform duration-200',
  'group-data-[state=open]:rotate-180',
]);

export const statDisclosureContentTheme = cva([
  'overflow-hidden',
  'data-[state=closed]:animate-collapsible-up',
  'data-[state=open]:animate-collapsible-down',
]);

export type StatDisclosureVariant = 'indigo';
