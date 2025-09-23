import { cva } from 'class-variance-authority';

export const tagDropdownTheme = cva(
  'absolute z-50 w-full mt-2 bg-white/95 dark:bg-slate-900/95 backdrop-blur-sm rounded-xl shadow-lg max-h-72 overflow-hidden animate-in fade-in-0 zoom-in-95 slide-in-from-top-2',
  {
    variants: {
      variant: {
        default:
          'border border-slate-200 dark:border-slate-800 shadow-slate-900/10 dark:shadow-black/20',
        neon: 'border border-blue-500/20 dark:border-blue-400/20 shadow-blue-500/10 dark:shadow-blue-400/10',
        minimal:
          'border border-slate-100 dark:border-slate-800 shadow-slate-900/5 dark:shadow-black/10',
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  },
);

export const tagItemTheme = cva(
  'w-full flex items-center gap-3 rounded-lg px-3 py-2.5 text-left transition-all duration-200 group relative overflow-hidden',
  {
    variants: {
      variant: {
        default: 'hover:bg-slate-50 dark:hover:bg-slate-800/50',
        neon: 'hover:bg-gradient-to-r hover:from-blue-500/5 hover:to-purple-500/5',
        minimal: 'hover:bg-slate-50/50 dark:hover:bg-slate-800/30',
      },
      selected: {
        true: '',
        false: '',
      },
    },
    compoundVariants: [
      {
        variant: 'neon',
        selected: true,
        class:
          'bg-gradient-to-r from-blue-500/10 to-purple-500/10 shadow-sm ring-1 ring-blue-500/20 dark:ring-blue-400/20',
      },
      {
        variant: 'default',
        selected: true,
        class:
          'bg-slate-100 dark:bg-slate-800 ring-1 ring-slate-300 dark:ring-slate-600',
      },
    ],
    defaultVariants: {
      variant: 'default',
      selected: false,
    },
  },
);

export const tagIconTheme = cva('h-4 w-4 transition-colors flex-shrink-0', {
  variants: {
    variant: {
      default:
        'text-slate-500 dark:text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300',
      neon: 'text-slate-500 dark:text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300',
      minimal:
        'text-slate-400 dark:text-slate-500 group-hover:text-slate-500 dark:group-hover:text-slate-400',
    },
    selected: {
      true: '',
      false: '',
    },
    depth: {
      1: '',
      2: 'opacity-75',
      3: 'opacity-75',
      4: 'opacity-75',
      5: 'opacity-75',
    },
  },
  compoundVariants: [
    {
      variant: 'neon',
      selected: true,
      class: 'text-blue-500 dark:text-blue-400',
    },
    {
      variant: 'default',
      selected: true,
      class: 'text-slate-700 dark:text-slate-300',
    },
    {
      variant: 'minimal',
      selected: true,
      class: 'text-slate-600 dark:text-slate-400',
    },
  ],
  defaultVariants: {
    variant: 'default',
    selected: false,
    depth: 1,
  },
});

export const tagTextTheme = cva('text-sm font-medium truncate leading-tight', {
  variants: {
    variant: {
      default:
        'text-slate-900 dark:text-slate-100 group-hover:text-slate-950 dark:group-hover:text-slate-50',
      neon: 'text-slate-900 dark:text-slate-100 group-hover:text-slate-950 dark:group-hover:text-slate-50',
      minimal:
        'text-slate-700 dark:text-slate-300 group-hover:text-slate-800 dark:group-hover:text-slate-200',
    },
    selected: {
      true: '',
      false: '',
    },
  },
  compoundVariants: [
    {
      variant: 'neon',
      selected: true,
      class: 'text-blue-700 dark:text-blue-300',
    },
    {
      variant: 'default',
      selected: true,
      class: 'text-slate-900 dark:text-slate-100',
    },
    {
      variant: 'minimal',
      selected: true,
      class: 'text-slate-800 dark:text-slate-200',
    },
  ],
  defaultVariants: {
    variant: 'default',
    selected: false,
  },
});

export const createIconTheme = cva('h-4 w-4 transition-colors', {
  variants: {
    variant: {
      default: 'text-slate-600 dark:text-slate-400',
      neon: 'text-emerald-600 dark:text-emerald-400',
      minimal: 'text-slate-500 dark:text-slate-500',
    },
  },
  defaultVariants: {
    variant: 'default',
  },
});

export const createTextTheme = cva('text-sm font-medium leading-tight', {
  variants: {
    variant: {
      default: 'text-slate-700 dark:text-slate-300',
      neon: 'text-emerald-700 dark:text-emerald-300',
      minimal: 'text-slate-600 dark:text-slate-400',
    },
  },
  defaultVariants: {
    variant: 'default',
  },
});

export const createSubtextTheme = cva('text-xs mt-0.5', {
  variants: {
    variant: {
      default: 'text-slate-600/70 dark:text-slate-400/70',
      neon: 'text-emerald-600/70 dark:text-emerald-400/70',
      minimal: 'text-slate-500/70 dark:text-slate-500/70',
    },
  },
  defaultVariants: {
    variant: 'default',
  },
});

export const createActionTheme = cva(
  'w-6 h-6 rounded-md flex items-center justify-center',
  {
    variants: {
      variant: {
        default: 'bg-slate-100 dark:bg-slate-800',
        neon: 'bg-emerald-500/10',
        minimal: 'bg-slate-50 dark:bg-slate-850',
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  },
);

export const createActionIconTheme = cva('h-3 w-3', {
  variants: {
    variant: {
      default: 'text-slate-600 dark:text-slate-400',
      neon: 'text-emerald-600 dark:text-emerald-400',
      minimal: 'text-slate-500 dark:text-slate-500',
    },
  },
  defaultVariants: {
    variant: 'default',
  },
});

export const imageCountTheme = cva(
  'inline-flex items-center px-2 py-1 rounded-md text-xs font-medium transition-colors',
  {
    variants: {
      variant: {
        default:
          'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 group-hover:bg-slate-200 dark:group-hover:bg-slate-700',
        neon: 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 group-hover:bg-slate-200 dark:group-hover:bg-slate-700',
        minimal:
          'bg-slate-50 text-slate-500 dark:bg-slate-850 dark:text-slate-500 group-hover:bg-slate-100 dark:group-hover:bg-slate-800',
      },
      selected: {
        true: '',
        false: '',
      },
    },
    compoundVariants: [
      {
        variant: 'neon',
        selected: true,
        class:
          'bg-blue-500/15 text-blue-700 dark:bg-blue-400/15 dark:text-blue-300',
      },
      {
        variant: 'default',
        selected: true,
        class:
          'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
      },
      {
        variant: 'minimal',
        selected: true,
        class:
          'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400',
      },
    ],
    defaultVariants: {
      variant: 'default',
      selected: false,
    },
  },
);

export const createButtonTheme = cva(
  'w-full flex items-center gap-3 rounded-lg px-3 py-3 text-left transition-all duration-200 group border border-dashed disabled:opacity-75 disabled:cursor-not-allowed',
  {
    variants: {
      variant: {
        default:
          'border-slate-300 dark:border-slate-600 hover:bg-slate-50 dark:hover:bg-slate-800/50',
        neon: 'border-emerald-200 dark:border-emerald-800/50 hover:bg-gradient-to-r hover:from-emerald-500/5 hover:to-blue-500/5',
        minimal:
          'border-slate-200 dark:border-slate-700 hover:bg-slate-25 dark:hover:bg-slate-850/50',
      },
      selected: {
        true: '',
        false: '',
      },
    },
    compoundVariants: [
      {
        variant: 'neon',
        selected: true,
        class:
          'bg-gradient-to-r from-emerald-500/10 to-blue-500/10 shadow-sm ring-1 ring-emerald-500/20 dark:ring-emerald-400/20',
      },
      {
        variant: 'default',
        selected: true,
        class:
          'bg-slate-100 dark:bg-slate-800 ring-1 ring-slate-300 dark:ring-slate-600',
      },
      {
        variant: 'minimal',
        selected: true,
        class:
          'bg-slate-100 dark:bg-slate-800 ring-1 ring-slate-200 dark:ring-slate-700',
      },
    ],
    defaultVariants: {
      variant: 'default',
      selected: false,
    },
  },
);
