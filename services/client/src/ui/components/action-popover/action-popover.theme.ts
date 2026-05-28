import { cva } from 'class-variance-authority';
import type { VariantProps } from 'class-variance-authority';

export const ActionPopoverContentTheme = cva(
  [
    'z-50 outline-none',
    'rounded-xl border shadow-xl',
    'data-[state=open]:animate-in data-[state=closed]:animate-out',
    'data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0',
    'data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95',
    'data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2',
    'origin-(--bits-popover-content-transform-origin)',
  ],
  {
    variants: {
      tone: {
        default: [
          'bg-white dark:bg-gray-900/95',
          'text-gray-900 dark:text-gray-100',
          'border-gray-200/80 dark:border-gray-700/80',
          'backdrop-blur-sm shadow-black/10 dark:shadow-black/25',
        ],
        success: [
          'bg-white dark:bg-gray-900/95',
          'text-gray-900 dark:text-gray-100',
          'border-emerald-200/80 dark:border-emerald-700/70',
          'backdrop-blur-sm shadow-black/10 dark:shadow-black/25',
        ],
        danger: [
          'bg-white dark:bg-gray-900/95',
          'text-gray-900 dark:text-gray-100',
          'border-red-200/80 dark:border-red-700/70',
          'backdrop-blur-sm shadow-black/10 dark:shadow-black/25',
        ],
      },
      size: {
        sm: 'p-3',
        md: 'p-4',
        lg: 'p-5',
      },
    },
    defaultVariants: {
      tone: 'default',
      size: 'md',
    },
  },
);

export const ActionPopoverHeaderTheme = cva(
  'flex items-start justify-between gap-3',
);

export const ActionPopoverIconBoxTheme = cva(
  [
    'flex h-10 w-10 shrink-0 items-center justify-center',
    'rounded-full shadow-sm',
  ],
  {
    variants: {
      tone: {
        default:
          'bg-blue-100 dark:bg-blue-900/30 border border-blue-200/40 dark:border-blue-800/30 text-blue-600 dark:text-blue-400',
        success:
          'bg-emerald-100 dark:bg-emerald-900/30 border border-emerald-200/40 dark:border-emerald-800/30 text-emerald-600 dark:text-emerald-400',
        danger:
          'bg-red-100 dark:bg-red-900/30 border border-red-200/40 dark:border-red-800/30 text-red-600 dark:text-red-400',
      },
    },
    defaultVariants: {
      tone: 'default',
    },
  },
);

export const ActionPopoverTitleBlockTheme = cva('flex min-w-0 flex-col');

export const ActionPopoverTitleTheme = cva(
  'text-sm font-semibold text-gray-900 dark:text-white',
);

export const ActionPopoverDescriptionTheme = cva(
  'text-xs text-gray-500 dark:text-gray-400',
);

export const ActionPopoverActionsTheme = cva(
  'flex shrink-0 items-center gap-1',
);

export const ActionPopoverCloseTheme = cva(
  'inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500/30 dark:text-gray-400 dark:hover:bg-gray-800/60 dark:hover:text-white cursor-pointer',
);

export const ActionPopoverBodyTheme = cva('', {
  variants: {
    hasHeader: {
      true: 'mt-4',
      false: '',
    },
  },
  defaultVariants: {
    hasHeader: false,
  },
});

export const ActionPopoverFooterTheme = cva('mt-4 flex items-center gap-2');

export const ActionPopoverArrowTheme = cva(
  'fill-current text-white dark:text-gray-900',
);

export type ActionPopoverTone = NonNullable<
  VariantProps<typeof ActionPopoverContentTheme>['tone']
>;
export type ActionPopoverSize = NonNullable<
  VariantProps<typeof ActionPopoverContentTheme>['size']
>;
