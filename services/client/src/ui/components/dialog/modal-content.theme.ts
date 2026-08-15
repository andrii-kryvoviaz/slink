import { cva } from 'class-variance-authority';

export type ModalVariant = 'blue' | 'green' | 'purple' | 'amber';
export type NoticeVariant = 'info' | 'warning' | 'success';

export const modalIconContainer = cva(
  [
    'w-10 h-10 rounded-lg flex items-center justify-center',
    'border flex-shrink-0',
  ],
  {
    variants: {
      variant: {
        blue: [
          'bg-info-wash/80 dark:bg-info-wash/16',
          'border-info-border/60 dark:border-info-border/18',
        ],
        green: [
          'bg-success-wash/80 dark:bg-success-wash/16',
          'border-success-border/60 dark:border-success-border/18',
        ],
        purple: ['bg-accent/12', 'border-accent/32 dark:border-accent/45'],
        amber: [
          'bg-warning-wash/80 dark:bg-warning-wash/16',
          'border-warning-border/60 dark:border-warning-border/18',
        ],
      },
    },
    defaultVariants: {
      variant: 'blue',
    },
  },
);

export const modalIcon = cva(['[&>svg]:h-5 [&>svg]:w-5'], {
  variants: {
    variant: {
      blue: 'text-info',
      green: 'text-success',
      purple: 'text-accent',
      amber: 'text-warning',
    },
  },
  defaultVariants: {
    variant: 'blue',
  },
});

export const noticeContainer = cva(
  [
    'relative rounded-xl p-4',
    'backdrop-blur-sm ring-1 ring-on-surface-inverse/[0.08]',
  ],
  {
    variants: {
      variant: {
        info: 'bg-info/[0.06]',
        warning: 'bg-warning/[0.06]',
        success: 'bg-success/[0.06]',
      },
    },
    defaultVariants: {
      variant: 'info',
    },
  },
);

export const noticeOverlay = cva(['hidden'], {
  variants: {
    variant: {
      info: '',
      warning: '',
      success: '',
    },
  },
  defaultVariants: {
    variant: 'info',
  },
});

export const noticeIconContainer = cva(
  [
    'w-8 h-8 rounded-lg flex items-center justify-center',
    'backdrop-blur-sm ring-1 ring-on-surface-inverse/[0.08]',
  ],
  {
    variants: {
      variant: {
        info: 'bg-info/10',
        warning: 'bg-warning/10',
        success: 'bg-success/10',
      },
    },
    defaultVariants: {
      variant: 'info',
    },
  },
);

export const noticeIcon = cva(['[&>svg]:h-4 [&>svg]:w-4'], {
  variants: {
    variant: {
      info: 'text-info',
      warning: 'text-warning',
      success: 'text-success',
    },
  },
  defaultVariants: {
    variant: 'info',
  },
});

export const noticeTitle = cva(['text-sm font-semibold leading-tight mb-1'], {
  variants: {
    variant: {
      info: 'text-info-text',
      warning: 'text-warning-text',
      success: 'text-success-text',
    },
  },
  defaultVariants: {
    variant: 'info',
  },
});

export const noticeText = cva(['text-sm leading-relaxed'], {
  variants: {
    variant: {
      info: 'text-info-text/80 dark:text-info-text/70',
      warning: 'text-warning-text/80 dark:text-warning-text/70',
      success: 'text-success-text/80 dark:text-success-text/70',
    },
  },
  defaultVariants: {
    variant: 'info',
  },
});

export const buttonVariantMap: Record<ModalVariant, string> = {
  blue: 'outline-blue',
  green: 'outline-green',
  purple: 'outline-accent',
  amber: 'outline-amber',
};
