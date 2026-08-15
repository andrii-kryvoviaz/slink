import { cva } from 'class-variance-authority';
import { tv } from 'tailwind-variants';

export const bookmarkButtonTheme = cva(
  'group/bookmark relative inline-flex items-center select-none transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-ring/85',
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
          'rounded-md px-2 py-1 hover:bg-accent-wash/50 dark:hover:bg-accent-wash/10',
        overlay: '',
        toolbar: '',
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
    defaultVariants: {
      size: 'md',
      variant: 'default',
      active: false,
      loading: false,
    },
  },
);

export const bookmarkTriggerTheme = cva('group/bookmark', {
  variants: {
    size: {
      sm: 'gap-1',
      md: 'gap-1.5',
      lg: 'gap-2',
    },
    variant: {
      default: '',
      subtle: '',
      overlay: 'min-w-7',
      toolbar: '',
    },
  },
  defaultVariants: {
    size: 'md',
    variant: 'default',
  },
});

export const bookmarkIconTheme = tv({
  base: 'transition-all duration-200',
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
      toolbar: 'h-4 w-4',
    },
    active: {
      true: '',
      false: '',
    },
    loading: {
      true: '',
      false: '',
    },
  },
  compoundVariants: [
    {
      variant: 'default',
      loading: false,
      class: 'group-hover/bookmark:scale-110',
    },
    {
      variant: 'subtle',
      loading: false,
      class: 'group-hover/bookmark:scale-110',
    },
    {
      variant: 'overlay',
      loading: false,
      class: 'group-hover/bookmark:scale-110',
    },
    {
      variant: 'default',
      active: true,
      class: 'text-accent',
    },
    {
      variant: 'default',
      active: false,
      class: 'text-foreground-subtle group-hover/bookmark:text-accent',
    },
    {
      variant: 'subtle',
      active: true,
      class: 'text-accent',
    },
    {
      variant: 'subtle',
      active: false,
      class: 'text-foreground-subtle group-hover/bookmark:text-accent',
    },
    {
      variant: 'overlay',
      active: true,
      class: 'text-on-surface-inverse drop-shadow-sm',
    },
    {
      variant: 'overlay',
      active: false,
      class: 'text-on-surface-inverse/80 group-hover/bookmark:text-accent',
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
        toolbar: '',
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
        class: 'text-accent',
      },
      {
        variant: 'default',
        active: false,
        class: 'text-foreground-muted',
      },
      {
        variant: 'subtle',
        active: true,
        class: 'text-accent',
      },
      {
        variant: 'subtle',
        active: false,
        class: 'text-foreground-muted',
      },
      {
        variant: 'overlay',
        active: true,
        class: 'text-on-surface-inverse drop-shadow-sm',
      },
      {
        variant: 'overlay',
        active: false,
        class: 'text-on-surface-inverse/80',
      },
    ],
    defaultVariants: {
      size: 'md',
      variant: 'default',
      active: false,
    },
  },
);

export type BookmarkButtonSize = 'sm' | 'md' | 'lg';
export type BookmarkButtonVariant =
  'default' | 'subtle' | 'overlay' | 'toolbar';
