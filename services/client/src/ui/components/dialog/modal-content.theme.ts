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
          'bg-blue-50/80 dark:bg-blue-950/80',
          'border-blue-200/60 dark:border-blue-800/60',
        ],
        green: [
          'bg-green-50/80 dark:bg-green-950/80',
          'border-green-200/60 dark:border-green-800/60',
        ],
        purple: [
          'bg-indigo-50/80 dark:bg-indigo-950/80',
          'border-indigo-200/60 dark:border-indigo-800/60',
        ],
        amber: [
          'bg-amber-50/80 dark:bg-amber-950/80',
          'border-amber-200/60 dark:border-amber-800/60',
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
      blue: 'text-blue-600 dark:text-blue-400',
      green: 'text-green-600 dark:text-green-400',
      purple: 'text-indigo-600 dark:text-indigo-400',
      amber: 'text-amber-600 dark:text-amber-400',
    },
  },
  defaultVariants: {
    variant: 'blue',
  },
});

export const noticeContainer = cva(
  ['relative rounded-xl p-4', 'backdrop-blur-sm ring-1 ring-white/[0.08]'],
  {
    variants: {
      variant: {
        info: 'bg-blue-500/[0.06] dark:bg-blue-400/[0.06]',
        warning: 'bg-amber-500/[0.06] dark:bg-amber-400/[0.06]',
        success: 'bg-green-500/[0.06] dark:bg-green-400/[0.06]',
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
    'backdrop-blur-sm ring-1 ring-white/[0.08]',
  ],
  {
    variants: {
      variant: {
        info: 'bg-blue-500/10 dark:bg-blue-400/10',
        warning: 'bg-amber-500/10 dark:bg-amber-400/10',
        success: 'bg-green-500/10 dark:bg-green-400/10',
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
      info: 'text-blue-600 dark:text-blue-400',
      warning: 'text-amber-600 dark:text-amber-400',
      success: 'text-green-600 dark:text-green-400',
    },
  },
  defaultVariants: {
    variant: 'info',
  },
});

export const noticeTitle = cva(['text-sm font-semibold leading-tight mb-1'], {
  variants: {
    variant: {
      info: 'text-blue-700 dark:text-blue-300',
      warning: 'text-amber-700 dark:text-amber-300',
      success: 'text-green-700 dark:text-green-300',
    },
  },
  defaultVariants: {
    variant: 'info',
  },
});

export const noticeText = cva(['text-sm leading-relaxed'], {
  variants: {
    variant: {
      info: 'text-blue-600/80 dark:text-blue-300/70',
      warning: 'text-amber-600/80 dark:text-amber-300/70',
      success: 'text-green-600/80 dark:text-green-300/70',
    },
  },
  defaultVariants: {
    variant: 'info',
  },
});

export const buttonVariantMap: Record<ModalVariant, string> = {
  blue: 'outline-blue',
  green: 'outline-green',
  purple: 'outline-purple',
  amber: 'outline-amber',
};
