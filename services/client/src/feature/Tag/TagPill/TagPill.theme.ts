import { cva } from 'class-variance-authority';
import type { VariantProps } from 'class-variance-authority';

export const tagPillVariants = cva(
  'inline-flex items-center gap-1.5 rounded-lg font-medium transition-all duration-200',
  {
    variants: {
      variant: {
        default:
          'bg-slate-100 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
        neon: [
          'bg-gradient-to-r from-blue-500/10 to-purple-500/10 text-blue-600 dark:text-blue-400',
          'border border-blue-500/20 dark:border-blue-400/30',
          'hover:from-blue-500/15 hover:to-purple-500/15',
          'focus-within:ring-2 focus-within:ring-blue-500/30 focus-within:ring-offset-2',
        ],
        minimal:
          'bg-slate-50 text-slate-600 border border-slate-100 dark:bg-slate-900/50 dark:text-slate-400 dark:border-slate-800',
      },
      size: {
        sm: 'px-2.5 py-1 text-xs',
        md: 'px-3 py-1.5 text-sm',
      },
      nested: {
        true: '',
        false: '',
      },
    },
    compoundVariants: [
      {
        variant: 'neon',
        nested: true,
        class:
          'shadow-[0_0_0_1px_rgba(59,130,246,0.15)] dark:shadow-[0_0_0_1px_rgba(96,165,250,0.2)]',
      },
    ],
    defaultVariants: {
      variant: 'neon',
      size: 'sm',
      nested: false,
    },
  },
);

export const tagPillIconVariants = cva('h-3 w-3', {
  variants: {
    variant: {
      default: 'text-slate-500',
      neon: 'text-blue-500 dark:text-blue-400',
      minimal: 'text-slate-500',
    },
  },
  defaultVariants: {
    variant: 'neon',
  },
});

export const tagPillTextVariants = cva('text-xs font-medium', {
  variants: {
    variant: {
      default: 'text-slate-700 dark:text-slate-300',
      neon: 'text-blue-700 dark:text-blue-300',
      minimal: 'text-slate-700 dark:text-slate-300',
    },
    type: {
      primary: '',
      secondary: '',
    },
  },
  compoundVariants: [
    {
      variant: 'neon',
      type: 'secondary',
      class: 'text-blue-600/60 dark:text-blue-400/60',
    },
    {
      variant: 'default',
      type: 'secondary',
      class: 'text-slate-500',
    },
    {
      variant: 'minimal',
      type: 'secondary',
      class: 'text-slate-500',
    },
  ],
  defaultVariants: {
    variant: 'neon',
    type: 'primary',
  },
});

export const tagPillBadgeVariants = cva(
  'px-1.5 py-0.5 rounded-full text-[10px] font-semibold',
  {
    variants: {
      variant: {
        default:
          'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-400',
        neon: 'bg-blue-500/20 text-blue-600 dark:bg-blue-400/20 dark:text-blue-400',
        minimal:
          'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-400',
      },
    },
    defaultVariants: {
      variant: 'neon',
    },
  },
);

export const tagPillRemoveButtonVariants = cva(
  'h-4 w-4 p-0 rounded-full ml-1 transition-colors',
  {
    variants: {
      variant: {
        default: 'hover:bg-destructive/20 text-destructive',
        neon: 'hover:bg-red-500/20 text-red-500 dark:text-red-400',
        minimal: 'hover:bg-destructive/20 text-destructive',
      },
    },
    defaultVariants: {
      variant: 'neon',
    },
  },
);

export type TagPillVariants = VariantProps<typeof tagPillVariants>;
export type TagPillIconVariants = VariantProps<typeof tagPillIconVariants>;
export type TagPillTextVariants = VariantProps<typeof tagPillTextVariants>;
export type TagPillBadgeVariants = VariantProps<typeof tagPillBadgeVariants>;
export type TagPillRemoveButtonVariants = VariantProps<
  typeof tagPillRemoveButtonVariants
>;
