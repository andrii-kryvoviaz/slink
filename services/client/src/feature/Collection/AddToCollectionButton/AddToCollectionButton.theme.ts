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
        subtle: 'rounded-md px-2 py-1 hover:bg-info-fill/5',
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
        class: 'bg-info-strong/80 hover:bg-info-strong/90',
      },
      {
        variant: 'overlay',
        active: false,
        class: 'bg-scrim/60 hover:bg-scrim/70',
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
      class: 'text-foreground-subtle group-hover/collection:text-info',
    },
    {
      variant: 'default',
      active: true,
      class: 'text-info',
    },
    {
      variant: 'subtle',
      active: false,
      class: 'text-muted-foreground group-hover/collection:text-info',
    },
    {
      variant: 'subtle',
      active: true,
      class: 'text-info',
    },
    {
      variant: 'overlay',
      active: false,
      class:
        'text-surface-inverse-foreground/90 group-hover/collection:text-surface-inverse-foreground',
    },
    {
      variant: 'overlay',
      active: true,
      class: 'text-surface-inverse-foreground',
    },
  ],
  defaultVariants: {
    size: 'md',
    variant: 'default',
    active: false,
    loading: false,
  },
});
