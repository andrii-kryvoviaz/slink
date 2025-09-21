import { cva } from 'class-variance-authority';

export const NoticeTheme = cva(`border-l-4`, {
  variants: {
    variant: {
      info: 'bg-violet-50 border-violet-400 text-violet-700 dark:bg-violet-900/20 dark:border-violet-500 dark:text-violet-300',
      success:
        'bg-green-50 border-green-400 text-green-700 dark:bg-green-900/20 dark:border-green-500 dark:text-green-300',
      warning:
        'bg-yellow-50 border-yellow-400 text-yellow-700 dark:bg-yellow-900/20 dark:border-yellow-500 dark:text-yellow-300',
      error:
        'bg-red-50 border-red-400 text-red-700 dark:bg-red-900/20 dark:border-red-500 dark:text-red-300',
    },
    size: {
      xs: 'text-xs p-2',
      sm: 'text-sm p-4',
      md: 'text-base p-6',
      lg: 'text-lg p-8',
    },
  },
});
