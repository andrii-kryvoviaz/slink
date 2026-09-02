import { cva } from 'class-variance-authority';
import type { VariantProps } from 'class-variance-authority';
import { tv } from 'tailwind-variants';

export const filterContainerVariants = cva(
  'flex items-center gap-2 transition-all duration-200 relative',
  {
    variants: {
      variant: {
        default: [
          'filter-glass',
          'border border-border/80',
          'hover:bg-hover',
          'focus-within:border-border-strong dark:focus-within:border-border',
          'focus-within:bg-card',
        ],
        neon: [
          'filter-glass',
          'border border-border/60',
          'shadow-sm',
          'hover:border-info/25',
          'hover:shadow-info-border/20 dark:hover:shadow-info-border/6 hover:shadow-sm',
          'hover:bg-primary-solid/6',
          'focus-within:border-info/40',
          'focus-within:shadow-info-border/30 dark:focus-within:shadow-info-border/9 focus-within:shadow-md',
          'focus-within:bg-primary-solid/10',
          'focus-within:ring-info/15 focus-within:ring-1',
        ],
        minimal: [
          'filter-glass',
          'border border-border/60',
          'hover:bg-hover',
          'focus-within:border-border-strong/80 dark:focus-within:border-border',
        ],
        subtle: [
          'bg-muted-soft backdrop-blur-sm',
          'border border-border/60',
          'hover:bg-hover',
          'focus-within:border-border-strong dark:focus-within:border-border',
          'focus-within:bg-card',
        ],
        pill: [
          'bg-card/80',
          'border border-border/60',
          'text-foreground-muted shadow-sm',
          'hover:text-foreground',
          'hover:bg-card dark:hover:bg-muted',
          'hover:border-border-strong',
          'hover:shadow-border/40 hover:shadow-lg dark:hover:shadow-card/40',
        ],
      },
      size: {
        sm: 'text-xs min-h-8 px-3',
        md: 'text-sm min-h-11 px-3 py-2',
        lg: 'text-base min-h-12 px-4 py-2.5',
      },
      rounded: {
        md: 'rounded-md',
        lg: 'rounded-lg',
        xl: 'rounded-xl',
        full: 'rounded-full',
      },
      disabled: {
        true: 'opacity-50 cursor-not-allowed',
        false: '',
      },
      open: {
        true: '',
        false: '',
      },
      wrap: {
        true: 'flex-wrap',
        false: 'flex-nowrap',
      },
      hasActiveSummary: {
        true: 'rounded-b-none border-b-0',
        false: '',
      },
    },
    compoundVariants: [
      {
        variant: 'pill',
        open: true,
        class: 'ring-info/20 border-info/40 shadow-md ring-2',
      },
    ],
    defaultVariants: {
      variant: 'default',
      size: 'md',
      rounded: 'lg',
      disabled: false,
      open: false,
      wrap: false,
      hasActiveSummary: false,
    },
  },
);

export const filterIconBoxVariants = cva(
  'shrink-0 w-7 h-7 rounded-lg flex items-center justify-center',
  {
    variants: {
      variant: {
        default: ['bg-muted', 'text-foreground-muted'],
        neon: ['bg-primary-solid/20', 'border border-info/30', 'text-info'],
        minimal: ['bg-muted/80', 'text-foreground-muted'],
        subtle: ['bg-transparent', 'text-foreground-subtle'],
        pill: ['bg-muted', 'text-foreground-muted'],
      },
    },
    defaultVariants: {
      variant: 'neon',
    },
  },
);

export const filterIconGlyphVariants = cva('h-3.5 w-3.5 transition-colors', {
  variants: {
    variant: {
      default: 'text-foreground-muted',
      neon: 'text-info',
      minimal: 'text-foreground-muted',
      subtle: 'text-foreground-subtle',
      pill: 'text-foreground-muted',
    },
  },
  defaultVariants: {
    variant: 'neon',
  },
});

export const filterLeadingIconVariants = cva('shrink-0 transition-colors', {
  variants: {
    variant: {
      default: 'text-foreground-muted',
      neon: 'text-info',
      minimal: 'text-foreground-muted',
      subtle: 'text-foreground-subtle',
      pill: 'text-foreground-subtle',
    },
    size: {
      sm: 'w-3.5 h-3.5',
      md: 'w-4 h-4',
      lg: 'w-5 h-5',
    },
  },
  defaultVariants: {
    variant: 'default',
    size: 'md',
  },
});

export const filterFieldVariants = cva(
  'flex-1 bg-transparent border-0 outline-none min-w-0 text-foreground-soft dark:text-foreground placeholder:text-foreground-subtle placeholder:transition-colors',
  {
    variants: {
      size: {
        sm: 'text-xs',
        md: 'text-sm',
        lg: 'text-base',
      },
      variant: {
        default: '',
        neon: '',
        minimal:
          'placeholder:text-foreground-muted dark:placeholder:text-foreground-subtle',
        subtle: '',
        pill: 'text-foreground-soft placeholder:text-foreground-subtle',
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'md',
    },
  },
);

export const filterClearButtonVariants = cva(
  'shrink-0 rounded-full transition-colors duration-150 flex items-center justify-center',
  {
    variants: {
      size: {
        sm: 'p-0.5 w-4 h-4',
        md: 'p-1 w-5 h-5',
        lg: 'p-1 w-6 h-6',
      },
      variant: {
        default: 'filter-clear-neutral',
        neon: 'text-info/70 hover:text-info-text hover:bg-primary-solid/10',
        minimal: 'filter-clear-neutral',
        subtle: 'filter-clear-neutral',
        pill: 'text-foreground-subtle hover:text-foreground-soft hover:bg-hover-strong dark:text-foreground-muted',
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'md',
    },
  },
);

export const filterDividerVariants = cva('shrink-0 bg-border', {
  variants: {
    orientation: {
      vertical: 'w-px h-4',
      horizontal: 'h-px w-full',
    },
  },
  defaultVariants: {
    orientation: 'vertical',
  },
});

export const filterGroupVariants = cva('flex min-w-0', {
  variants: {
    direction: {
      row: 'flex-row',
      col: 'flex-col w-full',
      responsive: 'flex-col w-full',
    },
    breakpoint: {
      sm: '',
      md: '',
      lg: '',
    },
    gap: {
      none: 'gap-0',
      sm: 'gap-2',
      md: 'gap-3',
      lg: 'gap-4',
    },
    grow: {
      true: 'flex-1',
      false: '',
    },
    align: {
      start: 'items-start',
      center: 'items-center',
      end: 'items-end',
      stretch: 'items-stretch',
      none: '',
    },
  },
  compoundVariants: [
    {
      direction: 'responsive',
      breakpoint: 'sm',
      class: 'sm:w-auto sm:flex-row sm:items-center',
    },
    {
      direction: 'responsive',
      breakpoint: 'md',
      class: 'md:w-auto md:flex-row md:items-center',
    },
    {
      direction: 'responsive',
      breakpoint: 'lg',
      class: 'lg:w-auto lg:flex-row lg:items-center',
    },
  ],
  defaultVariants: {
    direction: 'row',
    breakpoint: 'lg',
    gap: 'md',
    grow: false,
    align: 'center',
  },
});

export const filterChipVariants = cva(
  'inline-flex items-center gap-1 text-xs',
  {
    variants: {
      tone: {
        muted: 'text-foreground-muted',
        accent: 'text-info',
      },
    },
    defaultVariants: {
      tone: 'muted',
    },
  },
);

export const filterChipValueVariants = cva(
  'truncate font-medium text-foreground-soft',
  {
    variants: {
      maxWidth: {
        sm: 'max-w-[120px]',
        md: 'max-w-[160px]',
        lg: 'max-w-[240px]',
        none: '',
      },
    },
    defaultVariants: {
      maxWidth: 'md',
    },
  },
);

export const filterContentVariants = cva(
  [
    'z-50 w-[var(--bits-popover-anchor-width)] overflow-hidden',
    'filter-glass-popover',
    'data-[state=open]:animate-in data-[state=closed]:animate-out',
    'data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0',
    'data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95',
    'data-[side=bottom]:slide-in-from-top-2',
  ],
  {
    variants: {
      variant: {
        default:
          'border-border/80 shadow-border/50 dark:shadow-scrim/60 border shadow-xl',
        neon: 'border-border/60 shadow-foreground-muted/5 ring-muted/20 dark:shadow-scrim/50 dark:ring-border/50 border shadow-2xl ring-1',
        minimal:
          'border-border/60 shadow-border/40 dark:shadow-scrim/50 border shadow-lg',
        subtle:
          'border-border/60 shadow-border/50 dark:shadow-scrim/60 border shadow-xl',
        pill: 'border-border/60 shadow-border/50 dark:shadow-scrim/60 border shadow-xl',
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  },
);

export const filterItemVariants = cva(
  'group relative flex w-full cursor-pointer select-none items-center gap-2 px-3 py-2 text-sm font-medium rounded-lg outline-none transition-all duration-200',
  {
    variants: {
      variant: {
        default: 'filter-item-info',
        neon: 'filter-item-info',
        minimal: [
          'text-foreground-soft dark:text-foreground',
          'hover:bg-hover',
          'aria-selected:bg-hover',
          'aria-selected:text-foreground',
        ],
        subtle: [
          'text-foreground-soft dark:text-foreground',
          'hover:bg-hover',
          'aria-selected:bg-hover',
        ],
        pill: 'filter-item-info',
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  },
);

export const filterItemIconVariants = cva('h-4 w-4 shrink-0', {
  variants: {
    variant: {
      default: 'text-foreground-muted',
      neon: 'text-info group-hover:text-info-text group-aria-selected:text-info-text',
      minimal: 'text-foreground-muted',
      subtle: 'text-foreground-subtle',
      pill: 'text-foreground-muted',
    },
  },
  defaultVariants: {
    variant: 'default',
  },
});

export const filterItemCountVariants = cva(
  'text-xs font-medium px-2 py-0.5 rounded-md shrink-0 transition-all duration-200',
  {
    variants: {
      variant: {
        default: 'filter-item-count-info',
        neon: 'filter-item-count-info',
        minimal: [
          'text-foreground-muted bg-muted/80',
          'border border-border/50',
        ],
        subtle: ['text-foreground-subtle'],
        pill: 'filter-item-count-info',
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  },
);

export const filterScrollVariants = cva(
  [
    'overflow-y-auto overflow-x-hidden',
    '[scrollbar-width:thin]',
    '[scrollbar-color:var(--color-border-strong)_transparent]',
    '[&::-webkit-scrollbar]:w-1.5',
    '[&::-webkit-scrollbar-track]:bg-transparent',
    '[&::-webkit-scrollbar-thumb]:rounded-full',
    '[&::-webkit-scrollbar-thumb]:bg-border-strong',
    'hover:[&::-webkit-scrollbar-thumb]:bg-ring',
  ],
  {
    variants: {
      maxHeight: {
        sm: 'max-h-[240px]',
        md: 'max-h-[320px]',
        lg: 'max-h-[400px]',
        xl: 'max-h-[520px]',
      },
    },
    defaultVariants: {
      maxHeight: 'lg',
    },
  },
);

export const filterSummaryVariants = tv({
  slots: {
    root: [
      'mx-auto w-[calc(100%-1.5rem)]',
      'flex flex-wrap items-center gap-x-2 gap-y-1.5',
      'px-3 py-2 rounded-b-lg',
      'bg-card',
      'border border-t-0 border-border/60',
      'shadow-sm text-sm',
    ],
    leadIcon: 'w-3.5 h-3.5 text-info shrink-0',
    summary: 'text-foreground-soft',
    summaryLabel: 'hidden sm:inline',
    summaryCount: 'font-semibold text-info',
    clearButton: [
      'ml-auto inline-flex items-center gap-1',
      'px-2 py-0.5 rounded-md',
      'text-xs font-medium',
      'text-foreground-subtle',
      'hover:text-danger',
      'hover:bg-danger-wash dark:hover:bg-danger-wash/20',
      'transition-all duration-200',
      'disabled:opacity-50 disabled:cursor-not-allowed',
    ],
  },
});

export type FilterVariant = NonNullable<
  VariantProps<typeof filterContainerVariants>['variant']
>;
export type FilterSize = NonNullable<
  VariantProps<typeof filterContainerVariants>['size']
>;
export type FilterRounded = NonNullable<
  VariantProps<typeof filterContainerVariants>['rounded']
>;
export type FilterScrollMaxHeight = NonNullable<
  VariantProps<typeof filterScrollVariants>['maxHeight']
>;
