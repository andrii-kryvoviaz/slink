import { cva } from 'class-variance-authority';

export type AddToCollectionButtonSize = 'sm' | 'md' | 'lg';
export type AddToCollectionButtonVariant = 'default' | 'subtle' | 'overlay';

export const addToCollectionButtonTheme = cva(
  'group/collection relative inline-flex items-center select-none transition-all duration-200',
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
          'rounded-md px-2 py-1 hover:bg-blue-50/50 dark:hover:bg-blue-900/20',
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
          'bg-blue-600/80 hover:bg-blue-600/90 dark:bg-blue-500/80 dark:hover:bg-blue-500/90',
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

export const addToCollectionIconTheme = cva('transition-all duration-200', {
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
      true: 'animate-pulse',
      false: '',
    },
  },
  compoundVariants: [
    {
      variant: 'default',
      active: false,
      class:
        'text-gray-400 group-hover/collection:text-blue-500 dark:text-gray-500 dark:group-hover/collection:text-blue-400',
    },
    {
      variant: 'default',
      active: true,
      class: 'text-blue-500 dark:text-blue-400',
    },
    {
      variant: 'subtle',
      active: false,
      class:
        'text-gray-500 group-hover/collection:text-blue-600 dark:text-gray-400 dark:group-hover/collection:text-blue-400',
    },
    {
      variant: 'subtle',
      active: true,
      class: 'text-blue-600 dark:text-blue-400',
    },
    {
      variant: 'overlay',
      active: false,
      class: 'text-white/90 group-hover/collection:text-white',
    },
    {
      variant: 'overlay',
      active: true,
      class: 'text-white',
    },
  ],
  defaultVariants: {
    size: 'md',
    variant: 'default',
    active: false,
    loading: false,
  },
});
