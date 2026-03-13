import { cva } from 'class-variance-authority';

export const providerSelectTileTheme = cva(
  'group relative flex flex-col items-center gap-3 rounded-2xl border bg-white dark:bg-gray-900/50 p-6 transition-all duration-200 cursor-pointer',
  {
    variants: {
      intent: {
        provider:
          'border-gray-200/60 dark:border-gray-700/40 hover:border-gray-300 dark:hover:border-gray-700/60 hover:shadow-lg hover:shadow-gray-200/50 dark:hover:shadow-gray-900/50 hover:-translate-y-0.5',
        custom:
          'justify-center border-dashed border-gray-300/60 dark:border-gray-700/40 hover:border-gray-400 dark:hover:border-gray-700/60 hover:shadow-lg hover:shadow-gray-200/50 dark:hover:shadow-gray-900/50 hover:-translate-y-0.5',
      },
    },
    defaultVariants: {
      intent: 'provider',
    },
  },
);

export const providerSelectIconTheme = cva(
  'flex items-center justify-center w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-800 transition-transform duration-300 group-hover:scale-110',
);
