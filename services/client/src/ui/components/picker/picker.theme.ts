import { cva } from 'class-variance-authority';

export type PickerVariant = 'popover' | 'panel' | 'glass';
export type PickerColor = 'blue' | 'indigo';

export const pickerContainerTheme = cva('', {
  variants: {
    variant: {
      popover: 'w-64 max-w-screen',
      panel: '',
      glass: 'w-64 max-w-screen',
    },
  },
  defaultVariants: {
    variant: 'popover',
  },
});

export const pickerHeaderTheme = cva(
  'border-border/80 flex shrink-0 items-center justify-between gap-2 border-b px-3 py-2',
);

export const pickerHeaderTitleTheme = cva(
  'text-foreground truncate text-[13px] font-medium',
);

export const pickerHeaderCloseTheme = cva(
  'text-muted-foreground hover:bg-ghost-hover hover:text-foreground focus-visible:ring-info-fill/30 inline-flex h-7 w-7 shrink-0 cursor-pointer items-center justify-center rounded-md transition-colors focus:outline-none focus-visible:ring-2',
);

export const pickerListTheme = cva('', {
  variants: {
    variant: {
      popover: 'px-1.5 py-2 space-y-0.5',
      panel: 'space-y-1',
      glass: 'px-1.5 py-1.5 space-y-0.5',
    },
  },
  defaultVariants: {
    variant: 'popover',
  },
});

export const pickerItemTheme = cva(
  'flex items-center w-full text-left transition-all duration-150 outline-none group select-none cursor-pointer disabled:cursor-not-allowed disabled:opacity-50',
  {
    variants: {
      variant: {
        popover: 'gap-2.5 px-2.5 py-2 rounded-lg',
        panel: 'gap-3 px-3 py-2.5 rounded-lg',
        glass: 'gap-2.5 px-2.5 py-2 rounded-lg',
      },
      color: {
        blue: '',
        indigo: '',
      },
      selected: {
        true: '',
        false: 'hover:bg-ghost-hover',
      },
      highlighted: {
        true: 'bg-ghost-hover',
        false: '',
      },
    },
    compoundVariants: [
      {
        color: 'blue',
        selected: true,
        class: 'bg-info-fill/10',
      },
      {
        color: 'indigo',
        selected: true,
        class: 'bg-accent-subtle',
      },
      {
        variant: 'glass',
        color: 'blue',
        selected: true,
        class: 'bg-info-fill/20 border border-info-fill/30',
      },
      {
        variant: 'glass',
        color: 'indigo',
        selected: true,
        class: 'bg-accent/20 border border-accent/30',
      },
      {
        variant: 'glass',
        selected: false,
        class:
          'hover:bg-ghost-hover hover:border-border border border-transparent',
      },
    ],
    defaultVariants: {
      variant: 'popover',
      color: 'blue',
      selected: false,
      highlighted: false,
    },
  },
);

export const pickerCheckboxTheme = cva(
  'shrink-0 flex items-center justify-center transition-all duration-150',
  {
    variants: {
      variant: {
        popover: 'w-[18px] h-[18px] rounded-full border-[1.5px]',
        panel: 'w-5 h-5 rounded-md border-[1.5px]',
        glass: 'w-[18px] h-[18px] rounded-full border-[1.5px]',
      },
      color: {
        blue: '',
        indigo: '',
      },
      selected: {
        true: '',
        false: 'border-border-strong group-hover:border-ring',
      },
    },
    compoundVariants: [
      {
        color: 'blue',
        selected: true,
        class:
          'bg-info-surface border-info-surface text-info-surface-foreground',
      },
      {
        color: 'indigo',
        selected: true,
        class: 'bg-accent border-accent text-accent-foreground',
      },
    ],
    defaultVariants: {
      variant: 'popover',
      color: 'blue',
      selected: false,
    },
  },
);

export const pickerCheckIconTheme = cva('text-info-surface-foreground', {
  variants: {
    variant: {
      popover: 'w-2.5 h-2.5',
      panel: 'w-3 h-3',
      glass: 'w-2.5 h-2.5',
    },
  },
  defaultVariants: {
    variant: 'popover',
  },
});

export const pickerNameTheme = cva('truncate transition-colors duration-150', {
  variants: {
    variant: {
      popover: 'text-sm font-medium',
      panel: 'text-sm font-medium',
      glass: 'text-[13px] font-medium',
    },
    color: {
      blue: '',
      indigo: '',
    },
    selected: {
      true: '',
      false: 'text-foreground-soft',
    },
  },
  compoundVariants: [
    {
      color: 'blue',
      selected: true,
      class: 'text-info-subtle-foreground',
    },
    {
      color: 'indigo',
      selected: true,
      class: 'text-accent-subtle-foreground',
    },
  ],
  defaultVariants: {
    variant: 'popover',
    color: 'blue',
    selected: false,
  },
});

export const pickerSubtextTheme = cva(
  'truncate transition-colors duration-150',
  {
    variants: {
      variant: {
        popover: 'text-xs',
        panel: 'text-xs',
        glass: 'text-[11px]',
      },
      color: {
        blue: '',
        indigo: '',
      },
      selected: {
        true: '',
        false: 'text-foreground-subtle',
      },
    },
    compoundVariants: [
      {
        color: 'blue',
        selected: true,
        class: 'text-info',
      },
      {
        color: 'indigo',
        selected: true,
        class: 'text-accent',
      },
    ],
    defaultVariants: {
      variant: 'popover',
      color: 'blue',
      selected: false,
    },
  },
);

export const pickerCreateFooterTheme = cva(
  'flex items-center gap-2 px-2 py-1.5 rounded-lg text-sm transition-colors w-full',
  {
    variants: {
      color: {
        blue: '',
        indigo: '',
      },
      highlighted: {
        true: '',
        false:
          'text-muted-foreground hover:text-foreground-soft hover:bg-ghost-hover',
      },
    },
    compoundVariants: [
      {
        color: 'blue',
        highlighted: true,
        class: 'bg-info-fill/10 text-info hover:bg-info-fill/15',
      },
      {
        color: 'indigo',
        highlighted: true,
        class: 'bg-accent-subtle text-accent hover:bg-accent/15',
      },
    ],
    defaultVariants: {
      color: 'blue',
      highlighted: false,
    },
  },
);

export const pickerCreateRowTheme = cva(
  'flex items-center w-full text-left transition-all duration-150 outline-none group select-none cursor-pointer disabled:cursor-not-allowed disabled:opacity-60',
  {
    variants: {
      variant: {
        popover: 'gap-2.5 px-2.5 py-2 rounded-lg',
        panel: 'gap-3 px-3 py-2.5 rounded-lg',
        glass: 'gap-2.5 px-2.5 py-2 rounded-lg',
      },
      color: {
        blue: 'text-info hover:bg-info-fill/10',
        indigo: 'text-accent hover:bg-accent-subtle',
      },
    },
    defaultVariants: {
      variant: 'popover',
      color: 'blue',
    },
  },
);
