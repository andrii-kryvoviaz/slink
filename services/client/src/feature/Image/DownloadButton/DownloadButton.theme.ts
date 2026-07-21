import { cva } from 'class-variance-authority';
import { tv } from 'tailwind-variants';

export const downloadButtonTheme = cva(
  'group/download relative inline-flex items-center select-none transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-slate-400/70',
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
        'group-hover/download:scale-110 text-gray-400 dark:text-gray-500 group-hover/download:text-indigo-500 dark:group-hover/download:text-indigo-400',
    },
    {
      variant: 'subtle',
      loading: false,
      class:
        'group-hover/download:scale-110 text-gray-400 dark:text-gray-500 group-hover/download:text-indigo-500 dark:group-hover/download:text-indigo-400',
    },
    {
      variant: 'overlay',
      loading: false,
      class:
        'group-hover/download:scale-110 text-white/80 group-hover/download:text-white',
    },
    {
      variant: 'default',
      loading: true,
      class: 'text-gray-400 dark:text-gray-500',
    },
    {
      variant: 'subtle',
      loading: true,
      class: 'text-gray-400 dark:text-gray-500',
    },
    {
      variant: 'overlay',
      loading: true,
      class: 'text-white/80',
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
