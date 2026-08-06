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
          'rounded-full px-2.5 py-1 h-[26px] text-[11px] font-medium backdrop-blur-md shadow-lg border',
      },
    },
    compoundVariants: [
      {
        status: 'public',
        variant: 'default',
        class: 'bg-success-strong/20 text-success-subtle-foreground',
      },
      {
        status: 'public',
        variant: 'compact',
        class: 'bg-success-strong/15 text-success-subtle-foreground',
      },
      {
        status: 'public',
        variant: 'pill',
        class: 'bg-success-strong/20 text-success-subtle-foreground',
      },
      {
        status: 'public',
        variant: 'overlay',
        class:
          'bg-card/95 dark:bg-scrim/60 text-success-strong border-success-strong/30',
      },
      {
        status: 'private',
        variant: 'default',
        class: 'bg-warning-strong/20 text-warning-subtle-foreground',
      },
      {
        status: 'private',
        variant: 'compact',
        class: 'bg-warning-strong/15 text-warning-subtle-foreground',
      },
      {
        status: 'private',
        variant: 'pill',
        class: 'bg-warning-strong/20 text-warning-subtle-foreground',
      },
      {
        status: 'private',
        variant: 'overlay',
        class:
          'bg-card/95 dark:bg-scrim/60 text-warning-strong border-warning-strong/30',
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
