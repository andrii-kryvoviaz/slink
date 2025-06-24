import { cva } from 'class-variance-authority';

export const InputTheme = cva(
  `mt-2 block w-full border bg-input text-input focus:outline-hidden focus:ring-3`,
  {
    variants: {
      variant: {
        default:
          'border-bc-input hover:bg-input-hover focus:border-bc-input-focus focus:ring-rc-input-focus/40',
        error:
          'border-purple-300/60 dark:border-purple-600/40 focus:border-purple-400/70 dark:focus:border-purple-500/50 focus:ring-purple-500/20 dark:focus:ring-purple-400/20 bg-purple-25/30 dark:bg-purple-950/20 hover:bg-purple-50/40 dark:hover:bg-purple-950/30 transition-all duration-200',
        modern:
          'bg-gray-50/80 dark:bg-gray-800/50 focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-blue-500/20 hover:bg-gray-100/50 dark:hover:bg-gray-800/70 transition-all duration-200 border-gray-200/50 dark:border-gray-700/30 focus:border-gray-200/50 dark:focus:border-gray-700/30',
      },
      size: {
        sm: 'text-sm py-1 px-2',
        md: 'text-md py-2 px-4',
        lg: 'text-lg py-3 px-6',
      },
      rounded: {
        sm: 'rounded-xs',
        md: 'rounded-md',
        lg: 'rounded-lg',
      },
    },
  },
);
