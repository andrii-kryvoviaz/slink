import { cva } from 'class-variance-authority';

export const dropdownMenuItemTheme = cva(
  'outline-hidden relative flex cursor-default select-none items-center gap-2 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150 data-[disabled]:pointer-events-none data-[disabled]:opacity-50 [&_svg:not([class*="size-"])]:size-4 [&_svg]:pointer-events-none [&_svg]:shrink-0',
  {
    variants: {
      variant: {
        default:
          'text-gray-700 dark:text-gray-200 data-highlighted:bg-blue-100 dark:data-highlighted:bg-blue-800/40 data-highlighted:text-blue-600 dark:data-highlighted:text-blue-300 hover:bg-blue-100 dark:hover:bg-blue-800/40 hover:text-blue-600 dark:hover:text-blue-300',
        destructive:
          'text-gray-700 dark:text-gray-200 data-highlighted:bg-red-100 dark:data-highlighted:bg-red-900/60 data-highlighted:text-red-600 dark:data-highlighted:text-red-300 hover:bg-red-100 dark:hover:bg-red-900/60 hover:text-red-600 dark:hover:text-red-300 data-[variant=destructive]:*:[svg]:!text-red-600 dark:data-[variant=destructive]:*:[svg]:!text-red-300',
      },
      inset: {
        true: 'data-[inset]:pl-8',
        false: '',
      },
    },
    defaultVariants: {
      variant: 'default',
      inset: false,
    },
  },
);

export const dropdownMenuItemIconTheme = cva('transition-colors duration-150', {
  variants: {
    variant: {
      default: 'text-gray-500 dark:text-gray-400',
      destructive:
        'text-gray-500 dark:text-gray-400 group-hover:text-red-600 dark:group-hover:text-red-300 group-data-[highlighted]:text-red-600 dark:group-data-[highlighted]:text-red-300',
    },
  },
  defaultVariants: {
    variant: 'default',
  },
});
