import { cva } from 'class-variance-authority';
import type { VariantProps } from 'class-variance-authority';

export const tagFilterContainerVariants = cva(
  'group/filter relative rounded-xl border backdrop-blur-sm transition-all duration-300 ease-out cursor-pointer overflow-hidden active:scale-[0.995] shadow-sm',
  {
    variants: {
      variant: {
        default: [
          'bg-white dark:bg-slate-900',
          'border-slate-200/60 dark:border-slate-700/60',
          'hover:border-blue-300/70 dark:hover:border-blue-600/50',
          'hover:shadow-md hover:shadow-blue-200/40 dark:hover:shadow-blue-900/30',
          'hover:bg-blue-50/30 dark:hover:bg-blue-950/20',
        ],
        neon: [
          'bg-white dark:bg-slate-900',
          'border-slate-200/60 dark:border-slate-700/60',
          'hover:border-blue-300/70 dark:hover:border-blue-600/50',
          'hover:shadow-md hover:shadow-blue-200/40 dark:hover:shadow-blue-900/30',
          'hover:bg-blue-50/30 dark:hover:bg-blue-950/20',
        ],
        minimal: [
          'bg-white dark:bg-slate-900',
          'border-slate-200/60 dark:border-slate-700/60',
          'hover:border-blue-300/70 dark:hover:border-blue-500/50',
          'hover:shadow-md hover:shadow-blue-100/50 dark:hover:shadow-blue-900/30',
          'hover:bg-blue-50/30 dark:hover:bg-blue-950/20',
        ],
      },
      disabled: {
        true: 'opacity-50 cursor-not-allowed active:scale-100',
        false: '',
      },
    },
    defaultVariants: {
      variant: 'neon',
      disabled: false,
    },
  },
);

export const tagFilterContentVariants = cva(
  'relative z-10 flex items-center gap-4 px-4 py-3.5',
  {
    variants: {
      variant: {
        default: '',
        neon: '',
        minimal: 'px-3 py-3',
      },
    },
    defaultVariants: {
      variant: 'neon',
    },
  },
);

export const tagFilterCheckboxVariants = cva(
  'flex-shrink-0 p-1 rounded-md transition-all duration-300',
  {
    variants: {
      variant: {
        default:
          'bg-gray-50/50 dark:bg-gray-800/50 group-hover/filter:bg-gray-100/70 dark:group-hover/filter:bg-gray-700/70',
        neon: 'bg-gray-50/50 dark:bg-gray-800/50 group-hover/filter:bg-gray-100/70 dark:group-hover/filter:bg-gray-700/70',
        minimal:
          'bg-gray-50/50 dark:bg-gray-800/50 group-hover/filter:bg-gray-100/70 dark:group-hover/filter:bg-gray-700/70',
      },
    },
    defaultVariants: {
      variant: 'neon',
    },
  },
);

export const tagFilterCheckboxInputVariants = cva(
  'border border-gray-400 dark:border-gray-500 data-[state=unchecked]:border-gray-400 dark:data-[state=unchecked]:border-gray-500 group-hover/filter:border-gray-500 dark:group-hover/filter:border-gray-400 focus-visible:ring-0 transition-colors duration-300',
  {
    variants: {
      variant: {
        default: '',
        neon: '',
        minimal: '',
      },
    },
    defaultVariants: {
      variant: 'neon',
    },
  },
);

export const tagFilterTextVariants = cva('flex-1 space-y-1', {
  variants: {
    variant: {
      default: '',
      neon: '',
      minimal: 'space-y-0.5',
    },
  },
  defaultVariants: {
    variant: 'neon',
  },
});

export const tagFilterLabelVariants = cva(
  'block font-semibold cursor-pointer select-none transition-colors duration-300',
  {
    variants: {
      variant: {
        default:
          'text-gray-900 dark:text-gray-100 group-hover/filter:text-gray-950 dark:group-hover/filter:text-gray-50',
        neon: 'text-gray-900 dark:text-gray-100 group-hover/filter:text-gray-950 dark:group-hover/filter:text-gray-50',
        minimal:
          'text-gray-900 dark:text-gray-100 group-hover/filter:text-gray-950 dark:group-hover/filter:text-gray-50',
      },
      size: {
        sm: 'text-xs',
        md: 'text-sm',
        lg: 'text-base',
      },
    },
    defaultVariants: {
      variant: 'neon',
      size: 'md',
    },
  },
);

export const tagFilterDescriptionVariants = cva(
  'transition-colors duration-300 leading-relaxed',
  {
    variants: {
      variant: {
        default:
          'text-gray-600 dark:text-gray-400 group-hover/filter:text-gray-700 dark:group-hover/filter:text-gray-300',
        neon: 'text-gray-600 dark:text-gray-400 group-hover/filter:text-gray-700 dark:group-hover/filter:text-gray-300',
        minimal:
          'text-gray-600 dark:text-gray-400 group-hover/filter:text-gray-700 dark:group-hover/filter:text-gray-300',
      },
      size: {
        sm: 'text-[10px]',
        md: 'text-xs',
        lg: 'text-sm',
      },
    },
    defaultVariants: {
      variant: 'neon',
      size: 'md',
    },
  },
);

export const tagFilterCountBadgeVariants = cva(
  'inline-flex items-center ml-2 px-1.5 py-0.5 rounded-md text-[10px] font-medium transition-colors duration-200',
  {
    variants: {
      variant: {
        default:
          'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400',
        neon: 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400',
        minimal:
          'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400',
      },
    },
    defaultVariants: {
      variant: 'neon',
    },
  },
);

export const tagFilterClearButtonVariants = cva(
  'inline-flex items-center justify-center rounded-lg border backdrop-blur-sm transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-1 active:scale-95 font-medium',
  {
    variants: {
      variant: {
        default: [
          'bg-white/90 dark:bg-gray-900/90',
          'border-gray-200/60 dark:border-gray-700/60',
          'text-gray-500 hover:text-red-600 dark:hover:text-red-400',
          'hover:bg-red-50 dark:hover:bg-red-950/30',
          'hover:border-red-200/80 dark:hover:border-red-800/60',
          'focus-visible:ring-red-500/30',
          'shadow-sm hover:shadow-md',
        ],
        neon: [
          'bg-white/90 dark:bg-gray-900/90',
          'border-gray-200/60 dark:border-gray-700/60',
          'text-gray-500 hover:text-red-600 dark:hover:text-red-400',
          'hover:bg-red-50 dark:hover:bg-red-950/30',
          'hover:border-red-200/80 dark:hover:border-red-800/60',
          'focus-visible:ring-red-500/30',
          'shadow-sm hover:shadow-md',
        ],
        minimal: [
          'bg-white/90 dark:bg-gray-900/90',
          'border-gray-200/60 dark:border-gray-700/60',
          'text-gray-500 hover:text-red-600 dark:hover:text-red-400',
          'hover:bg-red-50 dark:hover:bg-red-950/30',
          'hover:border-red-200/80 dark:hover:border-red-800/60',
          'focus-visible:ring-red-500/30',
          'shadow-sm hover:shadow-md',
        ],
      },
      size: {
        sm: 'h-7 px-2.5 text-xs',
        md: 'h-8 px-3 text-sm',
        lg: 'h-9 px-4 text-base',
      },
    },
    defaultVariants: {
      variant: 'neon',
      size: 'md',
    },
  },
);

export type TagFilterContainerVariants = VariantProps<
  typeof tagFilterContainerVariants
>;

export type TagFilterContentVariants = VariantProps<
  typeof tagFilterContentVariants
>;

export type TagFilterCheckboxVariants = VariantProps<
  typeof tagFilterCheckboxVariants
>;

export type TagFilterTextVariants = VariantProps<typeof tagFilterTextVariants>;

export type TagFilterCountBadgeVariants = VariantProps<
  typeof tagFilterCountBadgeVariants
>;

export type TagFilterLabelVariants = VariantProps<
  typeof tagFilterLabelVariants
>;

export type TagFilterDescriptionVariants = VariantProps<
  typeof tagFilterDescriptionVariants
>;

export type TagFilterClearButtonVariants = VariantProps<
  typeof tagFilterClearButtonVariants
>;

export type TagFilterCheckboxInputVariants = VariantProps<
  typeof tagFilterCheckboxInputVariants
>;
