import { cva } from 'class-variance-authority';
import { tv } from 'tailwind-variants';

export const downloadButtonTheme = cva(
  'group/download relative inline-flex items-center select-none transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-ring/85',
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
      loading: {
        true: 'pointer-events-none opacity-70',
        false: 'cursor-pointer',
      },
    },
    defaultVariants: {
      size: 'md',
      variant: 'default',
      loading: false,
    },
  },
);

export const downloadIconTheme = tv({
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
    loading: {
      true: '',
      false: '',
    },
  },
  compoundVariants: [
    {
      variant: 'default',
      loading: false,
      class:
        'group-hover/download:scale-110 text-foreground-subtle group-hover/download:text-accent',
    },
    {
      variant: 'subtle',
      loading: false,
      class:
        'group-hover/download:scale-110 text-foreground-subtle group-hover/download:text-accent',
    },
    {
      variant: 'overlay',
      loading: false,
      class:
        'group-hover/download:scale-110 text-on-surface-inverse/80 group-hover/download:text-on-surface-inverse',
    },
    {
      variant: 'default',
      loading: true,
      class: 'text-foreground-subtle',
    },
    {
      variant: 'subtle',
      loading: true,
      class: 'text-foreground-subtle',
    },
    {
      variant: 'overlay',
      loading: true,
      class: 'text-on-surface-inverse/80',
    },
  ],
  defaultVariants: {
    size: 'md',
    variant: 'default',
    loading: false,
  },
});

export type DownloadButtonSize = 'sm' | 'md' | 'lg';
export type DownloadButtonVariant =
  'default' | 'subtle' | 'overlay' | 'toolbar';
