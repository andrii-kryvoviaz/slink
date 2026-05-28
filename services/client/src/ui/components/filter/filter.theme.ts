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
          'border border-gray-200/80 dark:border-white/10',
          'hover:bg-gray-50 dark:hover:bg-gray-900/70',
          'focus-within:border-gray-300 dark:focus-within:border-white/20',
          'focus-within:bg-white dark:focus-within:bg-gray-900/80',
        ],
        neon: [
          'filter-glass',
          'border border-gray-200/60 dark:border-white/10',
          'shadow-sm',
          'hover:border-blue-300/40 dark:hover:border-blue-500/25',
          'hover:shadow-sm hover:shadow-blue-200/20 dark:hover:shadow-blue-900/10',
          'hover:bg-blue-50/15 dark:hover:bg-blue-500/6',
          'focus-within:border-blue-400/60 dark:focus-within:border-blue-500/40',
          'focus-within:shadow-md focus-within:shadow-blue-200/30 dark:focus-within:shadow-blue-900/20',
          'focus-within:bg-blue-50/25 dark:focus-within:bg-blue-500/10',
          'focus-within:ring-1 focus-within:ring-blue-300/20 dark:focus-within:ring-blue-500/15',
        ],
        minimal: [
          'filter-glass',
          'border border-gray-200/60 dark:border-white/10',
          'hover:bg-gray-50/80 dark:hover:bg-gray-900/70',
          'focus-within:border-gray-300/80 dark:focus-within:border-white/20',
        ],
        subtle: [
          'bg-gray-50/50 dark:bg-gray-900/60 backdrop-blur-sm',
          'border border-gray-200/60 dark:border-white/8',
          'hover:bg-gray-100/50 dark:hover:bg-gray-900/70',
          'focus-within:border-gray-300 dark:focus-within:border-white/15',
          'focus-within:bg-white dark:focus-within:bg-gray-900/80',
        ],
        pill: [
          'bg-white/80 dark:bg-gray-900/80',
          'border border-gray-200/60 dark:border-gray-700/60',
          'text-gray-600 dark:text-gray-400 shadow-sm',
          'hover:text-gray-900 dark:hover:text-gray-100',
          'hover:bg-white dark:hover:bg-gray-800',
          'hover:border-gray-300 dark:hover:border-gray-600',
          'hover:shadow-lg hover:shadow-gray-200/40 dark:hover:shadow-gray-900/40',
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
        class:
          'ring-2 ring-blue-500/20 border-blue-300/60 shadow-md dark:border-blue-500/40',
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
        default: [
          'bg-gray-100 dark:bg-white/10',
          'text-gray-500 dark:text-white/60',
        ],
        neon: [
          'bg-blue-50 dark:bg-blue-500/20',
          'border border-blue-200/50 dark:border-blue-500/30',
          'text-blue-600 dark:text-blue-400',
        ],
        minimal: [
          'bg-gray-100/80 dark:bg-white/10',
          'text-gray-500 dark:text-white/60',
        ],
        subtle: ['bg-transparent', 'text-gray-400 dark:text-white/50'],
        pill: [
          'bg-gray-100 dark:bg-white/10',
          'text-gray-500 dark:text-white/60',
        ],
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
      default: 'text-gray-500 dark:text-white/60',
      neon: 'text-blue-600 dark:text-blue-400',
      minimal: 'text-gray-500 dark:text-white/60',
      subtle: 'text-gray-400 dark:text-white/50',
      pill: 'text-gray-500 dark:text-white/60',
    },
  },
  defaultVariants: {
    variant: 'neon',
  },
});

export const filterLeadingIconVariants = cva('shrink-0 transition-colors', {
  variants: {
    variant: {
      default: 'text-gray-500 dark:text-white/60',
      neon: 'text-blue-600 dark:text-blue-400',
      minimal: 'text-gray-500 dark:text-white/60',
      subtle: 'text-gray-400 dark:text-white/50',
      pill: 'text-gray-400 dark:text-gray-500',
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
  'flex-1 bg-transparent border-0 outline-none min-w-0 text-gray-700 dark:text-white placeholder:text-gray-400 dark:placeholder:text-white/40 placeholder:transition-colors',
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
        minimal: 'placeholder:text-gray-500',
        subtle: '',
        pill: 'text-gray-700 dark:text-gray-200 placeholder:text-gray-400 dark:placeholder:text-gray-500',
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
        neon: 'text-blue-500/70 hover:text-blue-600 hover:bg-blue-50 dark:text-blue-400/70 dark:hover:text-blue-300 dark:hover:bg-blue-500/10',
        minimal: 'filter-clear-neutral',
        subtle: 'filter-clear-neutral',
        pill: 'text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:text-gray-300 dark:hover:bg-gray-700',
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'md',
    },
  },
);

export const filterDividerVariants = cva(
  'shrink-0 bg-gray-200 dark:bg-gray-700',
  {
    variants: {
      orientation: {
        vertical: 'w-px h-4',
        horizontal: 'h-px w-full',
      },
    },
    defaultVariants: {
      orientation: 'vertical',
    },
  },
);

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
        muted: 'text-slate-500 dark:text-slate-400',
        accent: 'text-blue-600 dark:text-blue-400',
      },
    },
    defaultVariants: {
      tone: 'muted',
    },
  },
);

export const filterChipValueVariants = cva(
  'truncate font-medium text-slate-700 dark:text-slate-200',
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
          'border border-gray-200/80 dark:border-white/10 shadow-xl shadow-gray-200/50 dark:shadow-black/60',
        neon: 'border border-gray-200/60 dark:border-white/10 shadow-2xl shadow-gray-500/5 dark:shadow-black/50 ring-1 ring-gray-100/20 dark:ring-white/5',
        minimal:
          'border border-gray-200/60 dark:border-white/10 shadow-lg shadow-gray-200/40 dark:shadow-black/50',
        subtle:
          'border border-gray-200/60 dark:border-white/10 shadow-xl shadow-gray-200/50 dark:shadow-black/60',
        pill: 'border border-gray-200/60 dark:border-gray-700/60 shadow-xl shadow-gray-200/50 dark:shadow-black/60',
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
        default: 'filter-item-blue',
        neon: 'filter-item-blue',
        minimal: [
          'text-gray-700 dark:text-white',
          'hover:bg-gray-100 dark:hover:bg-white/10',
          'aria-selected:bg-gray-100 dark:aria-selected:bg-white/10',
          'aria-selected:text-gray-900 dark:aria-selected:text-white',
        ],
        subtle: [
          'text-gray-700 dark:text-white',
          'hover:bg-gray-50 dark:hover:bg-white/8',
          'aria-selected:bg-gray-50 dark:aria-selected:bg-white/8',
        ],
        pill: 'filter-item-blue',
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
      default: 'text-gray-500 dark:text-white/60',
      neon: 'text-blue-500 dark:text-blue-400 group-hover:text-blue-600 dark:group-hover:text-blue-300 group-aria-selected:text-blue-600 dark:group-aria-selected:text-blue-300',
      minimal: 'text-gray-500 dark:text-white/60',
      subtle: 'text-gray-400 dark:text-white/50',
      pill: 'text-gray-500 dark:text-white/60',
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
        default: 'filter-item-count-blue',
        neon: 'filter-item-count-blue',
        minimal: [
          'text-gray-600 bg-gray-100/80 dark:text-white/70 dark:bg-white/10',
          'border border-gray-200/50 dark:border-white/10',
        ],
        subtle: ['text-gray-400 dark:text-white/50'],
        pill: 'filter-item-count-blue',
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
    '[scrollbar-color:theme(colors.gray.300)_transparent]',
    'dark:[scrollbar-color:theme(colors.gray.600)_transparent]',
    '[&::-webkit-scrollbar]:w-1.5',
    '[&::-webkit-scrollbar-track]:bg-transparent',
    '[&::-webkit-scrollbar-thumb]:rounded-full',
    '[&::-webkit-scrollbar-thumb]:bg-gray-300',
    'dark:[&::-webkit-scrollbar-thumb]:bg-gray-600',
    'hover:[&::-webkit-scrollbar-thumb]:bg-gray-400',
    'dark:hover:[&::-webkit-scrollbar-thumb]:bg-gray-500',
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
      'bg-white dark:bg-gray-900/60',
      'border border-t-0 border-gray-200/60 dark:border-white/10',
      'shadow-sm text-sm',
    ],
    leadIcon: 'w-3.5 h-3.5 text-blue-500 dark:text-blue-400 shrink-0',
    summary: 'text-slate-600 dark:text-slate-300',
    summaryLabel: 'hidden sm:inline',
    summaryCount: 'font-semibold text-blue-600 dark:text-blue-400',
    clearButton: [
      'ml-auto inline-flex items-center gap-1',
      'px-2 py-0.5 rounded-md',
      'text-xs font-medium',
      'text-slate-400 dark:text-slate-500',
      'hover:text-red-600 dark:hover:text-red-400',
      'hover:bg-red-50 dark:hover:bg-red-950/30',
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
