import { cva } from 'class-variance-authority';

export type TagPickerVariant = 'popover' | 'panel';

export const tagPickerContainerTheme = cva('', {
  variants: {
    variant: {
      popover: 'w-64 max-w-screen',
      panel: '',
    },
  },
  defaultVariants: {
    variant: 'popover',
  },
});

export const tagPickerListTheme = cva('', {
  variants: {
    variant: {
      popover: 'px-1.5 py-1.5 space-y-1',
      panel: 'space-y-1',
    },
  },
  defaultVariants: {
    variant: 'popover',
  },
});

export const tagPickerItemTheme = cva(
  'flex items-center w-full text-left transition-all duration-150 outline-none group select-none cursor-pointer disabled:cursor-not-allowed disabled:opacity-50',
  {
    variants: {
      variant: {
        popover: 'gap-2.5 px-2 py-2 rounded-md',
        panel: 'gap-3 px-3 py-2.5 rounded-lg',
      },
      selected: {
        true: 'bg-blue-50/80 dark:bg-blue-500/10',
        false: 'hover:bg-gray-50 dark:hover:bg-white/5',
      },
    },
    defaultVariants: {
      variant: 'popover',
      selected: false,
    },
  },
);

export const tagPickerCheckboxTheme = cva(
  'shrink-0 flex items-center justify-center transition-all duration-150',
  {
    variants: {
      variant: {
        popover: 'w-[18px] h-[18px] rounded-full border-[1.5px]',
        panel: 'w-5 h-5 rounded-md border-[1.5px]',
      },
      selected: {
        true: 'bg-blue-600 border-blue-600 text-white',
        false:
          'border-gray-300 dark:border-gray-600 group-hover:border-gray-400 dark:group-hover:border-gray-500',
      },
    },
    defaultVariants: {
      variant: 'popover',
      selected: false,
    },
  },
);

export const tagPickerCheckIconTheme = cva('text-white', {
  variants: {
    variant: {
      popover: 'w-2.5 h-2.5',
      panel: 'w-3 h-3',
    },
  },
  defaultVariants: {
    variant: 'popover',
  },
});

export const tagPickerNameTheme = cva(
  'truncate transition-colors duration-150',
  {
    variants: {
      variant: {
        popover: 'text-[13px] font-medium',
        panel: 'text-sm font-medium',
      },
      selected: {
        true: 'text-blue-700 dark:text-blue-300',
        false: 'text-gray-700 dark:text-gray-200',
      },
    },
    defaultVariants: {
      variant: 'popover',
      selected: false,
    },
  },
);

export const tagPickerPathTheme = cva(
  'truncate transition-colors duration-150',
  {
    variants: {
      variant: {
        popover: 'text-[11px]',
        panel: 'text-xs',
      },
      selected: {
        true: 'text-blue-500 dark:text-blue-400',
        false: 'text-gray-400 dark:text-gray-500',
      },
    },
    defaultVariants: {
      variant: 'popover',
      selected: false,
    },
  },
);

export const tagPickerEmptyTheme = cva('', {
  variants: {
    variant: {
      popover: 'px-4',
      panel:
        'py-6 rounded-lg border border-dashed border-gray-200 dark:border-gray-700',
    },
  },
  defaultVariants: {
    variant: 'popover',
  },
});
