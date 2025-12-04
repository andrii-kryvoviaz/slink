import { cva } from 'class-variance-authority';

export const dropdownSimpleContentTheme = cva(
  'z-50 p-1 flex flex-col w-fit min-w-48 origin-top rounded-xl shadow-xl backdrop-blur-sm',
  {
    variants: {
      variant: {
        default:
          'bg-white dark:bg-gray-900 shadow-black/10 dark:shadow-black/25 border border-gray-200/80 dark:border-gray-700/80',
        dark: 'bg-neutral-900 shadow-black/30 border border-white/10',
      },
    },
    defaultVariants: {
      variant: 'default',
    },
  },
);

export const dropdownSimpleItemTheme = cva(
  'flex w-full cursor-pointer items-center gap-3 rounded-lg px-3 py-2.5 text-left text-sm font-medium transition-all duration-150 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:bg-transparent',
  {
    variants: {
      variant: {
        default: 'text-gray-700 dark:text-gray-200',
        dark: 'text-white/80',
      },
      danger: {
        true: '',
        false: '',
      },
      state: {
        normal: '',
        loading: 'opacity-70 pointer-events-none',
      },
    },
    compoundVariants: [
      {
        variant: 'default',
        danger: false,
        class:
          'hover:bg-blue-100 dark:hover:bg-blue-800/40 hover:text-blue-600 dark:hover:text-blue-300',
      },
      {
        variant: 'default',
        danger: true,
        class:
          'hover:bg-red-100 dark:hover:bg-red-900/60 hover:text-red-600 dark:hover:text-red-300',
      },
      {
        variant: 'dark',
        danger: false,
        class: 'hover:bg-white/10 hover:text-white',
      },
      {
        variant: 'dark',
        danger: true,
        class: 'hover:bg-red-500/20 hover:text-red-400',
      },
    ],
    defaultVariants: {
      variant: 'default',
      danger: false,
      state: 'normal',
    },
  },
);

export const dropdownSimpleItemIconTheme = cva(
  'shrink-0 w-4 h-4 flex items-center justify-center',
);

export const dropdownSimpleItemTextTheme = cva('flex-1 min-w-0 truncate');

export type DropdownSimpleContentVariant = NonNullable<
  Parameters<typeof dropdownSimpleContentTheme>[0]
>['variant'];

export type DropdownSimpleItemVariant = NonNullable<
  Parameters<typeof dropdownSimpleItemTheme>[0]
>['variant'];
