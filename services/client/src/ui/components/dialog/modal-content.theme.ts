import { cva } from 'class-variance-authority';

export type ModalVariant = 'blue' | 'green' | 'purple' | 'amber';
export type NoticeVariant = 'info' | 'warning' | 'success';

export const modalIconContainer = cva(
  [
    'w-12 h-12 rounded-xl flex items-center justify-center',
    'shadow-md backdrop-blur-sm border flex-shrink-0',
    'bg-gradient-to-br',
  ],
  {
    variants: {
      variant: {
        blue: [
          'from-blue-500/10 to-indigo-600/15',
          'dark:from-blue-400/20 dark:to-indigo-500/25',
          'border-blue-300/40 dark:border-blue-600/50',
        ],
        green: [
          'from-green-500/10 to-emerald-600/15',
          'dark:from-green-400/20 dark:to-emerald-500/25',
          'border-green-300/40 dark:border-green-600/50',
        ],
        purple: [
          'from-indigo-500/10 to-purple-600/15',
          'dark:from-indigo-400/20 dark:to-purple-500/25',
          'border-indigo-300/40 dark:border-indigo-600/50',
        ],
        amber: [
          'from-amber-500/10 to-orange-600/15',
          'dark:from-amber-400/20 dark:to-orange-500/25',
          'border-amber-300/40 dark:border-amber-600/50',
        ],
      },
    },
    defaultVariants: {
      variant: 'blue',
    },
  },
);

export const modalIcon = cva(['drop-shadow-sm [&>svg]:h-6 [&>svg]:w-6'], {
  variants: {
    variant: {
      blue: 'text-blue-700 dark:text-blue-300',
      green: 'text-green-700 dark:text-green-300',
      purple: 'text-indigo-700 dark:text-indigo-300',
      amber: 'text-amber-700 dark:text-amber-300',
    },
  },
  defaultVariants: {
    variant: 'blue',
  },
});

export const noticeContainer = cva(
  [
    'relative overflow-hidden rounded-2xl p-5',
    'shadow-lg backdrop-blur-sm border',
    'bg-gradient-to-br',
  ],
  {
    variants: {
      variant: {
        info: [
          'from-blue-50/90 via-white to-indigo-50/80',
          'dark:from-blue-950/20 dark:via-slate-800/50 dark:to-indigo-950/30',
          'border-blue-200/40 dark:border-blue-800/30',
        ],
        warning: [
          'from-amber-50/90 via-white to-orange-50/80',
          'dark:from-amber-950/20 dark:via-slate-800/50 dark:to-orange-950/30',
          'border-amber-200/40 dark:border-amber-800/30',
        ],
        success: [
          'from-green-50/90 via-white to-emerald-50/80',
          'dark:from-green-950/20 dark:via-slate-800/50 dark:to-emerald-950/30',
          'border-green-200/40 dark:border-green-800/30',
        ],
      },
    },
    defaultVariants: {
      variant: 'info',
    },
  },
);

export const noticeOverlay = cva(
  [
    'absolute inset-0',
    'bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))]',
  ],
  {
    variants: {
      variant: {
        info: [
          'from-blue-100/30 via-transparent to-indigo-100/20',
          'dark:from-blue-900/20 dark:via-transparent dark:to-indigo-900/10',
        ],
        warning: [
          'from-amber-100/30 via-transparent to-orange-100/20',
          'dark:from-amber-900/20 dark:via-transparent dark:to-orange-900/10',
        ],
        success: [
          'from-green-100/30 via-transparent to-emerald-100/20',
          'dark:from-green-900/20 dark:via-transparent dark:to-emerald-900/10',
        ],
      },
    },
    defaultVariants: {
      variant: 'info',
    },
  },
);

export const noticeIconContainer = cva(
  [
    'w-10 h-10 rounded-xl flex items-center justify-center shadow-lg bg-gradient-to-br',
  ],
  {
    variants: {
      variant: {
        info: 'from-blue-500 to-indigo-600',
        warning: 'from-amber-500 to-orange-600',
        success: 'from-green-500 to-emerald-600',
      },
    },
    defaultVariants: {
      variant: 'info',
    },
  },
);

export const noticeTitle = cva(['text-sm font-semibold leading-tight mb-2'], {
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
      info: 'text-blue-800/90 dark:text-blue-200/90',
      warning: 'text-amber-800/90 dark:text-amber-200/90',
      success: 'text-green-800/90 dark:text-green-200/90',
    },
  },
  defaultVariants: {
    variant: 'info',
  },
});

export const buttonVariantMap: Record<ModalVariant, string> = {
  blue: 'gradient-blue',
  green: 'gradient-green',
  purple: 'gradient-purple',
  amber: 'gradient-amber',
};
