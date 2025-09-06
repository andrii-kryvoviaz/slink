import { cva } from 'class-variance-authority';

export const dropdownSimpleItemTheme = cva(
  'flex w-full cursor-pointer items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm font-medium transition-all duration-150 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:bg-transparent',
  {
    variants: {
      variant: {
        default:
          'text-gray-700 dark:text-gray-200 hover:bg-blue-100 dark:hover:bg-blue-800/40 hover:text-blue-600 dark:hover:text-blue-300',
        danger:
          'text-gray-700 dark:text-gray-200 hover:bg-red-100 dark:hover:bg-red-900/60 hover:text-red-600 dark:hover:text-red-300',
      },
      state: {
        normal: '',
        loading: 'opacity-70 pointer-events-none',
      },
    },
    defaultVariants: {
      variant: 'default',
      state: 'normal',
    },
  },
);

export const dropdownSimpleItemIconTheme = cva(
  'flex-shrink-0 w-4 h-4 flex items-center justify-center',
);

export const dropdownSimpleItemTextTheme = cva('flex-1 min-w-0 truncate');
