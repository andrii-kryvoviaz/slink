import { cva } from 'class-variance-authority';

export const visibilityBadgeContainerTheme = cva(
  'inline-flex items-center gap-1.5 font-medium transition-all duration-200',
  {
    variants: {
      status: {
        public: '',
        private: '',
      },
      variant: {
        default: 'rounded-lg px-2.5 py-1 text-xs',
        compact: 'rounded-md px-2 py-0.5 text-[11px]',
        pill: 'rounded-full px-3 py-1 text-xs',
        overlay:
          'rounded-full px-2.5 py-1 text-[11px] font-medium backdrop-blur-md shadow-lg border',
      },
    },
    compoundVariants: [
      {
        status: 'public',
        variant: 'default',
        class:
          'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300',
      },
      {
        status: 'public',
        variant: 'compact',
        class:
          'bg-emerald-100/90 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400',
      },
      {
        status: 'public',
        variant: 'pill',
        class:
          'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-300',
      },
      {
        status: 'public',
        variant: 'overlay',
        class:
          'bg-white/95 text-emerald-600 border-emerald-300/50 dark:bg-black/60 dark:text-emerald-400 dark:border-emerald-500/30',
      },
      {
        status: 'private',
        variant: 'default',
        class:
          'bg-orange-100 text-orange-800 dark:bg-orange-500/20 dark:text-orange-300',
      },
      {
        status: 'private',
        variant: 'compact',
        class:
          'bg-orange-100/90 text-orange-700 dark:bg-orange-500/15 dark:text-orange-400',
      },
      {
        status: 'private',
        variant: 'pill',
        class:
          'bg-orange-100 text-orange-800 dark:bg-orange-500/20 dark:text-orange-300',
      },
      {
        status: 'private',
        variant: 'overlay',
        class:
          'bg-white/95 text-orange-600 border-orange-300/50 dark:bg-black/60 dark:text-orange-400 dark:border-orange-500/30',
      },
    ],
    defaultVariants: {
      status: 'public',
      variant: 'default',
    },
  },
);

export const visibilityBadgeIconTheme = cva('shrink-0', {
  variants: {
    variant: {
      default: 'w-3.5 h-3.5',
      compact: 'w-3 h-3',
      pill: 'w-3.5 h-3.5',
      overlay: 'w-3 h-3',
    },
  },
  defaultVariants: {
    variant: 'default',
  },
});

export type VisibilityStatus = 'public' | 'private';
export type VisibilityBadgeVariant = 'default' | 'compact' | 'pill' | 'overlay';
