import { cva } from 'class-variance-authority';

export const ScrollAreaTheme = cva('relative overflow-hidden', {
  variants: {
    variant: {
      default: '',
      minimal: '',
      modern: 'rounded-lg',
      glass: 'backdrop-blur-sm',
    },
    size: {
      sm: 'max-h-32',
      md: 'max-h-64',
      lg: 'max-h-96',
      xl: 'max-h-[32rem]',
      full: 'h-full',
      auto: '',
    },
  },
  defaultVariants: {
    variant: 'default',
    size: 'auto',
  },
});

export const ScrollAreaViewportTheme = cva('h-full w-full rounded-[inherit]', {
  variants: {
    variant: {
      default: '',
      minimal: '',
      modern: '',
      glass: '',
    },
    padding: {
      none: '',
      sm: 'p-2',
      md: 'px-3 py-4',
      lg: 'px-4 py-6',
    },
  },
  defaultVariants: {
    variant: 'default',
    padding: 'none',
  },
});

export const ScrollAreaScrollbarTheme = cva(
  'flex touch-none select-none transition-all duration-150 ease-out data-[state=visible]:animate-in data-[state=hidden]:animate-out data-[state=hidden]:fade-out-0 data-[state=visible]:fade-in-0',
  {
    variants: {
      variant: {
        default: [
          'bg-transparent hover:bg-gray-100/50 dark:hover:bg-gray-800/50',
          'rounded-none',
        ],
        minimal: ['bg-transparent', 'rounded-full'],
        modern: [
          'bg-gray-100/60 hover:bg-gray-200/80 dark:bg-gray-800/60 dark:hover:bg-gray-700/80',
          'rounded-full shadow-sm',
          'border border-gray-200/50 dark:border-gray-700/50',
        ],
        glass: [
          'bg-white/20 hover:bg-white/30 dark:bg-black/20 dark:hover:bg-black/30',
          'backdrop-blur-sm rounded-full',
          'border border-white/20 dark:border-white/10',
        ],
      },
      orientation: {
        vertical: '',
        horizontal: '',
      },
      size: {
        sm: '',
        md: '',
        lg: '',
      },
    },
    compoundVariants: [
      {
        variant: 'default',
        orientation: 'vertical',
        class: 'w-2 hover:w-2.5 border-l border-l-transparent p-px',
      },
      {
        variant: 'default',
        orientation: 'horizontal',
        class: 'h-2 hover:h-2.5 border-t border-t-transparent p-px',
      },
      {
        variant: 'minimal',
        orientation: 'vertical',
        class: 'w-1.5 hover:w-2 p-0',
      },
      {
        variant: 'minimal',
        orientation: 'horizontal',
        class: 'h-1.5 hover:h-2 p-0',
      },
      {
        variant: ['modern', 'glass'],
        orientation: 'vertical',
        class: 'w-2.5 hover:w-3 p-0.5',
      },
      {
        variant: ['modern', 'glass'],
        orientation: 'horizontal',
        class: 'h-2.5 hover:h-3 p-0.5',
      },
      {
        size: 'sm',
        orientation: 'vertical',
        class: 'w-1.5 hover:w-2',
      },
      {
        size: 'sm',
        orientation: 'horizontal',
        class: 'h-1.5 hover:h-2',
      },
      {
        size: 'lg',
        orientation: 'vertical',
        class: 'w-3 hover:w-3.5',
      },
      {
        size: 'lg',
        orientation: 'horizontal',
        class: 'h-3 hover:h-3.5',
      },
    ],
    defaultVariants: {
      variant: 'default',
      orientation: 'vertical',
      size: 'md',
    },
  },
);

export const ScrollAreaThumbTheme = cva(
  'flex-1 rounded-full transition-all duration-100 ease-out',
  {
    variants: {
      variant: {
        default: [
          'bg-gray-400/60 hover:bg-gray-500/80 active:bg-gray-600/90',
          'dark:bg-gray-500/60 dark:hover:bg-gray-400/80 dark:active:bg-gray-300/90',
        ],
        minimal: [
          'bg-gray-400/40 hover:bg-gray-500/60 active:bg-gray-600/80',
          'dark:bg-gray-500/40 dark:hover:bg-gray-400/60 dark:active:bg-gray-300/80',
        ],
        modern: [
          'bg-gray-600/80 hover:bg-gray-700/90 active:bg-gray-800',
          'dark:bg-gray-300/80 dark:hover:bg-gray-200/90 dark:active:bg-gray-100',
          'shadow-sm',
        ],
        glass: [
          'bg-gray-800/60 hover:bg-gray-900/80 active:bg-gray-900',
          'dark:bg-gray-200/60 dark:hover:bg-gray-100/80 dark:active:bg-gray-50',
          'backdrop-blur-sm',
        ],
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  },
);

export const ScrollAreaCornerTheme = cva('', {
  variants: {
    variant: {
      default: 'bg-transparent',
      minimal: 'bg-transparent',
      modern: 'bg-gray-100/60 dark:bg-gray-800/60 rounded-br-full',
      glass: 'bg-white/20 dark:bg-black/20 backdrop-blur-sm rounded-br-full',
    },
  },
  defaultVariants: {
    variant: 'default',
  },
});
