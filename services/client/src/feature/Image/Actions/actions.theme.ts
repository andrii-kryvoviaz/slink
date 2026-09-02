import { cva } from 'class-variance-authority';
import { tv } from 'tailwind-variants';

export const actionButtonVariants = cva('rounded-full', {
  variants: {
    layout: {
      default: '',
      hero: 'h-auto min-w-0 flex-1',
    },
    variant: {
      default: '',
      destructive:
        'text-danger hover:text-danger hover:bg-danger-wash dark:hover:bg-danger-wash/20',
    },
  },
  compoundVariants: [
    {
      layout: 'hero',
      variant: 'default',
      class:
        'p-2 bg-transparent text-foreground-muted hover:text-foreground-soft hover:bg-hover',
    },
    {
      layout: 'hero',
      variant: 'destructive',
      class: 'p-2 bg-transparent',
    },
  ],
  defaultVariants: {
    layout: 'default',
    variant: 'default',
  },
});

export const shareCapsuleVariants = tv({
  slots: {
    capsule: 'flex items-stretch overflow-hidden rounded-full',
    download:
      'h-full rounded-none bg-primary-surface text-on-primary-surface hover:bg-primary-surface-strong focus-visible:ring-inset focus-visible:ring-offset-0',
    copy: 'h-full rounded-none focus-visible:ring-inset focus-visible:ring-offset-0',
    caret:
      'h-full w-6 min-w-0 flex-none rounded-none border-l-[0.5px] border-border-strong px-0 focus-visible:ring-inset focus-visible:ring-offset-0',
    downloadIcon: 'shrink-0',
    label: 'font-medium truncate',
  },
  variants: {
    layout: {
      default: {
        capsule: 'h-8',
        download: 'gap-1.5 px-3',
        copy: 'gap-1.5 px-2.5',
        label: 'text-xs',
      },
      hero: {
        capsule:
          'flex-1 rounded-xl shadow-sm transition-shadow hover:shadow-md',
        download: 'h-auto min-w-0 flex-1 gap-2 px-5 py-2.5 text-sm',
        downloadIcon: 'h-5 w-5',
      },
    },
  },
  compoundSlots: [
    {
      slots: ['copy', 'caret'],
      class:
        'bg-border/75 text-foreground-soft hover:bg-border dark:bg-border-strong/55 dark:hover:bg-border-strong/75',
    },
  ],
  defaultVariants: {
    layout: 'default',
  },
});

export const iconSizeVariants = cva('', {
  variants: {
    layout: {
      default: 'h-3.5 w-3.5',
      hero: 'h-4 w-4',
    },
  },
  defaultVariants: {
    layout: 'default',
  },
});

export type ActionLayout = 'default' | 'hero';
