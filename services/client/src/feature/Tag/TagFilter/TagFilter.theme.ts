import { cva } from 'class-variance-authority';
import type { VariantProps } from 'class-variance-authority';

export const tagFilterContainerVariants = cva(
  'group relative rounded-xl border backdrop-blur-sm transition-all duration-300 ease-out cursor-pointer overflow-hidden active:scale-[0.995]',
  {
    variants: {
      variant: {
        default: [
          'bg-white/80 dark:bg-slate-900/80',
          'border-slate-200/60 dark:border-slate-700/60',
          'shadow-sm hover:shadow-lg shadow-slate-500/5 dark:shadow-black/10',
          'hover:bg-white/95 dark:hover:bg-slate-900/95',
          'hover:border-slate-300/70 dark:hover:border-slate-600/70',
          'ring-0 hover:ring-1 hover:ring-slate-200/50 dark:hover:ring-slate-700/50',
        ],
        neon: [
          'bg-white/90 dark:bg-slate-900/90',
          'border-indigo-200/60 dark:border-indigo-700/60',
          'shadow-[0_0_12px_rgba(99,102,241,0.08)] dark:shadow-[0_0_12px_rgba(99,102,241,0.12)]',
          'hover:border-indigo-300/80 dark:hover:border-indigo-600/80',
          'hover:shadow-[0_0_24px_rgba(99,102,241,0.18)] dark:hover:shadow-[0_0_24px_rgba(99,102,241,0.32)]',
          'hover:bg-indigo-50/70 dark:hover:bg-indigo-950/40',
          'ring-0 hover:ring-1 hover:ring-indigo-200/40 dark:hover:ring-indigo-700/40',
        ],
        minimal: [
          'bg-slate-50/60 dark:bg-slate-800/60',
          'border-slate-200/50 dark:border-slate-700/50',
          'hover:bg-slate-100/80 dark:hover:bg-slate-800/90',
          'hover:border-slate-300/60 dark:hover:border-slate-600/60',
          'shadow-none hover:shadow-md shadow-slate-500/5 dark:shadow-black/10',
        ],
      },
      disabled: {
        true: 'opacity-50 cursor-not-allowed hover:shadow-none hover:ring-0 active:scale-100',
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

export const tagFilterCheckboxVariants = cva('flex-shrink-0', {
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
});

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
  'block font-semibold cursor-pointer select-none transition-colors duration-200',
  {
    variants: {
      variant: {
        default:
          'text-slate-900 dark:text-slate-100 group-hover:text-slate-950 dark:group-hover:text-slate-50',
        neon: 'text-slate-900 dark:text-slate-100 group-hover:text-indigo-900 dark:group-hover:text-indigo-100',
        minimal:
          'text-slate-800 dark:text-slate-200 group-hover:text-slate-900 dark:group-hover:text-slate-100',
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
  'transition-colors duration-200 leading-relaxed',
  {
    variants: {
      variant: {
        default:
          'text-slate-600 dark:text-slate-400 group-hover:text-slate-700 dark:group-hover:text-slate-300',
        neon: 'text-slate-600 dark:text-slate-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-300',
        minimal:
          'text-slate-500 dark:text-slate-500 group-hover:text-slate-600 dark:group-hover:text-slate-400',
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

export const tagFilterClearButtonVariants = cva(
  'inline-flex items-center justify-center rounded-lg border backdrop-blur-sm transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-1 active:scale-95 font-medium',
  {
    variants: {
      variant: {
        default: [
          'bg-white/90 dark:bg-slate-900/90',
          'border-slate-200/60 dark:border-slate-700/60',
          'text-slate-500 hover:text-red-600 dark:hover:text-red-400',
          'hover:bg-red-50 dark:hover:bg-red-950/30',
          'hover:border-red-200/80 dark:hover:border-red-800/60',
          'focus-visible:ring-red-500/30',
          'shadow-sm hover:shadow-md',
        ],
        neon: [
          'bg-white/95 dark:bg-slate-900/95',
          'border-slate-200/60 dark:border-slate-700/60',
          'text-slate-500 hover:text-red-600 dark:hover:text-red-400',
          'hover:bg-red-50 dark:hover:bg-red-950/30',
          'hover:border-red-300/80 dark:hover:border-red-700/60',
          'focus-visible:ring-red-500/30',
          'shadow-sm hover:shadow-lg hover:shadow-red-500/10',
        ],
        minimal: [
          'bg-slate-50/80 dark:bg-slate-800/80',
          'border-slate-200/50 dark:border-slate-700/50',
          'text-slate-500 hover:text-red-600 dark:hover:text-red-400',
          'hover:bg-red-50/80 dark:hover:bg-red-950/20',
          'hover:border-red-200/60 dark:hover:border-red-800/40',
          'focus-visible:ring-red-500/20',
          'shadow-none hover:shadow-sm',
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

export type TagFilterLabelVariants = VariantProps<
  typeof tagFilterLabelVariants
>;

export type TagFilterDescriptionVariants = VariantProps<
  typeof tagFilterDescriptionVariants
>;

export type TagFilterClearButtonVariants = VariantProps<
  typeof tagFilterClearButtonVariants
>;
