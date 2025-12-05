import { cva } from 'class-variance-authority';

export const bookmarkButtonTheme = cva(
  'group/bookmark relative inline-flex items-center select-none transition-all duration-200',
  {
    variants: {
      size: {
        sm: 'gap-1',
        md: 'gap-1.5',
        lg: 'gap-2',
      },
      variant: {
        default: '',
        subtle:
          'rounded-md px-2 py-1 hover:bg-indigo-50/50 dark:hover:bg-indigo-900/20',
        overlay: 'rounded-full px-2.5 py-1 backdrop-blur-sm shadow-lg',
      },
      active: {
        true: '',
        false: '',
      },
      loading: {
        true: 'pointer-events-none opacity-70',
        false: 'cursor-pointer',
      },
    },
    compoundVariants: [
      {
        variant: 'overlay',
        active: true,
        class:
          'bg-indigo-600/80 hover:bg-indigo-600/90 dark:bg-indigo-500/80 dark:hover:bg-indigo-500/90',
      },
      {
        variant: 'overlay',
        active: false,
        class: 'bg-black/60 hover:bg-black/70',
      },
    ],
    defaultVariants: {
      size: 'md',
      variant: 'default',
      active: false,
      loading: false,
    },
  },
);

export const bookmarkIconTheme = cva('transition-all duration-200', {
  variants: {
    size: {
      sm: 'w-4 h-4',
      md: 'w-5 h-5',
      lg: 'w-6 h-6',
    },
    variant: {
      default: '',
      subtle: '',
      overlay: '',
    },
    active: {
      true: '',
      false: '',
    },
    loading: {
      true: '',
      false: 'group-hover/bookmark:scale-110',
    },
  },
  compoundVariants: [
    {
      variant: 'default',
      active: true,
      class: 'text-indigo-600 dark:text-indigo-400',
    },
    {
      variant: 'default',
      active: false,
      class:
        'text-gray-400 dark:text-gray-500 group-hover/bookmark:text-indigo-500 dark:group-hover/bookmark:text-indigo-400',
    },
    {
      variant: 'subtle',
      active: true,
      class: 'text-indigo-600 dark:text-indigo-400',
    },
    {
      variant: 'subtle',
      active: false,
      class:
        'text-gray-400 dark:text-gray-500 group-hover/bookmark:text-indigo-500 dark:group-hover/bookmark:text-indigo-400',
    },
    { variant: 'overlay', active: true, class: 'text-white drop-shadow-sm' },
    {
      variant: 'overlay',
      active: false,
      class: 'text-white/80 group-hover/bookmark:text-white',
    },
  ],
  defaultVariants: {
    size: 'md',
    variant: 'default',
    active: false,
    loading: false,
  },
});

export const bookmarkCountTheme = cva(
  'font-medium tabular-nums leading-none transition-colors duration-200',
  {
    variants: {
      size: {
        sm: 'text-xs',
        md: 'text-sm',
        lg: 'text-sm',
      },
      variant: {
        default: '',
        subtle: '',
        overlay: '',
      },
      active: {
        true: '',
        false: '',
      },
    },
    compoundVariants: [
      {
        variant: 'default',
        active: true,
        class: 'text-indigo-600 dark:text-indigo-400',
      },
      {
        variant: 'default',
        active: false,
        class: 'text-gray-500 dark:text-gray-400',
      },
      {
        variant: 'subtle',
        active: true,
        class: 'text-indigo-600 dark:text-indigo-400',
      },
      {
        variant: 'subtle',
        active: false,
        class: 'text-gray-500 dark:text-gray-400',
      },
      { variant: 'overlay', active: true, class: 'text-white drop-shadow-sm' },
      { variant: 'overlay', active: false, class: 'text-white/80' },
    ],
    defaultVariants: {
      size: 'md',
      variant: 'default',
      active: false,
    },
  },
);

export type BookmarkButtonSize = 'sm' | 'md' | 'lg';
export type BookmarkButtonVariant = 'default' | 'subtle' | 'overlay';
