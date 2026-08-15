import { tv } from 'tailwind-variants';

export const controls = {
  intro: tv({
    slots: {
      wrap: 'flex flex-col items-center text-center gap-3 py-2',
      title: 'text-sm font-semibold text-foreground leading-tight',
      description: 'text-xs text-muted-foreground leading-snug max-w-[18rem]',
      actions: 'mt-2 flex w-full flex-col gap-2',
    },
  }),

  list: tv({
    slots: {
      wrap: 'flex flex-col gap-2',
      header: 'w-full',
      item: 'flex w-full items-center gap-3 rounded-lg px-2.5 py-2.5 text-left transition-colors',
      icon: 'h-5 w-5 shrink-0 text-foreground-subtle',
      labels: 'flex min-w-0 flex-1 flex-col',
      label: 'text-sm font-medium text-foreground leading-tight',
      sublabel: 'text-xs text-muted-foreground leading-snug truncate',
      chevron: 'h-4 w-4 shrink-0 text-foreground-subtle',
      badge:
        'inline-flex items-center rounded-full px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide bg-muted text-foreground-subtle',
      separator: 'my-1 h-px bg-border',
    },
    variants: {
      state: {
        interactive: {
          item: 'hover:bg-ghost-hover focus:outline-none focus-visible:bg-ghost-hover cursor-pointer',
        },
        disabled: {
          item: 'opacity-60 cursor-not-allowed',
        },
      },
      tone: {
        neutral: {},
        danger: {
          item: 'text-danger hover:bg-danger-subtle focus-visible:bg-danger-subtle',
          icon: 'text-danger',
          label: 'text-danger',
          sublabel: 'text-danger/80',
          chevron: 'text-danger',
        },
      },
    },
    defaultVariants: {
      state: 'interactive',
      tone: 'neutral',
    },
  }),

  detail: tv({
    slots: {
      root: 'flex flex-col gap-3 px-1.5 py-1',
      header: 'flex items-start gap-2',
      back: 'inline-flex items-center justify-center shrink-0 rounded-md h-6 w-6 text-muted-foreground transition-colors cursor-pointer hover:bg-ghost-hover hover:text-foreground focus:outline-none focus-visible:ring-2 focus-visible:ring-info/30',
      backIcon: 'h-3.5 w-3.5',
      labels: 'flex min-w-0 flex-1 flex-col gap-1',
      titleRow: 'flex min-h-6 items-center justify-between gap-3',
      titleGroup: 'flex min-w-0 items-center gap-2',
      title: 'text-sm font-medium text-foreground leading-tight',
      description: 'text-xs text-muted-foreground leading-snug',
      body: 'space-y-2',
      presets: 'flex flex-wrap gap-1.5',
      chip: 'inline-flex items-center rounded-full font-medium leading-none transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-info/30 px-2.5 py-1 text-xs cursor-pointer',
      field:
        'h-auto border-transparent bg-transparent shadow-none rounded-lg px-2 py-2 text-sm hover:bg-ghost-hover dark:bg-transparent focus-visible:ring-0 focus-visible:ring-offset-0 focus-visible:border-transparent',
      fieldRow:
        'flex h-8 min-w-0 items-center gap-2 rounded-md border border-border bg-background dark:bg-input/30 px-3 shadow-xs transition-[color,box-shadow] focus-within:border-ring focus-within:ring-[3px] focus-within:ring-ring/50',
      fieldInput:
        'h-full min-w-0 flex-1 bg-transparent text-sm outline-none placeholder:text-muted-foreground disabled:cursor-not-allowed disabled:opacity-50',
      footerHint: 'text-[11px] text-muted-foreground leading-snug',
      setAction:
        'shrink-0 rounded-sm text-xs font-medium leading-none transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-info/30',
      removeAction:
        'shrink-0 rounded-sm text-xs font-medium leading-none text-danger transition-colors cursor-pointer hover:text-danger-subtle-foreground focus:outline-none focus-visible:ring-2 focus-visible:ring-danger/30',
    },
    variants: {
      chipActive: {
        true: {
          chip: 'bg-foreground text-background',
        },
        false: {
          chip: 'bg-muted text-muted-foreground hover:bg-ghost-hover-strong hover:text-foreground',
        },
      },
      setEnabled: {
        true: {
          setAction:
            'text-info hover:text-info-subtle-foreground cursor-pointer',
        },
        false: {
          setAction: 'text-foreground-subtle cursor-default',
        },
      },
    },
    defaultVariants: {
      chipActive: false,
      setEnabled: false,
    },
  }),
};
