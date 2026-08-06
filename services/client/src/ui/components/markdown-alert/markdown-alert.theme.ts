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
  [
    'relative rounded-xl p-4 my-3',
    'backdrop-blur-sm ring-1 ring-surface-inverse-foreground/[0.08]',
  ],
  {
    variants: {
      type: {
        note: 'bg-info/[0.06]',
        tip: 'bg-success/[0.06]',
        important: 'bg-accent/[0.06]',
        warning: 'bg-warning/[0.06]',
        caution: 'bg-danger/[0.06]',
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
    'backdrop-blur-sm ring-1 ring-surface-inverse-foreground/[0.08]',
  ],
  {
    variants: {
      type: {
        note: 'bg-info/10',
        tip: 'bg-success/10',
        important: 'bg-accent/10',
        warning: 'bg-warning/10',
        caution: 'bg-danger/10',
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
      note: 'text-info',
      tip: 'text-success',
      important: 'text-accent',
      warning: 'text-warning',
      caution: 'text-danger',
    },
  },
  defaultVariants: {
    type: 'note',
  },
});

export const alertTitle = cva(['text-sm font-semibold leading-tight mb-1'], {
  variants: {
    type: {
      note: 'text-info-subtle-foreground',
      tip: 'text-success-subtle-foreground',
      important: 'text-accent-subtle-foreground',
      warning: 'text-warning-subtle-foreground',
      caution: 'text-danger-subtle-foreground',
    },
  },
  defaultVariants: {
    type: 'note',
  },
});

export const alertText = cva(['text-sm leading-relaxed whitespace-pre-line'], {
  variants: {
    type: {
      note: 'text-info-subtle-foreground/80 dark:text-info-subtle-foreground/70',
      tip: 'text-success-subtle-foreground/80 dark:text-success-subtle-foreground/70',
      important:
        'text-accent-subtle-foreground/80 dark:text-accent-subtle-foreground/70',
      warning:
        'text-warning-subtle-foreground/80 dark:text-warning-subtle-foreground/70',
      caution:
        'text-danger-subtle-foreground/80 dark:text-danger-subtle-foreground/70',
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
