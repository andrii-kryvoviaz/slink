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
          'bg-card dark:bg-card/95',
          'text-foreground',
          'border-border/80',
          'backdrop-blur-sm shadow-scrim/10 dark:shadow-scrim/25',
        ],
        success: [
          'bg-card dark:bg-card/95',
          'text-foreground',
          'border-success-border/80 dark:border-success-border/21',
          'backdrop-blur-sm shadow-scrim/10 dark:shadow-scrim/25',
        ],
        danger: [
          'bg-card dark:bg-card/95',
          'text-foreground',
          'border-danger-border/80 dark:border-danger-border/21',
          'backdrop-blur-sm shadow-scrim/10 dark:shadow-scrim/25',
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
          'bg-info-wash dark:bg-info-wash/20 border border-info-border/40 dark:border-info-border/9 text-info',
        success:
          'bg-success-wash dark:bg-success-wash/20 border border-success-border/40 dark:border-success-border/9 text-success',
        danger:
          'bg-danger-wash dark:bg-danger-wash/20 border border-danger-border/40 dark:border-danger-border/9 text-danger',
      },
    },
    defaultVariants: {
      tone: 'default',
    },
  },
);

export const ActionPopoverTitleBlockTheme = cva('flex min-w-0 flex-col');

export const ActionPopoverTitleTheme = cva(
  'text-sm font-semibold text-foreground',
);

export const ActionPopoverDescriptionTheme = cva(
  'text-xs text-foreground-muted',
);

export const ActionPopoverActionsTheme = cva(
  'flex shrink-0 items-center gap-1',
);

export const ActionPopoverCloseTheme = cva(
  'inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-foreground-muted transition-colors hover:bg-hover hover:text-foreground focus:outline-none focus-visible:ring-2 focus-visible:ring-ring/30 cursor-pointer',
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

export const ActionPopoverArrowTheme = cva('fill-current text-card');

export type ActionPopoverTone = NonNullable<
  VariantProps<typeof ActionPopoverContentTheme>['tone']
>;
export type ActionPopoverSize = NonNullable<
  VariantProps<typeof ActionPopoverContentTheme>['size']
>;
