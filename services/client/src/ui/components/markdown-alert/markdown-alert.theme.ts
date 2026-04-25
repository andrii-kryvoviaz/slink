import { cva } from 'class-variance-authority';

export const ALERT_TYPES = [
  'note',
  'tip',
  'important',
  'warning',
  'caution',
] as const;

export type AlertType = (typeof ALERT_TYPES)[number];

export const alertContainer = cva(
  ['relative rounded-xl p-4 my-3', 'backdrop-blur-sm ring-1 ring-white/[0.08]'],
  {
    variants: {
      type: {
        note: 'bg-blue-500/[0.06] dark:bg-blue-400/[0.06]',
        tip: 'bg-green-500/[0.06] dark:bg-green-400/[0.06]',
        important: 'bg-indigo-500/[0.06] dark:bg-indigo-400/[0.06]',
        warning: 'bg-amber-500/[0.06] dark:bg-amber-400/[0.06]',
        caution: 'bg-red-500/[0.06] dark:bg-red-400/[0.06]',
      },
    },
    defaultVariants: {
      type: 'note',
    },
  },
);

export const alertIconContainer = cva(
  [
    'w-8 h-8 rounded-lg flex items-center justify-center',
    'backdrop-blur-sm ring-1 ring-white/[0.08]',
  ],
  {
    variants: {
      type: {
        note: 'bg-blue-500/10 dark:bg-blue-400/10',
        tip: 'bg-green-500/10 dark:bg-green-400/10',
        important: 'bg-indigo-500/10 dark:bg-indigo-400/10',
        warning: 'bg-amber-500/10 dark:bg-amber-400/10',
        caution: 'bg-red-500/10 dark:bg-red-400/10',
      },
    },
    defaultVariants: {
      type: 'note',
    },
  },
);

export const alertIcon = cva(['[&>svg]:h-4 [&>svg]:w-4'], {
  variants: {
    type: {
      note: 'text-blue-600 dark:text-blue-400',
      tip: 'text-green-600 dark:text-green-400',
      important: 'text-indigo-600 dark:text-indigo-400',
      warning: 'text-amber-600 dark:text-amber-400',
      caution: 'text-red-600 dark:text-red-400',
    },
  },
  defaultVariants: {
    type: 'note',
  },
});

export const alertTitle = cva(['text-sm font-semibold leading-tight mb-1'], {
  variants: {
    type: {
      note: 'text-blue-700 dark:text-blue-300',
      tip: 'text-green-700 dark:text-green-300',
      important: 'text-indigo-700 dark:text-indigo-300',
      warning: 'text-amber-700 dark:text-amber-300',
      caution: 'text-red-700 dark:text-red-300',
    },
  },
  defaultVariants: {
    type: 'note',
  },
});

export const alertText = cva(['text-sm leading-relaxed whitespace-pre-line'], {
  variants: {
    type: {
      note: 'text-blue-600/80 dark:text-blue-300/70',
      tip: 'text-green-600/80 dark:text-green-300/70',
      important: 'text-indigo-600/80 dark:text-indigo-300/70',
      warning: 'text-amber-600/80 dark:text-amber-300/70',
      caution: 'text-red-600/80 dark:text-red-300/70',
    },
  },
  defaultVariants: {
    type: 'note',
  },
});

export const ALERT_META: Record<AlertType, { label: string; icon: string }> = {
  note: { label: 'Note', icon: 'ph:info' },
  tip: { label: 'Tip', icon: 'ph:lightbulb' },
  important: { label: 'Important', icon: 'ph:megaphone-simple' },
  warning: { label: 'Warning', icon: 'ph:warning' },
  caution: { label: 'Caution', icon: 'ph:warning-octagon' },
};
