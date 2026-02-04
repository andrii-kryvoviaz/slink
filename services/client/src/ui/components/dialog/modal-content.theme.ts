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
  ['relative overflow-hidden rounded-xl p-4', 'border'],
  {
    variants: {
      variant: {
        info: [
          'bg-blue-50 dark:bg-blue-950',
          'border-blue-200 dark:border-blue-800',
        ],
        warning: [
          'bg-amber-50 dark:bg-amber-950',
          'border-amber-200 dark:border-amber-800',
        ],
        success: [
          'bg-green-50 dark:bg-green-950',
          'border-green-200 dark:border-green-800',
        ],
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
  ['w-8 h-8 rounded-lg flex items-center justify-center'],
  {
    variants: {
      variant: {
        info: 'bg-blue-100 dark:bg-blue-900',
        warning: 'bg-amber-100 dark:bg-amber-900',
        success: 'bg-green-100 dark:bg-green-900',
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
      info: 'text-blue-900 dark:text-blue-100',
      warning: 'text-amber-900 dark:text-amber-100',
      success: 'text-green-900 dark:text-green-100',
    },
  },
  defaultVariants: {
    variant: 'info',
  },
});

export const noticeText = cva(['text-sm leading-relaxed'], {
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

export const buttonVariantMap: Record<ModalVariant, string> = {
  blue: 'outline-blue',
  green: 'outline-green',
  purple: 'outline-purple',
  amber: 'outline-amber',
};
