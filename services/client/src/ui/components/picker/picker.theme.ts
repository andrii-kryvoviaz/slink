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

export const pickerListTheme = cva('', {
  variants: {
    variant: {
      popover: 'px-1.5 py-1.5 space-y-1',
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
        popover: 'gap-2.5 px-2 py-2 rounded-md',
        panel: 'gap-3 px-3 py-2.5 rounded-lg',
        glass: 'gap-2.5 px-2.5 py-2 rounded-lg',
      },
      color: {
        blue: '',
        indigo: '',
      },
      selected: {
        true: '',
        false: 'hover:bg-gray-50 dark:hover:bg-white/5',
      },
    },
    compoundVariants: [
      {
        color: 'blue',
        selected: true,
        class: 'bg-blue-50/80 dark:bg-blue-500/10',
      },
      {
        color: 'indigo',
        selected: true,
        class: 'bg-indigo-50/80 dark:bg-indigo-500/10',
      },
      {
        variant: 'glass',
        color: 'blue',
        selected: true,
        class:
          'bg-blue-100 dark:bg-blue-500/20 border border-blue-200 dark:border-blue-500/30',
      },
      {
        variant: 'glass',
        color: 'indigo',
        selected: true,
        class:
          'bg-indigo-100 dark:bg-indigo-500/20 border border-indigo-200 dark:border-indigo-500/30',
      },
      {
        variant: 'glass',
        selected: false,
        class:
          'hover:bg-slate-100 dark:hover:bg-slate-800 border border-transparent hover:border-slate-200 dark:hover:border-slate-700',
      },
    ],
    defaultVariants: {
      variant: 'popover',
      color: 'blue',
      selected: false,
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
        false:
          'border-gray-300 dark:border-gray-600 group-hover:border-gray-400 dark:group-hover:border-gray-500',
      },
    },
    compoundVariants: [
      {
        color: 'blue',
        selected: true,
        class: 'bg-blue-600 border-blue-600 text-white',
      },
      {
        color: 'indigo',
        selected: true,
        class: 'bg-indigo-600 border-indigo-600 text-white',
      },
    ],
    defaultVariants: {
      variant: 'popover',
      color: 'blue',
      selected: false,
    },
  },
);

export const pickerCheckIconTheme = cva('text-white', {
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
      popover: 'text-[13px] font-medium',
      panel: 'text-sm font-medium',
      glass: 'text-[13px] font-medium',
    },
    color: {
      blue: '',
      indigo: '',
    },
    selected: {
      true: '',
      false: 'text-gray-700 dark:text-gray-200',
    },
  },
  compoundVariants: [
    {
      color: 'blue',
      selected: true,
      class: 'text-blue-700 dark:text-blue-300',
    },
    {
      color: 'indigo',
      selected: true,
      class: 'text-indigo-700 dark:text-indigo-300',
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
        popover: 'text-[11px]',
        panel: 'text-xs',
        glass: 'text-[11px]',
      },
      color: {
        blue: '',
        indigo: '',
      },
      selected: {
        true: '',
        false: 'text-gray-400 dark:text-gray-500',
      },
    },
    compoundVariants: [
      {
        color: 'blue',
        selected: true,
        class: 'text-blue-500 dark:text-blue-400',
      },
      {
        color: 'indigo',
        selected: true,
        class: 'text-indigo-500 dark:text-indigo-400',
      },
    ],
    defaultVariants: {
      variant: 'popover',
      color: 'blue',
      selected: false,
    },
  },
);

export const pickerEmptyTheme = cva('', {
  variants: {
    variant: {
      popover: 'px-4',
      panel:
        'py-6 rounded-lg border border-dashed border-gray-200 dark:border-gray-700',
      glass: 'px-4',
    },
  },
  defaultVariants: {
    variant: 'popover',
  },
});
