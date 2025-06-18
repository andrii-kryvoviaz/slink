import { cva } from 'class-variance-authority';

export const TabMenuTheme = cva(
  'relative flex gap-1 p-1 rounded-xl bg-gray-100/80 dark:bg-gray-800/50 backdrop-blur-sm border border-gray-200/50 dark:border-gray-700/50',
  {
    variants: {
      variant: {
        default: '',
        minimal: 'bg-transparent border-0 gap-0 p-0',
        pills: 'gap-2 bg-transparent border-0 p-0',
        underline: 'bg-transparent border-0 border-b border-gray-200 dark:border-gray-700 rounded-none p-0 gap-0',
      },
      size: {
        xs: 'text-xs min-h-8',
        sm: 'text-sm min-h-9',
        md: 'text-base min-h-10',
        lg: 'text-lg min-h-12',
      },
      orientation: {
        horizontal: 'flex-row',
        vertical: 'flex-col',
      },
      rounded: {
        none: 'rounded-none',
        sm: 'rounded-lg',
        md: 'rounded-xl',
        lg: 'rounded-2xl',
        full: 'rounded-full',
      },
    },
    defaultVariants: {
      variant: 'default',
      size: 'sm',
      orientation: 'horizontal',
      rounded: 'md',
    },
  }
);

export const TabMenuItemTheme = cva(
  'relative z-10 flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition-all duration-200 ease-out cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500/20 focus-visible:ring-offset-1 select-none',
  {
    variants: {
      variant: {
        default: '',
        minimal: 'rounded-none px-3',
        pills: 'rounded-full bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700',
        underline: 'rounded-none border-b-2 border-transparent px-3 pb-3',
      },
      active: {
        true: 'text-gray-900 dark:text-white font-semibold',
        false: 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-gray-50/50 dark:hover:bg-gray-700/30',
      },
    },
    compoundVariants: [
      {
        variant: 'underline',
        active: true,
        class: 'border-indigo-500 dark:border-indigo-400',
      },
      {
        variant: 'pills',
        active: true,
        class: 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300',
      },
    ],
    defaultVariants: {
      variant: 'default',
      active: false,
    },
  }
);
